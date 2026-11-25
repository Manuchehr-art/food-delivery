<?php /** @var View $this */ ?>
<?php
$msgMap = [
    'created'    => 'Блюдо добавлено',
    'updated'    => 'Блюдо обновлено',
    'deleted'    => 'Блюдо удалено',
    'has_orders' => 'Нельзя удалить: на блюдо есть ссылки в заказах',
];
?>

<?php if (!empty($msg) && isset($msgMap[$msg])): ?>
  <div class="alert <?= $msg === 'has_orders' ? 'alert-warning' : 'alert-success' ?>">
    <?= $this->e($msgMap[$msg]) ?>
  </div>
<?php endif; ?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/menu-items" style="color:var(--mu)">Блюда</a></li>
    <li class="breadcrumb-item active"><?= $this->e($item['name']) ?></li>
  </ol>
</nav>

<div class="row g-4">
  <aside class="col-12 col-lg-4">
    <div class="card">
      <div class="card-body text-center">
        <?php if (!empty($item['image'])): ?>
          <img src="<?= $this->e($item['image']) ?>" alt="<?= $this->e($item['name']) ?>"
               class="img-fluid rounded mb-3" style="max-height:220px;object-fit:cover">
        <?php endif; ?>
        <h2 class="h3"><?= $this->e($item['name']) ?></h2>
        <span class="badge <?= $item['is_active'] ? 'bg-success' : 'bg-danger' ?>">
          <?= $item['is_active'] ? 'Активно' : 'Скрыто' ?>
        </span>
        <p class="mt-3 mb-0" style="color:var(--sf);font-weight:700">
          <?= number_format((float)$item['price'], 2, '.', ' ') ?> с.
        </p>
      </div>
    </div>
  </aside>

  <section class="col-12 col-lg-8">
    <div class="card mb-4">
      <div class="card-header">Информация</div>
      <div class="card-body">
        <dl class="details">
          <dt>Ресторан</dt>
          <dd><a href="/menu-items?restaurant_id=<?= (int)$item['restaurant_id'] ?>" style="color:var(--tx)"><?= $this->e($item['restaurant_name']) ?></a></dd>
          <dt>Категория</dt>
          <dd><?= $this->e($item['restaurant_category']) ?></dd>
          <dt>Владелец</dt>
          <dd><?= $this->e($item['owner_name'] ?? '—') ?></dd>
          <dt>Описание</dt>
          <dd><?= $this->e($item['description'] ?? '—') ?></dd>
        </dl>
      </div>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="/menu-items/<?= (int)$item['id'] ?>/edit">Изменить</a>
      <form method="post" action="/menu-items/<?= (int)$item['id'] ?>/delete"
            onsubmit="return confirm('Удалить блюдо «<?= $this->e($item['name']) ?>»?')">
        <button type="submit" class="btn btn-danger">Удалить</button>
      </form>
    </div>
  </section>
</div>