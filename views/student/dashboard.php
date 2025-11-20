<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Course.php';
require_once __DIR__ . '/../../models/Enrollment.php';

requireRole(ROLE_STUDENT);

$enrollment = new Enrollment();
$enrolledCourses = $enrollment->getStudentCourses($_SESSION['user_id']);

// Calculate statistics
$totalCourses = count($enrolledCourses);
$completedCourses = count(array_filter($enrolledCourses, function($course) {
    return $course['enrollment_status'] === 'completed';
}));
$inProgressCourses = $totalCourses - $completedCourses;
$avgProgress = $totalCourses > 0 ? array_sum(array_column($enrolledCourses, 'progress_percentage')) / $totalCourses : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">My Learning</a></li>
                    <li><a href="browse.php">Browse Courses</a></li>
                    <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                    <li>
                        <form action="../../controllers/AuthController.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="logout">
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
                                    <span class="badge badge-<?php echo $course['enrollment_status'] === 'completed' ? 'success' : 'primary'; ?>">
                                        <?php echo ucfirst($course['enrollment_status']); ?>
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
