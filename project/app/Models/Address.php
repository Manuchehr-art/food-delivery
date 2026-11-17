<?php
namespace App\Models;

use App\Core\Database;

class Address
{
    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO addresses (client_id, street, building, apartment, lat, lon, comment)
            VALUES (:client_id, :street, :building, :apartment, :lat, :lon, :comment)
        ");
        $stmt->execute([
            'client_id' => $data['client_id'],
            'street' => $data['street'],
            'building' => $data['building'],
            'apartment' => $data['apartment'] ?? null,
            'lat' => $data['lat'] ?? 38.560000,
            'lon' => $data['lon'] ?? 68.780000,
            'comment' => $data['comment'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM addresses WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByClient(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM addresses WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }
}
