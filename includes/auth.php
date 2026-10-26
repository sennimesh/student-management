<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name('STUDENTMGMTSESSID');

    $started = session_start([
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' =>
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
    ]);

    if (!$started) {
        http_response_code(500);
        exit('Unable to start the session.');
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}