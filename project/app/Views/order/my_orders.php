<?php
use App\Core\View;
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0"><i class="bi bi-bag-check-fill text-primary me-2"></i>История моих заказов</h2>
        <a href="/" class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-cart-plus me-1"></i>Сделать новый заказ
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div style="font-size: 4rem;" class="text-muted mb-3">📦</div>
            <h5 class="fw-bold">У вас пока нет оформленных заказов</h5>
            <p class="text-muted mb-4">Сделайте свой первый заказ прямо сейчас и насладитесь вкусной едой!</p>
            <div>
                <a href="/" class="btn btn-primary rounded-pill px-4">Смотреть рестораны</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Номер заказа</th>
                            <th>Ресторан</th>
                            <th>Дата и время</th>
                            <th>Сумма</th>
                            <th>Оплата</th>
                            <th>Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark"><?= View::e($o['number']) ?></span>
                                </td>
                                <td><?= View::e($o['restaurant_name']) ?></td>
                                <td class="text-muted small"><?= View::e($o['created_at']) ?></td>
                                <td class="fw-bold"><?= View::price($o['total_amount']) ?></td>
                                <td><?= View::paymentBadge($o['payment_method']) ?></td>
                                <td><?= View::statusBadge($o['status']) ?></td>
                                <td class="text-end">
                                    <a href="/order/track/<?= $o['number'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye me-1"></i>Детали и трекинг
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
