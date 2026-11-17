<?php
use App\Core\Csrf;
use App\Core\View;
?>

<div class="container">
    <!-- Hero Секция -->
    <div class="hero-section mb-5 shadow-sm">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Быстрая доставка по Душанбе
                </span>
                <h1 class="hero-title mb-3">
                    Вкусная еда из лучших ресторанов с доставкой на дом!
                </h1>
                <p class="hero-subtitle mb-4">
                    «Болаззат» — удобная платформа для заказа свежей пиццы, сочных бургеров, плова и шашлыка. Отслеживайте заказ в реальном времени.
                </p>

                <!-- Поиск -->
                <form action="/" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Найти блюдо (пицца, бургер, плов...)" value="<?= View::e($search) ?>">
                        <button class="btn btn-primary px-4 fw-semibold" type="submit">Найти</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <div class="p-4">
                    <div style="font-size: 8rem; line-height: 1;" class="text-warning">
                        🍕🍔🥗
                    </div>
                    <div class="mt-3 badge bg-white text-dark shadow-sm px-4 py-2 rounded-pill fs-6">
                        <i class="bi bi-clock-history text-danger me-1"></i> Доставка от 25 до 45 минут
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтр по категориям -->
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="fw-bold m-0"><i class="bi bi-tag-fill text-warning me-2"></i>Категории блюд</h3>
            <?php if (!empty($currentCategory) || !empty($search)): ?>
                <a href="/" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-x-circle me-1"></i>Сбросить фильтр
                </a>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="/" class="category-pill <?= empty($currentCategory) ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i> Все меню
            </a>
            <?php foreach ($categories as $catKey => $catData): ?>
                <a href="/?category=<?= $catKey ?>" class="category-pill <?= $currentCategory === $catKey ? 'active' : '' ?>">
                    <i class="bi <?= $catData['icon'] ?>"></i> <?= $catData['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Рестораны -->
    <div class="mb-5">
        <h3 class="fw-bold mb-3"><i class="bi bi-shop text-warning me-2"></i>Популярные рестораны</h3>
        <div class="row g-4">
            <?php foreach ($restaurants as $rest): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="restaurant-card">
                        <div class="restaurant-thumb">
                            <i class="bi bi-shop-window"></i>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold m-0 text-dark"><?= View::e($rest['name']) ?></h5>
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>4.9</span>
                            </div>
                            <p class="text-muted small mb-3 flex-grow-1">
                                <i class="bi bi-geo-alt me-1 text-danger"></i><?= View::e($rest['address']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center text-muted small border-top pt-3">
                                <span><i class="bi bi-clock me-1"></i><?= View::e($rest['work_hours']) ?></span>
                                <span><i class="bi bi-bag me-1"></i>Мин. <?= View::price($rest['min_order_amount']) ?></span>
                            </div>
                            <a href="/restaurant/<?= $rest['id'] ?>" class="btn btn-outline-primary mt-3 w-100 rounded-pill fw-semibold">
                                Смотреть меню (<?= $rest['dishes_count'] ?> блюд)
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Блюда в каталоге -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold m-0"><i class="bi bi-fire text-danger me-2"></i>Блюда для заказа</h3>
            <span class="text-muted small">Найдено: <?= count($dishes) ?></span>
        </div>

        <?php if (empty($dishes)): ?>
            <div class="alert alert-light border text-center py-5">
                <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                <h5>Блюда не найдены</h5>
                <p class="text-muted mb-3">Попробуйте изменить категорию или поисковый запрос</p>
                <a href="/" class="btn btn-primary rounded-pill">Показать все блюда</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($dishes as $dish): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="dish-card">
                            <div class="dish-thumb position-relative">
                                <?php
                                $emojiMap = [
                                    'pizza' => '🍕',
                                    'burgers' => '🍔',
                                    'salads' => '🥗',
                                    'drinks' => '🥤',
                                    'desserts' => '🍰',
                                ];
                                echo $emojiMap[$dish['category']] ?? '🍽️';
                                ?>
                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75">
                                    <?= $categories[$dish['category']]['label'] ?? $dish['category'] ?>
                                </span>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?= View::e($dish['name']) ?></h6>
                                <div class="text-muted small mb-2"><i class="bi bi-shop me-1"></i><?= View::e($dish['restaurant_name']) ?></div>
                                <p class="text-muted small flex-grow-1 mb-3" style="font-size: 0.85rem;">
                                    <?= View::e($dish['description'] ?? 'Свежее и вкусное блюдо') ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <span class="dish-price text-primary"><?= View::price($dish['price']) ?></span>

                                    <form action="/cart/add" method="POST" class="m-0">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="dish_id" value="<?= $dish['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold btn-add-cart-ajax" 
                                                data-dish-id="<?= $dish['id'] ?>">
                                            <i class="bi bi-cart-plus me-1"></i> В корзину
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
