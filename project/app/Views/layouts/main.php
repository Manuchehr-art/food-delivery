<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;

Session::start();
$cart = Session::get('cart', []);
$cartCount = array_sum($cart);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Болаззат — Доставка еды') ?></title>
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <!-- Навбар -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand navbar-brand-logo d-flex align-items-center" href="/">
                <i class="bi bi-fire text-warning me-2"></i>
                Болаззат<span>.tj</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-grid me-1"></i>Меню и рестораны</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about"><i class="bi bi-info-circle me-1"></i>О сервисе</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#trackOrderModal">
                            <i class="bi bi-geo-alt me-1"></i>Отследить заказ
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <!-- Корзина -->
                    <a href="/cart" class="btn btn-outline-primary position-relative rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-cart3 me-1"></i> Корзина
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge-count <?= $cartCount > 0 ? '' : 'd-none' ?>">
                            <?= $cartCount ?>
                        </span>
                    </a>

                    <!-- Авторизация / Профиль -->
                    <?php if (Auth::check()): ?>
                        <div class="dropdown">
                            <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 text-primary"></i>
                                <span class="fw-semibold"><?= htmlspecialchars(Auth::user()['username']) ?></span>
                                <span class="badge bg-secondary"><?= htmlspecialchars(Auth::role()) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <?php if (Auth::isManager()): ?>
                                    <li><a class="dropdown-item fw-bold text-primary" href="/admin"><i class="bi bi-speedometer2 me-2"></i>Панель администратора</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <?php if (Auth::isCourier()): ?>
                                    <li><a class="dropdown-item fw-bold text-success" href="/courier"><i class="bi bi-bicycle me-2"></i>Кабинет курьера</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/my-orders"><i class="bi bi-bag-check me-2"></i>Мои заказы</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Выйти</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Войти
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Всплывающие уведомления Flash -->
    <div class="container mt-3">
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

    <!-- Основное содержимое страницы -->
    <main class="main-content py-4">
        <?= $content ?>
    </main>

    <!-- Подвал (Footer) -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold text-warning mb-3">
                        <i class="bi bi-fire me-2"></i>Болаззат Food Delivery
                    </h5>
                    <p class="text-secondary small">
                        Системаи автоматикунонидашудаи фармоиш ва дастраскунии ғизо дар ш. Душанбе. Меъмории MVC, пойгоҳи додаҳои MySQL бо phpMyAdmin ва забони PHP.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary">PHP 8.2 MVC</span>
                        <span class="badge <?= Database::isMySQL() ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <i class="bi bi-database me-1"></i><?= Database::isMySQL() ? 'MySQL (Подключено)' : 'Демо (SQLite)' ?>
                        </span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Навигация</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><a href="/" class="text-decoration-none text-secondary">Рестораны</a></li>
                        <li class="mb-2"><a href="/cart" class="text-decoration-none text-secondary">Корзина</a></li>
                        <li class="mb-2"><a href="/about" class="text-decoration-none text-secondary">О сервисе</a></li>
                        <li class="mb-2"><a href="/admin" class="text-decoration-none text-secondary">Панель управления</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Оплата и доставка</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><i class="bi bi-cash me-2 text-warning"></i>Наличные (Нақд)</li>
                        <li class="mb-2"><i class="bi bi-qr-code me-2 text-warning"></i>Alif Mobi (QR)</li>
                        <li class="mb-2"><i class="bi bi-wallet2 me-2 text-warning"></i>Dushanbe City (DC)</li>
                        <li class="mb-2"><i class="bi bi-credit-card me-2 text-warning"></i>Банковские карты</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Контакты</h6>
                    <p class="small text-secondary mb-1"><i class="bi bi-geo-alt me-2 text-warning"></i>г. Душанбе, пр. Исмоила Сомони 42</p>
                    <p class="small text-secondary mb-1"><i class="bi bi-telephone me-2 text-warning"></i>+992 31 200 0001</p>
                    <p class="small text-secondary mb-1"><i class="bi bi-envelope me-2 text-warning"></i>support@bolazzat.tj</p>
                    <p class="small text-secondary mb-0"><i class="bi bi-clock me-2 text-warning"></i>Ежедневно с 09:00 до 23:30</p>
                </div>
            </div>

            <hr class="border-secondary my-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center small text-secondary">
                <div>© <?= date('Y') ?> Болаззат. Ҳамаи ҳуқуқҳо ҳифз шудаанд.</div>
                <div>Лоиҳаи дипломӣ / курсӣ: «Автоматикунонии раванди фармоиш ва дастраскунии ғизо»</div>
            </div>
        </div>
    </footer>

    <!-- Модальное окно быстрого отслеживания заказа по номеру -->
    <div class="modal fade" id="trackOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Отследить статус заказа</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Введите номер вашего заказа (например: <strong>ORD-2026-00121</strong>):</p>
                    <form action="/order/track" method="GET" onsubmit="event.preventDefault(); window.location.href='/order/track/' + encodeURIComponent(this.number.value.trim());">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="number" class="form-control" placeholder="ORD-2026-..." required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-semibold">Найти заказ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script src="/js/app.js"></script>
</body>
</html>
