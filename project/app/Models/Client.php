<?php
namespace App\Models;

use App\Core\Database;

class Client
{
    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE phone = :phone LIMIT 1");
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO clients (user_id, last_name, first_name, middle_name, phone, email, created_at)
            VALUES (:user_id, :last_name, :first_name, :middle_name, :phone, :email, " . (Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')") . ")
        ");
        $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function addPhone(int $clientId, string $phone): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO client_phones (client_id, phone) VALUES (:client_id, :phone)");
        $stmt->execute(['client_id' => $clientId, 'phone' => $phone]);
    }

    public static function getPhones(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT phone FROM client_phones WHERE client_id = :client_id");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
    }
}
