<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Course.php';

requireRole(ROLE_INSTRUCTOR);

$course_id = intval($_GET['id'] ?? 0);
$courseModel = new Course();
$course = $courseModel->getCourseById($course_id);

// Verify ownership
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    redirect('Instructor/dashboard.php');
}

$modules = $courseModel->getCourseModules($course_id);

// Load lessons for each module
foreach ($modules as &$module) {
    $module['lessons'] = $courseModel->getModuleLessons($module['module_id']);
}

// Handle module creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_module'])) {
    $module_title = sanitizeInput($_POST['module_title']);
    $description = sanitizeInput($_POST['module_description']);
    
    $result = $courseModel->addModule($course_id, $module_title, $description);
    if ($result['success']) {
        $_SESSION['success'] = 'Module created successfully!';
        redirect('Instructor/manage_content.php?id=' . $course_id);
    }
}

// Handle lesson creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_lesson'])) {
    $module_id = intval($_POST['module_id']);
    $lesson_title = sanitizeInput($_POST['lesson_title']);
    $content_type = sanitizeInput($_POST['content_type']);
    $content_text = sanitizeInput($_POST['content_text'] ?? '');
    $content_url = sanitizeInput($_POST['content_url'] ?? '');
    $file_path = null;

    // Handle file upload
    if ($content_type === 'pdf' && isset($_FILES['lesson_file']) && $_FILES['lesson_file']['error'] === 0) {
        $upload_dir = __DIR__ . '/../assets/uploads/courses/';
        $file_name = time() . '_' . basename($_FILES['lesson_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['lesson_file']['tmp_name'], $target_file)) {
            $file_path = 'assets/uploads/courses/' . $file_name;
        }
    }

    $result = $courseModel->addLesson($module_id, $lesson_title, $content_type, $content_text, $content_url, $file_path);
    if ($result['success']) {
        $_SESSION['success'] = 'Lesson created successfully!';
        redirect('Instructor/manage_content.php?id=' . $course_id);
    }
}

