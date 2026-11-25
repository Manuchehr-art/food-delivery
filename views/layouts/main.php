<?php /** @var View $this */ ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->e($title ?? 'Болаззат') ?> — FoodGo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&family=Unbounded:wght@700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg app-navbar">
  <div class="container">
    <a class="navbar-brand" href="/menu-items">Food<span>Go</span> · Болаззат</a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Меню">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/menu-items">Блюда</a></li>
        <li class="nav-item"><a class="nav-link" href="/menu-items/create">+ Новое блюдо</a></li>
        <li class="nav-item"><a class="nav-link" href="/reports/categories">Отчёт</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?= $content ?? '' ?>
</main>

<footer class="app-footer">
  <div class="container text-center">
    <p class="mb-0">FoodGo © 2026 — Технология ва забонҳои интернет барномасозӣ</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>