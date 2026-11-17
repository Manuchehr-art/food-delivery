<?php
use App\Core\Csrf;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem;" class="text-warning mb-2">📝</div>
                    <h3 class="fw-bold">Регистрация клиента</h3>
                    <p class="text-muted small">Создайте личный кабинет для быстрой доставки любимых блюд</p>
                </div>

                <form action="/register" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Имя <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="Далер" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Фамилия <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="Каримов" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Номер телефона <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+992</span>
                                <input type="text" name="phone" class="form-control" placeholder="921112233" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">E-mail (необязательно)</label>
                            <input type="email" name="email" class="form-control" placeholder="name@mail.tj">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Логин для входа <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="daler92" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Пароль <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold shadow-sm mt-4">
                        Зарегистрироваться
                    </button>
                </form>

                <div class="text-center small mt-4 pt-3 border-top">
                    Уже зарегистрированы? <a href="/login" class="fw-bold text-decoration-none">Войти в аккаунт</a>
                </div>
            </div>
        </div>
    </div>
</div>
