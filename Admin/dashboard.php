<?php
// Lightweight admin dashboard: use session + PDO (no models required)
session_start();

// Basic admin check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Shared/login.php');
    exit;
}

require_once __DIR__ . '/../Shared/db_connection.php';

// Get users
$stmt = $pdo->query("SELECT user_id, full_name, role, admin_status, instructor_status FROM users ORDER BY user_id DESC");
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get courses with instructor name and enrollment counts
$stmt = $pdo->query("SELECT c.course_id, c.title, u.full_name AS instructor_name, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.user_id
    ORDER BY c.course_id DESC");
$allCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStudents = count(array_filter($allUsers, fn($u) => isset($u['role']) && $u['role'] === 'student'));
$totalInstructors = count(array_filter($allUsers, fn($u) => isset($u['role']) && $u['role'] === 'instructor'));
$totalCourses = count($allCourses);
$totalUsers = count($allUsers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">LMS</a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="course_approvals.php">Approvals</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><span>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                    <li>
                        <form action="../Shared/logout.php" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <h1>Admin Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalInstructors; ?></div>
                <div class="stat-label">Instructors</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalCourses; ?></div>
                <div class="stat-label">Published Courses</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Users</h2>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recentUsers = array_slice($allUsers, 0, 5);
                        foreach ($recentUsers as $user): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span></td>
                                <td>
                                    <?php
                                        $status = 'active';
                                        if ($user['role'] === 'admin') {
                                            $status = $user['admin_status'] ?? 'pending';
                                        } elseif ($user['role'] === 'instructor') {
                                            $status = $user['instructor_status'] ?? 'pending';
                                        }
                                    ?>
                                    <span class="badge badge-<?php echo ($status === 'active' || $status === 'accepted') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><?php echo isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="users.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem;">View All Users</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Courses</h2>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Enrollments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recentCourses = array_slice($allCourses, 0, 5);
                        foreach ($recentCourses as $course): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo isset($course['enrollment_count']) ? (int)$course['enrollment_count'] : 0; ?> students</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="courses.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem;">View All Courses</a>
            </div>
        </div>
    </div>
</body>
</html>