<?php

require_once __DIR__ . '/includes/auth.php';
requireLogin();

// Accept logout requests through POST only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Please use the Logout button.');
}

// Validate the form token.
$token = is_string($_POST['csrf_token'] ?? null)
    ? $_POST['csrf_token']
    : '';

if (!hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('Invalid request. Refresh the page and try again.');
}

// Clear the session variables.
$_SESSION = [];

// Expire the current session cookie.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// Remove the server-side session data.
session_destroy();

// Redirect to the login page using a GET request.
header('Location: login.php', true, 303);
exit;