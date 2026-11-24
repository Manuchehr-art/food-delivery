<?php
// public/api/restaurants/index.php — ВРЕМЕННАЯ заглушка API для лаб. 11
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

$restaurants = [
    [
        'id' => 1,
        'name' => 'Пиццаи Олӣ',
        'category' => 'Пицца',
        'description' => 'Пиццаи итолиёвӣ бо панири зиёд',
        'rating' => 4.8,
        'votes' => 124,
        'delivery_time' => 30,
        'delivery_fee' => 5,
        'is_open' => 1,
    ],
    [
        'id' => 2,
        'name' => 'Бургер Хаус',
        'category' => 'Бургер',
        'description' => 'Бӯргери гӯшти тоза бо соуси махсус',
        'rating' => 4.9,
        'votes' => 89,
        'delivery_time' => 25,
        'delivery_fee' => 5,
        'is_open' => 1,
    ],
    [
        'id' => 3,
        'name' => 'Оши Марказӣ',
        'category' => 'Миллӣ',
        'description' => 'Хӯроки миллӣ ва анъанавӣ',
        'rating' => 4.7,
        'votes' => 201,
        'delivery_time' => 40,
        'delivery_fee' => 7,
        'is_open' => 1,
    ],
    [
        'id' => 4,
        'name' => 'Салати Сабз',
        'category' => 'Салат',
        'description' => 'Салатҳои тоза ва солим',
        'rating' => 4.6,
        'votes' => 56,
        'delivery_time' => 20,
        'delivery_fee' => 4,
        'is_open' => 1,
    ],
    [
        'id' => 5,
        'name' => 'Суши Мастер',
        'category' => 'Японӣ',
        'description' => 'Суши ва роллҳои тоза',
        'rating' => 4.5,
        'votes' => 78,
        'delivery_time' => 35,
        'delivery_fee' => 6,
        'is_open' => 0,
    ],
    [
        'id' => 6,
        'name' => '<img src=x onerror="alert(\'XSS\')"> Тестовый ресторан',
        'category' => 'Тест',
        'description' => 'Только для проверки экранирования',
        'rating' => 3.0,
        'votes' => 1,
        'delivery_time' => 99,
        'delivery_fee' => 10,
        'is_open' => 1,
    ],
];

$q = mb_strtolower(trim((string)($_GET['q'] ?? '')));
$category = trim((string)($_GET['category'] ?? ''));

if ($q !== '' || $category !== '') {
    $restaurants = array_values(array_filter(
        $restaurants,
        function (array $r) use ($q, $category): bool {
            $matchesQ = $q === '' || str_contains(mb_strtolower($r['name'] . ' ' . $r['description']), $q);
            $matchesCat = $category === '' || $r['category'] === $category;
            return $matchesQ && $matchesCat;
        }
    ));
}

echo json_encode($restaurants, JSON_UNESCAPED_UNICODE);