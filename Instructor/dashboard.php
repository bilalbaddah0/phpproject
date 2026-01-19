<?php
session_start();
require_once __DIR__ . '/../Shared/db_connection.php';
// Ensure instructor is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'instructor') {
    header('Location: ../Shared/login.php');
    exit;
}
$instructor_id = $_SESSION['user_id'];
// Fetch courses for this instructor with category, approval, and enrollment count
$stmt = $pdo->prepare("SELECT c.course_id, c.title, c.approval_status, cat.category_name,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.instructor_id = ?");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Calculate statistics
$totalCourses = count($courses);
$publishedCourses = count(array_filter($courses, function($c) { return ($c['approval_status'] ?? '') === 'approved'; }));
$totalEnrollments = array_sum(array_map(function($c) { return (int)($c['enrollment_count'] ?? 0); }, $courses));
function formatDate($dt) {
    if (!$dt) return '-';
    $ts = strtotime($dt);
    return $ts ? date('M d, Y', $ts) : $dt;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor Dashboard - LMS</title>
<style>
/* Inlined Dashboard CSS */
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #111827;
    background-color: #F9FAFB;
}

.container { max-width: 1200px; margin:0 auto; padding:0 20px; }

header {
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: sticky; top:0; z-index:1000;
}

.navbar { display:flex; justify-content:space-between; align-items:center; padding:1rem 0; }

.logo { font-size:1.5rem; font-weight:bold; color:#4F46E5; text-decoration:none; }

.nav-links { display:flex; list-style:none; gap:2rem; align-items:center; }

.nav-links a { text-decoration:none; color:#111827; font-weight:500; transition:color 0.3s; }

.nav-links a:hover { color:#4F46E5; }

.btn {
    display:inline-block;
    padding:0.75rem 1.5rem;
    border:none;
    border-radius:0.5rem;
    font-size:1rem;
    font-weight:500;
    cursor:pointer;
    text-decoration:none;
    text-align:center;
    transition:all 0.3s;
}

.btn-primary { background-color:#4F46E5; color:white; }
.btn-primary:hover { background-color:#4338CA; }

.btn-outline {
    background-color:transparent;
    border:2px solid #4F46E5;
    color:#4F46E5;
}
.btn-outline:hover { background-color:#4F46E5; color:white; }

.btn-sm { padding:0.5rem 1rem; font-size:0.875rem; }

.card {
    background:white;
    border-radius:0.75rem;
    padding:1.5rem;
    box-shadow:0 1px 3px rgba(0,0,0,0.1);
    margin-bottom:1.5rem;
}

.card-header { border-bottom:1px solid #E5E7EB; padding-bottom:1rem; margin-bottom:1rem; }
.card-title { font-size:1.5rem; font-weight:600; color:#111827; }

.table {
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:0.75rem;
    overflow:hidden;
    box-shadow:0 1px 3px rgba(0,0,0,0.1);
}

.table th, .table td { padding:1rem; text-align:left; border-bottom:1px solid #E5E7EB; }
.table th { background-color:#F9FAFB; font-weight:600; color:#111827; }
.table tr:hover { background-color:#F9FAFB; }

.stats-grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
    gap:1.5rem;
    margin-bottom:2rem;
}

.stat-card {
    background:white;
    padding:1.5rem;
    border-radius:0.75rem;
    box-shadow:0 1px 3px rgba(0,0,0,0.1);
}

.stat-value { font-size:2rem; font-weight:bold; color:#4F46E5; }
.stat-label { color:#6B7280; font-size:0.875rem; margin-top:0.5rem; }

.badge {
    display:inline-block;
    padding:0.25rem 0.75rem;
    border-radius:1rem;
    font-size:0.75rem;
    font-weight:600;
}

.badge-success { background-color:#D1FAE5; color:#065F46; }
.badge-warning { background-color:#FEF3C7; color:#92400E; }
.badge-danger { background-color:#FEE2E2; color:#991B1B; }

.text-secondary { color:#6B7280; }
a { color:#4F46E5; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>
<header>
    <div class="container">
        <nav class="navbar">
            <a href="dashboard.php" class="logo">LMS</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="courses.php">My Courses</a></li>
                <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                <li>
                    <form action="../Shared/logout.php" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h1>Instructor Dashboard</h1>
        <a href="create_course.php" class="btn btn-primary">+ Create New Course</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $totalCourses; ?></div>
            <div class="stat-label">Total Courses</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $publishedCourses; ?></div>
            <div class="stat-label">Published Courses</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $totalEnrollments; ?></div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Courses</h2>
        </div>

        <?php if (empty($courses)): ?>
            <p style="text-align:center; color:#6B7280; padding:2rem;">
                You haven't created any courses yet. 
                <a href="create_course.php">Create your first course</a>
            </p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Title</th>
                        <th>Category</th>
                        <th>Approval</th>
                        <th>Enrollments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($course['title'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($course['category_name'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo ($course['approval_status'] ?? '') === 'approved' ? 'success' : 
                                        (($course['approval_status'] ?? '') === 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo htmlspecialchars(ucfirst($course['approval_status'] ?? '-')); ?>
                                </span>
                            </td>
                            <td><?php echo (int)($course['enrollment_count'] ?? 0); ?> students</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
