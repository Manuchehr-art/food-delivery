<?php
// public/index.php - Единая точка входа (Front Controller)

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Регистрация автозагрузчика классов PSR-4
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Core\Session;
use App\Controllers\HomeController;
use App\Controllers\RestaurantController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AuthController;
use App\Controllers\CourierController;
use App\Controllers\AdminController;

Session::start();

$router = new Router();

// Публичные маршруты
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/restaurant/{id}', [RestaurantController::class, 'show']);

// Корзина
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/clear', [CartController::class, 'clear']);

// Оформление заказа и трекинг
$router->get('/checkout', [OrderController::class, 'checkout']);
$router->post('/order/place', [OrderController::class, 'placeOrder']);
$router->get('/order/track/{number}', [OrderController::class, 'track']);
$router->get('/my-orders', [OrderController::class, 'myOrders']);
$router->post('/order/review', [OrderController::class, 'submitReview']);

// Аутентификация
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/quick-login', [AuthController::class, 'quickLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Курьерский кабинет
$router->get('/courier', [CourierController::class, 'index']);
$router->post('/courier/status', [CourierController::class, 'updateStatus']);
$router->post('/courier/toggle-busy', [CourierController::class, 'toggleBusy']);

// Панель администратора и менеджера (Admin Panel)
$router->get('/admin', [AdminController::class, 'dashboard']);

// Заказы в админке
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/show/{id}', [AdminController::class, 'orderShow']);
$router->post('/admin/orders/status', [AdminController::class, 'updateOrderStatus']);
$router->post('/admin/orders/assign-courier', [AdminController::class, 'assignCourier']);

// Рестораны в админке
$router->get('/admin/restaurants', [AdminController::class, 'restaurants']);
$router->get('/admin/restaurants/create', [AdminController::class, 'restaurantCreate']);
$router->post('/admin/restaurants/store', [AdminController::class, 'restaurantStore']);
$router->get('/admin/restaurants/edit/{id}', [AdminController::class, 'restaurantEdit']);
$router->post('/admin/restaurants/update/{id}', [AdminController::class, 'restaurantUpdate']);
$router->post('/admin/restaurants/delete/{id}', [AdminController::class, 'restaurantDelete']);

// Меню и блюда в админке
$router->get('/admin/dishes', [AdminController::class, 'dishes']);
$router->get('/admin/dishes/create', [AdminController::class, 'dishCreate']);
$router->post('/admin/dishes/store', [AdminController::class, 'dishStore']);
$router->get('/admin/dishes/edit/{id}', [AdminController::class, 'dishEdit']);
$router->post('/admin/dishes/update/{id}', [AdminController::class, 'dishUpdate']);
$router->post('/admin/dishes/toggle/{id}', [AdminController::class, 'dishToggle']);
$router->post('/admin/dishes/delete/{id}', [AdminController::class, 'dishDelete']);

// Курьеры в админке
$router->get('/admin/couriers', [AdminController::class, 'couriers']);
$router->get('/admin/couriers/create', [AdminController::class, 'courierCreate']);
$router->post('/admin/couriers/store', [AdminController::class, 'courierStore']);
$router->post('/admin/couriers/toggle/{id}', [AdminController::class, 'courierToggle']);

// Отзывы в админке
$router->get('/admin/reviews', [AdminController::class, 'reviews']);

// Пользователи и клиенты
$router->get('/admin/users', [AdminController::class, 'users']);

// Запуск маршрутизации
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($uri, $method);
