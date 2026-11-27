<?php

if (!function_exists('view')) {
    /**
     * Render a view file with optional data array
     */
    function view(string $viewPath, array $data = []): void
    {
        extract($data);
        $baseDir = dirname(__DIR__, 2);
        
        // Convert dot notation to slash (e.g. 'auth.login' -> 'auth/login')
        $viewFile = str_replace('.', '/', $viewPath);
        $fullPath = $baseDir . '/app/Views/' . $viewFile . '.php';

        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            echo "Görünüm dosyası bulunamadı: " . htmlspecialchars($fullPath);
        }
    }
}

if (!function_exists('base_url')) {
    /**
     * Return base URL of the application
     */
    function base_url(string $path = ''): string
    {
        $envUrl = \App\Config\App::get('APP_URL');
        if (!empty($envUrl)) {
            $baseUrl = rtrim($envUrl, '/\\');
        } else {
            $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $baseUrl = rtrim($scriptName, '/\\');
        }
        
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Return asset URL
     */
    function asset(string $path): string
    {
        return base_url('public/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to relative path or absolute URL
     */
    function redirect(string $path): void
    {
        if (!headers_sent()) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                header('Location: ' . $path);
            } else {
                header('Location: ' . base_url($path));
            }
        }
        exit;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML output for security
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('turkish_strcmp')) {
    /**
     * Compare two Turkish strings using proper Turkish alphabet collation
     * Alphabet: a, b, c, ç, d, e, f, g, ğ, h, ı, i, j, k, l, m, n, o, ö, p, r, s, ş, t, u, ü, v, y, z
     */
    function turkish_strcmp(?string $a, ?string $b): int
    {
        $a = $a ?? '';
        $b = $b ?? '';

        static $orderMap = null;
        if ($orderMap === null) {
            $alphabet = [
                'a','A','b','B','c','C','ç','Ç','d','D','e','E','f','F','g','G','ğ','Ğ',
                'h','H','ı','I','i','İ','j','J','k','K','l','L','m','M','n','N','o','O',
                'ö','Ö','p','P','r','R','s','S','ş','Ş','t','T','u','U','ü','Ü','v','V',
                'y','Y','z','Z','0','1','2','3','4','5','6','7','8','9'
            ];
            $orderMap = [];
            foreach ($alphabet as $idx => $char) {
                $orderMap[$char] = $idx;
            }
        }

        $lowerA = mb_strtolower($a, 'UTF-8');
        $lowerB = mb_strtolower($b, 'UTF-8');

        if ($lowerA === $lowerB) {
            return 0;
        }

        $charsA = mb_str_split($lowerA, 1, 'UTF-8');
        $charsB = mb_str_split($lowerB, 1, 'UTF-8');

        $lenA = count($charsA);
        $lenB = count($charsB);
        $minLen = min($lenA, $lenB);

        for ($i = 0; $i < $minLen; $i++) {
            $cA = $charsA[$i];
            $cB = $charsB[$i];

            if ($cA === $cB) {
                continue;
            }

            $posA = $orderMap[$cA] ?? 999;
            $posB = $orderMap[$cB] ?? 999;

            if ($posA !== $posB) {
                return $posA <=> $posB;
            }
        }

        return $lenA <=> $lenB;
    }
}

if (!function_exists('get_stock_brand')) {
    /**
     * Extract brand name from stock item title
     */
    function get_stock_brand(string $name): string
    {
        $nameLower = mb_strtolower($name, 'UTF-8');
        if (str_contains($nameLower, 'hp')) return 'HP';
        if (str_contains($nameLower, 'lexmark')) return 'Lexmark';
        if (str_contains($nameLower, 'canon')) return 'Canon';
        if (str_contains($nameLower, 'epson')) return 'Epson';
        if (str_contains($nameLower, 'logitech')) return 'Logitech';
        if (str_contains($nameLower, 'a4tech')) return 'A4Tech';
        if (str_contains($nameLower, 'reçber')) return 'Reçber';
        if (str_contains($nameLower, 'zebra')) return 'Zebra';
        if (str_contains($nameLower, 'cisco')) return 'Cisco';
        if (str_contains($nameLower, 'apc')) return 'APC';
        if (str_contains($nameLower, 'dell')) return 'Dell';
        if (str_contains($nameLower, 'lenovo')) return 'Lenovo';
        
        $parts = explode(' ', trim($name));
        return $parts[0] ?? '';
    }
}

if (!function_exists('mask_tc')) {
    /**
     * Mask TC Identification Number for KVKK compliance
     * Example: 12345678901 -> 123******01
     */
    function mask_tc(?string $tc): string
    {
        $tc = trim($tc ?? '');
        if (strlen($tc) !== 11) {
            return !empty($tc) ? substr($tc, 0, 3) . '*****' . substr($tc, -2) : '---';
        }
        return substr($tc, 0, 3) . '******' . substr($tc, -2);
    }
}

if (!function_exists('log_audit')) {
    /**
     * Record system action to global audit logs
     */
    function log_audit(string $action, string $module, string $details): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['mock_audit_logs'])) {
            $_SESSION['mock_audit_logs'] = [];
        }
        $user = \App\Helpers\Auth::user();
        $username = $user['name'] ?? $user['username'] ?? 'Sistem Yöneticisi';

        $newId = count($_SESSION['mock_audit_logs']) > 0
            ? max(array_column($_SESSION['mock_audit_logs'], 'id')) + 1
            : 1;

        array_unshift($_SESSION['mock_audit_logs'], [
            'id'           => $newId,
            'inventory_id' => 0,
            'action'       => $action,
            'module'       => $module,
            'performed_by' => $username,
            'details'      => $details,
            'timestamp'    => date('Y-m-d H:i:s'),
            'date'         => date('d.m.Y H:i')
        ]);
    }
}


