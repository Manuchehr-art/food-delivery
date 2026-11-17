<?php
use App\Core\Csrf;
?>

<div class="mb-4">
    <a href="/admin/couriers" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Назад к списку курьеров
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h4 class="fw-bold mb-4">
                <i class="bi bi-plus-circle-fill text-primary me-2"></i>Добавление нового курьера
            </h4>

            <form action="/admin/couriers/store" method="POST">
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">ФИО курьера <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="Алиев Фарход" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Номер телефона <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">+992</span>
                        <input type="text" name="phone" class="form-control" placeholder="905551122" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Тип транспорта <span class="text-danger">*</span></label>
                    <select name="transport" class="form-select" required>
                        <option value="bike">Велосипед / Скутер (bike)</option>
                        <option value="car">Автомобиль (car)</option>
                        <option value="foot">Пеший курьер (foot)</option>
                    </select>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="/admin/couriers" class="btn btn-outline-secondary px-4 rounded-pill">Отмена</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">
                        Сохранить курьера
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
