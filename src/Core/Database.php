<?php
// src/Core/Database.php — одно PDO-соединение на весь запрос
final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function configure(array $config): void { self::$config = $config; }

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            // charset=utf8mb4 — иначе кириллица превратится в «????»
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $c['host'], $c['port'], $c['name']);
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // ошибки → исключения
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // строки как массивы
                PDO::ATTR_EMULATE_PREPARES   => false,                    // настоящие prepared statements
            ]);
        }
        return self::$pdo;
    }
}