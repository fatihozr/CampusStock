<?php

namespace App\Helpers;

use App\Models\Role;
use App\Models\User;

class Auth
{
    private const REMEMBER_COOKIE_NAME = 'campusstock_remember_token';

    /**
     * Start session if not started
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Check if user is logged in with valid session
     */
    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Get logged-in user data
     */
    public static function user(): ?array
    {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Log in a user and set authenticated session
     */
    public static function login(array $user, bool $remember = false): void
    {
        self::init();
        
        // Regenerate session ID for security against session fixation if headers not sent
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $roleModel = new Role();
        $role = $roleModel->findBySlug($user['role_slug'] ?? 'personnel');

        $_SESSION['user'] = [
            'id'          => $user['id'],
            'username'    => $user['username'],
            'email'       => $user['email'],
            'first_name'  => $user['first_name'] ?? 'Ahmet',
            'last_name'   => $user['last_name'] ?? 'Yılmaz',
            'role_id'     => $user['role_id'] ?? 1,
            'role_slug'   => $user['role_slug'] ?? 'admin',
            'role_name'   => $user['role_name'] ?? 'Sistem Yöneticisi',
            'permissions' => $role ? ($role['permissions'] ?? ['dashboard']) : ['dashboard'],
            'title'       => $user['title'] ?? 'Bilgi İşlem Sorumlusu',
            'department'  => $user['department'] ?? 'Bilgi İşlem Müdürlüğü'
        ];

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie(self::REMEMBER_COOKIE_NAME, $token, [
                'expires'  => time() + (30 * 24 * 60 * 60), // 30 days
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    /**
     * Log out current user and destroy all session data & cookies
     */
    public static function logout(): void
    {
        self::init();
        unset($_SESSION['user']);

        if (isset($_COOKIE[self::REMEMBER_COOKIE_NAME])) {
            setcookie(self::REMEMBER_COOKIE_NAME, '', [
                'expires' => time() - 3600,
                'path'    => '/'
            ]);
        }
    }

    /**
     * Middleware: Require logged-in user, redirect to login if guest
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'GÜVENLİK UYARISI: Lütfen önce sisteme kullanıcı adınız ve şifreniz ile giriş yapınız.';
            redirect('/login');
            exit;
        }
    }

    /**
     * Alias for requireAuth
     */
    public static function requireLogin(): void
    {
        self::requireAuth();
    }

    /**
     * Middleware: Require guest, redirect to dashboard if already logged in
     */
    public static function requireGuest(): void
    {
        if (self::check()) {
            redirect('/dashboard');
            exit;
        }
    }

    /**
     * Check if currently logged in user has permission for a module
     */
    public static function hasPermission(string $module): bool
    {
        if (!self::check()) return false;
        
        $user = self::user();
        if (empty($user)) return false;
        
        // Admin role (by slug 'admin' or role_id 1) has full access to all system modules
        if (strtolower($user['role_slug'] ?? '') === 'admin' || (int)($user['role_id'] ?? 0) === 1) {
            return true;
        }

        // Fetch fresh permissions from role model
        $roleModel = new Role();
        $role = $roleModel->findBySlug($user['role_slug'] ?? '');

        $permissions = $role ? $role['permissions'] : ($user['permissions'] ?? []);
        return in_array($module, $permissions, true);
    }

    /**
     * Check if currently logged in user is an Admin
     */
    public static function isAdmin(): bool
    {
        if (!self::check()) return false;
        $user = self::user();
        if (empty($user)) return false;
        return (strtolower($user['role_slug'] ?? '') === 'admin' || (int)($user['role_id'] ?? 0) === 1);
    }

    /**
     * Check if currently logged in user is an Admin, Director, IT Staff or Warehouse Manager
     */
    public static function isManager(): bool
    {
        if (!self::check()) return false;
        $user = self::user();
        if (empty($user)) return false;
        $slug = strtolower($user['role_slug'] ?? '');
        return in_array($slug, ['admin', 'kadmin', 'director', 'warehouse_manager'], true) || (int)($user['role_id'] ?? 0) === 1;
    }

    /**
     * Check if currently logged in user has specific role by slug(s)
     */
    public static function hasRole(string|array $roles): bool
    {
        if (!self::check()) return false;
        $user = self::user();
        if (empty($user)) return false;
        $userRole = strtolower($user['role_slug'] ?? '');
        if (is_array($roles)) {
            $roles = array_map('strtolower', $roles);
            return in_array($userRole, $roles, true);
        }
        return $userRole === strtolower($roles);
    }

    /**
     * Middleware: Require specific module permission or redirect to dashboard with 403 error
     */
    public static function requirePermission(string $module): void
    {
        self::requireAuth();

        if (!self::hasPermission($module)) {
            $_SESSION['flash_error'] = 'YETKİSİZ ERİŞİM! Bu sayfayı (' . htmlspecialchars($module) . ') görüntüleme yetkiniz bulunmamaktadır!';
            redirect('/dashboard');
            exit;
        }
    }
}
