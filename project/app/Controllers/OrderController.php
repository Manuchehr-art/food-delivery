<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Address;
use App\Models\Client;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Review;

class OrderController extends Controller
{
    public function checkout(): void
    {
        $cartController = new CartController();
        $cart = $cartController->getCartDetails();

        if (empty($cart['items'])) {
            Session::setFlash('warning', 'Ваша корзина пуста.');
            $this->redirect('/');
        }

        if (!$cart['min_amount_met']) {
            $min = number_format($cart['restaurant']['min_order_amount'], 2);
            Session::setFlash('warning', "Минимальная сумма заказа для этого ресторана — {$min} TJS.");
            $this->redirect('/cart');
        }

        $client = null;
        $savedAddresses = [];

        if (Auth::check()) {
            $user = Auth::user();
            if (!empty($user['client_id'])) {
                $client = Client::findById((int)$user['client_id']);
                $savedAddresses = Address::getByClient((int)$user['client_id']);
            }
        }

        $this->render('cart/checkout', [
            'title' => 'Оформление заказа — Болаззат',
            'cart' => $cart,
            'client' => $client,
            'savedAddresses' => $savedAddresses,
        ]);
    }

    public function placeOrder(): void
    {
        $this->requireCsrf();

        $cartController = new CartController();
        $cart = $cartController->getCartDetails();

        if (empty($cart['items'])) {
            Session::setFlash('danger', 'Корзина пуста.');
            $this->redirect('/');
        }

        // Данные клиента
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Данные адреса
        $addressId = !empty($_POST['saved_address_id']) ? (int)$_POST['saved_address_id'] : null;
        $street = trim($_POST['street'] ?? '');
        $building = trim($_POST['building'] ?? '');
        $apartment = trim($_POST['apartment'] ?? '');
        $addressComment = trim($_POST['address_comment'] ?? '');

        // Способ оплаты и комментарий к заказу
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $comment = trim($_POST['comment'] ?? '');

        if (!$addressId && (empty($street) || empty($building))) {
            Session::setFlash('danger', 'Пожалуйста, укажите улицу и номер дома.');
            $this->redirect('/checkout');
        }

        if (empty($phone) || empty($firstName)) {
            Session::setFlash('danger', 'Пожалуйста, укажите имя и номер телефона.');
            $this->redirect('/checkout');
        }

        try {
            // Определяем клиента
            $clientId = null;
            if (Auth::check() && Auth::clientId()) {
                $clientId = Auth::clientId();
            } else {
                // Ищем по телефону или создаем
                $existing = Client::findByPhone($phone);
                if ($existing) {
                    $clientId = (int)$existing['id'];
                } else {
                    $clientId = Client::create([
                        'first_name' => $firstName,
                        'last_name' => !empty($lastName) ? $lastName : 'Клиент',
                        'phone' => $phone,
                        'email' => !empty($email) ? $email : null,
                    ]);
                }
            }

            // Определяем адрес
            if (!$addressId) {
                $addressId = Address::create([
                    'client_id' => $clientId,
                    'street' => $street,
                    'building' => $building,
                    'apartment' => !empty($apartment) ? $apartment : null,
                    'comment' => !empty($addressComment) ? $addressComment : null,
                ]);
            }

            // Формируем позиции заказа
            $items = [];
            foreach ($cart['items'] as $item) {
                $items[] = [
                    'dish_id' => $item['dish']['id'],
                    'quantity' => $item['quantity'],
                ];
            }

            $orderResult = Order::create([
                'client_id' => $clientId,
                'restaurant_id' => $cart['restaurant']['id'],
                'address_id' => $addressId,
                'payment_method' => $paymentMethod,
                'comment' => $comment,
            ], $items);

            // Очищаем корзину
            Session::remove('cart');

            Session::setFlash('success', "Заказ успешно оформлен! Номер заказа: {$orderResult['number']}");
            $this->redirect("/order/track/{$orderResult['number']}");
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Ошибка оформления заказа: ' . $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    public function track(string $number): void
    {
        $order = Order::findByNumber($number);
        if (!$order) {
            Session::setFlash('danger', "Заказ № {$number} не найден.");
            $this->redirect('/');
        }

        $items = Order::getItems((int)$order['id']);
        $review = Review::getByOrder((int)$order['id']);

        $this->render('order/track', [
            'title' => "Отслеживание заказа {$order['number']} — Болаззат",
            'order' => $order,
            'items' => $items,
            'review' => $review,
        ]);
    }

    public function myOrders(): void
    {
        $this->requireAuth();
        $clientId = Auth::clientId();

        $orders = [];
        if ($clientId) {
            $orders = Order::getByClient($clientId);
        }

        $this->render('order/my_orders', [
            'title' => 'Мои заказы — Болаззат',
            'orders' => $orders,
        ]);
    }

    public function submitReview(): void
    {
        $this->requireCsrf();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        $order = Order::findById($orderId);
        if (!$order || $order['status'] !== 'delivered') {
            Session::setFlash('danger', 'Оставить отзыв можно только на доставленный заказ.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        if (Review::getByOrder($orderId)) {
            Session::setFlash('warning', 'Вы уже оставили отзыв на этот заказ.');
            $this->redirect("/order/track/{$order['number']}");
        }

        Review::create([
            'order_id' => $orderId,
            'client_id' => $order['client_id'],
            'rating' => $rating,
            'comment' => $comment,
        ]);

        Session::setFlash('success', 'Спасибо за ваш отзыв! Он поможет нам стать лучше.');
        $this->redirect("/order/track/{$order['number']}");
    }
}
