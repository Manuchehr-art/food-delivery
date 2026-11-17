<?php
namespace App\Models;

use App\Core\Database;

class Dish
{
    public const CATEGORIES = [
        'pizza' => ['label' => 'Пицца', 'icon' => 'bi-pie-chart-fill'],
        'burgers' => ['label' => 'Бургеры и горячее', 'icon' => 'bi-badge-ad-fill'],
        'salads' => ['label' => 'Салаты и блюда', 'icon' => 'bi-flower1'],
        'drinks' => ['label' => 'Напитки', 'icon' => 'bi-cup-straw'],
        'desserts' => ['label' => 'Десерты', 'icon' => 'bi-cake2'],
    ];

    public static function getAll(array $filters = []): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT d.*, r.name AS restaurant_name
            FROM dishes d
            JOIN restaurants r ON r.id = d.restaurant_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['restaurant_id'])) {
            $sql .= " AND d.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND d.category = :category";
            $params['category'] = $filters['category'];
        }

        if (isset($filters['is_available'])) {
            $sql .= " AND d.is_available = :is_available";
            $params['is_available'] = (int)$filters['is_available'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (d.name LIKE :search OR d.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY d.restaurant_id ASC, d.category ASC, d.id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT d.*, r.name AS restaurant_name, r.min_order_amount, r.delivery_radius_km
            FROM dishes d
            JOIN restaurants r ON r.id = d.restaurant_id
            WHERE d.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByRestaurant(int $restaurantId, bool $onlyAvailable = false): array
    {
        $db = Database::getConnection();
        $sql = "SELECT * FROM dishes WHERE restaurant_id = :restaurant_id";
        if ($onlyAvailable) {
            $sql .= " AND is_available = 1";
        }
        $sql .= " ORDER BY category ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['restaurant_id' => $restaurantId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO dishes (restaurant_id, name, description, category, price, is_available)
            VALUES (:restaurant_id, :name, :description, :category, :price, :is_available)
        ");
        $stmt->execute([
            'restaurant_id' => $data['restaurant_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'price' => $data['price'],
            'is_available' => isset($data['is_available']) ? (int)$data['is_available'] : 1,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE dishes
            SET restaurant_id = :restaurant_id,
                name = :name,
                description = :description,
                category = :category,
                price = :price,
                is_available = :is_available
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'restaurant_id' => $data['restaurant_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'price' => $data['price'],
            'is_available' => isset($data['is_available']) ? (int)$data['is_available'] : 1,
        ]);
    }

    public static function toggleAvailability(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE dishes SET is_available = CASE WHEN is_available = 1 THEN 0 ELSE 1 END WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM dishes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
