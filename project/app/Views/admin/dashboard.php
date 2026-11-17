<?php
use App\Core\Csrf;
use App\Core\View;

$totalDelivered = $statusCounts['delivered'] ?? 0;
$totalOrders = array_sum($statusCounts);
?>

<div class="container-fluid p-0">
    <!-- Информационный баннер СУБД -->
    <div class="alert alert-white border shadow-sm rounded-4 p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                <i class="bi bi-database fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">СУБД: <?= $isMySQL ? 'MySQL 8.0 (InnoDB) — phpMyAdmin' : 'Локальный режим SQLite' ?></h6>
                <div class="text-muted small">
                    <?= $isMySQL 
                        ? 'Подключение к MySQL активно. База данных: <code>food_delivery</code>.' 
                        : 'Сайт работает автономно. Для импорта в phpMyAdmin используйте готовый файл <code>database/food_delivery.sql</code>.' ?>
                </div>
            </div>
        </div>
        <div>
            <a href="/database/food_delivery.sql" class="btn btn-sm btn-outline-primary" download>
                <i class="bi bi-download me-1"></i> Скачать SQL для phpMyAdmin
            </a>
        </div>
    </div>

    <!-- KPI карточки аналитики -->
    <div class="row g-3 mb-4">
        <!-- Выручка -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Выручка (TJS)</span>
                    <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </span>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1"><?= View::price($totalRevenue) ?></div>
                <div class="small text-muted">По доставленным заказам</div>
            </div>
        </div>

        <!-- Всего заказов -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Всего заказов</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                        <i class="bi bi-bag-check fs-5"></i>
                    </span>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1"><?= $totalOrders ?></div>
                <div class="small text-muted">За всё время работы</div>
            </div>
        </div>

        <!-- Заказы в работе -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">В работе сейчас</span>
                    <span class="badge bg-warning bg-opacity-25 text-warning p-2 rounded-circle">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </span>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1"><?= $activeCount ?></div>
                <div class="small text-muted">Новые, на кухне и в пути</div>
            </div>
        </div>

        <!-- Доставлено -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Успешно доставлено</span>
                    <span class="badge bg-info bg-opacity-10 text-info p-2 rounded-circle">
                        <i class="bi bi-patch-check fs-5"></i>
                    </span>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1"><?= $totalDelivered ?></div>
                <div class="small text-muted"><?= $totalOrders > 0 ? round(($totalDelivered / $totalOrders) * 100) : 0 ?>% успешных заказов</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Статистика по статусам заказов -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Распределение по статусам (GROUP BY status)</h6>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-primary">new</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['new'] ?? 0 ?></div>
                            <div class="small text-muted">Новые</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-info text-dark">accepted</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['accepted'] ?? 0 ?></div>
                            <div class="small text-muted">Приняты</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-warning text-dark">cooking</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['cooking'] ?? 0 ?></div>
                            <div class="small text-muted">Готовятся</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-purple text-white">on_the_way</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['on_the_way'] ?? 0 ?></div>
                            <div class="small text-muted">В пути</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-success">delivered</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['delivered'] ?? 0 ?></div>
                            <div class="small text-muted">Доставлены</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="badge bg-danger">cancelled</span>
                            <div class="fs-4 fw-bold mt-2"><?= $statusCounts['cancelled'] ?? 0 ?></div>
                            <div class="small text-muted">Отменены</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Топ популярных блюд -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Топ-5 продаваемых блюд</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Блюдо</th>
                                <th>Ресторан</th>
                                <th class="text-center">Продано шт.</th>
                                <th class="text-end">Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popularDishes as $dish): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= View::e($dish['name']) ?></div>
                                        <span class="badge bg-light text-dark border" style="font-size: 0.7rem;"><?= View::e($dish['category']) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= View::e($dish['restaurant_name']) ?></td>
                                    <td class="text-center fw-bold"><?= $dish['total_sold'] ?></td>
                                    <td class="text-end fw-bold text-success"><?= View::price($dish['total_sum']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние заказы -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i>Свежие заказы в системе</h6>
            <a href="/admin/orders" class="btn btn-sm btn-outline-primary rounded-pill">Все заказы &rarr;</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Номер</th>
                        <th>Клиент</th>
                        <th>Ресторан</th>
                        <th>Адрес доставки</th>
                        <th>Сумма</th>
                        <th>Курьер</th>
                        <th>Статус</th>
                        <th class="text-end">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                <a href="/admin/orders/show/<?= $order['id'] ?>" class="fw-bold text-decoration-none">
                                    <?= View::e($order['number']) ?>
                                </a>
                            </td>
                            <td>
                                <div><?= View::e($order['client_name']) ?></div>
                                <small class="text-muted"><?= View::e($order['client_phone']) ?></small>
                            </td>
                            <td class="small"><?= View::e($order['restaurant_name']) ?></td>
                            <td class="small text-muted"><?= View::e($order['street']) ?>, <?= View::e($order['building']) ?></td>
                            <td class="fw-bold text-dark"><?= View::price($order['total_amount']) ?></td>
                            <td>
                                <?php if (!empty($order['courier_name'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-bicycle me-1 text-primary"></i><?= View::e($order['courier_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Не назначен</span>
                                <?php endif; ?>
                            </td>
                            <td><?= View::statusBadge($order['status']) ?></td>
                            <td class="text-end">
                                <a href="/admin/orders/show/<?= $order['id'] ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
