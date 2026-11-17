<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Courier;
use App\Models\Order;

class CourierController extends Controller
{
    public function index(): void
    {
        $this->requireAuth(['courier', 'admin']);

        $courierId = Auth::courierId();
        if (!$courierId) {
            // Если зашел админ, берем первого курьера для демо
            $first = Courier::getAll()[0] ?? null;
            $courierId = $first['id'] ?? 1;
        }

        $courier = Courier::findById((int)$courierId);
        $orders = Order::getByCourier((int)$courierId);

        $this->render('courier/index', [
            'title' => 'Кабинет курьера — Болаззат',
            'courier' => $courier,
            'orders' => $orders,
        ], 'layouts/courier');
    }

    public function updateStatus(): void
    {
        $this->requireAuth(['courier', 'admin']);
        $this->requireCsrf();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        if (!in_array($newStatus, ['on_the_way', 'delivered'], true)) {
            Session::setFlash('danger', 'Недопустимый статус.');
            $this->redirect('/courier');
        }

        Order::updateStatus($orderId, $newStatus);

        $msg = ($newStatus === 'on_the_way') ? 'Заказ взят в доставку! Вы в пути.' : 'Заказ успешно доставлен и закрыт!';
        Session::setFlash('success', $msg);
        $this->redirect('/courier');
    }

    public function toggleBusy(): void
    {
        $this->requireAuth(['courier', 'admin']);
        $this->requireCsrf();

        $courierId = Auth::courierId() ?? 1;
        Courier::toggleBusy((int)$courierId);

        Session::setFlash('info', 'Статус занятости обновлен.');
        $this->redirect('/courier');
    }
}
