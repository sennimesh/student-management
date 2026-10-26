<?php

require_once __DIR__ . '/includes/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$departments = ['IT', 'Physical Science'];
$years = [1, 2, 3, 4];

$values = [
    'student_id' => '',
    'student_name' => '',
    'email' => '',
    'department' => '',
    'academic_year' => ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($values) as $field) {
        $input = $_POST[$field] ?? '';

        $values[$field] = is_string($input)
            ? trim($input)
            : '';
    }

    $token = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Your form expired. Refresh the page and try again.';
    }

    if (
        $values['student_id'] === '' ||
        mb_strlen($values['student_id'], 'UTF-8') > 20
    ) {
        $errors[] = 'Student ID is required and must not exceed 20 characters.';
    }

    if (
        $values['student_name'] === '' ||
        mb_strlen($values['student_name'], 'UTF-8') > 100
    ) {
        $errors[] = 'Student name is required and must not exceed 100 characters.';
    }

    if (
        !filter_var($values['email'], FILTER_VALIDATE_EMAIL) ||
        strlen($values['email']) > 150
    ) {
        $errors[] = 'Enter a valid email address, up to 150 characters.';
    }

    if (!in_array($values['department'], $departments, true)) {
        $errors[] = 'Select a valid department.';
    }

    $year = filter_var(
        $values['academic_year'],
        FILTER_VALIDATE_INT
    );

    if (!in_array($year, $years, true)) {
        $errors[] = 'Select a valid academic year.';
    }

    if ($errors === []) {
        try {
            $stmt = $conn->prepare(
                'INSERT INTO students
                    (student_id, student_name, email,
                     department, academic_year)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'ssssi',
                $values['student_id'],
                $values['student_name'],
                $values['email'],
                $values['department'],
                $year
            );

            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] =
                'Student registered successfully.';

            header('Location: students.php', true, 303);
            exit;

        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                $errors[] = 'This Student ID or email already exists.';
            } else {
                error_log($exception->getMessage());
                $errors[] = 'Unable to register the student. Please try again.';
            }
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

    <title>Register Student</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Student Management</h2>

        <nav aria-label="Main navigation">
            <a href="dashboard.php">Dashboard</a>
            <a href="students.php">Manage Students</a>

            <a href="add_student.php" aria-current="page">
                Add Student
            </a>

            <form action="logout.php" method="post"
                  class="logout-form">
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <button type="submit" class="logout-button">
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Register Student</h1>

        <p class="welcome">
            Enter the student's details below.
        </p>

        <?php if ($errors !== []): ?>
            <div class="form-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="add_student.php" method="post"
              class="student-form">

            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['csrf_token']) ?>">

            <label for="student_id">Student ID</label>
            <input
                type="text"
                id="student_id"
                name="student_id"
                maxlength="20"
                value="<?= e($values['student_id']) ?>"
                required
            >

            <label for="student_name">Student Name</label>
            <input
                type="text"
                id="student_name"
                name="student_name"
                maxlength="100"
                value="<?= e($values['student_name']) ?>"
                required
            >

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                value="<?= e($values['email']) ?>"
                required
            >

            <label for="department">Department</label>
            <select id="department" name="department" required>
                <option value="">Select department</option>

                <?php foreach ($departments as $department): ?>
                    <option
                        value="<?= e($department) ?>"
                        <?= $values['department'] === $department
                            ? 'selected' : '' ?>
                    >
                        <?= e($department) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="academic_year">Academic Year</label>
            <select id="academic_year"
                    name="academic_year" required>
                <option value="">Select academic year</option>

                <?php foreach ($years as $option): ?>
                    <option
                        value="<?= $option ?>"
                        <?= $values['academic_year'] === (string) $option
                            ? 'selected' : '' ?>
                    >
                        Year <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="button">
                    Register Student
                </button>

                <a href="students.php" class="button secondary">
                    Cancel
                </a>
            </div>
        </form>
    </main>

</body>
</html>