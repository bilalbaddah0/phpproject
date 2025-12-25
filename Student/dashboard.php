<?php
session_start();

require_once __DIR__ . '/../Shared/db_connection.php';

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'student') {
    header('Location: ../Shared/login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch enrolled courses with instructor name and enrollment id
$stmt = $pdo->prepare("SELECT e.enrollment_id, e.course_id, 
    c.title, c.description, c.level, c.price, u.full_name AS instructor_name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    LEFT JOIN users u ON c.instructor_id = u.user_id 
    WHERE e.student_id = ?");
$stmt->execute([$student_id]);
$enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine completion state for each enrollment via student_course_status table
$courseIds = array_map(fn($c) => $c['course_id'], $enrolledCourses);
$placeholders = $courseIds ? implode(',', array_fill(0, count($courseIds), '?')) : 'NULL';
$statusMap = [];
if (!empty($courseIds)) {
    $stmt = $pdo->prepare("SELECT course_id, is_completed FROM student_course_status WHERE student_id = ? AND course_id IN ($placeholders)");
    $params = array_merge([$student_id], $courseIds);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $statusMap[$r['course_id']] = (int)$r['is_completed'];
    }
}

foreach ($enrolledCourses as &$course) {
    $course['is_completed'] = isset($statusMap[$course['course_id']]) && $statusMap[$course['course_id']] == 1;
}
unset($course);

// Calculate statistics
$totalCourses = count($enrolledCourses);
$completedCourses = count(array_filter($enrolledCourses, fn($c) => $c['is_completed']));
$inProgressCourses = $totalCourses - $completedCourses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">LMS</a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">My Learning</a></li>
                    <li><a href="browse.php">Browse Courses</a></li>
                    <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
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
        <h1>My Learning Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalCourses; ?></div>
                <div class="stat-label">Total Enrolled Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $inProgressCourses; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completedCourses; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">My Courses</h2>
            </div>

            <?php if (empty($enrolledCourses)): ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    You haven't enrolled in any courses yet. 
                    <a href="browse.php" style="color: var(--primary-color);">Browse available courses</a>
                </p>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="course-card">
                            <div class="course-thumbnail">
                                📚
                            </div>
                            <div class="course-body">
                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-instructor">👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                                
                                <div>
                                    <div style="display:flex; gap:0.5rem; align-items:center;">
                                        <?php if ($course['is_completed']): ?>
                                            <span class="badge badge-success">Completed</span>
                                            <form method="POST" action="toggle_course_status.php" style="margin:0 0 0 0; display:inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                <input type="hidden" name="completed" value="0">
                                                <button type="submit" class="btn btn-sm btn-outline">Mark as not done</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-primary">In Progress</span>
                                            <form method="POST" action="toggle_course_status.php" style="margin:0 0 0 0; display:inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                <input type="hidden" name="completed" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary">Mark as done</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>