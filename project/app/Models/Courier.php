<?php
namespace App\Models;

use App\Core\Database;

class Courier
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        return $db->query("
            SELECT c.*, u.username
            FROM couriers c
            LEFT JOIN users u ON u.id = c.user_id
            ORDER BY c.id ASC
        ")->fetchAll();
    }

    public static function getAvailable(): array
    {
        $db = Database::getConnection();
        return $db->query("
            SELECT * FROM couriers
            WHERE is_busy = 0
            ORDER BY full_name ASC
        ")->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM couriers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM couriers WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO couriers (user_id, full_name, phone, transport, is_busy)
            VALUES (:user_id, :full_name, :phone, :transport, :is_busy)
        ");
        $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'transport' => $data['transport'] ?? 'bike',
            'is_busy' => isset($data['is_busy']) ? (int)$data['is_busy'] : 0,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE couriers
            SET full_name = :full_name,
                phone = :phone,
                transport = :transport,
                is_busy = :is_busy
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'transport' => $data['transport'],
            'is_busy' => (int)($data['is_busy'] ?? 0),
        ]);
    }

    public static function toggleBusy(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE couriers SET is_busy = CASE WHEN is_busy = 1 THEN 0 ELSE 1 END WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function setBusy(int $id, bool $busy): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE couriers SET is_busy = :busy WHERE id = :id");
        return $stmt->execute(['id' => $id, 'busy' => $busy ? 1 : 0]);
    }
}
