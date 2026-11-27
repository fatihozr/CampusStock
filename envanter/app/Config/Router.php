<?php

namespace App\Config;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array|callable $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, array|callable $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, array|callable $handler): void
    {
        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler
        ];
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $rawPath       = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestUri    = rawurldecode($rawPath);

        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptName !== '/' && $scriptName !== '.' && str_starts_with($requestUri, $scriptName)) {
            $requestUri = substr($requestUri, strlen($scriptName));
        }

        $uri = '/' . trim($requestUri, '/');
        if ($uri === '//' || $uri === '/index.php') {
            $uri = '/';
        }

        foreach (self::$routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $uri) {
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func($handler);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $method)) {
                            $controller->$method();
                            return;
                        }
                    }
                }
            }
        }

        // Handle 404 Not Found
        http_response_code(404);
        echo "<h1 style='font-family:sans-serif; text-align:center; margin-top:50px;'>404 - Sayfa Bulunamadı</h1>";
    }
}
