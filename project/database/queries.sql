-- database/queries.sql — контрольные запросы
-- Каждый запрос проверяет одно требование из ТЗ
USE food_delivery;
SET NAMES utf8mb4;

-- 1. JOIN трёх таблиц: заказы с клиентом и рестораном (ФТ: просмотр заказов)
SELECT o.number,
       CONCAT(c.last_name, ' ', c.first_name) AS client,
       r.name AS restaurant,
       o.status,
       o.created_at
FROM orders o
JOIN clients      c ON c.id = o.client_id
JOIN restaurants  r ON r.id = o.restaurant_id
ORDER BY o.created_at DESC;

-- 2. Поиск клиента по началу фамилии (ФТ: поиск)
SELECT id, last_name, first_name, phone
FROM clients
WHERE last_name LIKE 'К%';

-- 3. GROUP BY + COUNT: количество заказов по статусам (ФТ: отчёт)
SELECT status, COUNT(*) AS total
FROM orders
GROUP BY status
ORDER BY total DESC;

-- 4. LEFT JOIN: все рестораны + количество блюд (даже если 0)
SELECT r.name AS restaurant,
       COUNT(d.id) AS dishes_count
FROM restaurants r
LEFT JOIN dishes d ON d.restaurant_id = r.id
GROUP BY r.id, r.name
ORDER BY dishes_count DESC;

-- 5. Фильтр по статусу: заказы в работе (ФТ: панель оператора)
SELECT o.number,
       CONCAT(c.last_name, ' ', c.first_name) AS client,
       a.street, a.building, a.apartment,
       o.status
FROM orders o
JOIN clients   c ON c.id = o.client_id
JOIN addresses a ON a.id = o.address_id
WHERE o.status IN ('new', 'accepted', 'cooking', 'on_the_way')
ORDER BY o.created_at;

-- 6. LIMIT/OFFSET: пагинация — вторая страница по 5 заказов (ФТ: просмотр истории)
SELECT id, number, status, created_at
FROM orders
ORDER BY created_at DESC
LIMIT 5 OFFSET 5;

-- 7. Бонус: сумма заказа через order_items (ФТ: итоговая сумма)
SELECT o.number,
       SUM(oi.quantity * oi.price) AS total
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.id IN (1, 2, 3)
GROUP BY o.id, o.number;