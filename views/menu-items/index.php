<?php /** @var View $this */ ?>
<h1 class="mb-4">Блюда</h1>

<!-- Форма поиска и фильтров (GET — параметры попадут в URL) -->
<form class="card card-body mb-4" method="get" action="/menu-items" role="search">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <label class="visually-hidden" for="q">Поиск по названию</label>
      <input class="form-control" type="search" id="q" name="q"
             value="<?= $this->e($filters['q']) ?>" placeholder="Поиск по названию...">
    </div>
    <div class="col-6 col-md-3">
      <label class="visually-hidden" for="restaurant_id">Ресторан</label>
      <select class="form-select" id="restaurant_id" name="restaurant_id">
        <option value="">Все рестораны</option>
        <?php foreach ($restaurants as $r): ?>
          <option value="<?= (int)$r['id'] ?>" <?= (int)$filters['restaurant_id'] === (int)$r['id'] ? 'selected' : '' ?>>
            <?= $this->e($r['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="visually-hidden" for="category">Категория</label>
      <select class="form-select" id="category" name="category">
        <option value="">Все категории</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $this->e($c) ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>>
            <?= $this->e($c) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="visually-hidden" for="is_active">Статус</label>
      <select class="form-select" id="is_active" name="is_active">
        <option value="">Любой</option>
        <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Активные</option>
        <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Скрытые</option>
      </select>
    </div>
    <div class="col-6 col-md-1 d-grid">
      <button class="btn btn-primary" type="submit">Найти</button>
    </div>
  </div>
</form>

<!-- Таблица результатов -->
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th scope="col">Название</th>
          <th scope="col">Ресторан</th>
          <th scope="col">Категория</th>
          <th scope="col">Цена</th>
          <th scope="col">Статус</th>
          <th scope="col"><span class="visually-hidden">Действия</span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($result['items'])): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Ничего не найдено</td></tr>
        <?php endif; ?>
        <?php foreach ($result['items'] as $m): ?>
          <tr>
            <td><a href="/menu-items/<?= (int)$m['id'] ?>"><?= $this->e($m['name']) ?></a></td>
            <td><?= $this->e($m['restaurant_name']) ?></td>
            <td><?= $this->e($m['restaurant_category']) ?></td>
            <td style="color:var(--sf);font-weight:700"><?= number_format((float)$m['price'], 2, '.', ' ') ?> с.</td>
            <td>
              <span class="badge <?= $m['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                <?= $m['is_active'] ? 'Активно' : 'Скрыто' ?>
              </span>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-light" href="/menu-items/<?= (int)$m['id'] ?>/edit">Изменить</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Пагинация (фильтры сохраняются в ссылках) -->
<nav aria-label="Страницы" class="mt-4">
  <ul class="pagination justify-content-center">
    <?php
    $qs = http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 0));
    for ($p = 1; $p <= $result['pages']; $p++): ?>
      <li class="page-item <?= $p === $result['page'] ? 'active' : '' ?>">
        <a class="page-link" href="/menu-items?<?= $qs ?><?= $qs ? '&' : '' ?>page=<?= $p ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

<p class="text-muted text-center">Найдено: <?= (int)$result['total'] ?></p>