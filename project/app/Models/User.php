<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, 
                   c.id AS client_id, CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   co.id AS courier_id
            FROM users u
            LEFT JOIN clients c ON c.user_id = u.id
            LEFT JOIN couriers co ON co.user_id = u.id
            WHERE u.username = :username
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, 
                   c.id AS client_id, 
                   co.id AS courier_id
            FROM users u
            LEFT JOIN clients c ON c.user_id = u.id
            LEFT JOIN couriers co ON co.user_id = u.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(string $username, string $password, string $role = 'client'): int
    {
        $db = Database::getConnection();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("
            INSERT INTO users (username, password_hash, role, is_active, created_at)
            VALUES (:username, :hash, :role, 1, " . (Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')") . ")
        ");
        $stmt->execute([
            'username' => $username,
            'hash' => $hash,
            'role' => $role
        ]);
        return (int)$db->lastInsertId();
    }

    public static function updateLastLogin(int $id): void
    {
        $db = Database::getConnection();
        $now = Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')";
        $db->prepare("UPDATE users SET last_login_at = {$now} WHERE id = :id")->execute(['id' => $id]);
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT u.id, u.username, u.role, u.is_active, u.created_at, u.last_login_at,
                   c.first_name, c.last_name, c.phone, c.email
            FROM users u
            LEFT JOIN clients c ON c.user_id = u.id
            ORDER BY u.id DESC
        ";
        return $db->query($sql)->fetchAll();
    }
}
