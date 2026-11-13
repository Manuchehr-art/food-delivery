-- database/seed.sql — тестовые данные (вымышленные, на кириллице)
USE food_delivery;
SET NAMES utf8mb4;

-- === Пользователи (пароль у всех: Secret123!) ===
INSERT INTO users (id, username, password_hash, role) VALUES
(1,  'admin',        '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'admin'),
(2,  'manager_anna', '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'manager'),
(3,  'courier_aliev','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'courier'),
(4,  'courier_saidov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','courier'),
(5,  'client_karimov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','client');

-- === Рестораны ===
INSERT INTO restaurants (id, name, address, phone, work_hours, min_order_amount, delivery_radius_km) VALUES
(1, 'Вкусно и быстро №1', 'пр. Исмоила Сомони 42', '+992312000001', '09:00–23:00', 30.00, 5.00),
(2, 'Вкусно и быстро №2', 'ул. Рудаки 15',        '+992312000002', '10:00–22:00', 25.00, 4.00),
(3, 'Вкусно и быстро №3', 'ул. Айни 12',          '+992312000003', '11:00–23:30', 35.00, 6.00);

-- === Блюда (по 4 в каждом ресторане) ===
INSERT INTO dishes (id, restaurant_id, name, description, category, price) VALUES
(1,  1, 'Пицца Маргарита',      'Томатный соус, моцарелла, базилик',     'pizza',    55.00),
(2,  1, 'Пицца Пепперони',      'Острая салями, сыр, томат',             'pizza',    65.00),
(3,  1, 'Бургер Классик',       'Говядина, салат, томат, соус',          'burgers',  35.00),
(4,  1, 'Кола 0.5 л',           'Газированный напиток',                  'drinks',    8.00),
(5,  2, 'Салат Цезарь',         'Курица, салат романо, пармезан',        'salads',   28.00),
(6,  2, 'Чай зелёный',          'Зелёный чай с жасмином',                'drinks',    5.00),
(7,  2, 'Тирамису',             'Классический итальянский десерт',       'desserts', 22.00),
(8,  2, 'Картофель фри',        'Хрустящий картофель с солью',           'salads',   15.00),
(9,  3, 'Шашлык из курицы',     'Куриное филе на углях, 200 г',          'burgers',  40.00),
(10, 3, 'Плов',                 'Узбекский плов с бараниной',            'salads',   45.00),
(11, 3, 'Компот',               'Домашний компот из сухофруктов',        'drinks',   10.00),
(12, 3, 'Чизкейк',              'Нью-Йорк чизкейк',                      'desserts', 25.00);

-- === Клиенты ===
INSERT INTO clients (id, user_id, last_name, first_name, middle_name, phone, email) VALUES
(1, 5,    'Каримов',  'Далер',  NULL,          '921112233', 'karimov@mail.tj'),
(2, NULL, 'Назарова', 'Мадина', 'Исмоиловна',  '987776655', 'nazarova@mail.tj'),
(3, NULL, 'Рахимов',  'Фарход', 'Алиевич',     '935554433', NULL),
(4, NULL, 'Шарипова', 'Зарина', NULL,          '908887766', 'sharipova@mail.tj');

-- === Дополнительные телефоны ===
INSERT INTO client_phones (client_id, phone) VALUES
(1, '934445566'),
(2, '919998877');

-- === Адреса ===
INSERT INTO addresses (id, client_id, street, building, apartment, lat, lon, comment) VALUES
(1, 1, 'ул. Рудаки',        '15', '4',  38.559000, 68.774000, '3 этаж, код домофона 45'),
(2, 1, 'ул. Гагарина',      '8',  NULL, 38.560000, 68.780000, 'частный дом'),
(3, 2, 'пр. Исмоила Сомони','100','12', 38.561000, 68.775000, NULL),
(4, 3, 'ул. Айни',          '25', '7',  38.562000, 68.776000, 'офис');

-- === Курьеры ===
INSERT INTO couriers (id, user_id, full_name, phone, transport) VALUES
(1, 3, 'Алиев Фарход',  '905551122', 'car'),
(2, 4, 'Саидов Рустам', '912223344', 'bike'),
(3, NULL, 'Хасанов Умед','923334455', 'foot');

-- === Заказы (10+) ===
INSERT INTO orders (id, number, client_id, restaurant_id, address_id, courier_id, manager_id, status, payment_method, created_at, delivered_at) VALUES
(1,  'ORD-2026-00121', 1, 1, 1, 1, 2, 'delivered',   'cash',        '2026-11-10 12:15:00', '2026-11-10 13:05:00'),
(2,  'ORD-2026-00122', 1, 1, 1, 1, 2, 'on_the_way',  'card_online', '2026-11-10 12:40:00', NULL),
(3,  'ORD-2026-00123', 2, 3, 3, 2, 2, 'cooking',     'cash',        '2026-11-10 13:05:00', NULL),
(4,  'ORD-2026-00124', 3, 2, 4, NULL,2, 'accepted',    'alif',        '2026-11-10 13:20:00', NULL),
(5,  'ORD-2026-00125', 4, 1, 2, NULL,NULL,'new',         'cash',        '2026-11-10 13:30:00', NULL),
(6,  'ORD-2026-00126', 1, 2, 1, 2, 2, 'delivered',   'dc',          '2026-11-09 19:00:00', '2026-11-09 19:45:00'),
(7,  'ORD-2026-00127', 2, 3, 3, 1, 2, 'delivered',   'cash',        '2026-11-09 20:10:00', '2026-11-09 21:00:00'),
(8,  'ORD-2026-00128', 3, 1, 4, 2, 2, 'cancelled',   'card_online', '2026-11-09 18:30:00', NULL),
(9,  'ORD-2026-00129', 4, 2, 1, 1, 2, 'delivered',   'cash',        '2026-11-08 12:00:00', '2026-11-08 12:40:00'),
(10, 'ORD-2026-00130', 1, 3, 2, 2, 2, 'delivered',   'alif',        '2026-11-08 13:15:00', '2026-11-08 14:00:00'),
(11, 'ORD-2026-00131', 2, 1, 3, 1, 2, 'delivered',   'cash',        '2026-11-07 19:30:00', '2026-11-07 20:15:00');

-- === Позиции заказов ===
INSERT INTO order_items (order_id, dish_id, quantity, price) VALUES
(1,  1,  2, 55.00), (1,  4,  1,  8.00),
(2,  3,  1, 35.00), (2,  8,  1, 15.00),
(3,  5,  2, 28.00), (3,  6,  2,  5.00),
(4,  7,  1, 22.00),
(5,  2,  1, 65.00),
(6,  5,  1, 28.00), (6,  6,  2,  5.00),
(7,  9,  2, 40.00), (7,  11, 2, 10.00),
(8,  1,  1, 55.00),
(9,  3,  2, 35.00), (9,  4,  2,  8.00),
(10, 10, 1, 45.00), (10, 12, 1, 25.00),
(11, 1,  1, 55.00), (11, 3,  1, 35.00);

-- === Отзывы (только для доставленных заказов) ===
INSERT INTO reviews (order_id, client_id, rating, comment) VALUES
(1,  1, 5, 'Очень вкусная пицца, курьер приехал быстро!'),
(6,  1, 4, 'Салат свежий, но ждал долго'),
(7,  2, 5, 'Шашлык превосходный, спасибо!'),
(9,  4, 4, NULL),
(10, 1, 5, 'Плов как у бабушки'),
(11, 2, 3, 'Пицца остыла, пока везли');