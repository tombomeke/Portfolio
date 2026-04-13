<?php
require_once __DIR__ . '/db.php';

/**
 * PDO singleton — one connection per request, shared by all models.
 *
 * Configuration comes from constants defined in db.php:
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
 * Those constants are populated from environment variables
 * (PORTFOLIO_DB_HOST / _NAME / _USER / _PASS).
 *
 * PDO is configured with:
 *   - ERRMODE_EXCEPTION — all errors throw PDOException
 *   - FETCH_ASSOC       — rows returned as associative arrays
 *   - EMULATE_PREPARES false — use real prepared statements
 */
class Database {
    private static $connection = null;

    /**
     * Return the shared PDO connection, creating it on first call.
     * Throws RuntimeException if credentials are missing.
     */
    public static function getConnection() {
        if (self::$connection === null) {
            if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || DB_PASS === '') {
                throw new RuntimeException('Database credentials are not configured. Set PORTFOLIO_DB_HOST, PORTFOLIO_DB_NAME, PORTFOLIO_DB_USER and PORTFOLIO_DB_PASS.');
            }

            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$connection;
    }
}
