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

$successMessage = (string) (
    $_SESSION['success_message'] ?? ''
);

unset($_SESSION['success_message']);

// Read all student records into an array.
$students = [];

try {
    $result = $conn->query(
        'SELECT id, student_id, student_name,
                email, department, academic_year
         FROM students
         ORDER BY id DESC'
    );

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $result->free();

} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());

    http_response_code(500);
    exit('Unable to load student records.');
}

$conn->close();

$totalStudents = count($students);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Records</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Student Management</h2>

        <nav aria-label="Main navigation">
            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="students.php" aria-current="page">
                Manage Students
            </a>

            <a href="add_student.php">
                Add Student
            </a>

            <form
                action="logout.php"
                method="post"
                class="logout-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <button
                    type="submit"
                    class="logout-button"
                >
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Student Records</h1>

        <p class="welcome">
            Registered students:
            <strong><?= $totalStudents ?></strong>
        </p>

        <?php if ($successMessage !== ''): ?>
            <p class="success-message" role="status">
                <?= e($successMessage) ?>
            </p>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Student ID</th>
                        <th scope="col">Student Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Department</th>
                        <th scope="col">Academic Year</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($students === []): ?>

                        <tr>
                            <td colspan="7" class="empty-state">
                                No students registered yet.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($students as $student): ?>

                            <tr>
                                <td>
                                    <?= (int) $student['id'] ?>
                                </td>

                                <td>
                                    <?= e((string) $student['student_id']) ?>
                                </td>

                                <td>
                                    <?= e((string) $student['student_name']) ?>
                                </td>

                                <td>
                                    <?= e((string) $student['email']) ?>
                                </td>

                                <td>
                                    <?= e((string) $student['department']) ?>
                                </td>

                                <td>
                                    <?= (int) $student['academic_year'] ?>
                                </td>

                                <td>
                                    <a
                                        href="edit_student.php?id=<?= (int) $student['id'] ?>"
                                        class="button"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete_student.php?id=<?= (int) $student['id'] ?>"
                                        class="button danger"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>