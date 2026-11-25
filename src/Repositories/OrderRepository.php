<?php
// src/Repositories/OrderRepository.php — отчёты и проверка FK
final class OrderRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Есть ли ссылки на блюдо в заказах? (для проверки перед удалением) */
    public function hasOrderItems(int $menuItemId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM order_items WHERE menu_item_id = ? LIMIT 1'
        );
        $stmt->execute([$menuItemId]);
        return (bool)$stmt->fetchColumn();
    }

    /** Отчёт: блюда по категориям ресторанов (GROUP BY) */
    public function reportByCategory(): array
    {
        return $this->db->query(
            "SELECT r.category,
                    COUNT(m.id) AS dishes,
                    COALESCE(ROUND(AVG(m.price), 2), 0) AS avg_price,
                    COALESCE(SUM(oi.qty), 0) AS sold
             FROM restaurants r
             LEFT JOIN menu_items m ON m.restaurant_id = r.id AND m.is_active = 1
             LEFT JOIN order_items oi ON oi.menu_item_id = m.id
             GROUP BY r.category
             ORDER BY sold DESC, r.category"
        )->fetchAll();
    }
}