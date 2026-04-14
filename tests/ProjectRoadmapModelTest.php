<?php
// tests/ProjectRoadmapModelTest.php — unit tests for ProjectRoadmapModel::upsertFromSync()
// Uses SQLite in-memory + reflection to inject the connection without touching MySQL.

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

// ── Bootstrap: load real Database class and inject SQLite via reflection ──────

// Load the real Database class (it will define DB_HOST etc. from env, but won't connect).
require_once __DIR__ . '/../app/Config/Database.php';

// Create SQLite in-memory connection with the same options the real class would use.
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Inject it into Database::$connection via reflection so getConnection() returns it.
$ref  = new ReflectionProperty(Database::class, 'connection');
$ref->setAccessible(true);
$ref->setValue(null, $pdo);

// ── Create in-memory schema ───────────────────────────────────────────────────

$pdo->exec("CREATE TABLE project_roadmap_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    file TEXT NOT NULL DEFAULT '',
    line INTEGER NOT NULL DEFAULT 0,
    text TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'open',
    priority TEXT NOT NULL DEFAULT 'normal',
    last_seen_at TEXT,
    api_contract_version TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now'))
)");

// Now load the model — Database is already defined and injected.
require_once __DIR__ . '/../app/Models/ProjectRoadmapModel.php';

$model = new ProjectRoadmapModel();

// ── Test 1: Fresh sync inserts items correctly ────────────────────────────────

$items1 = [
    ['file' => 'src/Foo.php', 'line' => 10, 'text' => 'Refactor this', 'status' => 'open', 'priority' => 'high'],
    ['file' => 'src/Bar.php', 'line' => 20, 'text' => 'Fix that',      'status' => 'open', 'priority' => 'normal'],
];
$count = $model->upsertFromSync(1, $items1, 'v1');
assertTrue($count === 2, 'Test 1: should insert 2 items');

$rows = $model->getByProjectId(1);
assertTrue(count($rows) === 2, 'Test 1: should have 2 rows in DB');

// ── Test 2: Re-sync with same items preserves 'open' status ──────────────────

$count2 = $model->upsertFromSync(1, $items1, 'v2');
assertTrue($count2 === 2, 'Test 2: should still have 2 items');
$rows2 = $model->getByProjectId(1);
assertTrue($rows2[0]['status'] === 'open', 'Test 2: status should stay open');

// ── Test 3: Manually set first item to done, then re-sync — must stay done ───

$firstId = (int) $pdo->query("SELECT id FROM project_roadmap_items WHERE project_id=1 AND file='src/Foo.php' LIMIT 1")->fetchColumn();
assertTrue($firstId > 0, 'Test 3: should find the item to set done');
$model->setStatus($firstId, 'done');

// Re-sync with same items (both marked 'open' by API)
$diff = [];
$count3 = $model->upsertFromSync(1, $items1, 'v3', $diff);
assertTrue($count3 === 2, 'Test 3: should still have 2 items');

$rows3 = $pdo->query("SELECT * FROM project_roadmap_items WHERE project_id=1")->fetchAll(PDO::FETCH_ASSOC);
$fooRow = null;
foreach ($rows3 as $r) {
    if ($r['file'] === 'src/Foo.php') { $fooRow = $r; break; }
}
assertTrue($fooRow !== null, 'Test 3: should still find src/Foo.php row');
assertTrue($fooRow['status'] === 'done', 'Test 3: manually-set done must be preserved after re-sync');

// ── Test 4: Diff counters after re-sync ───────────────────────────────────────

assertTrue(isset($diff['new'], $diff['kept'], $diff['removed']), 'Test 4: diff counters must be returned');
assertTrue($diff['new']     === 0, 'Test 4: new should be 0 (same items)');
assertTrue($diff['kept']    === 2, 'Test 4: kept should be 2');
assertTrue($diff['removed'] === 0, 'Test 4: removed should be 0');

// ── Test 5: An item removed from incoming set is deleted ─────────────────────

$itemsReduced = [
    ['file' => 'src/Foo.php', 'line' => 10, 'text' => 'Refactor this', 'status' => 'open', 'priority' => 'high'],
    // src/Bar.php removed from incoming set
];
$diff2 = [];
$count5 = $model->upsertFromSync(1, $itemsReduced, 'v4', $diff2);
assertTrue($count5 === 1, 'Test 5: should have 1 item after reducing set');
assertTrue($diff2['removed'] === 1, 'Test 5: removed counter should be 1');

$rows5 = $pdo->query("SELECT * FROM project_roadmap_items WHERE project_id=1")->fetchAll(PDO::FETCH_ASSOC);
assertTrue(count($rows5) === 1, 'Test 5: DB should have exactly 1 row');
assertTrue($rows5[0]['file'] === 'src/Foo.php', 'Test 5: remaining item should be src/Foo.php');

// ── Test 6: setStatus() updates a row correctly ───────────────────────────────

$existingId = (int) $pdo->query("SELECT id FROM project_roadmap_items WHERE project_id=1 LIMIT 1")->fetchColumn();
$result = $model->setStatus($existingId, 'done');
assertTrue($result === true, 'Test 6: setStatus should return true on success');
$updated = $pdo->query("SELECT status FROM project_roadmap_items WHERE id=$existingId")->fetchColumn();
assertTrue($updated === 'done', 'Test 6: status should be done after setStatus');

$model->setStatus($existingId, 'open');
$reset = $pdo->query("SELECT status FROM project_roadmap_items WHERE id=$existingId")->fetchColumn();
assertTrue($reset === 'open', 'Test 6: status should be open after resetting');

// ── Test 7: setStatus() rejects invalid status values ────────────────────────

$model->setStatus($existingId, 'hacked');
$unchanged = $pdo->query("SELECT status FROM project_roadmap_items WHERE id=$existingId")->fetchColumn();
assertTrue($unchanged === 'open', 'Test 7: invalid status value must be normalised to open');

echo "ProjectRoadmapModelTest passed\n";
