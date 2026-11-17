<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Client;
use App\Models\Courier;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->requireAuth(['admin', 'manager']);
    }

    // === 1. Панель управления / Главная аналитика ===
    public function dashboard(): void
    {
        $statusCounts = Order::getStatusCounts();
        $totalRevenue = Order::getTotalRevenue();
        $popularDishes = Order::getPopularDishes(5);
        $recentOrders = Order::getAll(['limit' => 10]);
        $availableCouriers = Courier::getAvailable();
        $restaurants = Restaurant::getAll();

        $activeCount = ($statusCounts['new'] ?? 0) + 
                       ($statusCounts['accepted'] ?? 0) + 
                       ($statusCounts['cooking'] ?? 0) + 
                       ($statusCounts['on_the_way'] ?? 0);

        $this->render('admin/dashboard', [
            'title' => 'Панель администратора — Болаззат',
            'statusCounts' => $statusCounts,
            'totalRevenue' => $totalRevenue,
            'activeCount' => $activeCount,
            'popularDishes' => $popularDishes,
            'recentOrders' => $recentOrders,
            'availableCouriers' => $availableCouriers,
            'restaurants' => $restaurants,
            'dbDriver' => Database::getDriver(),
            'isMySQL' => Database::isMySQL(),
            'dbError' => Database::getLastError(),
        ], 'layouts/admin');
    }

    // === 2. Заказы (Orders) ===
    public function orders(): void
    {
        $status = $_GET['status'] ?? null;
        $restaurantId = !empty($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;
        $search = trim($_GET['search'] ?? '');

        $orders = Order::getAll([
            'status' => $status,
            'restaurant_id' => $restaurantId,
            'search' => $search,
        ]);

        $restaurants = Restaurant::getAll();
        $couriers = Courier::getAll();
        $availableCouriers = Courier::getAvailable();

        $this->render('admin/orders/index', [
            'title' => 'Управление заказами — Болаззат',
            'orders' => $orders,
            'restaurants' => $restaurants,
            'couriers' => $couriers,
            'availableCouriers' => $availableCouriers,
            'currentStatus' => $status,
            'currentRestaurant' => $restaurantId,
            'search' => $search,
            'statuses' => Order::STATUSES,
        ], 'layouts/admin');
    }

    public function orderShow(int|string $id): void
    {
        $id = (int)$id;
        $order = Order::findById($id);

        if (!$order) {
            Session::setFlash('danger', 'Заказ не найден.');
            $this->redirect('/admin/orders');
        }

        $items = Order::getItems($id);
        $review = Review::getByOrder($id);
        $couriers = Courier::getAll();

        $this->render('admin/orders/show', [
            'title' => "Заказ {$order['number']} — Болаззат",
            'order' => $order,
            'items' => $items,
            'review' => $review,
            'couriers' => $couriers,
            'statuses' => Order::STATUSES,
        ], 'layouts/admin');
    }

    public function updateOrderStatus(): void
    {
        $this->requireCsrf();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!array_key_exists($status, Order::STATUSES)) {
            Session::setFlash('danger', 'Недопустимый статус заказа.');
            $this->redirect('/admin/orders');
        }

        Order::updateStatus($orderId, $status, Auth::id());

        Session::setFlash('success', "Статус заказа обновлен на «" . Order::STATUSES[$status] . "».");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/orders');
    }

    public function assignCourier(): void
    {
        $this->requireCsrf();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $courierId = (int)($_POST['courier_id'] ?? 0);

        if ($orderId <= 0 || $courierId <= 0) {
            Session::setFlash('danger', 'Выберите заказ и курьера.');
            $this->redirect('/admin/orders');
        }

        Order::assignCourier($orderId, $courierId, Auth::id());

        $courier = Courier::findById($courierId);
        $name = $courier['full_name'] ?? 'курьер';

        Session::setFlash('success', "Курьер {$name} назначен на заказ!");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/orders');
    }

    // === 3. Рестораны (Restaurants) ===
    public function restaurants(): void
    {
        $restaurants = Restaurant::getWithDishCount();
        $this->render('admin/restaurants/index', [
            'title' => 'Рестораны — Болаззат',
            'restaurants' => $restaurants,
        ], 'layouts/admin');
    }

    public function restaurantCreate(): void
    {
        $this->render('admin/restaurants/form', [
            'title' => 'Добавить ресторан — Болаззат',
            'restaurant' => null,
        ], 'layouts/admin');
    }

    public function restaurantStore(): void
    {
        $this->requireCsrf();

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $workHours = trim($_POST['work_hours'] ?? '09:00–23:00');
        $minOrder = (float)($_POST['min_order_amount'] ?? 0);
        $radius = (float)($_POST['delivery_radius_km'] ?? 5);

        if (empty($name) || empty($address) || empty($phone)) {
            Session::setFlash('danger', 'Заполните обязательные поля: название, адрес, телефон.');
            $this->redirect('/admin/restaurants/create');
        }

        Restaurant::create([
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'work_hours' => $workHours,
            'min_order_amount' => $minOrder,
            'delivery_radius_km' => $radius,
        ]);

        Session::setFlash('success', "Ресторан «{$name}» успешно добавлен!");
        $this->redirect('/admin/restaurants');
    }

    public function restaurantEdit(int|string $id): void
    {
        $id = (int)$id;
        $restaurant = Restaurant::findById($id);
        if (!$restaurant) {
            Session::setFlash('danger', 'Ресторан не найден.');
            $this->redirect('/admin/restaurants');
        }

        $this->render('admin/restaurants/form', [
            'title' => 'Редактировать ресторан — Болаззат',
            'restaurant' => $restaurant,
        ], 'layouts/admin');
    }

    public function restaurantUpdate(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $workHours = trim($_POST['work_hours'] ?? '09:00–23:00');
        $minOrder = (float)($_POST['min_order_amount'] ?? 0);
        $radius = (float)($_POST['delivery_radius_km'] ?? 5);

        Restaurant::update($id, [
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'work_hours' => $workHours,
            'min_order_amount' => $minOrder,
            'delivery_radius_km' => $radius,
        ]);

        Session::setFlash('success', 'Данные ресторана успешно обновлены!');
        $this->redirect('/admin/restaurants');
    }

    public function restaurantDelete(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;
        try {
            Restaurant::delete($id);
            Session::setFlash('success', 'Ресторан удален.');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Невозможно удалить ресторан, так как к нему привязаны блюда или заказы.');
        }
        $this->redirect('/admin/restaurants');
    }

    // === 4. Меню и блюда (Dishes) ===
    public function dishes(): void
    {
        $restaurantId = !empty($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;
        $category = $_GET['category'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $dishes = Dish::getAll([
            'restaurant_id' => $restaurantId,
            'category' => $category,
            'search' => $search,
        ]);

        $restaurants = Restaurant::getAll();

        $this->render('admin/dishes/index', [
            'title' => 'Меню и блюда — Болаззат',
            'dishes' => $dishes,
            'restaurants' => $restaurants,
            'currentRestaurant' => $restaurantId,
            'currentCategory' => $category,
            'search' => $search,
            'categories' => Dish::CATEGORIES,
        ], 'layouts/admin');
    }

    public function dishCreate(): void
    {
        $restaurants = Restaurant::getAll();
        $this->render('admin/dishes/form', [
            'title' => 'Добавить блюдо в меню — Болаззат',
            'dish' => null,
            'restaurants' => $restaurants,
            'categories' => Dish::CATEGORIES,
        ], 'layouts/admin');
    }

    public function dishStore(): void
    {
        $this->requireCsrf();

        $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'pizza';
        $price = (float)($_POST['price'] ?? 0);
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        if (empty($name) || $restaurantId <= 0 || $price <= 0) {
            Session::setFlash('danger', 'Заполните название, выберите ресторан и укажите корректную цену.');
            $this->redirect('/admin/dishes/create');
        }

        Dish::create([
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'price' => $price,
            'is_available' => $isAvailable,
        ]);

        Session::setFlash('success', "Блюдо «{$name}» успешно добавлено в меню!");
        $this->redirect('/admin/dishes');
    }

    public function dishEdit(int|string $id): void
    {
        $id = (int)$id;
        $dish = Dish::findById($id);
        if (!$dish) {
            Session::setFlash('danger', 'Блюдо не найдено.');
            $this->redirect('/admin/dishes');
        }

        $restaurants = Restaurant::getAll();
        $this->render('admin/dishes/form', [
            'title' => 'Редактировать блюдо — Болаззат',
            'dish' => $dish,
            'restaurants' => $restaurants,
            'categories' => Dish::CATEGORIES,
        ], 'layouts/admin');
    }

    public function dishUpdate(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;

        $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'pizza';
        $price = (float)($_POST['price'] ?? 0);
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        Dish::update($id, [
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'price' => $price,
            'is_available' => $isAvailable,
        ]);

        Session::setFlash('success', 'Блюдо успешно обновлено!');
        $this->redirect('/admin/dishes');
    }

    public function dishToggle(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;

        Dish::toggleAvailability($id);
        Session::setFlash('info', 'Статус доступности блюда изменен (is_available).');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/dishes');
    }

    public function dishDelete(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;
        try {
            Dish::delete($id);
            Session::setFlash('success', 'Блюдо удалено.');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Невозможно удалить блюдо, так как оно уже участвует в оформленных заказах.');
        }
        $this->redirect('/admin/dishes');
    }

    // === 5. Курьеры (Couriers) ===
    public function couriers(): void
    {
        $couriers = Courier::getAll();
        $this->render('admin/couriers/index', [
            'title' => 'Курьеры — Болаззат',
            'couriers' => $couriers,
        ], 'layouts/admin');
    }

    public function courierCreate(): void
    {
        $this->render('admin/couriers/form', [
            'title' => 'Добавить курьера — Болаззат',
            'courier' => null,
        ], 'layouts/admin');
    }

    public function courierStore(): void
    {
        $this->requireCsrf();

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $transport = $_POST['transport'] ?? 'bike';

        if (empty($fullName) || empty($phone)) {
            Session::setFlash('danger', 'Заполните ФИО и телефон курьера.');
            $this->redirect('/admin/couriers/create');
        }

        Courier::create([
            'full_name' => $fullName,
            'phone' => $phone,
            'transport' => $transport,
            'is_busy' => 0,
        ]);

        Session::setFlash('success', "Курьер {$fullName} добавлен!");
        $this->redirect('/admin/couriers');
    }

    public function courierToggle(int|string $id): void
    {
        $this->requireCsrf();
        $id = (int)$id;
        Courier::toggleBusy($id);
        Session::setFlash('info', 'Статус занятости курьера изменен.');
        $this->redirect('/admin/couriers');
    }

    // === 6. Отзывы (Reviews) ===
    public function reviews(): void
    {
        $reviews = Review::getAll();
        $this->render('admin/reviews/index', [
            'title' => 'Отзывы клиентов — Болаззат',
            'reviews' => $reviews,
        ], 'layouts/admin');
    }

    // === 7. Пользователи и клиенты (Users) ===
    public function users(): void
    {
        $users = User::getAll();
        $clients = Client::getAll();

        $this->render('admin/users/index', [
            'title' => 'Пользователи и клиенты — Болаззат',
            'users' => $users,
            'clients' => $clients,
        ], 'layouts/admin');
    }
}
