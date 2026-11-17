<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="bi bi-shop text-primary me-2"></i>Рестораны и заведения</h4>
            <div class="text-muted small">Управление ресторанами-партнёрами сервиса «Болаззат»</div>
        </div>
        <a href="/admin/restaurants/create" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-circle me-1"></i> Добавить ресторан
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Адрес</th>
                    <th>Телефон</th>
                    <th>Режим работы</th>
                    <th>Мин. заказ</th>
                    <th>Радиус</th>
                    <th>Блюд</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $r): ?>
                    <tr>
                        <td class="text-muted"><?= $r['id'] ?></td>
                        <td>
                            <strong class="text-dark"><?= View::e($r['name']) ?></strong>
                        </td>
                        <td class="small text-muted"><?= View::e($r['address']) ?></td>
                        <td class="small"><?= View::e($r['phone']) ?></td>
                        <td class="small text-muted"><?= View::e($r['work_hours']) ?></td>
                        <td class="fw-semibold"><?= View::price($r['min_order_amount']) ?></td>
                        <td><?= (float)$r['delivery_radius_km'] ?> км</td>
                        <td>
                            <span class="badge bg-secondary"><?= $r['dishes_count'] ?></span>
                        </td>
                        <td class="text-end">
                            <a href="/admin/restaurants/edit/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Редактировать">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="/admin/restaurants/delete/<?= $r['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Удалить ресторан «<?= View::e($r['name']) ?>»?');">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
