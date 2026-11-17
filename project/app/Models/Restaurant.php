<?php
namespace App\Models;

use App\Core\Database;

class Restaurant
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM restaurants ORDER BY id ASC")->fetchAll();
    }

    public static function getWithDishCount(): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT r.*, COUNT(d.id) AS dishes_count
            FROM restaurants r
            LEFT JOIN dishes d ON d.restaurant_id = r.id
            GROUP BY r.id, r.name, r.address, r.phone, r.work_hours, r.min_order_amount, r.delivery_radius_km
            ORDER BY r.id ASC
        ";
        return $db->query($sql)->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO restaurants (name, address, phone, work_hours, min_order_amount, delivery_radius_km)
            VALUES (:name, :address, :phone, :work_hours, :min_order_amount, :delivery_radius_km)
        ");
        $stmt->execute([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'work_hours' => $data['work_hours'] ?? '09:00–23:00',
            'min_order_amount' => $data['min_order_amount'] ?? 0,
            'delivery_radius_km' => $data['delivery_radius_km'] ?? 5.0,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE restaurants
            SET name = :name,
                address = :address,
                phone = :phone,
                work_hours = :work_hours,
                min_order_amount = :min_order_amount,
                delivery_radius_km = :delivery_radius_km
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'work_hours' => $data['work_hours'],
            'min_order_amount' => $data['min_order_amount'],
            'delivery_radius_km' => $data['delivery_radius_km'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM restaurants WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
