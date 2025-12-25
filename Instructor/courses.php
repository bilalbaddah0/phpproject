<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Course.php';

requireRole(ROLE_INSTRUCTOR);

$courseModel = new Course();
$courses = $courseModel->getCoursesByInstructor($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">My Courses</a></li>
                    <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>My Courses</h1>
            <a href="create_course.php" class="btn btn-primary">+ Create New Course</a>
        </div>

        <div class="card">
            <?php if (empty($courses)): ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">You have no courses yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Enrollments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['category_name']); ?></td>
                                <td><?php echo ucfirst($course['status']); ?></td>
                                <td><?php echo $course['enrollment_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>