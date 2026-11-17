<?php
use App\Core\Csrf;
use App\Core\View;

$isEdit = !empty($restaurant['id']);
$action = $isEdit ? "/admin/restaurants/update/{$restaurant['id']}" : "/admin/restaurants/store";
?>

<div class="mb-4">
    <a href="/admin/restaurants" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Назад к списку ресторанов
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h4 class="fw-bold mb-4">
                <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> text-primary me-2"></i>
                <?= $isEdit ? 'Редактирование ресторана' : 'Добавление нового ресторана' ?>
            </h4>

            <form action="<?= $action ?>" method="POST">
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Название ресторана <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="Например: Вкусно и быстро №1 (Болаззат)" 
                           value="<?= View::e($restaurant['name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Адрес в г. Душанбе <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" 
                           placeholder="пр. Исмоила Сомони 42" 
                           value="<?= View::e($restaurant['address'] ?? '') ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Телефон заведения <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" 
                               placeholder="+992312000001" 
                               value="<?= View::e($restaurant['phone'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Часы работы</label>
                        <input type="text" name="work_hours" class="form-control" 
                               placeholder="09:00–23:00" 
                               value="<?= View::e($restaurant['work_hours'] ?? '09:00–23:00') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Минимальная сумма заказа (TJS)</label>
                        <input type="number" step="0.01" name="min_order_amount" class="form-control" 
                               value="<?= (float)($restaurant['min_order_amount'] ?? 30.00) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Радиус доставки (км)</label>
                        <input type="number" step="0.1" name="delivery_radius_km" class="form-control" 
                               value="<?= (float)($restaurant['delivery_radius_km'] ?? 5.0) ?>" required>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="/admin/restaurants" class="btn btn-outline-secondary px-4 rounded-pill">Отмена</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">
                        <?= $isEdit ? 'Сохранить изменения' : 'Добавить ресторан' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
