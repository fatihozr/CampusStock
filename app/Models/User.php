<?php

namespace App\Models;

use App\Services\MockDataService;

class User
{
    private const TABLE = 'users';

    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $identifier = mb_strtolower(trim($identifier), 'UTF-8');
        foreach (MockDataService::all(self::TABLE) as $user) {
            if (
                mb_strtolower($user['username'] ?? '', 'UTF-8') === $identifier ||
                mb_strtolower($user['email'] ?? '', 'UTF-8') === $identifier
            ) {
                return $user;
            }
        }
        return null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function getAllUsers(): array
    {
        return MockDataService::all(self::TABLE);
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function storeUser(array $data): array
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $plain = !empty($data['password']) ? trim($data['password']) : 'admin123';

        $roleId = (int)($data['role_id'] ?? 3);
        $roleName = match($roleId) {
            1 => 'Genel Admin',
            2 => 'Kırtasiye Yöneticisi (KAdmin)',
            default => 'Üye (Öğrenci)'
        };
        $roleSlug = match($roleId) {
            1 => 'admin',
            2 => 'kadmin',
            default => 'uye'
        };

        $newUser = [
            'id'            => $newId,
            'role_id'       => $roleId,
            'role_slug'     => $roleSlug,
            'role_name'     => $roleName,
            'username'      => trim($data['username'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'password_hash' => password_hash($plain, PASSWORD_DEFAULT),
            'first_name'    => trim($data['first_name'] ?? ''),
            'last_name'     => trim($data['last_name'] ?? ''),
            'phone'         => trim($data['phone'] ?? ''),
            'title'         => trim($data['title'] ?? $roleName),
            'status'        => $data['status'] ?? 'active'
        ];

        $rows[] = $newUser;
        MockDataService::saveAll(self::TABLE, $rows);
        return $newUser;
    }

    public function updateUser(int $id, array $data): bool
    {
        $rows = MockDataService::all(self::TABLE);
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $id) {
                $row['first_name'] = trim($data['first_name'] ?? $row['first_name']);
                $row['last_name']  = trim($data['last_name'] ?? $row['last_name']);
                $row['email']      = trim($data['email'] ?? $row['email']);
                $row['username']   = trim($data['username'] ?? $row['username']);
                $row['title']      = trim($data['title'] ?? $row['title']);
                $row['status']     = $data['status'] ?? $row['status'];
                if (!empty($data['password'])) {
                    $row['password_hash'] = password_hash(trim($data['password']), PASSWORD_DEFAULT);
                }
                MockDataService::saveAll(self::TABLE, $rows);
                return true;
            }
        }
        return false;
    }

    public function deleteUser(int $id): bool
    {
        $rows = array_filter(MockDataService::all(self::TABLE), fn($u) => (int)$u['id'] !== $id);
        MockDataService::saveAll(self::TABLE, $rows);
        return true;
    }

    public function updateLastLogin(int $userId): void
    {
        // No-op in mock mode
    }
}
