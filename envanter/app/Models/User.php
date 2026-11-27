<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class User
{
    private ?PDO $db;
    private Role $roleModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->roleModel = new Role();
        $this->initMockSessionUsers();
    }

    /**
     * Initialize mock system users dataset
     */
    private function initMockSessionUsers(): void
    {
        if (!isset($_SESSION['mock_users'])) {
            $bcryptHash = '$2y$10$.LpBgfRi7rEsyZFz1zPIkugmg84ClCg13q8JlhH6YddJMQp.jur4W'; // admin123

            $_SESSION['mock_users'] = [
                [
                    'id'            => 1,
                    'username'      => 'admin',
                    'email'         => 'admin@belediye.bel.tr',
                    'password_hash' => $bcryptHash,
                    'role_id'       => 1,
                    'role_slug'     => 'admin',
                    'role_name'     => 'Admin (Sistem Yöneticisi)',
                    'first_name'    => 'Ahmet',
                    'last_name'     => 'Yılmaz',
                    'title'         => 'Sistem Yöneticisi',
                    'department'    => 'Bilgi İşlem Müdürlüğü',
                    'status'        => 'active'
                ],
                [
                    'id'            => 2,
                    'username'      => 'bt_ahmet',
                    'email'         => 'ahmet.yilmaz@belediye.bel.tr',
                    'password_hash' => $bcryptHash,
                    'role_id'       => 2,
                    'role_slug'     => 'it_staff',
                    'role_name'     => 'Bilgi İşlem (BT Teknik)',
                    'first_name'    => 'Ahmet',
                    'last_name'     => 'Yılmaz',
                    'title'         => 'Bilgi İşlem Uzmanı',
                    'department'    => 'Bilgi İşlem Müdürlüğü',
                    'status'        => 'active'
                ],
                [
                    'id'            => 3,
                    'username'      => 'depo_ayse',
                    'email'         => 'ayse.kaya@belediye.bel.tr',
                    'password_hash' => $bcryptHash,
                    'role_id'       => 3,
                    'role_slug'     => 'warehouse_manager',
                    'role_name'     => 'Depo Sorumlusu',
                    'first_name'    => 'Ayşe',
                    'last_name'     => 'Kaya',
                    'title'         => 'Depo Sorumlusu',
                    'department'    => 'Destek Hizmetleri',
                    'status'        => 'active'
                ],
                [
                    'id'            => 4,
                    'username'      => 'mudur_fatma',
                    'email'         => 'fatma.sahin@belediye.bel.tr',
                    'password_hash' => $bcryptHash,
                    'role_id'       => 4,
                    'role_slug'     => 'director',
                    'role_name'     => 'Müdür (Birim Yöneticisi)',
                    'first_name'    => 'Fatma',
                    'last_name'     => 'Şahin',
                    'title'         => 'Birim Müdürü',
                    'department'    => 'Yazı İşleri Müdürlüğü',
                    'status'        => 'active'
                ],
                [
                    'id'            => 5,
                    'username'      => 'personel_ali',
                    'email'         => 'ali.kaya@belediye.bel.tr',
                    'password_hash' => $bcryptHash,
                    'role_id'       => 5,
                    'role_slug'     => 'personnel',
                    'role_name'     => 'Personel (Standart Kullanıcı)',
                    'first_name'    => 'Ali',
                    'last_name'     => 'Kaya',
                    'title'         => 'İnşaat Mühendisi',
                    'department'    => 'Fen İşleri Müdürlüğü',
                    'status'        => 'active'
                ]
            ];
        }
    }

    /**
     * Find user by username or email
     */
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $identifier = trim($identifier);

        // 1. Try querying real Database if available
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.*, r.name as role_name, r.slug as role_slug, p.first_name, p.last_name, p.title
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN personnel p ON u.personnel_id = p.id
                    WHERE (u.username = :ident OR u.email = :ident) AND u.status = 'active'
                    LIMIT 1
                ");
                $stmt->execute(['ident' => $identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    return $user;
                }
            } catch (Exception $e) {
                // DB table not ready; fallback to mock
            }
        }

        // 2. Fallback Mock Users
        foreach ($_SESSION['mock_users'] as $u) {
            if (strtolower($u['username']) === strtolower($identifier) || strtolower($u['email']) === strtolower($identifier)) {
                return $u;
            }
        }

        return null;
    }

    /**
     * Verify user password using PHP native password_verify
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $_SESSION['mock_users'];
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_users'] as $u) {
            if ($u['id'] === $id) return $u;
        }
        return null;
    }

    /**
     * Store new user account
     */
    public function storeUser(array $data): array
    {
        $newId = count($_SESSION['mock_users']) > 0
            ? max(array_column($_SESSION['mock_users'], 'id')) + 1
            : 1;

        $role = $this->roleModel->find((int)($data['role_id'] ?? 5));
        $plainPassword = !empty($data['password']) ? trim($data['password']) : 'admin123';

        $newUser = [
            'id'            => $newId,
            'username'      => trim($data['username'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'role_id'       => $role ? $role['id'] : 5,
            'role_slug'     => $role ? $role['slug'] : 'personnel',
            'role_name'     => $role ? $role['name'] : 'Personel (Standart Kullanıcı)',
            'first_name'    => trim($data['first_name'] ?? 'Yeni'),
            'last_name'     => trim($data['last_name'] ?? 'Kullanıcı'),
            'title'         => trim($data['title'] ?? 'Memur'),
            'department'    => trim($data['department'] ?? 'Bilgi İşlem Müdürlüğü'),
            'status'        => $data['status'] ?? 'active'
        ];

        array_unshift($_SESSION['mock_users'], $newUser);

        return $newUser;
    }

    /**
     * Update user account
     */
    public function updateUser(int $id, array $data): bool
    {
        foreach ($_SESSION['mock_users'] as &$u) {
            if ($u['id'] === $id) {
                $role = $this->roleModel->find((int)($data['role_id'] ?? $u['role_id']));

                $u['username']   = trim($data['username'] ?? $u['username']);
                $u['email']      = trim($data['email'] ?? $u['email']);
                $u['first_name'] = trim($data['first_name'] ?? $u['first_name']);
                $u['last_name']  = trim($data['last_name'] ?? $u['last_name']);
                $u['title']      = trim($data['title'] ?? $u['title']);
                $u['department'] = trim($data['department'] ?? $u['department']);
                $u['status']     = $data['status'] ?? $u['status'];

                if ($role) {
                    $u['role_id']   = $role['id'];
                    $u['role_slug'] = $role['slug'];
                    $u['role_name'] = $role['name'];
                }

                if (!empty($data['password'])) {
                    $u['password_hash'] = password_hash(trim($data['password']), PASSWORD_DEFAULT);
                }

                return true;
            }
        }
        return false;
    }

    /**
     * Delete user account
     */
    public function deleteUser(int $id): bool
    {
        foreach ($_SESSION['mock_users'] as $index => $u) {
            if ($u['id'] === $id) {
                unset($_SESSION['mock_users'][$index]);
                $_SESSION['mock_users'] = array_values($_SESSION['mock_users']);
                return true;
            }
        }
        return false;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): void
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
                $stmt->execute(['id' => $userId]);
            } catch (Exception $e) {
                // Ignore
            }
        }
    }
}
