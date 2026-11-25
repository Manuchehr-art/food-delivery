<?php
// config/config.example.php — шаблон настроек.
// Скопируйте в config/config.php и укажите реальные данные.
// config.php НЕ попадает в Git (он в .gitignore).
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'foodgo',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ],
    'debug' => (getenv('APP_DEBUG') ?: '1') === '1',
    'base_url' => getenv('BASE_URL') ?: '',
];