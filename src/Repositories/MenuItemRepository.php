<?php
// src/Repositories/MenuItemRepository.php — CRUD блюд
final class MenuItemRepository
{
    /** Белый список полей — только их можно записать из формы */
    public const FIELDS = [
        'restaurant_id', 'name', 'description', 'price', 'image', 'is_active'
    ];

    /** SELECT с JOIN двух справочников: ресторан и его владелец */
    private const SELECT = '
        SELECT m.*,
               r.name AS restaurant_name,
               r.category AS restaurant_category,
               u.name AS owner_name
        FROM menu_items m
        JOIN restaurants r ON r.id = m.restaurant_id
        LEFT JOIN users u ON u.id = r.owner_id';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** WHERE из фильтров: q, restaurant_id, is_active, category */
    private function buildWhere(array $f): array
    {
        $conditions = [];
        $params = [];

        $q = trim((string)($f['q'] ?? ''));
        if ($q !== '') {
            // Экранируем спецсимволы LIKE: поиск строки «%» ищет процент, а не «всё подряд»
            $conditions[] = '(m.name LIKE :q OR m.description LIKE :q)';
            $params[':q'] = '%' . addcslashes($q, '%_\\') . '%';
        }

        if (!empty($f['restaurant_id'])) {
            $conditions[] = 'm.restaurant_id = :restaurant_id';
            $params[':restaurant_id'] = (int)$f['restaurant_id'];
        }

        if (isset($f['is_active']) && $f['is_active'] !== '') {
            $conditions[] = 'm.is_active = :is_active';
            $params[':is_active'] = (int)$f['is_active'];
        }

        if (!empty($f['category'])) {
            $conditions[] = 'r.category = :category';
            $params[':category'] = (string)$f['category'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    /** Список с пагинацией */
    public function paginate(array $filters, int $page, int $perPage = 10): array
    {
        [$where, $params] = $this->buildWhere($filters);

        // 1) Сколько всего записей подходит под фильтр
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM menu_items m
             JOIN restaurants r ON r.id = m.restaurant_id {$where}"
        );
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // 2) Номер страницы не может быть < 1 и > последней
        $pages = max(1, (int)ceil($total / $perPage));
        $page  = min(max(1, $page), $pages);

        // 3) LIMIT/OFFSET через PARAM_INT — надёжно при любых настройках PDO
        $sql = self::SELECT . "{$where} ORDER BY m.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->bindValue(':limit',  $perPage,      PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'   => $stmt->fetchAll(),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $perPage,
        ];
    }

    /** Найти одно блюдо по ID */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE m.id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Создать блюдо */
    public function create(array $data): int
    {
        $columns     = implode(', ', self::FIELDS);
        $placeholders = ':' . implode(', :', self::FIELDS);
        $stmt = $this->db->prepare(
            "INSERT INTO menu_items ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute($this->onlyFields($data));
        return (int)$this->db->lastInsertId();
    }

    /** Обновить блюдо */
    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn($f) => "{$f} = :{$f}", self::FIELDS));
        $stmt = $this->db->prepare(
            "UPDATE menu_items SET {$set} WHERE id = :id"
        );
        $stmt->execute($this->onlyFields($data) + ['id' => $id]);
    }

    /** Удалить блюдо */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM menu_items WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Уникальность названия внутри ресторана (правило с обращением к БД) */
    public function nameExists(int $restaurantId, string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM menu_items WHERE restaurant_id = :rid AND name = :name';
        $params = [':rid' => $restaurantId, ':name' => $name];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :eid';
            $params[':eid'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /** Оставить только поля из белого списка */
    private function onlyFields(array $data): array
    {
        return array_intersect_key(
            array_map(fn($v) => is_string($v) ? trim($v) : $v, $data),
            array_flip(self::FIELDS)
        );
    }
}