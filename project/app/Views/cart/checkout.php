<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
?>

<div class="container py-4">
    <div class="mb-4">
        <a href="/cart" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Вернуться в корзину
        </a>
        <h2 class="fw-bold mt-2"><i class="bi bi-check2-circle text-success me-2"></i>Оформление заказа</h2>
    </div>

    <form action="/order/place" method="POST">
        <?= Csrf::field() ?>

        <div class="row g-4">
            <!-- Левая колонка: форма контактов и адреса -->
            <div class="col-lg-8">
                <!-- 1. Контактные данные -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">1</span>
                        Контактные данные
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Имя <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?= View::e($client['first_name'] ?? (Auth::check() ? Auth::user()['username'] : '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?= View::e($client['last_name'] ?? '') ?>" placeholder="По желанию">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Номер телефона <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+992</span>
                                <input type="text" name="phone" class="form-control" 
                                       placeholder="921112233" 
                                       value="<?= View::e($client['phone'] ?? '') ?>" required>
                            </div>
                            <small class="text-muted">Курьер позвонит по этому номеру</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">E-mail</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= View::e($client['email'] ?? '') ?>" placeholder="name@mail.tj">
                        </div>
                    </div>
                </div>

                <!-- 2. Адрес доставки -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">2</span>
                        Адрес доставки в г. Душанбе
                    </h5>

                    <?php if (!empty($savedAddresses)): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Выберите из сохранённых адресов:</label>
                            <select name="saved_address_id" class="form-select" id="savedAddressSelect">
                                <option value="">-- Указать новый адрес ниже --</option>
                                <?php foreach ($savedAddresses as $addr): ?>
                                    <option value="<?= $addr['id'] ?>">
                                        <?= View::e($addr['street']) ?>, д. <?= View::e($addr['building']) ?>
                                        <?= !empty($addr['apartment']) ? ', кв. ' . View::e($addr['apartment']) : '' ?>
                                        <?= !empty($addr['comment']) ? ' (' . View::e($addr['comment']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-muted small mb-3">или заполните форму нового адреса:</div>
                    <?php endif; ?>

                    <div class="row g-3" id="newAddressFields">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Улица / Проспект <span class="text-danger">*</span></label>
                            <input type="text" name="street" class="form-control" placeholder="пр. Рудаки или ул. Айни">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Дом / Строение <span class="text-danger">*</span></label>
                            <input type="text" name="building" class="form-control" placeholder="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Квартира / Офис</label>
                            <input type="text" name="apartment" class="form-control" placeholder="4">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Комментарий курьеру к адресу</label>
                            <input type="text" name="address_comment" class="form-control" placeholder="Код домофона, 3 этаж, частный дом...">
                        </div>
                    </div>
                </div>

                <!-- 3. Способ оплаты -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle me-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">3</span>
                        Способ оплаты
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100">
                                <input type="radio" name="payment_method" value="cash" class="form-check-input mt-0" checked>
                                <div>
                                    <div class="fw-bold"><i class="bi bi-cash me-1 text-success"></i>Наличные (Нақд)</div>
                                    <small class="text-muted">Оплата курьеру при получении</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100">
                                <input type="radio" name="payment_method" value="alif" class="form-check-input mt-0">
                                <div>
                                    <div class="fw-bold"><i class="bi bi-qr-code me-1 text-primary"></i>Alif Mobi (QR)</div>
                                    <small class="text-muted">Сканирование QR при получении</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100">
                                <input type="radio" name="payment_method" value="dc" class="form-check-input mt-0">
                                <div>
                                    <div class="fw-bold"><i class="bi bi-wallet2 me-1 text-warning"></i>Dushanbe City (DC)</div>
                                    <small class="text-muted">Кошелек или карта DC</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100">
                                <input type="radio" name="payment_method" value="card_online" class="form-check-input mt-0">
                                <div>
                                    <div class="fw-bold"><i class="bi bi-credit-card me-1 text-info"></i>Банковская карта</div>
                                    <small class="text-muted">Онлайн оплата (Корти милли)</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label small fw-semibold">Пожелания к заказу</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Например: позвонить за 5 минут, без лука в салате..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Правая колонка: сводка заказа -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Состав заказа</h5>
                    <div class="text-muted small mb-3">Ресторан: <strong><?= View::e($cart['restaurant']['name']) ?></strong></div>

                    <ul class="list-unstyled mb-3 border-bottom pb-3">
                        <?php foreach ($cart['items'] as $item): ?>
                            <li class="d-flex justify-content-between align-items-center mb-2 small">
                                <div>
                                    <span class="fw-bold"><?= $item['quantity'] ?> ×</span>
                                    <span><?= View::e($item['dish']['name']) ?></span>
                                </div>
                                <span class="fw-semibold text-dark"><?= View::price($item['subtotal']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Сумма:</span>
                        <span><?= View::price($cart['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">Доставка:</span>
                        <span class="<?= $cart['delivery_cost'] == 0 ? 'text-success fw-bold' : '' ?>">
                            <?= $cart['delivery_cost'] == 0 ? 'Бесплатно' : View::price($cart['delivery_cost']) ?>
                        </span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold">Итого:</span>
                        <span class="fs-4 fw-bold text-primary"><?= View::price($cart['total']) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm">
                        <i class="bi bi-bag-check-fill me-2"></i> Подтвердить заказ
                    </button>

                    <div class="text-center text-muted small mt-3">
                        Нажимая кнопку, вы соглашаетесь с условиями доставки сервиса «Болаззат».
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
