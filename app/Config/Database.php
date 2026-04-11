<?php
require_once __DIR__ . '/db.php';

class Database {
    private static $connection = null;

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
