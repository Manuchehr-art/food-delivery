<?php
use App\Core\Csrf;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem;" class="text-warning mb-2">🔐</div>
                    <h3 class="fw-bold">Вход в систему</h3>
                    <p class="text-muted small">Войдите в личный кабинет «Болаззат»</p>
                </div>

                <!-- Стандартная форма входа -->
                <form action="/login" method="POST" class="mb-4">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Логин (Username)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="admin или номер/логин" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold shadow-sm">
                        Войти в аккаунт
                    </button>
                </form>

                <div class="text-center small mb-4">
                    Нет аккаунта? <a href="/register" class="fw-bold text-decoration-none">Зарегистрироваться</a>
                </div>

                <!-- Быстрый демо-вход по ролям -->
                <div class="border-top pt-4">
                    <div class="text-center text-muted small fw-bold mb-3 text-uppercase" style="font-size: 0.75rem;">
                        ⚡ Быстрый вход для проверки ролей (1 клик)
                    </div>

                    <div class="d-grid gap-2">
                        <form action="/quick-login" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="role" value="admin">
                            <button type="submit" class="btn btn-sm btn-outline-dark w-100 text-start py-2">
                                👑 <strong>Администратор</strong> (admin) — полный доступ к системе
                            </button>
                        </form>

                        <form action="/quick-login" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="role" value="manager">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 text-start py-2">
                                📋 <strong>Менеджер</strong> (manager_anna) — обработка заказов
                            </button>
                        </form>

                        <form action="/quick-login" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="role" value="courier">
                            <button type="submit" class="btn btn-sm btn-outline-success w-100 text-start py-2">
                                🚴 <strong>Курьер</strong> (courier_aliev) — доставка заказов
                            </button>
                        </form>

                        <form action="/quick-login" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="role" value="client">
                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark w-100 text-start py-2">
                                👤 <strong>Клиент</strong> (client_karimov) — история и оформление
                            </button>
                        </form>
                    </div>

                    <div class="text-center text-muted small mt-3" style="font-size: 0.75rem;">
                        Пароль по умолчанию для всех демо-пользователей: <code>Secret123!</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
