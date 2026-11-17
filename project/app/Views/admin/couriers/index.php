<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="bi bi-bicycle text-primary me-2"></i>Курьеры сервиса</h4>
            <div class="text-muted small">Штат службы доставки и текущий статус занятости курьеров</div>
        </div>
        <a href="/admin/couriers/create" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-circle me-1"></i> Добавить курьера
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>ID</th>
                    <th>ФИО курьера</th>
                    <th>Телефон</th>
                    <th>Транспорт</th>
                    <th>Учётная запись</th>
                    <th>Статус занятости (is_busy)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($couriers as $c): ?>
                    <tr>
                        <td class="text-muted"><?= $c['id'] ?></td>
                        <td>
                            <strong class="text-dark"><?= View::e($c['full_name']) ?></strong>
                        </td>
                        <td>
                            <a href="tel:<?= View::e($c['phone']) ?>" class="text-decoration-none">
                                +992 <?= View::e($c['phone']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-uppercase">
                                <?php
                                $transportIcons = [
                                    'car' => 'bi-car-front-fill text-primary',
                                    'bike' => 'bi-bicycle text-success',
                                    'foot' => 'bi-person-walking text-warning',
                                ];
                                $icon = $transportIcons[$c['transport']] ?? 'bi-bicycle';
                                ?>
                                <i class="bi <?= $icon ?> me-1"></i>
                                <?= $c['transport'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($c['username'])): ?>
                                <span class="badge bg-secondary"><?= View::e($c['username']) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Не привязан</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="/admin/couriers/toggle/<?= $c['id'] ?>" method="POST" class="m-0">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm <?= empty($c['is_busy']) ? 'btn-success' : 'btn-warning text-dark' ?> rounded-pill px-3 py-1 fw-semibold" title="Кликните, чтобы изменить статус">
                                    <?= empty($c['is_busy']) ? '🟢 Свободен' : '🟡 На заказе (is_busy = 1)' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
