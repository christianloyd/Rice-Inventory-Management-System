<?php

return [
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Manila',
    'session_name' => $_ENV['SESSION_NAME'] ?? 'risms_session',
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/risms/public',
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 1800),
    'login_max_attempts' => (int)($_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5),
    'login_lockout_minutes' => (int)($_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15),
];
