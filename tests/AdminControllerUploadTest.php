<?php

require_once __DIR__ . '/../app/Controllers/AdminController.php';

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

$controller = (new ReflectionClass(AdminController::class))->newInstanceWithoutConstructor();
$method = new ReflectionMethod(AdminController::class, 'detectImageMimeType');
$method->setAccessible(true);

$pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/6XkAAAAASUVORK5CYII=');
$pngFile = tempnam(sys_get_temp_dir(), 'upload-mime-');
file_put_contents($pngFile, $pngBytes);

$textFile = tempnam(sys_get_temp_dir(), 'upload-mime-text-');
file_put_contents($textFile, 'plain text');

try {
    $pngMime = $method->invoke($controller, $pngFile);
    assertTrue($pngMime === 'image/png', 'PNG file should be detected as image/png');

    $textMime = $method->invoke($controller, $textFile);
    assertTrue(is_string($textMime) && $textMime !== 'image/png', 'Text file should not be detected as an image mime type');
} finally {
    @unlink($pngFile);
    @unlink($textFile);
}

echo "AdminControllerUploadTest passed\n";
