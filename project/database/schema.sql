-- database/schema.sql — физическая модель food_delivery

DROP DATABASE IF EXISTS food_delivery;
CREATE DATABASE food_delivery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_delivery;
SET NAMES utf8mb4;

-- ============================================================
-- 1. users — учётные записи (все роли)
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
    lat       DECIMAL(9,6)  NOT NULL,
    lon       DECIMAL(9,6)  NOT NULL,
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