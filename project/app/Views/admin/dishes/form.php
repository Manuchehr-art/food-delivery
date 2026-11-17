<?php
use App\Core\Csrf;
use App\Core\View;

$isEdit = !empty($dish['id']);
$action = $isEdit ? "/admin/dishes/update/{$dish['id']}" : "/admin/dishes/store";
?>

<div class="mb-4">
    <a href="/admin/dishes" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Назад к списку блюд
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h4 class="fw-bold mb-4">
                <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> text-primary me-2"></i>
                <?= $isEdit ? 'Редактирование блюда' : 'Добавление нового блюда в меню' ?>
            </h4>

            <form action="<?= $action ?>" method="POST">
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Ресторан <span class="text-danger">*</span></label>
                    <select name="restaurant_id" class="form-select" required>
                        <option value="">-- Выберите ресторан --</option>
                        <?php foreach ($restaurants as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (!empty($dish['restaurant_id']) && $dish['restaurant_id'] == $r['id']) ? 'selected' : '' ?>>
                                <?= View::e($r['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Название блюда <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="Например: Пицца 4 сыра" 
                           value="<?= View::e($dish['name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Описание и состав</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Состав, соус, граммовка..."><?= View::e($dish['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Категория блюда <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $catKey => $catInfo): ?>
                                <option value="<?= $catKey ?>" <?= (!empty($dish['category']) && $dish['category'] === $catKey) ? 'selected' : '' ?>>
                                    <?= $catInfo['label'] ?> (<?= $catKey ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Цена (TJS) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.50" name="price" class="form-control" 
                                   placeholder="45.00" 
                                   value="<?= (float)($dish['price'] ?? 0) ?>" required>
                            <span class="input-group-text">TJS</span>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_available" value="1" id="isAvailableCheck" 
                           <?= (!isset($dish['is_available']) || !empty($dish['is_available'])) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="isAvailableCheck">
                        Блюдо доступно для заказа (is_available = 1)
                    </label>
                    <div class="text-muted small">Если ингредиенты закончились, снимите галочку, чтобы скрыть блюдо в каталоге.</div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="/admin/dishes" class="btn btn-outline-secondary px-4 rounded-pill">Отмена</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">
                        <?= $isEdit ? 'Сохранить изменения' : 'Добавить блюдо' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
