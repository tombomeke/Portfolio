<?php
// app/Support/Uploads.php

/**
 * Shared helper for safe file deletion of user-uploaded images.
 * Validates that the resolved path stays inside the expected uploads subfolder
 * before calling unlink(), blocking path-traversal delete attacks.
 */
class Uploads {
    /** Allowed subfolder names under public/images/uploads/. */
    private static array $VALID_SUBFOLDERS = ['news', 'projects', 'avatars'];

    /**
     * Delete a file at $relativePath only if it resolves inside the expected subfolder.
     *
     * @param string $relativePath    Web-relative path as stored in the DB
     *                                (e.g. "public/images/uploads/news/foo.jpg").
     * @param string $expectedSubfolder  One of: news, projects, avatars.
     * @return bool  True if deleted, false if absent or outside allowed root.
     */
    public static function safeDelete(string $relativePath, string $expectedSubfolder): bool {
        if (!in_array($expectedSubfolder, self::$VALID_SUBFOLDERS, true)) {
            return false;
        }

        // Project root is two directories up from app/Support/
        $projectRoot = dirname(__DIR__, 2);

        $expectedRoot = realpath(
            $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
            . 'images' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $expectedSubfolder
        );
        if ($expectedRoot === false) {
            return false; // Expected upload directory does not exist on disk
        }

        // Build the full filesystem path from the DB-stored relative path
        $normalized = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        $fullPath    = $projectRoot . DIRECTORY_SEPARATOR . $normalized;
        $realPath    = realpath($fullPath);

        if ($realPath === false) {
            return false; // File not found
        }

        // Confirm the resolved path begins with the expected root directory
        if (!str_starts_with($realPath, $expectedRoot . DIRECTORY_SEPARATOR)) {
            return false; // Path-traversal attempt — refuse to delete
        }

        return unlink($realPath);
    }
}
