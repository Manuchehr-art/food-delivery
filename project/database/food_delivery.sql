-- ============================================================
-- Полнофункциональная база данных для ИС "Болаззат" (food_delivery)
-- Подходит для импорта через phpMyAdmin или консоль MySQL
-- Кодировка: UTF-8 (utf8mb4)
-- ============================================================

DROP DATABASE IF EXISTS food_delivery;
CREATE DATABASE food_delivery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_delivery;
SET NAMES utf8mb4;

-- ============================================================
-- 1. users — учётные записи (все роли: admin, manager, courier, client)
-- ============================================================
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','manager','courier','client') NOT NULL DEFAULT 'client',
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME     NULL,
    CONSTRAINT uq_users_username UNIQUE (username)
) ENGINE=InnoDB;

-- ============================================================
-- 2. clients — карточки клиентов
-- ============================================================
CREATE TABLE clients (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,
    last_name   VARCHAR(60)  NOT NULL,
    first_name  VARCHAR(60)  NOT NULL,
    middle_name VARCHAR(60)  NULL,
    phone       VARCHAR(20)  NOT NULL,
    email       VARCHAR(120) NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_clients_user  UNIQUE (user_id),
    CONSTRAINT uq_clients_phone UNIQUE (phone),
    CONSTRAINT uq_clients_email UNIQUE (email),
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_clients_last_name (last_name)
) ENGINE=InnoDB;

