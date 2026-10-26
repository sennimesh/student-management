<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'student-management';
$dbPort = 3306;

try {
    $conn = new mysqli(
        $dbHost,
        $dbUser,
        $dbPassword,
        $dbName,
        $dbPort
    );

    $conn->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit('Database connection failed. Check your XAMPP MySQL settings.');
}