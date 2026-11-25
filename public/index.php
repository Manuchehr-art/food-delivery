<?php
// public/index.php — точка входа серверной части (админка)
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));

// 1. Чтение конфига
$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = BASE_PATH . '/config/config.example.php';
}
$config = require $configFile;
$GLOBALS['base_url'] = $config['base_url'] ?? '';

// 2. Настройка отображения ошибок
ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting(E_ALL);

// 3. Автозагрузка наших классов (простая, через require)
require BASE_PATH . '/src/Core/Database.php';
require BASE_PATH . '/src/Core/Router.php';
require BASE_PATH . '/src/Core/View.php';
require BASE_PATH . '/src/Core/Validator.php';
require BASE_PATH . '/src/Repositories/RestaurantRepository.php';
require BASE_PATH . '/src/Repositories/OrderRepository.php';
require BASE_PATH . '/src/Repositories/MenuItemRepository.php';
require BASE_PATH . '/src/Controllers/MenuItemController.php';

// 4. Подключение к БД
Database::configure($config['db']);

// 5. Обработчик непредвиденных ошибок (страница 500)
set_exception_handler(function (Throwable $e) use ($config): void {
    error_log((string)$e);
    http_response_code(500);
    $view = new View(BASE_PATH . '/views');
    echo $view->render('layouts/main', [
        'title'   => 'Ошибка 500',
        'content' => '<div class="alert alert-danger">' .
            ($config['debug'] ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : 'Попробуйте позже или обратитесь к администратору.') .
            '</div>',
    ]);
});

// 6. Регистрация маршрутов
$router = new Router();
$router->get('/menu-items',             [MenuItemController::class, 'index']);
$router->get('/menu-items/create',      [MenuItemController::class, 'create']);
$router->post('/menu-items/create',     [MenuItemController::class, 'store']);
$router->get('/menu-items/{id}',        [MenuItemController::class, 'show']);
$router->get('/menu-items/{id}/edit',   [MenuItemController::class, 'edit']);
$router->post('/menu-items/{id}/edit',  [MenuItemController::class, 'update']);
$router->post('/menu-items/{id}/delete',[MenuItemController::class, 'delete']);
$router->get('/reports/categories',     [MenuItemController::class, 'report']);

// 7. Запуск роутера
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);