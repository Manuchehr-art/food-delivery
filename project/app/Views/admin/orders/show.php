<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="mb-4">
    <a href="/admin/orders" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Назад к списку заказов
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold m-0 text-dark">Карточка заказа: <?= View::e($order['number']) ?></h4>
            <div class="text-muted small mt-1">Оформлен: <?= View::e($order['created_at']) ?></div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?= View::statusBadge($order['status']) ?>
            <?= View::paymentBadge($order['payment_method']) ?>
        </div>
    </div>

    <!-- Управление статусом и курьером -->
    <div class="row g-3 p-3 bg-light rounded-3 mb-4 align-items-center">
        <div class="col-md-6">
            <form action="/admin/orders/status" method="POST" class="d-flex align-items-center gap-2 m-0">
                <?= Csrf::field() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <label class="form-label small fw-bold m-0 text-nowrap">Изменить статус:</label>
                <select name="status" class="form-select form-select-sm">
                    <?php foreach ($statuses as $sKey => $sLabel): ?>
                        <option value="<?= $sKey ?>" <?= $order['status'] === $sKey ? 'selected' : '' ?>>
                            <?= $sLabel ?> (<?= $sKey ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Применить</button>
            </form>
        </div>

        <div class="col-md-6">
            <form action="/admin/orders/assign-courier" method="POST" class="d-flex align-items-center gap-2 m-0">
                <?= Csrf::field() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <label class="form-label small fw-bold m-0 text-nowrap">Назначить курьера:</label>
                <select name="courier_id" class="form-select form-select-sm">
                    <option value="">-- Выберите курьера --</option>
                    <?php foreach ($couriers as $courier): ?>
                        <option value="<?= $courier['id'] ?>" <?= $order['courier_id'] == $courier['id'] ? 'selected' : '' ?>>
                            <?= View::e($courier['full_name']) ?> (<?= $courier['transport'] ?>) <?= $courier['is_busy'] ? ' - занят' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-success">Назначить</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Информация о клиенте и доставке -->
        <div class="col-md-6">
            <h6 class="fw-bold mb-3 border-bottom pb-2">Участники заказа</h6>
            <ul class="list-unstyled small mb-0">
                <li class="mb-2">
                    <span class="text-muted">Клиент:</span>
                    <strong><?= View::e($order['client_name']) ?></strong>
                </li>
                <li class="mb-2">
                    <span class="text-muted">Телефон:</span>
                    <a href="tel:<?= View::e($order['client_phone']) ?>" class="fw-semibold text-decoration-none">
                        +992 <?= View::e($order['client_phone']) ?>
                    </a>
                </li>
                <li class="mb-2">
                    <span class="text-muted">Адрес доставки:</span>
                    <strong><?= View::e($order['street']) ?>, д. <?= View::e($order['building']) ?><?= !empty($order['apartment']) ? ', кв. ' . View::e($order['apartment']) : '' ?></strong>
                </li>
                <?php if (!empty($order['address_comment'])): ?>
                    <li class="mb-2">
                        <span class="text-muted">Комментарий к адресу:</span>
                        <em>"<?= View::e($order['address_comment']) ?>"</em>
                    </li>
                <?php endif; ?>
                <li class="mb-2 pt-2 border-top">
                    <span class="text-muted">Ресторан:</span>
                    <strong><?= View::e($order['restaurant_name']) ?></strong> (<?= View::e($order['restaurant_address']) ?>)
                </li>
                <li class="mb-2">
                    <span class="text-muted">Курьер:</span>
                    <strong><?= View::e($order['courier_name'] ?? 'Не назначен') ?></strong>
                    <?php if (!empty($order['courier_phone'])): ?>
                        (+992 <?= View::e($order['courier_phone']) ?>)
                    <?php endif; ?>
                </li>
                <?php if (!empty($order['manager_username'])): ?>
                    <li class="mb-2">
                        <span class="text-muted">Обработал менеджер:</span>
                        <span class="badge bg-secondary"><?= View::e($order['manager_username']) ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Позиции блюд -->
        <div class="col-md-6">
            <h6 class="fw-bold mb-3 border-bottom pb-2">Позиции в заказе</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Блюдо</th>
                            <th class="text-center">Кол-во</th>
                            <th class="text-end">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= View::e($item['dish_name']) ?></div>
                                    <small class="text-muted"><?= View::price($item['price']) ?> / шт.</small>
                                </td>
                                <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                                <td class="text-end fw-bold"><?= View::price($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Итого к оплате:</th>
                            <th class="text-end text-primary fs-6"><?= View::price($order['total_amount']) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($review)): ?>
                <div class="mt-4 p-3 bg-light rounded-3">
                    <div class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-1"></i>Отзыв клиента:</div>
                    <div class="text-warning mb-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="small mb-0 text-muted"><?= View::e($review['comment'] ?? 'Без текста') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
