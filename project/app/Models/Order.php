<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Order
{
    public const STATUSES = [
        'new' => 'Новый',
        'accepted' => 'Принят',
        'cooking' => 'Готовится',
        'on_the_way' => 'В пути',
        'delivered' => 'Доставлен',
        'cancelled' => 'Отменён',
    ];

    public static function create(array $orderData, array $items): ?array
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // Генерация уникального номера заказа
            $year = date('Y');
            $randomSuffix = rand(10000, 99999);
            $number = "ORD-{$year}-{$randomSuffix}";

            $now = Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')";

            $stmt = $db->prepare("
                INSERT INTO orders (number, client_id, restaurant_id, address_id, courier_id, manager_id, status, payment_method, comment, created_at)
                VALUES (:number, :client_id, :restaurant_id, :address_id, NULL, NULL, 'new', :payment_method, :comment, {$now})
            ");

            $stmt->execute([
                'number' => $number,
                'client_id' => $orderData['client_id'],
                'restaurant_id' => $orderData['restaurant_id'],
                'address_id' => $orderData['address_id'],
                'payment_method' => $orderData['payment_method'],
                'comment' => $orderData['comment'] ?? null,
            ]);

            $orderId = (int)$db->lastInsertId();

            // Добавляем позиции заказа (order_items)
            $itemStmt = $db->prepare("
                INSERT INTO order_items (order_id, dish_id, quantity, price)
                VALUES (:order_id, :dish_id, :quantity, :price)
            ");

            $total = 0;
            foreach ($items as $item) {
                $dish = Dish::findById($item['dish_id']);
                if (!$dish) {
                    continue;
                }
                $price = (float)$dish['price'];
                $qty = (int)$item['quantity'];
                $total += $price * $qty;

                $itemStmt->execute([
                    'order_id' => $orderId,
                    'dish_id' => $dish['id'],
                    'quantity' => $qty,
                    'price' => $price,
                ]);
            }

            $db->commit();
            return [
                'id' => $orderId,
                'number' => $number,
                'total' => $total
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   c.phone AS client_phone,
                   c.email AS client_email,
                   r.name AS restaurant_name,
                   r.address AS restaurant_address,
                   r.phone AS restaurant_phone,
                   a.street, a.building, a.apartment, a.comment AS address_comment,
                   co.full_name AS courier_name,
                   co.phone AS courier_phone,
                   co.transport AS courier_transport,
                   u.username AS manager_username,
                   (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount
            FROM orders o
            JOIN clients c ON c.id = o.client_id
            JOIN restaurants r ON r.id = o.restaurant_id
            JOIN addresses a ON a.id = o.address_id
            LEFT JOIN couriers co ON co.id = o.courier_id
            LEFT JOIN users u ON u.id = o.manager_id
            WHERE o.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByNumber(string $number): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   c.phone AS client_phone,
                   r.name AS restaurant_name,
                   r.phone AS restaurant_phone,
                   a.street, a.building, a.apartment, a.comment AS address_comment,
                   co.full_name AS courier_name,
                   co.phone AS courier_phone,
                   (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount
            FROM orders o
            JOIN clients c ON c.id = o.client_id
            JOIN restaurants r ON r.id = o.restaurant_id
            JOIN addresses a ON a.id = o.address_id
            LEFT JOIN couriers co ON co.id = o.courier_id
            WHERE o.number = :number
            LIMIT 1
        ");
        $stmt->execute(['number' => $number]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getItems(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT oi.*, d.name AS dish_name, d.category, (oi.quantity * oi.price) AS subtotal
            FROM order_items oi
            JOIN dishes d ON d.id = oi.dish_id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function getAll(array $filters = []): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT o.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   c.phone AS client_phone,
                   r.name AS restaurant_name,
                   a.street, a.building, a.apartment,
                   co.full_name AS courier_name,
                   (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount
            FROM orders o
            JOIN clients c ON c.id = o.client_id
            JOIN restaurants r ON r.id = o.restaurant_id
            JOIN addresses a ON a.id = o.address_id
            LEFT JOIN couriers co ON co.id = o.courier_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['restaurant_id'])) {
            $sql .= " AND o.restaurant_id = :restaurant_id";
            $params['restaurant_id'] = $filters['restaurant_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (o.number LIKE :search OR c.last_name LIKE :search OR c.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY o.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getByClient(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*,
                   r.name AS restaurant_name,
                   (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount,
                   rw.rating AS review_rating
            FROM orders o
            JOIN restaurants r ON r.id = o.restaurant_id
            LEFT JOIN reviews rw ON rw.order_id = o.id
            WHERE o.client_id = :client_id
            ORDER BY o.id DESC
        ");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function getByCourier(int $courierId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*,
                   CONCAT(c.last_name, ' ', c.first_name) AS client_name,
                   c.phone AS client_phone,
                   r.name AS restaurant_name,
                   r.address AS restaurant_address,
                   r.phone AS restaurant_phone,
                   a.street, a.building, a.apartment, a.comment AS address_comment,
                   (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount
            FROM orders o
            JOIN clients c ON c.id = o.client_id
            JOIN restaurants r ON r.id = o.restaurant_id
            JOIN addresses a ON a.id = o.address_id
            WHERE o.courier_id = :courier_id
            ORDER BY CASE WHEN o.status IN ('on_the_way','cooking','accepted') THEN 1 ELSE 2 END, o.id DESC
        ");
        $stmt->execute(['courier_id' => $courierId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $orderId, string $status, ?int $managerId = null): bool
    {
        $db = Database::getConnection();
        $now = Database::isMySQL() ? 'NOW()' : "datetime('now', 'localtime')";

        $updates = ["status = :status"];
        $params = ['id' => $orderId, 'status' => $status];

        if ($status === 'accepted') {
            $updates[] = "accepted_at = {$now}";
            if ($managerId) {
                $updates[] = "manager_id = :manager_id";
                $params['manager_id'] = $managerId;
            }
        } elseif ($status === 'delivered') {
            $updates[] = "delivered_at = {$now}";
        }

        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);

        // Если заказ доставлен или отменён, освобождаем курьера
        if ($ok && in_array($status, ['delivered', 'cancelled'], true)) {
            $order = self::findById($orderId);
            if (!empty($order['courier_id'])) {
                Courier::setBusy((int)$order['courier_id'], false);
            }
        }

        return $ok;
    }

    public static function assignCourier(int $orderId, int $courierId, ?int $managerId = null): bool
    {
        $db = Database::getConnection();
        $params = [
            'id' => $orderId,
            'courier_id' => $courierId,
        ];
        $managerSql = "";
        if ($managerId) {
            $managerSql = ", manager_id = :manager_id";
            $params['manager_id'] = $managerId;
        }

        $stmt = $db->prepare("
            UPDATE orders
            SET courier_id = :courier_id,
                status = CASE WHEN status = 'new' THEN 'accepted' ELSE status END
                {$managerSql}
            WHERE id = :id
        ");
        $ok = $stmt->execute($params);
        if ($ok) {
            Courier::setBusy($courierId, true);
        }
        return $ok;
    }

    // === Статистика для аналитики / Admin Dashboard ===

    public static function getStatusCounts(): array
    {
        $db = Database::getConnection();
        $sql = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
        $rows = $db->query($sql)->fetchAll();
        $result = [];
        foreach ($rows as $r) {
            $result[$r['status']] = (int)$r['count'];
        }
        return $result;
    }

    public static function getTotalRevenue(): float
    {
        $db = Database::getConnection();
        $sql = "
            SELECT SUM(oi.quantity * oi.price) AS revenue
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE o.status = 'delivered'
        ";
        $row = $db->query($sql)->fetch();
        return (float)($row['revenue'] ?? 0);
    }

    public static function getPopularDishes(int $limit = 5): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT d.name, d.category, r.name AS restaurant_name,
                   SUM(oi.quantity) AS total_sold,
                   SUM(oi.quantity * oi.price) AS total_sum
            FROM order_items oi
            JOIN dishes d ON d.id = oi.dish_id
            JOIN restaurants r ON r.id = d.restaurant_id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status != 'cancelled'
            GROUP BY d.id, d.name, d.category, r.name
            ORDER BY total_sold DESC
            LIMIT {$limit}
        ";
        return $db->query($sql)->fetchAll();
    }
}
