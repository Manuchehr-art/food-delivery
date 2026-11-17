<?php
use App\Core\View;
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                <div class="text-center mb-5">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                        О проекте «Болаззат»
                    </span>
                    <h2 class="fw-bold">Автоматизированная система доставки еды</h2>
                    <p class="text-muted">Системаи автоматикунонии раванди фармоиш ва дастраскунии ғизо дар ш. Душанбе</p>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100">
                            <i class="bi bi-layers-fill fs-1 text-primary mb-2 d-block"></i>
                            <h5 class="fw-bold">Архитектура MVC</h5>
                            <p class="small text-muted mb-0">Чёткое разделение модели данных, бизнес-логики и пользовательского интерфейса (Model-View-Controller).</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100">
                            <i class="bi bi-database-fill-check fs-1 text-success mb-2 d-block"></i>
                            <h5 class="fw-bold">MySQL & phpMyAdmin</h5>
                            <p class="small text-muted mb-0">10 нормализованных реляционных таблиц (до 3НФ) с внешними ключами и ограничениями целостности.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100">
                            <i class="bi bi-people-fill fs-1 text-warning mb-2 d-block"></i>
                            <h5 class="fw-bold">4 Роли пользователей</h5>
                            <p class="small text-muted mb-0">Клиент (Client), Менеджер (Manager), Курьер (Courier) и Администратор (Admin) с разграничением прав.</p>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-3"><i class="bi bi-arrow-repeat text-danger me-2"></i>Жизненный цикл заказа (Order Status)</h4>
                <div class="p-4 bg-light rounded-3 mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-center">
                        <div class="p-2 bg-white rounded shadow-sm flex-fill">
                            <span class="badge bg-primary">new</span>
                            <div class="small fw-bold mt-1">Новый (Нав)</div>
                            <div class="text-muted" style="font-size:0.75rem;">Оформлен клиентом</div>
                        </div>
                        <i class="bi bi-arrow-right text-muted fs-4"></i>
                        <div class="p-2 bg-white rounded shadow-sm flex-fill">
                            <span class="badge bg-info text-dark">accepted</span>
                            <div class="small fw-bold mt-1">Принят (Қабул)</div>
                            <div class="text-muted" style="font-size:0.75rem;">Подтверждён менеджером</div>
                        </div>
                        <i class="bi bi-arrow-right text-muted fs-4"></i>
                        <div class="p-2 bg-white rounded shadow-sm flex-fill">
                            <span class="badge bg-warning text-dark">cooking</span>
                            <div class="small fw-bold mt-1">Готовится (Дар ошхона)</div>
                            <div class="text-muted" style="font-size:0.75rem;">На кухне ресторана</div>
                        </div>
                        <i class="bi bi-arrow-right text-muted fs-4"></i>
                        <div class="p-2 bg-white rounded shadow-sm flex-fill">
                            <span class="badge bg-purple text-white">on_the_way</span>
                            <div class="small fw-bold mt-1">В пути (Дар роҳ)</div>
                            <div class="text-muted" style="font-size:0.75rem;">Курьер везёт клиенту</div>
                        </div>
                        <i class="bi bi-arrow-right text-muted fs-4"></i>
                        <div class="p-2 bg-white rounded shadow-sm flex-fill">
                            <span class="badge bg-success">delivered</span>
                            <div class="small fw-bold mt-1">Доставлен (Дастрас)</div>
                            <div class="text-muted" style="font-size:0.75rem;">Оплата и вручение</div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-3"><i class="bi bi-wallet2 text-success me-2"></i>Местные способы оплаты в Таджикистане</h4>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <i class="bi bi-cash-stack fs-2 text-success mb-1 d-block"></i>
                            <div class="fw-bold">Наличные (Нақд)</div>
                            <div class="small text-muted">При получении курьеру</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <i class="bi bi-qr-code-scan fs-2 text-primary mb-1 d-block"></i>
                            <div class="fw-bold">Alif Mobi</div>
                            <div class="small text-muted">Оплата по QR коду</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <i class="bi bi-credit-card-2-front fs-2 text-warning mb-1 d-block"></i>
                            <div class="fw-bold">Dushanbe City</div>
                            <div class="small text-muted">Карты и кошелёк DC</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <i class="bi bi-credit-card fs-2 text-info mb-1 d-block"></i>
                            <div class="fw-bold">Банковские карты</div>
                            <div class="small text-muted">Корти миллӣ / Visa</div>
                        </div>
                    </div>
                </div>

                <div class="text-center pt-3 border-top">
                    <a href="/" class="btn btn-primary rounded-pill px-4 py-2 me-2">
                        <i class="bi bi-cart me-1"></i> Перейти к заказу
                    </a>
                    <a href="/login" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                        <i class="bi bi-lock me-1"></i> Войти в личный кабинет
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
