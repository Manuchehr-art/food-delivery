<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="row g-3 mb-4">
    <!-- Статус курьера -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-3 text-warning">
                        <i class="bi bi-bicycle fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold m-0"><?= View::e($courier['full_name'] ?? 'Курьер') ?></h5>
                        <div class="small text-muted">
                            Телефон: +992 <?= View::e($courier['phone'] ?? '') ?> &bull; 
                            Транспорт: <?= View::e($courier['transport'] ?? 'bike') ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= empty($courier['is_busy']) ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2">
                        <?= empty($courier['is_busy']) ? '🟢 Свободен (готов к заказам)' : '🟡 Занят (на заказе)' ?>
                    </span>

                    <form action="/courier/toggle-busy" method="POST" class="m-0">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Сменить статус
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold mb-3"><i class="bi bi-card-checklist text-primary me-2"></i>Мои назначенные заказы</h5>

<?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
        <h6 class="fw-bold">Нет активных заказов</h6>
        <p class="text-muted small">Как только менеджер назначит вам заказ, он появится здесь.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($orders as $order): ?>
            <?php
            $isDone = in_array($order['status'], ['delivered', 'cancelled'], true);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 <?= $isDone ? 'opacity-75 bg-light' : 'bg-white border-primary' ?>">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold text-dark fs-5"><?= View::e($order['number']) ?></span>
                            <?= View::statusBadge($order['status']) ?>
                        </div>

                        <div class="mb-3">
                            <?= View::paymentBadge($order['payment_method']) ?>
                            <span class="fw-bold text-primary ms-2"><?= View::price($order['total_amount']) ?></span>
                        </div>

                        <!-- Откуда забрать -->
                        <div class="p-2 rounded bg-light mb-2 small">
                            <strong class="text-dark"><i class="bi bi-shop text-warning me-1"></i>Откуда (Ресторан):</strong>
                            <div class="fw-semibold"><?= View::e($order['restaurant_name']) ?></div>
                            <div class="text-muted"><?= View::e($order['restaurant_address']) ?></div>
                            <a href="tel:<?= View::e($order['restaurant_phone']) ?>" class="text-decoration-none text-success small">
                                <i class="bi bi-telephone me-1"></i><?= View::e($order['restaurant_phone']) ?>
                            </a>
                        </div>

                        <!-- Куда доставить -->
                        <div class="p-2 rounded bg-light mb-3 small flex-grow-1">
                            <strong class="text-dark"><i class="bi bi-geo-alt text-danger me-1"></i>Куда (Клиент):</strong>
                            <div class="fw-semibold"><?= View::e($order['client_name']) ?></div>
                            <div><?= View::e($order['street']) ?>, д. <?= View::e($order['building']) ?><?= !empty($order['apartment']) ? ', кв. ' . View::e($order['apartment']) : '' ?></div>
                            <?php if (!empty($order['address_comment'])): ?>
                                <div class="text-muted fst-italic">"<?= View::e($order['address_comment']) ?>"</div>
                            <?php endif; ?>
                            <a href="tel:<?= View::e($order['client_phone']) ?>" class="btn btn-sm btn-outline-success mt-2 w-100">
                                <i class="bi bi-telephone-outbound me-1"></i> Позвонить клиенту (+992 <?= View::e($order['client_phone']) ?>)
                            </a>
                        </div>

                        <!-- Действия курьера -->
                        <div class="mt-auto pt-2 border-top">
                            <?php if ($order['status'] === 'accepted' || $order['status'] === 'cooking'): ?>
                                <form action="/courier/status" method="POST">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="on_the_way">
                                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2 rounded-pill">
                                        <i class="bi bi-bicycle me-1"></i> Взял заказ (В пути)
                                    </button>
                                </form>
                            <?php elseif ($order['status'] === 'on_the_way'): ?>
                                <form action="/courier/status" method="POST">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-pill">
                                        <i class="bi bi-check2-circle me-1"></i> Доставлен (Оплата получена)
                                    </button>
                                </form>
                            <?php elseif ($order['status'] === 'delivered'): ?>
                                <div class="text-center text-success small fw-bold py-1">
                                    <i class="bi bi-check-all me-1"></i> Доставлено и закрыто
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
