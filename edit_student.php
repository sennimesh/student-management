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

// Validate the internal database record ID.
$rawId = $_GET['id'] ?? '';

$id = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($id === false) {
    http_response_code(400);
    exit('Invalid student record ID.');
}

$departments = ['IT', 'Physical Science'];
$years = [1, 2, 3, 4];
$errors = [];

// Retrieve the selected student's existing details.
$stmt = $conn->prepare(
    'SELECT student_id, student_name, email,
            department, academic_year
     FROM students
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$stmt->bind_result(
    $studentId,
    $studentName,
    $email,
    $department,
    $academicYear
);

$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    $conn->close();
    http_response_code(404);
    exit('Student not found.');
}

$values = [
    'student_id' => (string) $studentId,
    'student_name' => (string) $studentName,
    'email' => (string) $email,
    'department' => (string) $department,
    'academic_year' => (string) $academicYear
];

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
                'UPDATE students
                 SET student_id = ?,
                     student_name = ?,
                     email = ?,
                     department = ?,
                     academic_year = ?
                 WHERE id = ?'
            );

            $stmt->bind_param(
                'ssssii',
                $values['student_id'],
                $values['student_name'],
                $values['email'],
                $values['department'],
                $year,
                $id
            );

            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] =
                'Student details updated successfully.';

            header('Location: students.php', true, 303);
            exit;

        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                $errors[] = 'Another student already uses this Student ID or email.';
            } else {
                error_log($exception->getMessage());
                $errors[] = 'Unable to update the student. Please try again.';
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

    <title>Edit Student</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Student Management</h2>

        <nav aria-label="Main navigation">
            <a href="dashboard.php">Dashboard</a>
            <a href="students.php">Manage Students</a>
            <a href="add_student.php">Add Student</a>

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
        <h1>Edit Student</h1>

        <p class="welcome">
            Update the selected student's details.
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

        <form
            action="edit_student.php?id=<?= $id ?>"
            method="post"
            class="student-form"
        >
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

                <?php foreach ($departments as $option): ?>
                    <option
                        value="<?= e($option) ?>"
                        <?= $values['department'] === $option
                            ? 'selected' : '' ?>
                    >
                        <?= e($option) ?>
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
                    Save Changes
                </button>

                <a href="students.php" class="button secondary">
                    Cancel
                </a>
            </div>
        </form>
    </main>

</body>
</html>