-- ============================================================
-- 3. client_phones — дополнительные телефоны (1НФ)
-- ============================================================
CREATE TABLE client_phones (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    phone     VARCHAR(20)  NOT NULL,
    CONSTRAINT uq_client_phone UNIQUE (client_id, phone),
    CONSTRAINT fk_phones_client FOREIGN KEY (client_id)
        REFERENCES clients(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. addresses — адреса доставки
-- ============================================================
CREATE TABLE addresses (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED  NOT NULL,
    street    VARCHAR(150)  NOT NULL,
    building  VARCHAR(10)   NOT NULL,
    apartment VARCHAR(10)   NULL,
    lat       DECIMAL(9,6)  NOT NULL DEFAULT 38.560000,
    lon       DECIMAL(9,6)  NOT NULL DEFAULT 68.780000,
    comment   VARCHAR(255)  NULL,
    CONSTRAINT fk_addresses_client FOREIGN KEY (client_id)
        REFERENCES clients(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. restaurants — рестораны
-- ============================================================
CREATE TABLE restaurants (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(150)   NOT NULL,
    address            VARCHAR(200)   NOT NULL,
    phone              VARCHAR(20)    NOT NULL,
    work_hours         VARCHAR(50)    NOT NULL,
    min_order_amount   DECIMAL(10,2)  NOT NULL,
    delivery_radius_km DECIMAL(5,2)   NOT NULL,
    CONSTRAINT uq_restaurants_name UNIQUE (name),
    CONSTRAINT chk_restaurant_amounts CHECK (min_order_amount >= 0 AND delivery_radius_km > 0)
) ENGINE=InnoDB;

-- ============================================================
-- 6. dishes — блюда (меню ресторана)
-- ============================================================
CREATE TABLE dishes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT UNSIGNED NOT NULL,
    name          VARCHAR(150) NOT NULL,
    description   VARCHAR(500) NULL,
    category      ENUM('pizza','burgers','salads','drinks','desserts') NOT NULL,
    price         DECIMAL(10,2) NOT NULL,
    is_available  TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT uq_dish_in_restaurant UNIQUE (restaurant_id, name),
    CONSTRAINT chk_dish_price CHECK (price >= 0),
    CONSTRAINT fk_dishes_restaurant FOREIGN KEY (restaurant_id)
        REFERENCES restaurants(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dishes_category (category)
) ENGINE=InnoDB;

-- ============================================================
-- 7. couriers — курьеры
-- ============================================================
CREATE TABLE couriers (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id   INT UNSIGNED NULL,
    full_name VARCHAR(150) NOT NULL,
    phone     VARCHAR(20)  NOT NULL,
    transport ENUM('foot','bike','car') NOT NULL,
    is_busy   TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT uq_couriers_user  UNIQUE (user_id),
    CONSTRAINT uq_couriers_phone UNIQUE (phone),
    CONSTRAINT fk_couriers_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. orders — заказы
-- ============================================================
CREATE TABLE orders (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number         VARCHAR(30)  NOT NULL,
    client_id      INT UNSIGNED NOT NULL,
    restaurant_id  INT UNSIGNED NOT NULL,
    address_id     INT UNSIGNED NOT NULL,
    courier_id     INT UNSIGNED NULL,
    manager_id     INT UNSIGNED NULL,
    status         ENUM('new','accepted','cooking','on_the_way','delivered','cancelled')
                                 NOT NULL DEFAULT 'new',
    payment_method ENUM('cash','card_online','alif','dc') NOT NULL,
    comment        VARCHAR(255) NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at    DATETIME     NULL,
    delivered_at   DATETIME     NULL,
    CONSTRAINT uq_orders_number UNIQUE (number),
    CONSTRAINT chk_order_dates CHECK (delivered_at IS NULL OR delivered_at >= created_at),
    CONSTRAINT fk_orders_client     FOREIGN KEY (client_id)
        REFERENCES clients(id)     ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_restaurant FOREIGN KEY (restaurant_id)
        REFERENCES restaurants(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_address    FOREIGN KEY (address_id)
        REFERENCES addresses(id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_courier    FOREIGN KEY (courier_id)
        REFERENCES couriers(id)    ON DELETE SET NULL  ON UPDATE CASCADE,
    CONSTRAINT fk_orders_manager    FOREIGN KEY (manager_id)
        REFERENCES users(id)       ON DELETE SET NULL  ON UPDATE CASCADE,
    INDEX idx_orders_status  (status),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 9. order_items — позиции заказа (связка M:N заказы ↔ блюда)
-- ============================================================
CREATE TABLE order_items (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED  NOT NULL,
    dish_id  INT UNSIGNED  NOT NULL,
    quantity INT           NOT NULL,
    price    DECIMAL(10,2) NOT NULL,
    CONSTRAINT uq_order_dish UNIQUE (order_id, dish_id),
    CONSTRAINT chk_item_qty   CHECK (quantity > 0),
    CONSTRAINT chk_item_price CHECK (price >= 0),
    CONSTRAINT fk_items_order FOREIGN KEY (order_id)
        REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_items_dish  FOREIGN KEY (dish_id)
        REFERENCES dishes(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 10. reviews — отзывы
-- ============================================================
CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    client_id  INT UNSIGNED NOT NULL,
    rating     TINYINT      NOT NULL,
    comment    VARCHAR(500) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_reviews_order UNIQUE (order_id),
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_order FOREIGN KEY (order_id)
        REFERENCES orders(id)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_client FOREIGN KEY (client_id)
        REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ТЕСТОВЫЕ ДАННЫЕ (Пароль у всех пользователей: Secret123!)
-- ============================================================

INSERT INTO users (id, username, password_hash, role) VALUES
(1,  'admin',        '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'admin'),
(2,  'manager_anna', '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'manager'),
(3,  'courier_aliev','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'courier'),
(4,  'courier_saidov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','courier'),
(5,  'client_karimov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','client');

INSERT INTO restaurants (id, name, address, phone, work_hours, min_order_amount, delivery_radius_km) VALUES
(1, 'Вкусно и быстро №1 (Болаззат Марказ)', 'пр. Исмоила Сомони 42', '+992312000001', '09:00–23:00', 30.00, 5.00),
(2, 'Вкусно и быстро №2 (Болаззат Рудаки)', 'ул. Рудаки 15',        '+992312000002', '10:00–22:00', 25.00, 4.00),
(3, 'Вкусно и быстро №3 (Болаззат Айни)',   'ул. Айни 12',          '+992312000003', '11:00–23:30', 35.00, 6.00);

INSERT INTO dishes (id, restaurant_id, name, description, category, price, is_available) VALUES
(1,  1, 'Пицца Маргарита',      'Томатный соус, моцарелла, базилик',     'pizza',    55.00, 1),
(2,  1, 'Пицца Пепперони',      'Острая салями, сыр моцарелла, томат',   'pizza',    65.00, 1),
(3,  1, 'Бургер Классик',       'Сочная говяжья котлета, салат, томат, фирменный соус', 'burgers', 35.00, 1),
(4,  1, 'Кола 0.5 л',           'Освежающий газированный напиток',       'drinks',    8.00, 1),
(5,  2, 'Салат Цезарь',         'Нежное филе цыпленка, салат романо, пармезан, сухарики', 'salads', 28.00, 1),
(6,  2, 'Чай зелёный с жасмином','Традиционный зелёный чай с жасмином',  'drinks',    5.00, 1),
(7,  2, 'Тирамису Классик',     'Классический итальянский кофейный десерт', 'desserts', 22.00, 1),
(8,  2, 'Картофель фри хрустящий','Золотистый картофель фри с морской солью', 'salads', 15.00, 1),
(9,  3, 'Шашлык из курицы',     'Ароматное куриное филе на мангале, 200 г с луком', 'burgers', 40.00, 1),
(10, 3, 'Плов Душанбинский',    'Праздничный плов с отборной бараниной и жёлтой морковью', 'salads', 45.00, 1),
(11, 3, 'Домашний компот',      'Натуральный прохладный компот из сухофруктов', 'drinks', 10.00, 1),
(12, 3, 'Чизкейк Нью-Йорк',     'Нежный сливочный чизкейк',              'desserts', 25.00, 1);

INSERT INTO clients (id, user_id, last_name, first_name, middle_name, phone, email) VALUES
(1, 5,    'Каримов',  'Далер',  NULL,          '921112233', 'karimov@mail.tj'),
(2, NULL, 'Назарова', 'Мадина', 'Исмоиловна',  '987776655', 'nazarova@mail.tj'),
(3, NULL, 'Рахимов',  'Фарход', 'Алиевич',     '935554433', NULL),
(4, NULL, 'Шарипова', 'Зарина', NULL,          '908887766', 'sharipova@mail.tj');

INSERT INTO client_phones (client_id, phone) VALUES
(1, '934445566'),
(2, '919998877');

INSERT INTO addresses (id, client_id, street, building, apartment, lat, lon, comment) VALUES
(1, 1, 'ул. Рудаки',         '15', '4',  38.559000, 68.774000, '3 этаж, код домофона 45'),
(2, 1, 'ул. Гагарина',       '8',  NULL, 38.560000, 68.780000, 'частный дом'),
(3, 2, 'пр. Исмоила Сомони', '100','12', 38.561000, 68.775000, 'подъезд 2'),
(4, 3, 'ул. Айни',           '25', '7',  38.562000, 68.776000, 'офисный центр, 2 этаж');

INSERT INTO couriers (id, user_id, full_name, phone, transport, is_busy) VALUES
(1, 3, 'Алиев Фарход',  '905551122', 'car', 0),
(2, 4, 'Саидов Рустам', '912223344', 'bike', 0),
(3, NULL, 'Хасанов Умед','923334455', 'foot', 0);

INSERT INTO orders (id, number, client_id, restaurant_id, address_id, courier_id, manager_id, status, payment_method, comment, created_at, delivered_at) VALUES
(1,  'ORD-2026-00121', 1, 1, 1, 1, 2, 'delivered',   'cash',        'Позвонить за 5 минут', '2026-11-10 12:15:00', '2026-11-10 13:05:00'),
(2,  'ORD-2026-00122', 1, 1, 1, 1, 2, 'on_the_way',  'card_online', 'Оставить у двери',     '2026-11-10 12:40:00', NULL),
(3,  'ORD-2026-00123', 2, 3, 3, 2, 2, 'cooking',     'cash',        NULL,                   '2026-11-10 13:05:00', NULL),
(4,  'ORD-2026-00124', 3, 2, 4, NULL,2, 'accepted',    'alif',        'Оплата через Alif QR', '2026-11-10 13:20:00', NULL),
(5,  'ORD-2026-00125', 4, 1, 2, NULL,NULL,'new',         'cash',        'Срочный заказ',        '2026-11-10 13:30:00', NULL),
(6,  'ORD-2026-00126', 1, 2, 1, 2, 2, 'delivered',   'dc',          'Dushanbe City карта',  '2026-11-09 19:00:00', '2026-11-09 19:45:00'),
(7,  'ORD-2026-00127', 2, 3, 3, 1, 2, 'delivered',   'cash',        NULL,                   '2026-11-09 20:10:00', '2026-11-09 21:00:00'),
(8,  'ORD-2026-00128', 3, 1, 4, 2, 2, 'cancelled',   'card_online', 'Клиент отменил',       '2026-11-09 18:30:00', NULL),
(9,  'ORD-2026-00129', 4, 2, 1, 1, 2, 'delivered',   'cash',        NULL,                   '2026-11-08 12:00:00', '2026-11-08 12:40:00'),
(10, 'ORD-2026-00130', 1, 3, 2, 2, 2, 'delivered',   'alif',        NULL,                   '2026-11-08 13:15:00', '2026-11-08 14:00:00'),
(11, 'ORD-2026-00131', 2, 1, 3, 1, 2, 'delivered',   'cash',        'Без лука',             '2026-11-07 19:30:00', '2026-11-07 20:15:00');

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

INSERT INTO reviews (order_id, client_id, rating, comment) VALUES
(1,  1, 5, 'Очень вкусная пицца, курьер приехал быстро! Горячая!'),
(6,  1, 4, 'Салат свежий, но курьер немного задержался в пробке'),
(7,  2, 5, 'Шашлык превосходный, мясо сочное, спасибо поварам!'),
(9,  4, 4, 'Хороший сытный бургер, упакован отлично.'),
(10, 1, 5, 'Плов как дома, порция большая, рекомендую!'),
(11, 2, 3, 'Пицца остыла, пока везли');
