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

// Validate the internal record ID.
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

// Retrieve the student for confirmation.
$stmt = $conn->prepare(
    'SELECT student_id, student_name, email
     FROM students
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$stmt->bind_result(
    $studentId,
    $studentName,
    $email
);

$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    $conn->close();
    http_response_code(404);
    exit('Student not found.');
}

$error = '';

// Delete only after the confirmation form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Your form expired. Refresh the page and try again.';

    } else {
        try {
            $stmt = $conn->prepare(
                'DELETE FROM students WHERE id = ?'
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();

            $deletedRows = $stmt->affected_rows;
            $stmt->close();

            if ($deletedRows !== 1) {
                http_response_code(404);
                exit('Student record is no longer available.');
            }

            $_SESSION['success_message'] =
                'Student deleted successfully.';

            header('Location: students.php', true, 303);
            exit;

        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = 'Unable to delete the student. Please try again.';
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

    <title>Delete Student</title>

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
        <h1>Delete Student</h1>

        <p class="welcome">
            Review the student details before deleting.
        </p>

        <?php if ($error !== ''): ?>
            <div class="form-errors" role="alert">
                <ul>
                    <li><?= e($error) ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <article class="delete-card">
            <h2>Confirm Deletion</h2>

            <p class="delete-warning">
                This will permanently delete the student record.
            </p>

            <dl class="student-details">
                <dt>Student ID</dt>
                <dd><?= e((string) $studentId) ?></dd>

                <dt>Student Name</dt>
                <dd><?= e((string) $studentName) ?></dd>

                <dt>Email</dt>
                <dd><?= e((string) $email) ?></dd>
            </dl>

            <form
                id="delete-form"
                action="delete_student.php?id=<?= $id ?>"
                method="post"
            >
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-actions">
                    <button
                        type="submit"
                        id="confirm-delete"
                        class="button danger"
                    >
                        Delete Student
                    </button>

                    <a href="students.php"
                       class="button secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </article>
    </main>

    <script>
        const deleteForm = document.getElementById('delete-form');
        const deleteButton = document.getElementById('confirm-delete');

        deleteForm.addEventListener('submit', function () {
            deleteButton.disabled = true;
            deleteButton.textContent = 'Deleting...';
        });
    </script>

</body>
</html>