<?php

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$successMessage = (string) (
    $_SESSION['success_message'] ?? ''
);

unset($_SESSION['success_message']);

require_once __DIR__ . '/config/database.php';

$result = $conn->query(
    'SELECT id, student_id, student_name, email,
            department, academic_year
     FROM students
     ORDER BY id DESC'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

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

            <form action="logout.php" method="post" class="logout-form">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

            <button type="submit" class="logout-button">
                Logout
            </button>
        </form>
                </nav>
            </aside>

    <main class="main-content">
        <h1>Student Records</h1>

        <?php if ($successMessage !== ''): ?>
    <p class="success-message" role="status">
        <?= htmlspecialchars(
            $successMessage,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>
<?php endif; ?>

        <p class="welcome">
            View registered student information.
        </p>

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
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result->num_rows === 0): ?>

                        <tr>
                            <td colspan="6" class="empty-state">
                                No students registered yet.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php while ($student = $result->fetch_assoc()): ?>

                            <tr>
                                <td>
                                    <?= (int) $student['id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $student['student_id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $student['student_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $student['email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $student['department'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $student['academic_year'] ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>

<?php
$conn->close();