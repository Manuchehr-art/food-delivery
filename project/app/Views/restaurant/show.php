<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="container py-4">
    <!-- Шапка ресторана -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="restaurant-thumb" style="height: 180px;">
            <i class="bi bi-shop-window" style="font-size: 5rem;"></i>
        </div>
        <div class="p-4 p-md-5 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="fw-bold mb-2"><?= View::e($restaurant['name']) ?></h2>
                    <p class="text-muted mb-2"><i class="bi bi-geo-alt text-danger me-1"></i><?= View::e($restaurant['address']) ?></p>
                    <p class="text-muted mb-0"><i class="bi bi-telephone text-success me-1"></i><?= View::e($restaurant['phone']) ?></p>
                </div>
                <div class="d-flex flex-column align-items-md-end gap-2">
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">
                        <i class="bi bi-star-fill me-1"></i>Рейтинг 4.9
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>Режим: <?= View::e($restaurant['work_hours']) ?>
                    </span>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3 text-center">
                <div class="col-sm-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <div class="small text-muted">Мин. заказ</div>
                        <div class="fw-bold text-dark"><?= View::price($restaurant['min_order_amount']) ?></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <div class="small text-muted">Радиус доставки</div>
                        <div class="fw-bold text-dark"><?= (float)$restaurant['delivery_radius_km'] ?> км</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <div class="small text-muted">Время доставки</div>
                        <div class="fw-bold text-dark">~35 минут</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Меню ресторана по категориям -->
    <?php if (empty($groupedDishes)): ?>
        <div class="alert alert-info text-center py-5">
            <h5>Меню ресторана пока пустое</h5>
            <p class="text-muted">Администратор скоро добавит вкусные позиции.</p>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $catKey => $catInfo): ?>
            <?php if (!empty($groupedDishes[$catKey])): ?>
                <div class="mb-5">
                    <h3 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi <?= $catInfo['icon'] ?> text-warning me-2"></i>
                        <?= $catInfo['label'] ?>
                    </h3>

                    <div class="row g-4">
                        <?php foreach ($groupedDishes[$catKey] as $dish): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="dish-card <?= empty($dish['is_available']) ? 'opacity-75' : '' ?>">
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold text-dark mb-1"><?= View::e($dish['name']) ?></h5>
                                            <?php if (empty($dish['is_available'])): ?>
                                                <span class="badge bg-danger">Закончилось</span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-muted small flex-grow-1 mb-3">
                                            <?= View::e($dish['description'] ?? 'Свежее качественное блюдо от шеф-повара') ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                                            <div class="dish-price text-primary"><?= View::price($dish['price']) ?></div>

                                            <?php if (!empty($dish['is_available'])): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold btn-add-cart-ajax" 
                                                        data-dish-id="<?= $dish['id'] ?>">
                                                    <i class="bi bi-cart-plus me-1"></i> В корзину
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary rounded-pill px-3 py-2" disabled>
                                                    Недоступно
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Отзывы -->
    <?php if (!empty($reviews)): ?>
        <div class="mt-5 pt-4 border-top">
            <h3 class="fw-bold mb-4"><i class="bi bi-chat-quote-fill text-warning me-2"></i>Отзывы гостей</h3>
            <div class="row g-3">
                <?php foreach ($reviews as $rev): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><?= View::e($rev['client_name']) ?></span>
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-0"><?= View::e($rev['comment'] ?? 'Оценка без текста') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
