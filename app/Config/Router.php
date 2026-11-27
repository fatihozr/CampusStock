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

    public static function put(string $path, array|callable $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    public static function delete(string $path, array|callable $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    public static function options(string $path, array|callable $handler): void
    {
        self::addRoute('OPTIONS', $path, $handler);
    }

    public static function any(string $path, array|callable $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'] as $method) {
            self::addRoute($method, $path, $handler);
        }
    }

    private static function addRoute(string $method, string $path, array|callable $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        
        // Convert route pattern {param} to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $normalizedPath);
        $regex = '#^' . $pattern . '$#u';

        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => $normalizedPath,
            'regex'   => $regex,
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

        // Support PUT/DELETE through POST method override if submitted via HTML form
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        // Automatic CORS Support for API routes
        if (str_starts_with($uri, '/api/')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');

            if ($requestMethod === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Check exact match first, then regex match for parameterized routes
            $isMatch = false;
            $params = [];

            if ($route['path'] === $uri) {
                $isMatch = true;
            } elseif (preg_match($route['regex'], $uri, $matches)) {
                $isMatch = true;
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                        $_GET[$key] = $value; // Ensure query access
                    }
                }
            }

            if ($isMatch) {
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, array_values($params));
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $method)) {
                            call_user_func_array([$controller, $method], array_values($params));
                            return;
                        }
                    }
                }
            }
        }

        // Handle 404 Not Found
        http_response_code(404);
        if (str_starts_with($uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'API endpoint bulunamadı.',
                'method'  => $requestMethod,
                'path'    => $uri
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo "<h1 style='font-family:sans-serif; text-align:center; margin-top:50px;'>404 - Sayfa Bulunamadı</h1>";
        }
    }
}
