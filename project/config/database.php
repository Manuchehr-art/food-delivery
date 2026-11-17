<?php
// config/database.php - конфигурация подключения к базе данных

return [
    // Предпочтительный драйвер: 'mysql' (по ТЗ и phpMyAdmin)
    'driver' => 'mysql',

    // Настройки MySQL (phpMyAdmin, OpenServer, XAMPP, Laragon)
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'food_delivery',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],

    // Резервный SQLite (позволяет сайту работать автономно до запуска MySQL)
    'sqlite' => [
        'path' => __DIR__ . '/../database/food_delivery.sqlite',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    ]
];
