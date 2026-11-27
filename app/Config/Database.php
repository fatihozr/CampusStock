<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): ?PDO
    {
        if (self::$instance === null) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ];

            $sqliteFile = dirname(__DIR__, 2) . "/database.sqlite";
            $dbConnection = \App\Config\App::get('DB_CONNECTION', 'mysql');

            if ($dbConnection === 'sqlite') {
                try {
                    $dsn = "sqlite:" . $sqliteFile;
                    self::$instance = new PDO($dsn, null, null, $options);
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                    return self::$instance;
                } catch (PDOException $e) {
                    error_log("SQLite Bağlantı Hatası: " . $e->getMessage());
                    return null;
                }
            }

            // MySQL Connection Attempt
            try {
                $host = \App\Config\App::get('DB_HOST', 'localhost');
                $port = \App\Config\App::get('DB_PORT', '3306');
                $db   = \App\Config\App::get('DB_DATABASE', 'kirtasiye_stok_satis');
                $user = \App\Config\App::get('DB_USERNAME', 'root');
                $pass = \App\Config\App::get('DB_PASSWORD', '');

                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
                $mysqlOptions = $options;
                $mysqlOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';
                
                self::$instance = new PDO($dsn, $user, $pass, $mysqlOptions);
                self::$instance->exec("SET SESSION time_zone = '+03:00'");
            } catch (PDOException $e) {
                error_log("MySQL Bağlantı Hatası (" . $e->getMessage() . "). SQLite yedeği kontrol ediliyor...");
                
                // Fallback to SQLite if local MySQL is not running
                if (file_exists($sqliteFile)) {
                    try {
                        self::$instance = new PDO("sqlite:" . $sqliteFile, null, null, $options);
                        self::$instance->exec("PRAGMA foreign_keys = ON;");
                        return self::$instance;
                    } catch (PDOException $sqle) {
                        error_log("SQLite Fallback Hatası: " . $sqle->getMessage());
                    }
                }
                return null;
            }
        }

        return self::$instance;
    }
}
