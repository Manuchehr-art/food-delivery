<?php
use App\Core\View;
?>

<div class="row g-4">
    <!-- Учётные записи (users) -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i>Учётные записи (users)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Последний вход</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="text-muted"><?= $u['id'] ?></td>
                                <td>
                                    <strong class="text-dark"><?= View::e($u['username']) ?></strong>
                                    <?php if (!empty($u['first_name'])): ?>
                                        <div class="small text-muted"><?= View::e($u['last_name']) ?> <?= View::e($u['first_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $roleBadges = [
                                        'admin' => 'bg-dark text-white',
                                        'manager' => 'bg-primary text-white',
                                        'courier' => 'bg-success text-white',
                                        'client' => 'bg-warning text-dark',
                                    ];
                                    $badge = $roleBadges[$u['role']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= View::e($u['role']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $u['is_active'] ? 'bg-success bg-opacity-25 text-success' : 'bg-danger bg-opacity-25 text-danger' ?>">
                                        <?= $u['is_active'] ? 'Активен' : 'Блок' ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= View::e($u['last_login_at'] ?? 'Никогда') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Клиенты (clients) -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-people text-warning me-2"></i>Карточки клиентов (clients)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>ФИО</th>
                            <th>Телефон</th>
                            <th>E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $cl): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><?= View::e($cl['last_name']) ?> <?= View::e($cl['first_name']) ?></div>
                                    <small class="text-muted"><?= View::e($cl['middle_name'] ?? '') ?></small>
                                </td>
                                <td>
                                    <a href="tel:<?= View::e($cl['phone']) ?>" class="small text-decoration-none">
                                        +992 <?= View::e($cl['phone']) ?>
                                    </a>
                                </td>
                                <td class="small text-muted"><?= View::e($cl['email'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
