<?php
use App\Core\Csrf;
use App\Core\View;

$statusSteps = [
    'new' => ['label' => 'Новый', 'tajik' => 'Нав', 'icon' => 'bi-file-earmark-plus'],
    'accepted' => ['label' => 'Принят', 'tajik' => 'Қабул', 'icon' => 'bi-check2-circle'],
    'cooking' => ['label' => 'Готовится', 'tajik' => 'Дар ошхона', 'icon' => 'bi-fire'],
    'on_the_way' => ['label' => 'В пути', 'tajik' => 'Дар роҳ', 'icon' => 'bi-bicycle'],
    'delivered' => ['label' => 'Доставлен', 'tajik' => 'Дастрас шуд', 'icon' => 'bi-house-check'],
];

$orderStatus = $order['status'];
$stepOrder = ['new', 'accepted', 'cooking', 'on_the_way', 'delivered'];
$currentIndex = array_search($orderStatus, $stepOrder);
if ($currentIndex === false) {
    $currentIndex = -1; // cancelled
}
?>

<div class="container py-4">
    <!-- Верхняя плашка -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <span class="text-muted small">Отслеживание заказа</span>
            <h2 class="fw-bold m-0 text-dark"><?= View::e($order['number']) ?></h2>
            <div class="text-muted small mt-1">Оформлен: <?= View::e($order['created_at']) ?></div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?= View::statusBadge($order['status']) ?>
            <?= View::paymentBadge($order['payment_method']) ?>
        </div>
    </div>

    <!-- Интерактивный таймлайн статуса -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
        <?php if ($orderStatus === 'cancelled'): ?>
            <div class="alert alert-danger d-flex align-items-center mb-0">
                <i class="bi bi-x-circle-fill fs-2 me-3"></i>
                <div>
                    <h5 class="fw-bold m-0">Заказ отменён</h5>
                    <div class="small">Данный заказ был отменён клиентом или оператором.</div>
                </div>
            </div>
        <?php else: ?>
            <h5 class="fw-bold mb-4 text-center">Статус доставки в реальном времени</h5>

            <div class="status-timeline">
                <?php foreach ($stepOrder as $index => $stepKey): ?>
                    <?php
                    $step = $statusSteps[$stepKey];
                    $isCompleted = ($currentIndex !== false && $index <= $currentIndex);
                    $isActive = ($currentIndex !== false && $index === $currentIndex);
                    ?>
                    <div class="timeline-step <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                        <div class="step-icon">
                            <i class="bi <?= $step['icon'] ?>"></i>
                        </div>
                        <div class="step-label"><?= $step['label'] ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= $step['tajik'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Текущий статус подсказка -->
            <div class="mt-4 p-3 bg-light rounded-3 text-center">
                <?php if ($orderStatus === 'new'): ?>
                    <i class="bi bi-hourglass-split text-primary me-1"></i> Ваш заказ передан ресторану и ожидает подтверждения.
                <?php elseif ($orderStatus === 'accepted'): ?>
                    <i class="bi bi-check-circle text-info me-1"></i> Ресторан подтвердил заказ и передает список блюд поварам.
                <?php elseif ($orderStatus === 'cooking'): ?>
                    <i class="bi bi-fire text-warning me-1"></i> Повара готовят ваши блюда. Скоро заказ будет упакован!
                <?php elseif ($orderStatus === 'on_the_way'): ?>
                    <i class="bi bi-bicycle text-purple me-1"></i> Курьер уже забрал еду из ресторана и спешит по вашему адресу!
                <?php elseif ($orderStatus === 'delivered'): ?>
                    <i class="bi bi-patch-check-fill text-success me-1"></i> Заказ успешно доставлен! Приятного аппетита!
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Детали доставки и ресторана -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Информация о доставке</h5>

                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <span class="text-muted small d-block">Ресторан:</span>
                        <strong><?= View::e($order['restaurant_name']) ?></strong>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= View::e($order['restaurant_phone']) ?></div>
                    </li>

                    <li class="mb-3">
                        <span class="text-muted small d-block">Адрес доставки:</span>
                        <strong><?= View::e($order['street']) ?>, д. <?= View::e($order['building']) ?><?= !empty($order['apartment']) ? ', кв. ' . View::e($order['apartment']) : '' ?></strong>
                        <?php if (!empty($order['address_comment'])): ?>
                            <div class="small text-muted"><i class="bi bi-chat-left-dots me-1"></i><?= View::e($order['address_comment']) ?></div>
                        <?php endif; ?>
                    </li>

                    <li class="mb-3">
                        <span class="text-muted small d-block">Получатель:</span>
                        <strong><?= View::e($order['client_name']) ?></strong>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>+992 <?= View::e($order['client_phone']) ?></div>
                    </li>

                    <?php if (!empty($order['courier_name'])): ?>
                        <li class="p-3 bg-light rounded-3 mt-3">
                            <span class="badge bg-success mb-1">Назначен курьер</span>
                            <div class="fw-bold fs-6"><?= View::e($order['courier_name']) ?></div>
                            <div class="small text-muted mb-2">Транспорт: <?= View::e($order['courier_transport'] ?? 'bike') ?></div>
                            <a href="tel:<?= View::e($order['courier_phone']) ?>" class="btn btn-sm btn-outline-success rounded-pill">
                                <i class="bi bi-telephone-outbound me-1"></i> Позвонить курьеру (+992 <?= View::e($order['courier_phone']) ?>)
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Позиции заказа -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bag text-warning me-2"></i>Состав заказа</h5>

                <div class="table-responsive mb-3">
                    <table class="table align-middle">
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
                                        <div class="fw-bold text-dark"><?= View::e($item['dish_name']) ?></div>
                                        <small class="text-muted"><?= View::price($item['price']) ?> за шт.</small>
                                    </td>
                                    <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-bold"><?= View::price($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">Итоговая сумма:</span>
                    <span class="fs-4 fw-bold text-primary"><?= View::price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Форма отзыва, если заказ доставлен -->
    <?php if ($order['status'] === 'delivered'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
            <h5 class="fw-bold mb-2"><i class="bi bi-star-fill text-warning me-2"></i>Оценка и отзыв</h5>
            <?php if (!empty($review)): ?>
                <div class="p-3 bg-light rounded-3">
                    <div class="text-warning mb-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                        <span class="fw-bold text-dark ms-2"><?= $review['rating'] ?> из 5</span>
                    </div>
                    <p class="mb-0 text-muted small"><?= View::e($review['comment'] ?? 'Без текста отзыва') ?></p>
                </div>
            <?php else: ?>
                <p class="text-muted small">Пожалуйста, оцените качество блюд и скорость доставки курьером:</p>
                <form action="/order/review" method="POST">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ваша оценка:</label>
                        <select name="rating" class="form-select w-auto">
                            <option value="5">⭐⭐⭐⭐⭐ 5 — Отлично, всё вкусно и быстро</option>
                            <option value="4">⭐⭐⭐⭐ 4 — Хорошо</option>
                            <option value="3">⭐⭐⭐ 3 — Нормально</option>
                            <option value="2">⭐⭐ 2 — Были замечания</option>
                            <option value="1">⭐ 1 — Плохо</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ваш комментарий (по желанию):</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Расскажите о блюде, упаковке или курьере..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                        Отправить отзыв
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
