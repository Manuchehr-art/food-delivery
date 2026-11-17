<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="fw-bold m-0"><i class="bi bi-bag-check-fill text-primary me-2"></i>Управление заказами</h4>
        <span class="badge bg-light text-dark border fs-6">Всего в списке: <?= count($orders) ?></span>
    </div>

    <!-- Фильтры -->
    <form action="/admin/orders" method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Поиск по номеру, клиенту, тел..." value="<?= View::e($search) ?>">
            </div>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <?php foreach ($statuses as $sKey => $sLabel): ?>
                    <option value="<?= $sKey ?>" <?= $currentStatus === $sKey ? 'selected' : '' ?>>
                        <?= $sLabel ?> (<?= $sKey ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="restaurant_id" class="form-select" onchange="this.form.submit()">
                <option value="">Все рестораны</option>
                <?php foreach ($restaurants as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $currentRestaurant == $r['id'] ? 'selected' : '' ?>>
                        <?= View::e($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Фильтр</button>
            <?php if (!empty($search) || !empty($currentStatus) || !empty($currentRestaurant)): ?>
                <a href="/admin/orders" class="btn btn-outline-secondary" title="Сбросить"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Таблица заказов -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>№ Заказа</th>
                    <th>Дата / Время</th>
                    <th>Клиент</th>
                    <th>Ресторан</th>
                    <th>Адрес</th>
                    <th>Сумма</th>
                    <th>Курьер</th>
                    <th>Статус</th>
                    <th class="text-end">Управление</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Заказов по выбранным критериям не найдено.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="/admin/orders/show/<?= $o['id'] ?>" class="fw-bold text-decoration-none">
                                    <?= View::e($o['number']) ?>
                                </a>
                            </td>
                            <td class="small text-muted"><?= View::e($o['created_at']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= View::e($o['client_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>+992 <?= View::e($o['client_phone']) ?></small>
                            </td>
                            <td class="small"><?= View::e($o['restaurant_name']) ?></td>
                            <td class="small text-muted"><?= View::e($o['street']) ?>, <?= View::e($o['building']) ?><?= !empty($o['apartment']) ? ' кв. ' . View::e($o['apartment']) : '' ?></td>
                            <td class="fw-bold text-dark"><?= View::price($o['total_amount']) ?></td>
                            <td>
                                <!-- Назначение курьера -->
                                <form action="/admin/orders/assign-courier" method="POST" class="d-inline-flex gap-1 m-0">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="courier_id" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                                        <option value="">-- Назначить --</option>
                                        <?php foreach ($couriers as $courier): ?>
                                            <option value="<?= $courier['id'] ?>" <?= $o['courier_id'] == $courier['id'] ? 'selected' : '' ?>>
                                                <?= View::e($courier['full_name']) ?> <?= $courier['is_busy'] ? ' (занят)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <!-- Смена статуса заказа -->
                                <form action="/admin/orders/status" method="POST" class="m-0">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
                                        <?php foreach ($statuses as $sKey => $sLabel): ?>
                                            <option value="<?= $sKey ?>" <?= $o['status'] === $sKey ? 'selected' : '' ?>>
                                                <?= $sLabel ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="/admin/orders/show/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary" title="Подробнее">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
