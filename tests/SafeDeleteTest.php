<?php

require_once __DIR__ . '/../app/Support/Uploads.php';

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

// ── Set up a temp uploads/news directory so realpath() can resolve it ────────

$uploadsBase = __DIR__ . '/../public/images/uploads';
$newsDir     = $uploadsBase . '/news';
$createdDirs = [];

foreach ([$uploadsBase, $newsDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        $createdDirs[] = $dir;
    }
}

// 1. Happy path: a file inside uploads/news/ is deleted successfully.
$goodFile = $newsDir . '/safe_delete_test_' . bin2hex(random_bytes(4)) . '.txt';
file_put_contents($goodFile, 'test content');
assertTrue(file_exists($goodFile), 'Test file should exist before deletion');

$relativePath = 'public/images/uploads/news/' . basename($goodFile);
$result = Uploads::safeDelete($relativePath, 'news');
assertTrue($result === true, 'safeDelete should return true for a valid file inside uploads/news/');
assertTrue(!file_exists($goodFile), 'File should be gone after safeDelete');

// 2. Path-traversal attempt: a path containing "../.." must be refused.
// Create a real file outside uploads to confirm we don't delete it.
$outsideFile = sys_get_temp_dir() . '/safe_delete_traversal_' . bin2hex(random_bytes(4)) . '.txt';
file_put_contents($outsideFile, 'should not be deleted');

// Craft a relative path that would resolve outside uploads/news/ via traversal.
$traversalPath = 'public/images/uploads/news/../../../../../../' . ltrim($outsideFile, '/');
$result = Uploads::safeDelete($traversalPath, 'news');
assertTrue($result === false, 'safeDelete must refuse a path-traversal attempt');
assertTrue(file_exists($outsideFile), 'Target file outside uploads/ must NOT be deleted by safeDelete');
@unlink($outsideFile);

// 3. Non-existent file returns false (no error).
$result = Uploads::safeDelete('public/images/uploads/news/does_not_exist.jpg', 'news');
assertTrue($result === false, 'safeDelete should return false for a non-existent file');

// 4. Invalid subfolder name is rejected.
$result = Uploads::safeDelete('public/images/uploads/evil/foo.jpg', 'evil');
assertTrue($result === false, 'safeDelete must reject unknown subfolder names');

// Clean up any directories we created for the test.
foreach (array_reverse($createdDirs) as $dir) {
    @rmdir($dir);
}

echo "SafeDeleteTest passed\n";
