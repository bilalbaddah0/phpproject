<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Course.php';
require_once __DIR__ . '/../../models/Enrollment.php';

requireRole(ROLE_STUDENT);

$course_id = intval($_GET['id'] ?? 0);
$courseModel = new Course();
$enrollmentModel = new Enrollment();

// Check if enrolled
if (!$enrollmentModel->isEnrolled($_SESSION['user_id'], $course_id)) {
    $_SESSION['error'] = 'You must enroll in this course first.';
    redirect('views/student/browse.php');
}

$course = $courseModel->getCourseById($course_id);
$enrollment = $enrollmentModel->getEnrollment($_SESSION['user_id'], $course_id);
$modules = $courseModel->getCourseModules($course_id);
$progress = $enrollmentModel->getLessonProgress($enrollment['enrollment_id']);

// Load lessons for each module
foreach ($modules as &$module) {
    $module['lessons'] = $courseModel->getModuleLessons($module['module_id']);
}

// Handle lesson completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_lesson'])) {
    $lesson_id = intval($_POST['lesson_id']);
    if ($enrollmentModel->markLessonComplete($enrollment['enrollment_id'], $lesson_id)) {
        $_SESSION['success'] = 'Lesson marked as complete!';
        redirect('views/student/course_view.php?id=' . $course_id);
    }
}

$selected_lesson_id = intval($_GET['lesson'] ?? 0);
$selected_lesson = null;
if ($selected_lesson_id > 0) {
    $selected_lesson = $courseModel->getLessonById($selected_lesson_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo SITE_NAME; ?></title>
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

    <div style="display: flex; min-height: calc(100vh - 80px);">
        <!-- Course Sidebar -->
        <div style="width: 350px; background: white; padding: 1.5rem; box-shadow: var(--shadow); overflow-y: auto;">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($course['title']); ?></h2>
                <p style="font-size: 0.875rem; color: var(--text-secondary);">
                    👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?>
                </p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.875rem; font-weight: 600;">Course Progress</span>
                    <span style="font-size: 0.875rem; font-weight: 600;">
                        <?php echo round($enrollment['progress_percentage'], 1); ?>%
                    </span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $enrollment['progress_percentage']; ?>%"></div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Modules and Lessons -->
            <?php foreach ($modules as $module): ?>
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--primary-color);">
                        <?php echo htmlspecialchars($module['module_title']); ?>
                    </h3>
                    
                    <?php foreach ($module['lessons'] as $lesson): ?>
                        <?php 
                        $isCompleted = isset($progress[$lesson['lesson_id']]) && $progress[$lesson['lesson_id']]['is_completed'];
                        $isActive = $lesson['lesson_id'] == $selected_lesson_id;
                        ?>
                        <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $lesson['lesson_id']; ?>" 
                           style="display: block; padding: 0.75rem; margin-bottom: 0.5rem; 
                                  background: <?php echo $isActive ? 'var(--primary-color)' : 'var(--light-color)'; ?>; 
                                  color: <?php echo $isActive ? 'white' : 'var(--text-primary)'; ?>;
                                  border-radius: 0.5rem; text-decoration: none; 
                                  transition: all 0.3s;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 1.25rem;">
                                    <?php echo $isCompleted ? '✅' : '⭕'; ?>
                                </span>
                                <span style="flex: 1; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($lesson['lesson_title']); ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Main Content Area -->
        <div style="flex: 1; padding: 2rem; overflow-y: auto;">
            <?php if ($selected_lesson): ?>
                <div class="card">
                    <h1 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($selected_lesson['lesson_title']); ?></h1>
                    
                    <?php if ($selected_lesson['content_type'] === 'video'): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <?php if (strpos($selected_lesson['content_url'], 'youtube.com') !== false || strpos($selected_lesson['content_url'], 'youtu.be') !== false): ?>
                                <?php
                                // Extract YouTube video ID
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?\/]+)/', $selected_lesson['content_url'], $matches);
                                $video_id = $matches[1] ?? '';
                                ?>
                                <iframe width="100%" height="500" 
                                        src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
                                        frameborder="0" allowfullscreen style="border-radius: 0.5rem;"></iframe>
                            <?php else: ?>
                                <video controls width="100%" style="border-radius: 0.5rem;">
                                    <source src="<?php echo htmlspecialchars($selected_lesson['content_url']); ?>">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($selected_lesson['content_type'] === 'external_link'): ?>
                        <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light-color); border-radius: 0.5rem;">
                            <p style="margin-bottom: 0.5rem;">External Resource:</p>
                            <a href="<?php echo htmlspecialchars($selected_lesson['content_url']); ?>" target="_blank" 
                               class="btn btn-primary">Open Link</a>
                        </div>
                    <?php elseif ($selected_lesson['content_type'] === 'pdf' && $selected_lesson['file_path']): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <embed src="../../<?php echo htmlspecialchars($selected_lesson['file_path']); ?>" 
                                   type="application/pdf" width="100%" height="600px" style="border-radius: 0.5rem;" />
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_lesson['content_text']): ?>
                        <div style="line-height: 1.8; margin-bottom: 1.5rem;">
                            <?php echo nl2br(htmlspecialchars($selected_lesson['content_text'])); ?>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $isCompleted = isset($progress[$selected_lesson['lesson_id']]) && $progress[$selected_lesson['lesson_id']]['is_completed'];
                    ?>
                    
                    <?php if (!$isCompleted): ?>
                        <form method="POST">
                            <input type="hidden" name="lesson_id" value="<?php echo $selected_lesson['lesson_id']; ?>">
                            <button type="submit" name="complete_lesson" class="btn btn-secondary">
                                ✓ Mark as Complete
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success">
                            ✅ You have completed this lesson!
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <h1 style="margin-bottom: 1rem;">Welcome to <?php echo htmlspecialchars($course['title']); ?></h1>
                    <p style="line-height: 1.8; margin-bottom: 1.5rem;">
                        <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                    </p>
                    
                    <div style="padding: 1.5rem; background: var(--light-color); border-radius: 0.5rem;">
                        <h3 style="margin-bottom: 1rem;">Course Information</h3>
                        <p style="margin-bottom: 0.5rem;">📚 Category: <?php echo htmlspecialchars($course['category_name']); ?></p>
                        <p style="margin-bottom: 0.5rem;">📊 Level: <?php echo ucfirst($course['level']); ?></p>
                        <p style="margin-bottom: 0.5rem;">👨‍🏫 Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                    </div>

                    <p style="margin-top: 1.5rem; color: var(--text-secondary);">
                        ← Select a lesson from the sidebar to begin learning
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
