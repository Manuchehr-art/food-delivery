<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Dish;
use App\Models\Restaurant;

class CartController extends Controller
{
    public function index(): void
    {
        $cartData = $this->getCartDetails();

        $this->render('cart/index', [
            'title' => 'Корзина заказа — Болаззат',
            'cart' => $cartData,
        ]);
    }

    public function add(): void
    {
        $this->requireCsrf();

        $dishId = (int)($_POST['dish_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $dish = Dish::findById($dishId);
        if (!$dish || empty($dish['is_available'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Блюдо временно недоступно'], 400);
            }
            Session::setFlash('danger', 'Блюдо временно недоступно.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        Session::start();
        $cart = Session::get('cart', []);

        // Проверяем, если в корзине уже есть блюда из другого ресторана
        if (!empty($cart)) {
            $firstDishId = array_key_first($cart);
            $firstDish = Dish::findById((int)$firstDishId);
            if ($firstDish && $firstDish['restaurant_id'] !== $dish['restaurant_id']) {
                $msg = 'В одном заказе можно выбирать блюда только из одного ресторана. Очистите корзину, чтобы заказать из другого места.';
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => $msg], 400);
                }
                Session::setFlash('warning', $msg);
                $this->redirect('/cart');
            }
        }

        $cart[$dishId] = ($cart[$dishId] ?? 0) + $qty;
        Session::set('cart', $cart);

        $cartCount = array_sum($cart);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "«{$dish['name']}» добавлен в корзину!",
                'cart_count' => $cartCount,
            ]);
        }

        Session::setFlash('success', "«{$dish['name']}» добавлен в корзину!");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cart');
    }

    public function update(): void
    {
        $this->requireCsrf();

        $dishId = (int)($_POST['dish_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);

        Session::start();
        $cart = Session::get('cart', []);

        if ($qty <= 0) {
            unset($cart[$dishId]);
        } else {
            $cart[$dishId] = $qty;
        }

        Session::set('cart', $cart);

        if ($this->isAjax()) {
            $details = $this->getCartDetails();
            $this->json([
                'success' => true,
                'cart_count' => array_sum($cart),
                'total' => $details['total'],
                'subtotal' => $details['subtotal'],
            ]);
        }

        $this->redirect('/cart');
    }

    public function remove(): void
    {
        $this->requireCsrf();
        $dishId = (int)($_POST['dish_id'] ?? 0);

        Session::start();
        $cart = Session::get('cart', []);
        unset($cart[$dishId]);
        Session::set('cart', $cart);

        Session::setFlash('info', 'Позиция удалена из корзины.');
        $this->redirect('/cart');
    }

    public function clear(): void
    {
        $this->requireCsrf();
        Session::remove('cart');
        Session::setFlash('info', 'Корзина очищена.');
        $this->redirect('/cart');
    }

    public function getCartDetails(): array
    {
        Session::start();
        $cart = Session::get('cart', []);

        $items = [];
        $subtotal = 0.0;
        $restaurant = null;

        foreach ($cart as $dishId => $qty) {
            $dish = Dish::findById((int)$dishId);
            if (!$dish) {
                continue;
            }

            if ($restaurant === null) {
                $restaurant = Restaurant::findById((int)$dish['restaurant_id']);
            }

            $price = (float)$dish['price'];
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'dish' => $dish,
                'quantity' => $qty,
                'price' => $price,
                'subtotal' => $lineTotal,
            ];
        }

        $deliveryCost = ($subtotal > 0 && $subtotal < 100) ? 10.00 : 0.00;
        $total = $subtotal + $deliveryCost;

        $minAmountMet = true;
        if ($restaurant && $subtotal < (float)$restaurant['min_order_amount']) {
            $minAmountMet = false;
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_cost' => $deliveryCost,
            'total' => $total,
            'restaurant' => $restaurant,
            'min_amount_met' => $minAmountMet,
            'count' => array_sum($cart),
        ];
    }
}
