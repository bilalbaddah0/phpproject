<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Shared/login.php');
    exit;
}

require_once __DIR__ . '/../Shared/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    if ($category_name === '') {
        $_SESSION['error'] = 'Category name is required';
    } else {
        $ins = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        if ($ins->execute([$category_name, $description])) {
            $_SESSION['success'] = 'Category created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create category';
        }
    }
    header('Location: categories.php');
    exit;
}

if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    $del = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    if ($del->execute([$category_id])) {
        $_SESSION['success'] = 'Category deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete category. Make sure no courses are using it.';
    }
    header('Location: categories.php');
    exit;
}

$stmt = $pdo->query("SELECT category_id, category_name, description FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cntStmt = $pdo->prepare("SELECT COUNT(*) as count FROM courses WHERE category_id = ?");
foreach ($categories as &$cat) {
    $cntStmt->execute([$cat['category_id']]);
    $res = $cntStmt->fetch(PDO::FETCH_ASSOC);
    $cat['course_count'] = $res ? (int)$res['count'] : 0;
}
unset($cat);if (!function_exists('formatDate')) {
    function formatDate($d) {
        $ts = strtotime($d);
        if ($ts === false) return 'N/A';
        return date('Y-m-d', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - LMS</title>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Manage Categories</h1>
            <button onclick="document.getElementById('categoryModal').style.display='block'" class="btn btn-primary">
                + Add Category
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['category_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td><?php echo $category['course_count']; ?> courses</td>
                            <td>
                                <?php if ($category['course_count'] == 0): ?>
                                    <a href="?delete=<?php echo $category['category_id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 0.875rem;">In use</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 10% auto; padding: 2rem; width: 500px; border-radius: 0.75rem;">
            <h2 style="margin-bottom: 1.5rem;">Create New Category</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="category_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="create_category" class="btn btn-primary">Create Category</button>
                    <button type="button" onclick="document.getElementById('categoryModal').style.display='none'" class="btn btn-outline">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>