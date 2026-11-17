<?php
namespace App\Models;

use App\Core\Database;

class Review
{
    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $now = Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')";
        $stmt = $db->prepare("
            INSERT INTO reviews (order_id, client_id, rating, comment, created_at)
            VALUES (:order_id, :client_id, :rating, :comment, {$now})
        ");
        $stmt->execute([
            'order_id' => $data['order_id'],
            'client_id' => $data['client_id'],
            'rating' => max(1, min(5, (int)$data['rating'])),
            'comment' => $data['comment'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getByOrder(int $orderId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM reviews WHERE order_id = :order_id LIMIT 1");
        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT rw.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   o.number AS order_number,
                   r.name AS restaurant_name
            FROM reviews rw
            JOIN clients c ON c.id = rw.client_id
            JOIN orders o ON o.id = rw.order_id
            JOIN restaurants r ON r.id = o.restaurant_id
            ORDER BY rw.id DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    public static function getByRestaurant(int $restaurantId): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT rw.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   o.number AS order_number
            FROM reviews rw
            JOIN clients c ON c.id = rw.client_id
            JOIN orders o ON o.id = rw.order_id
            WHERE o.restaurant_id = :restaurant_id
            ORDER BY rw.id DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['restaurant_id' => $restaurantId]);
        return $stmt->fetchAll();
    }
}
