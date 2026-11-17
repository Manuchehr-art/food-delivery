<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="bi bi-cart3 text-warning me-2"></i>Ваша корзина</h2>

    <?php if (empty($cart['items'])): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div style="font-size: 5rem;" class="text-muted mb-3">🛒</div>
            <h4 class="fw-bold">В корзине пока ничего нет</h4>
            <p class="text-muted mb-4">Выберите вкусные блюда из меню ресторанов прямо сейчас!</p>
            <div>
                <a href="/" class="btn btn-primary rounded-pill px-4 py-2">
                    <i class="bi bi-arrow-left me-1"></i> Перейти в каталог
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Список блюд -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="text-muted small">Ресторан:</span>
                            <h5 class="fw-bold m-0"><?= View::e($cart['restaurant']['name']) ?></h5>
                        </div>
                        <form action="/cart/clear" method="POST" onsubmit="return confirm('Очистить всю корзину?');">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                <i class="bi bi-trash3 me-1"></i>Очистить
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Блюдо</th>
                                    <th>Цена</th>
                                    <th class="text-center" style="width: 140px;">Количество</th>
                                    <th class="text-end">Итого</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= View::e($item['dish']['name']) ?></div>
                                            <small class="text-muted"><?= View::e($item['dish']['description'] ?? '') ?></small>
                                        </td>
                                        <td><?= View::price($item['price']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <form action="/cart/update" method="POST" class="m-0">
                                                    <?= Csrf::field() ?>
                                                    <input type="hidden" name="dish_id" value="<?= $item['dish']['id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                                    <button class="btn btn-sm btn-outline-secondary px-2 py-0" type="submit">-</button>
                                                </form>
                                                <span class="fw-bold px-2"><?= $item['quantity'] ?></span>
                                                <form action="/cart/update" method="POST" class="m-0">
                                                    <?= Csrf::field() ?>
                                                    <input type="hidden" name="dish_id" value="<?= $item['dish']['id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                                    <button class="btn btn-sm btn-outline-secondary px-2 py-0" type="submit">+</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            <?= View::price($item['subtotal']) ?>
                                        </td>
                                        <td class="text-end">
                                            <form action="/cart/remove" method="POST" class="m-0">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="dish_id" value="<?= $item['dish']['id'] ?>">
                                                <button class="btn btn-link text-danger p-0" title="Удалить"><i class="bi bi-x-circle fs-5"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="/restaurant/<?= $cart['restaurant']['id'] ?>" class="btn btn-link text-decoration-none p-0 text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Добавить еще блюда из этого ресторана
                </a>
            </div>

            <!-- Итоги заказа -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Детали заказа</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Сумма блюд:</span>
                        <span class="fw-bold"><?= View::price($cart['subtotal']) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Доставка:</span>
                        <span class="fw-semibold <?= $cart['delivery_cost'] == 0 ? 'text-success' : '' ?>">
                            <?= $cart['delivery_cost'] == 0 ? 'Бесплатно' : View::price($cart['delivery_cost']) ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 text-muted small">
                        <span>Мин. заказ ресторана:</span>
                        <span><?= View::price($cart['restaurant']['min_order_amount']) ?></span>
                    </div>

                    <?php if (!$cart['min_amount_met']): ?>
                        <div class="alert alert-warning small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Минимальная сумма для заказа в «<?= View::e($cart['restaurant']['name']) ?>» составляет <strong><?= View::price($cart['restaurant']['min_order_amount']) ?></strong>.
                            Пожалуйста, добавьте еще блюда.
                        </div>
                    <?php endif; ?>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold">К оплате:</span>
                        <span class="fs-4 fw-bold text-primary"><?= View::price($cart['total']) ?></span>
                    </div>

                    <?php if ($cart['min_amount_met']): ?>
                        <a href="/checkout" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm">
                            Перейти к оформлению <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg rounded-pill w-100 fw-bold" disabled>
                            Не достигнут мин. заказ
                        </button>
                    <?php endif; ?>

                    <div class="mt-3 text-center text-muted small">
                        <i class="bi bi-shield-check text-success me-1"></i> Быстрая и безопасная оплата
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
