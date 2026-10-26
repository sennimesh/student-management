<?php

require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null)
        ? trim($_POST['email'])
        : '';

    $password = is_string($_POST['password'] ?? null)
        ? $_POST['password']
        : '';

    $token = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Please submit the form again.';

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        $password === '' ||
        strlen($password) > 72
    ) {
        $error = 'Invalid email or password.';

    } else {
        try {
            $stmt = $conn->prepare(
                'SELECT id, name, password
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $stmt->bind_result(
                $userId,
                $userName,
                $storedHash
            );

            $found = $stmt->fetch();
            $stmt->close();

            if (
                $found &&
                password_verify($password, $storedHash)
            ) {
                if (!session_regenerate_id(true)) {
                    http_response_code(500);
                    exit('Unable to complete login.');
                }

                $_SESSION = [
                    'user_id' => (int) $userId,
                    'user_name' => $userName,
                    'csrf_token' => bin2hex(random_bytes(32))
                ];

                header('Location: dashboard.php');
                exit;
            }

            $error = 'Invalid email or password.';

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = 'Unable to log in. Please try again.';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Administrator Login</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">

    <main class="login-card">
        <h1>Administrator Login</h1>
        <p class="welcome">Student Management System</p>

        <?php if ($error !== ''): ?>
            <p class="login-error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                autocomplete="username"
                value="<?= htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Log In</button>
        </form>
    </main>

</body>
</html>