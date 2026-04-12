<?php
/**
 * One-time migration: project_roadmaps.json → project_roadmap_items DB table.
 * Run AFTER migrate_v3.sql has been applied.
 *
 * Usage: php database/migrate_roadmap_data.php
 */

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/ProjectRoadmapModel.php';

$jsonPath = __DIR__ . '/../app/Config/project_roadmaps.json';

if (!file_exists($jsonPath)) {
    echo "No project_roadmaps.json found — nothing to migrate.\n";
    exit(0);
}

$raw = file_get_contents($jsonPath);
$store = json_decode($raw, true);

if (!is_array($store) || empty($store['projects'])) {
    echo "JSON store is empty — nothing to migrate.\n";
    exit(0);
}

$model  = new ProjectRoadmapModel();
$total  = 0;
$errors = [];

foreach ($store['projects'] as $key => $record) {
    $projectId = (int) ($record['projectId'] ?? 0);
    if ($projectId <= 0) {
        $errors[] = "Skipping key '{$key}': no projectId.";
        continue;
    }

    $items   = (array) ($record['items'] ?? []);
    $version = (string) ($record['apiContractVersion'] ?? 'migrated');

    try {
        $count = $model->upsertFromSync($projectId, $items, $version);
        $model->logSync($projectId, $count, $version, true);
        echo "project-{$projectId}: {$count} items migrated.\n";
        $total += $count;
    } catch (\Throwable $e) {
        $errors[] = "project-{$projectId}: " . $e->getMessage();
    }
}

echo "\nDone. Total items migrated: {$total}\n";
if ($errors) {
    echo "\nErrors:\n";
    foreach ($errors as $err) echo "  - {$err}\n";
}
