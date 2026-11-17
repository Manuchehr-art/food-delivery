<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;

Session::start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Курьер — Болаззат') ?></title>
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">

    <header class="bg-dark text-white px-3 py-2 sticky-top shadow-sm d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bicycle text-warning fs-3"></i>
            <div>
                <div class="fw-bold lh-1">Болаззат Курьер</div>
                <small class="text-secondary"><?= htmlspecialchars(Auth::user()['username'] ?? 'Курьер') ?></small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="/" class="btn btn-sm btn-outline-light"><i class="bi bi-house"></i></a>
            <a href="/logout" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </header>

    <div class="container py-3">
        <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
            <?php if (Session::hasFlash($type)): ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?= Session::getFlash($type) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
