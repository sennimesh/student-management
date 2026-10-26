<?php

require_once __DIR__ . '/includes/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

// Get the total number of students.
$result = $conn->query(
    'SELECT COUNT(*) AS total FROM students'
);

$row = $result->fetch_assoc();
$totalStudents = (int) $row['total'];

$conn->close();

// Get the logged-in administrator's name.
$userName = (string) (
    $_SESSION['user_name'] ?? 'Administrator'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Management Dashboard</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Student Management</h2>

        <nav aria-label="Main navigation">

            <a href="dashboard.php" aria-current="page">
                Dashboard
            </a>

            <a href="students.php">
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
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
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
        <h1>Admin Dashboard</h1>

        <p class="welcome">
            Welcome,
            <?= htmlspecialchars(
                $userName,
                ENT_QUOTES,
                'UTF-8'
            ) ?>!
        </p>

        <section
            class="cards"
            aria-label="Dashboard summary"
        >
            <article class="card">
                <h2>Total Students</h2>

                <p class="total">
                    <?= $totalStudents ?>
                </p>

                <p>Number of registered students.</p>
            </article>

            <article class="card">
                <h2>Student Records</h2>

                <p>View registered student information.</p>

                <a href="students.php">
                    View Students
                </a>
            </article>
        </section>
    </main>

</body>
</html>