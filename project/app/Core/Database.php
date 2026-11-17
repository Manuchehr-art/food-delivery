<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static string $driverUsed = 'none';
    private static ?string $lastError = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/../../config/database.php';
        
        // 1. Попытка подключения к MySQL
        if (($config['driver'] ?? 'mysql') === 'mysql' && extension_loaded('pdo_mysql')) {
            try {
                $my = $config['mysql'];
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $my['host'],
                    $my['port'],
                    $my['database'],
                    $my['charset']
                );
                $pdo = new PDO($dsn, $my['username'], $my['password'], $my['options']);
                self::$instance = $pdo;
                self::$driverUsed = 'mysql';
                return self::$instance;
            } catch (PDOException $e) {
                self::$lastError = "MySQL недоступен ({$e->getMessage()}). Переключение на локальный режим SQLite.";
            }
        }

        // 2. Резервный режим SQLite (гарантирует мгновенный запуск и автономную работу)
        if (extension_loaded('pdo_sqlite')) {
            try {
                $sqlitePath = $config['sqlite']['path'];
                $isNew = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
                
                $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $config['sqlite']['options']);
                $pdo->exec('PRAGMA foreign_keys = ON;');
                
                self::$instance = $pdo;
                self::$driverUsed = 'sqlite';

                if ($isNew) {
                    self::initSqliteTables($pdo);
                }

                return self::$instance;
            } catch (PDOException $e) {
                self::$lastError = "Ошибка SQLite: " . $e->getMessage();
            }
        }

        throw new \RuntimeException(
            "Не удалось подключиться к базе данных! MySQL ошибка: " . (self::$lastError ?? 'неизвестно') . 
            ". Пожалуйста, запустите MySQL в phpMyAdmin или включите pdo_mysql / pdo_sqlite в PHP."
        );
    }

    public static function getDriver(): string
    {
        return self::$driverUsed;
    }

    public static function isMySQL(): bool
    {
        return self::$driverUsed === 'mysql';
    }

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    /**
     * Автоматическая инициализация схемы и демо-данных для SQLite
     */
    private static function initSqliteTables(PDO $pdo): void
    {
        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'client',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
            last_login_at TEXT NULL
        );

        CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NULL UNIQUE REFERENCES users(id) ON DELETE SET NULL,
            last_name TEXT NOT NULL,
            first_name TEXT NOT NULL,
            middle_name TEXT NULL,
            phone TEXT NOT NULL UNIQUE,
            email TEXT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
        );

        CREATE TABLE IF NOT EXISTS client_phones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id INTEGER NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
            phone TEXT NOT NULL,
            UNIQUE(client_id, phone)
        );

        CREATE TABLE IF NOT EXISTS addresses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id INTEGER NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
            street TEXT NOT NULL,
            building TEXT NOT NULL,
            apartment TEXT NULL,
            lat REAL NOT NULL DEFAULT 38.560000,
            lon REAL NOT NULL DEFAULT 68.780000,
            comment TEXT NULL
        );

        CREATE TABLE IF NOT EXISTS restaurants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            address TEXT NOT NULL,
            phone TEXT NOT NULL,
            work_hours TEXT NOT NULL,
            min_order_amount REAL NOT NULL,
            delivery_radius_km REAL NOT NULL
        );

        CREATE TABLE IF NOT EXISTS dishes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE RESTRICT,
            name TEXT NOT NULL,
            description TEXT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL,
            is_available INTEGER NOT NULL DEFAULT 1,
            UNIQUE(restaurant_id, name)
        );

        CREATE TABLE IF NOT EXISTS couriers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NULL UNIQUE REFERENCES users(id) ON DELETE SET NULL,
            full_name TEXT NOT NULL,
            phone TEXT NOT NULL UNIQUE,
            transport TEXT NOT NULL,
            is_busy INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            number TEXT NOT NULL UNIQUE,
            client_id INTEGER NOT NULL REFERENCES clients(id),
            restaurant_id INTEGER NOT NULL REFERENCES restaurants(id),
            address_id INTEGER NOT NULL REFERENCES addresses(id),
            courier_id INTEGER NULL REFERENCES couriers(id) ON DELETE SET NULL,
            manager_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
            status TEXT NOT NULL DEFAULT 'new',
            payment_method TEXT NOT NULL,
            comment TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
            accepted_at TEXT NULL,
            delivered_at TEXT NULL
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
            dish_id INTEGER NOT NULL REFERENCES dishes(id),
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            UNIQUE(order_id, dish_id)
        );

        CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL UNIQUE REFERENCES orders(id) ON DELETE CASCADE,
            client_id INTEGER NOT NULL REFERENCES clients(id),
            rating INTEGER NOT NULL,
            comment TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
        );

        -- Начальные пользователи (пароль Secret123!)
        INSERT OR IGNORE INTO users (id, username, password_hash, role) VALUES
        (1,  'admin',        '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'admin'),
        (2,  'manager_anna', '$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'manager'),
        (3,  'courier_aliev','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC', 'courier'),
        (4,  'courier_saidov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','courier'),
        (5,  'client_karimov','$2y$10$UNkIFfDEqAOi7ySnyD6Y/eOwI/mKH3g0is1OYNEv4OkXNXk3XRGRC','client');

        -- Рестораны
        INSERT OR IGNORE INTO restaurants (id, name, address, phone, work_hours, min_order_amount, delivery_radius_km) VALUES
        (1, 'Вкусно и быстро №1 (Болаззат Марказ)', 'пр. Исмоила Сомони 42', '+992312000001', '09:00–23:00', 30.00, 5.00),
        (2, 'Вкусно и быстро №2 (Болаззат Рудаки)', 'ул. Рудаки 15',        '+992312000002', '10:00–22:00', 25.00, 4.00),
        (3, 'Вкусно и быстро №3 (Болаззат Айни)',   'ул. Айни 12',          '+992312000003', '11:00–23:30', 35.00, 6.00);

        -- Блюда
        INSERT OR IGNORE INTO dishes (id, restaurant_id, name, description, category, price, is_available) VALUES
        (1,  1, 'Пицца Маргарита',       'Томатный соус, моцарелла, базилик',     'pizza',    55.00, 1),
        (2,  1, 'Пицца Пепперони',       'Острая салями, сыр моцарелла, томат',   'pizza',    65.00, 1),
        (3,  1, 'Бургер Классик',        'Сочная говяжья котлета, салат, томат, фирменный соус', 'burgers', 35.00, 1),
        (4,  1, 'Кола 0.5 л',            'Освежающий газированный напиток',       'drinks',    8.00, 1),
        (5,  2, 'Салат Цезарь',          'Нежное филе цыпленка, салат романо, пармезан, сухарики', 'salads', 28.00, 1),
        (6,  2, 'Чай зелёный с жасмином','Традиционный зелёный чай с жасмином',   'drinks',    5.00, 1),
        (7,  2, 'Тирамису Классик',      'Классический итальянский кофейный десерт', 'desserts', 22.00, 1),
        (8,  2, 'Картофель фри хрустящий','Золотистый картофель фри с солью',     'salads',   15.00, 1),
        (9,  3, 'Шашлык из курицы',      'Ароматное куриное филе на мангале, 200 г с луком', 'burgers', 40.00, 1),
        (10, 3, 'Плов Душанбинский',     'Праздничный плов с отборной бараниной и жёлтой морковью', 'salads', 45.00, 1),
        (11, 3, 'Домашний компот',       'Натуральный прохладный компот из сухофруктов', 'drinks', 10.00, 1),
        (12, 3, 'Чизкейк Нью-Йорк',      'Нежный сливочный чизкейк',              'desserts', 25.00, 1);

        -- Клиенты
        INSERT OR IGNORE INTO clients (id, user_id, last_name, first_name, middle_name, phone, email) VALUES
        (1, 5,    'Каримов',  'Далер',  NULL,          '921112233', 'karimov@mail.tj'),
        (2, NULL, 'Назарова', 'Мадина', 'Исмоиловна',  '987776655', 'nazarova@mail.tj'),
        (3, NULL, 'Рахимов',  'Фарход', 'Алиевич',     '935554433', NULL),
        (4, NULL, 'Шарипова', 'Зарина', NULL,          '908887766', 'sharipova@mail.tj');

        INSERT OR IGNORE INTO client_phones (client_id, phone) VALUES
        (1, '934445566'),
        (2, '919998877');

        INSERT OR IGNORE INTO addresses (id, client_id, street, building, apartment, lat, lon, comment) VALUES
        (1, 1, 'ул. Рудаки',         '15', '4',  38.559000, 68.774000, '3 этаж, код домофона 45'),
        (2, 1, 'ул. Гагарина',       '8',  NULL, 38.560000, 68.780000, 'частный дом'),
        (3, 2, 'пр. Исмоила Сомони', '100','12', 38.561000, 68.775000, 'подъезд 2'),
        (4, 3, 'ул. Айни',           '25', '7',  38.562000, 68.776000, 'офисный центр, 2 этаж');

        INSERT OR IGNORE INTO couriers (id, user_id, full_name, phone, transport, is_busy) VALUES
        (1, 3, 'Алиев Фарход',  '905551122', 'car', 0),
        (2, 4, 'Саидов Рустам', '912223344', 'bike', 0),
        (3, NULL, 'Хасанов Умед','923334455', 'foot', 0);

        INSERT OR IGNORE INTO orders (id, number, client_id, restaurant_id, address_id, courier_id, manager_id, status, payment_method, comment, created_at, delivered_at) VALUES
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

        INSERT OR IGNORE INTO order_items (order_id, dish_id, quantity, price) VALUES
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

        INSERT OR IGNORE INTO reviews (order_id, client_id, rating, comment) VALUES
        (1,  1, 5, 'Очень вкусная пицца, курьер приехал быстро! Горячая!'),
        (6,  1, 4, 'Салат свежий, но курьер немного задержался в пробке'),
        (7,  2, 5, 'Шашлык превосходный, мясо сочное, спасибо поварам!'),
        (9,  4, 4, 'Хороший сытный бургер, упакован отлично.'),
        (10, 1, 5, 'Плов как дома, порция большая, рекомендую!'),
        (11, 2, 3, 'Пицца остыла, пока везли');
SQL;
        $pdo->exec($sql);
    }
}
