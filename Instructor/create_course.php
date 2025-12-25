<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Course.php';

requireRole(ROLE_INSTRUCTOR);

$courseModel = new Course();
$categories = $courseModel->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $level = sanitizeInput($_POST['level']);

    $result = $courseModel->createCourse($_SESSION['user_id'], $title, $description, $category_id, $price, $level);

    if ($result['success']) {
        $_SESSION['success'] = 'Course created successfully!';
        redirect('Instructor/manage_content.php?id=' . $result['course_id']);
    } else {
        $_SESSION['error'] = 'Failed to create course';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - <?php echo SITE_NAME; ?></title>
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
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem;">Create New Course</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Course Title *</label>
                        <input type="text" name="title" class="form-control" required 
                               placeholder="e.g., Introduction to Web Development">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course Description *</label>
                        <textarea name="description" class="form-control" required 
                                  placeholder="Describe what students will learn in this course..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Level *</label>
                            <select name="level" class="form-control" required>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="0" 
                               placeholder="0.00">
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Create Course</button>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>