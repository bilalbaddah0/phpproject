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

// Compute progress for each enrollment
foreach ($enrolledCourses as &$course) {
    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $totalStmt->execute([$course['course_id']]);
    $totalLessons = (int)$totalStmt->fetchColumn();

    if ($totalLessons > 0) {
        $completedStmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE enrollment_id = ? AND is_completed = 1");
        $completedStmt->execute([$course['enrollment_id']]);
        $completed = (int)$completedStmt->fetchColumn();
        $course['progress_percentage'] = round(100 * $completed / $totalLessons, 1);
    } else {
        $course['progress_percentage'] = 0;
    }
}
unset($course);

// Calculate statistics
$totalCourses = count($enrolledCourses);
$completedCourses = count(array_filter($enrolledCourses, function($course) {
    return isset($course['progress_percentage']) && ((float)$course['progress_percentage'] >= 100);
}));
$inProgressCourses = $totalCourses - $completedCourses;
$avgProgress = $totalCourses > 0 ? array_sum(array_map(fn($c) => (float)($c['progress_percentage'] ?? 0), $enrolledCourses)) / $totalCourses : 0;
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
            <div class="stat-card">
                <div class="stat-value"><?php echo round($avgProgress, 1); ?>%</div>
                <div class="stat-label">Average Progress</div>
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
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.875rem;">Progress</span>
                                        <span style="font-size: 0.875rem; font-weight: 600;">
                                            <?php echo round($course['progress_percentage'], 1); ?>%
                                        </span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                    </div>
                                </div>

                                <div class="course-meta">
                                    <?php $enroll_status = ((float)($course['progress_percentage'] ?? 0) >= 100) ? 'completed' : 'in progress'; ?>
                                    <span class="badge badge-<?php echo $enroll_status === 'completed' ? 'success' : 'primary'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($enroll_status)); ?>
                                    </span>
                                    <a href="course_view.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-primary">
                                        <?php echo $course['progress_percentage'] > 0 ? 'Continue' : 'Start'; ?>
                                    </a>
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