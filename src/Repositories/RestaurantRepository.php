<?php
// src/Repositories/RestaurantRepository.php — справочник ресторанов
final class RestaurantRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Все рестораны для выпадающего списка в форме */
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, name, category FROM restaurants ORDER BY name'
        )->fetchAll();
    }

    /** ID ресторанов — для проверки in() на сервере */
    public function ids(): array
    {
        return array_column(
            $this->db->query('SELECT id FROM restaurants')->fetchAll(),
            'id'
        );
    }
}