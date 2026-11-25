<?php /** @var View $this */ ?>
<h1 class="mb-4">Отчёт: блюда по категориям ресторанов</h1>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th scope="col">Категория</th>
          <th scope="col">Блюд</th>
          <th scope="col">Средняя цена</th>
          <th scope="col">Продано</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= $this->e($r['category']) ?></td>
            <td><?= (int)$r['dishes'] ?></td>
            <td><?= number_format((float)$r['avg_price'], 2, '.', ' ') ?> с.</td>
            <td><?= (int)$r['sold'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>