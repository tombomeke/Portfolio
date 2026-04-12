<?php
require_once __DIR__ . '/../Config/Database.php';

class ReadmeSyncScanLogModel {
    public function log(array $entry): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO readmesync_scan_logs (
                    user_id,
                    username,
                    user_role,
                    source_client,
                    source_user_id,
                    source_user_name,
                    repo_url,
                    success,
                    http_code,
                    language,
                    todo_count,
                    api_contract_version,
                    response_keys,
                    error_message,
                    created_at
                ) VALUES (
                    :user_id,
                    :username,
                    :user_role,
                    :source_client,
                    :source_user_id,
                    :source_user_name,
                    :repo_url,
                    :success,
                    :http_code,
                    :language,
                    :todo_count,
                    :api_contract_version,
                    :response_keys,
                    :error_message,
                    NOW()
                )"
            );

            $stmt->execute([
                ':user_id' => $entry['user_id'] ?? null,
                ':username' => $entry['username'] ?? null,
                ':user_role' => $entry['user_role'] ?? null,
                ':source_client' => $entry['source_client'] ?? 'portfolio',
                ':source_user_id' => $entry['source_user_id'] ?? null,
                ':source_user_name' => $entry['source_user_name'] ?? null,
                ':repo_url' => $entry['repo_url'] ?? null,
                ':success' => !empty($entry['success']) ? 1 : 0,
                ':http_code' => isset($entry['http_code']) ? (int) $entry['http_code'] : null,
                ':language' => $entry['language'] ?? null,
                ':todo_count' => isset($entry['todo_count']) ? (int) $entry['todo_count'] : null,
                ':api_contract_version' => $entry['api_contract_version'] ?? null,
                ':response_keys' => $entry['response_keys'] ?? null,
                ':error_message' => $entry['error_message'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Never break UX if log table is missing or DB temporarily unavailable.
        }
    }
}
