<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;

Session::start();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Панель администратора — Болаззат') ?></title>
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; flex-shrink: 0; }
        .admin-body { flex-grow: 1; background-color: #f1f5f9; display: flex; flex-direction: column; }
        @media (max-width: 991.98px) {
            .admin-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100%; min-height: auto; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Сайдбар -->
    <aside class="admin-sidebar p-3 d-flex flex-column">
        <a href="/admin" class="d-flex align-items-center text-white text-decoration-none mb-4 px-2">
            <i class="bi bi-fire fs-3 text-warning me-2"></i>
            <div>
                <div class="fw-bold fs-5">Болаззат</div>
                <div class="text-secondary small">Панель управления</div>
            </div>
        </a>

        <div class="text-uppercase text-secondary fw-bold small px-3 mb-2" style="font-size: 0.75rem;">Основное</div>
        <nav class="nav flex-column gap-1 mb-4">
            <a href="/admin" class="<?= $currentUri === '/admin' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Аналитика (Dashboard)
            </a>
            <a href="/admin/orders" class="<?= str_starts_with($currentUri, '/admin/orders') ? 'active' : '' ?>">
                <i class="bi bi-bag-check"></i> Заказы
            </a>
        </nav>

        <div class="text-uppercase text-secondary fw-bold small px-3 mb-2" style="font-size: 0.75rem;">Каталог и заведения</div>
        <nav class="nav flex-column gap-1 mb-4">
            <a href="/admin/restaurants" class="<?= str_starts_with($currentUri, '/admin/restaurants') ? 'active' : '' ?>">
                <i class="bi bi-shop"></i> Рестораны
            </a>
            <a href="/admin/dishes" class="<?= str_starts_with($currentUri, '/admin/dishes') ? 'active' : '' ?>">
                <i class="bi bi-egg-fried"></i> Меню и блюда
            </a>
        </nav>

        <div class="text-uppercase text-secondary fw-bold small px-3 mb-2" style="font-size: 0.75rem;">Персонал и клиенты</div>
        <nav class="nav flex-column gap-1 mb-4">
            <a href="/admin/couriers" class="<?= str_starts_with($currentUri, '/admin/couriers') ? 'active' : '' ?>">
                <i class="bi bi-bicycle"></i> Курьеры
            </a>
            <a href="/admin/reviews" class="<?= str_starts_with($currentUri, '/admin/reviews') ? 'active' : '' ?>">
                <i class="bi bi-star"></i> Отзывы клиентов
            </a>
            <a href="/admin/users" class="<?= str_starts_with($currentUri, '/admin/users') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Пользователи
            </a>
        </nav>

        <hr class="border-secondary mt-auto">

        <nav class="nav flex-column gap-1">
            <a href="/" target="_blank" class="text-info">
                <i class="bi bi-box-arrow-up-right"></i> Перейти на сайт
            </a>
            <a href="/logout" class="text-danger">
                <i class="bi bi-box-arrow-right"></i> Выйти
            </a>
        </nav>
    </aside>

    <!-- Основная часть админки -->
    <div class="admin-body">
        <!-- Верхняя панель -->
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-dark fs-5"><?= htmlspecialchars($title ?? 'Панель управления') ?></span>
                <span class="badge <?= Database::isMySQL() ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <i class="bi bi-database me-1"></i><?= Database::isMySQL() ? 'MySQL (phpMyAdmin)' : 'SQLite Демо' ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold small text-dark"><?= htmlspecialchars(Auth::user()['username'] ?? '') ?></div>
                    <div class="text-secondary small text-capitalize"><?= htmlspecialchars(Auth::role() ?? '') ?></div>
                </div>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr(Auth::user()['username'] ?? 'A', 0, 1)) ?>
                </div>
            </div>
        </header>

        <!-- Flash сообщения -->
        <div class="container-fluid px-4 pt-3">
            <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
                <?php if (Session::hasFlash($type)): ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="bi <?= $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> me-2 fs-5"></i>
                        <div><?= Session::getFlash($type) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Контент страницы -->
        <main class="flex-grow-1 p-4">
            <?= $content ?>
        </main>

        <!-- Подвал админки -->
        <footer class="bg-white border-top px-4 py-2 text-secondary small text-center">
            Болаззат &copy; <?= date('Y') ?> &bull; СУБД: MySQL 8.0 / phpMyAdmin &bull; Архитектура: MVC
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
