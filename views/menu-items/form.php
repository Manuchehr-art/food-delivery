<?php
/** @var View $this */
$action = $id ? "/menu-items/{$id}/edit" : '/menu-items/create';
$val    = fn(string $f): string => $this->e((string)($data[$f] ?? ''));
$inv    = fn(string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
$err    = fn(string $f): string => isset($errors[$f])
    ? '<div class="invalid-feedback">' . $this->e($errors[$f]) . '</div>' : '';
?>

<h1 class="mb-4"><?= $id ? 'Изменить блюдо' : 'Новое блюдо' ?></h1>

<form class="card card-body" method="post" action="<?= $this->url($action) ?>">

  <div class="mb-3">
    <label class="form-label" for="name">Название *</label>
    <input class="form-control<?= $inv('name') ?>" id="name" name="name"
           required maxlength="100" value="<?= $val('name') ?>">
    <?= $err('name') ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="restaurant_id">Ресторан *</label>
    <select class="form-select<?= $inv('restaurant_id') ?>" id="restaurant_id" name="restaurant_id" required>
      <option value="">Выберите ресторан</option>
      <?php foreach ($restaurants as $r): ?>
        <option value="<?= (int)$r['id'] ?>" <?= (int)($data['restaurant_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>>
          <?= $this->e($r['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?= $err('restaurant_id') ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="price">Цена, сомони *</label>
    <input class="form-control<?= $inv('price') ?>" id="price" name="price"
           type="number" step="0.01" min="0.01" required value="<?= $val('price') ?>">
    <?= $err('price') ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="description">Описание</label>
    <textarea class="form-control<?= $inv('description') ?>" id="description"
              name="description" maxlength="255" rows="3"><?= $val('description') ?></textarea>
    <?= $err('description') ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="image">URL изображения</label>
    <input class="form-control<?= $inv('image') ?>" id="image" name="image"
           type="url" maxlength="500" value="<?= $val('image') ?>">
    <?= $err('image') ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="is_active">Статус</label>
    <select class="form-select" id="is_active" name="is_active">
      <option value="1" <?= (int)($data['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Активно</option>
      <option value="0" <?= (int)($data['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Скрыто</option>
    </select>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><?= $id ? 'Сохранить' : 'Создать' ?></button>
    <a href="/menu-items" class="btn btn-outline-light">Отмена</a>
  </div>

</form>