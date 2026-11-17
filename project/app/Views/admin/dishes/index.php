<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="bi bi-egg-fried text-primary me-2"></i>Меню и блюда ресторанов</h4>
            <div class="text-muted small">Управление позициями меню, ценами и статусом наличия (is_available)</div>
        </div>
        <a href="/admin/dishes/create" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-circle me-1"></i> Добавить блюдо
        </a>
    </div>

    <!-- Фильтры -->
    <form action="/admin/dishes" method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Поиск по названию блюда..." value="<?= View::e($search) ?>">
            </div>
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

        <div class="col-md-3">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">Все категории</option>
                <?php foreach ($categories as $catKey => $catInfo): ?>
                    <option value="<?= $catKey ?>" <?= $currentCategory === $catKey ? 'selected' : '' ?>>
                        <?= $catInfo['label'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Фильтр</button>
            <?php if (!empty($search) || !empty($currentCategory) || !empty($currentRestaurant)): ?>
                <a href="/admin/dishes" class="btn btn-outline-secondary" title="Сбросить"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Таблица блюд -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>ID</th>
                    <th>Блюдо</th>
                    <th>Категория</th>
                    <th>Ресторан</th>
                    <th>Цена</th>
                    <th>Наличие (is_available)</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dishes)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Блюд не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dishes as $d): ?>
                        <tr>
                            <td class="text-muted"><?= $d['id'] ?></td>
                            <td>
                                <strong class="text-dark"><?= View::e($d['name']) ?></strong>
                                <?php if (!empty($d['description'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 280px;"><?= View::e($d['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= $categories[$d['category']]['label'] ?? $d['category'] ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= View::e($d['restaurant_name']) ?></td>
                            <td class="fw-bold text-dark"><?= View::price($d['price']) ?></td>
                            <td>
                                <!-- Быстрый тумблер наличия блюда (из ТЗ!) -->
                                <form action="/admin/dishes/toggle/<?= $d['id'] ?>" method="POST" class="m-0">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm <?= !empty($d['is_available']) ? 'btn-success' : 'btn-danger' ?> rounded-pill px-3 py-1 fw-semibold" title="Кликните, чтобы включить/отключить блюдо в меню">
                                        <i class="bi <?= !empty($d['is_available']) ? 'bi-check-circle' : 'bi-slash-circle' ?> me-1"></i>
                                        <?= !empty($d['is_available']) ? 'Доступно' : 'Стоп-лист' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="/admin/dishes/edit/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/admin/dishes/delete/<?= $d['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Удалить блюдо «<?= View::e($d['name']) ?>»?');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
