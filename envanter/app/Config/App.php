<?php

namespace App\Config;

class App
{
    private static array $config = [];

    public static function init(): void
    {
        // Set default timezone
        date_default_timezone_set('Europe/Istanbul');

        // Configure error display for production safety (hide raw file paths & stack traces)
        ini_set('display_errors', '1');
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        set_exception_handler(function (\Throwable $e) {
            error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            if (!headers_sent()) {
                http_response_code(500);
            }
            echo '<div style="font-family: Inter, sans-serif; padding: 30px; background: #FFF; border-radius: 12px; border: 2px solid red;">';
            echo '<h3 style="color: #0F172A; margin-bottom: 10px;">İşlem Tamamlanamadı</h3>';
            echo '<pre>' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString() . '</pre>';
            echo '<a href="' . (function_exists('base_url') ? base_url('dashboard') : '/') . '" style="display: inline-block; padding: 10px 20px; background: #2563EB; color: #FFF; text-decoration: none; border-radius: 6px; font-weight: 600;">Ana Sayfaya Dön</a>';
            echo '</div>';
            exit;
        });

        // Load env variables if available
        self::loadEnv();
    }

    private static function loadEnv(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envFile)) {
            $envFile = dirname(__DIR__, 2) . '/.env.example';
        }

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }

                if (str_contains($line, '=')) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    self::$config[$name] = $value;
                    $_ENV[$name] = $value;
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $_ENV[$key] ?? $default;
    }
}