// Handle lesson deletion
if (isset($_GET['delete_lesson'])) {
    $lesson_id = intval($_GET['delete_lesson']);
    if ($courseModel->deleteLesson($lesson_id)) {
        $_SESSION['success'] = 'Lesson deleted successfully!';
        redirect('Instructor/manage_content.php?id=' . $course_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content - <?php echo htmlspecialchars($course['title']); ?></title>
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
        <div style="margin-bottom: 2rem;">
            <a href="edit_course.php?id=<?php echo $course_id; ?>" style="color: var(--primary-color);">← Back to Course Settings</a>
            <h1 style="margin-top: 1rem;"><?php echo htmlspecialchars($course['title']); ?></h1>
            <p style="color: var(--text-secondary);">Manage course modules and lessons</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="card-title">Course Content</h2>
                        <button onclick="document.getElementById('moduleModal').style.display='block'" class="btn btn-primary">
                            + Add Module
                        </button>
                    </div>

                    <?php if (empty($modules)): ?>
                        <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            No modules yet. Create your first module to start adding content.
                        </p>
                    <?php else: ?>
                        <?php foreach ($modules as $module): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($module['module_title']); ?>
                                        </h3>
                                        <?php if ($module['description']): ?>
                                            <p style="color: var(--text-secondary); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($module['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <button onclick="showLessonModal(<?php echo $module['module_id']; ?>)" class="btn btn-sm btn-primary">
                                        + Add Lesson
                                    </button>
                                </div>

                                <?php if (empty($module['lessons'])): ?>
                                    <p style="color: var(--text-secondary); font-size: 0.875rem; padding: 1rem; background: var(--light-color); border-radius: 0.5rem;">
                                        No lessons in this module yet.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($module['lessons'] as $lesson): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--light-color); border-radius: 0.5rem; margin-bottom: 0.5rem;">
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <span style="font-size: 1.25rem;">
                                                    <?php 
                                                    echo $lesson['content_type'] === 'video' ? '🎥' : 
                                                        ($lesson['content_type'] === 'pdf' ? '📄' : 
                                                        ($lesson['content_type'] === 'external_link' ? '🔗' : '📝'));
                                                    ?>
                                                </span>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($lesson['lesson_title']); ?></strong>
                                                    <span style="color: var(--text-secondary); font-size: 0.75rem; margin-left: 0.5rem;">
                                                        (<?php echo ucfirst(str_replace('_', ' ', $lesson['content_type'])); ?>)
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="?id=<?php echo $course_id; ?>&delete_lesson=<?php echo $lesson['lesson_id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this lesson?')">
                                                Delete
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Quick Stats</h3>
                    <div style="padding: 1rem; background: var(--light-color); border-radius: 0.5rem; margin-bottom: 0.75rem;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                            <?php echo count($modules); ?>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">Total Modules</div>
                    </div>
                    <div style="padding: 1rem; background: var(--light-color); border-radius: 0.5rem;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                            <?php 
                            $total_lessons = 0;
                            foreach ($modules as $m) {
                                $total_lessons += count($m['lessons']);
                            }
                            echo $total_lessons;
                            ?>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">Total Lessons</div>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Actions</h3>
                    <a href="edit_course.php?id=<?php echo $course_id; ?>" class="btn btn-outline" style="width: 100%; margin-bottom: 0.5rem;">
                        Edit Course Details
                    </a>
                    <a href="dashboard.php" class="btn btn-outline" style="width: 100%;">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Module Modal -->
        <div id="moduleModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div style="background-color: white; margin: 10% auto; padding: 2rem; width: 500px; border-radius: 0.75rem;">
                <h2 style="margin-bottom: 1.5rem;">Create New Module</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Module Title *</label>
                        <input type="text" name="module_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="module_description" class="form-control"></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="create_module" class="btn btn-primary">Create Module</button>
                        <button type="button" onclick="document.getElementById('moduleModal').style.display='none'" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lesson Modal -->
        <div id="lessonModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div style="background-color: white; margin: 5% auto; padding: 2rem; width: 600px; border-radius: 0.75rem;">
                <h2 style="margin-bottom: 1.5rem;">Create New Lesson</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="module_id" id="lesson_module_id">
                    
                    <div class="form-group">
                        <label class="form-label">Lesson Title *</label>
                        <input type="text" name="lesson_title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Content Type *</label>
                        <select name="content_type" id="content_type" class="form-control" required onchange="toggleContentFields()">
                            <option value="text">Text Content</option>
                            <option value="video">Video (YouTube or External Link)</option>
                            <option value="pdf">PDF Document</option>
                            <option value="external_link">External Link</option>
                        </select>
                    </div>

                    <div id="text_field" class="form-group">
                        <label class="form-label">Text Content</label>
                        <textarea name="content_text" class="form-control" rows="6"></textarea>
                    </div>

                    <div id="url_field" class="form-group" style="display: none;">
                        <label class="form-label">Content URL</label>
                        <input type="url" name="content_url" class="form-control" placeholder="https://...">
                    </div>

                    <div id="file_field" class="form-group" style="display: none;">
                        <label class="form-label">Upload PDF</label>
                        <input type="file" name="lesson_file" class="form-control" accept=".pdf">
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="create_lesson" class="btn btn-primary">Create Lesson</button>
                        <button type="button" onclick="document.getElementById('lessonModal').style.display='none'" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function showLessonModal(moduleId) {
            document.getElementById('lesson_module_id').value = moduleId;
            document.getElementById('lessonModal').style.display = 'block';
        }

        function toggleContentFields() {
            const type = document.getElementById('content_type').value;
            document.getElementById('text_field').style.display = 'none';
            document.getElementById('url_field').style.display = 'none';
            document.getElementById('file_field').style.display = 'none';

            if (type === 'text') {
                document.getElementById('text_field').style.display = 'block';
            } else if (type === 'video' || type === 'external_link') {
                document.getElementById('url_field').style.display = 'block';
            } else if (type === 'pdf') {
                document.getElementById('file_field').style.display = 'block';
            }
        }
        </script>
</body>
</html>