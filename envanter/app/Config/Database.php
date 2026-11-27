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
            $host = App::get('DB_HOST', '127.0.0.1');
            $port = App::get('DB_PORT', '3306');
            $db   = App::get('DB_NAME', 'belediye_envanter');
            $user = App::get('DB_USER', 'root');
            $pass = App::get('DB_PASS', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // PDO Exception handled safely without breaking app initialization if DB is not created yet
                error_log("Veritabanı Bağlantı Hatası: " . $e->getMessage());
                return null;
            }
        }

        return self::$instance;
    }
}
