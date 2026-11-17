<?php
use App\Core\View;
?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i>Отзывы и оценки клиентов</h4>
            <div class="text-muted small">Обратная связь от покупателей после доставки заказов</div>
        </div>
        <span class="badge bg-light text-dark border fs-6">Всего отзывов: <?= count($reviews) ?></span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>№ Заказа</th>
                    <th>Клиент</th>
                    <th>Ресторан</th>
                    <th>Оценка (Rating)</th>
                    <th>Комментарий</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Отзывов пока нет</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td>
                                <a href="/admin/orders/show/<?= $rev['order_id'] ?>" class="fw-bold text-decoration-none">
                                    <?= View::e($rev['order_number']) ?>
                                </a>
                            </td>
                            <td class="fw-semibold text-dark"><?= View::e($rev['client_name']) ?></td>
                            <td class="small text-muted"><?= View::e($rev['restaurant_name']) ?></td>
                            <td>
                                <span class="text-warning text-nowrap">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="badge bg-light text-dark border ms-1"><?= $rev['rating'] ?>/5</span>
                            </td>
                            <td>
                                <span class="small"><?= View::e($rev['comment'] ?? 'Без текста') ?></span>
                            </td>
                            <td class="small text-muted"><?= View::e($rev['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
