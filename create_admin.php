<?php

// Run this setup only through the terminal.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this setup script from the terminal.');
}

// Read a value entered in the terminal.
function readSetupInput(string $prompt): string
{
    echo $prompt;

    $value = fgets(STDIN);

    if ($value === false) {
        exit("Input cancelled.\n");
    }

    return rtrim($value, "\r\n");
}

try {
    require_once __DIR__ . '/config/database.php';

    // Stop if the initial account already exists.
    $result = $conn->query(
        'SELECT id FROM users LIMIT 1'
    );

    if ($result->num_rows > 0) {
        exit("An account already exists. Setup stopped.\n");
    }

    $name = trim(
        readSetupInput('Administrator name: ')
    );

    $email = trim(
        readSetupInput('Administrator email: ')
    );

    echo "Password input will be visible in this terminal.\n";

    $password = readSetupInput('Password: ');
    $confirmation = readSetupInput('Confirm password: ');

    if ($name === '') {
        exit("Administrator name is required.\n");
    }

    if (
        strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        exit("Enter a valid email address, up to 150 characters.\n");
    }

    if (strlen($password) < 12 || strlen($password) > 72) {
        exit("Use a password between 12 and 72 bytes.\n");
    }

    if ($password !== $confirmation) {
        exit("Passwords do not match. Run the script again.\n");
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $conn->prepare(
        'INSERT INTO users (name, email, password)
         VALUES (?, ?, ?)'
    );

    $stmt->bind_param(
        'sss',
        $name,
        $email,
        $passwordHash
    );

    $stmt->execute();

    echo "\nAdministrator created successfully!\n";
    echo "Login email: " . $email . "\n";

    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    exit("Database error: " . $e->getMessage() . PHP_EOL);
}