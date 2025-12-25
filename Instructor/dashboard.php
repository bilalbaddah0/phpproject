<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Course.php';

requireRole(ROLE_INSTRUCTOR);

$courseModel = new Course();
$courses = $courseModel->getCoursesByInstructor($_SESSION['user_id']);

// Calculate statistics
$totalCourses = count($courses);
$publishedCourses = count(array_filter($courses, function($c) { return $c['status'] === 'published'; }));
$totalEnrollments = array_sum(array_column($courses, 'enrollment_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - <?php echo SITE_NAME; ?></title>
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
                    <li>
                        <form action="../controllers/AuthController.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
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
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalCourses - $publishedCourses; ?></div>
                <div class="stat-label">Draft Courses</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">My Courses</h2>
            </div>

            <?php if (empty($courses)): ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    You haven't created any courses yet. 
                    <a href="create_course.php" style="color: var(--primary-color);">Create your first course</a>
                </p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Enrollments</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['category_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $course['status'] === 'published' ? 'success' : 
                                            ($course['status'] === 'draft' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $course['approval_status'] === 'approved' ? 'success' : 
                                            ($course['approval_status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($course['approval_status']); ?>
                                    </span>
                                    <?php if ($course['approval_status'] === 'rejected' && $course['rejection_reason']): ?>
                                        <span style="cursor: help; font-size: 0.875rem;" title="<?php echo htmlspecialchars($course['rejection_reason']); ?>">ℹ️</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $course['enrollment_count']; ?> students</td>
                                <td><?php echo formatDate($course['created_at']); ?></td>
                                <td>
                                    <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="manage_content.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-secondary">Content</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>