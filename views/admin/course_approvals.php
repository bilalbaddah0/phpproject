<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

requireRole(ROLE_ADMIN);

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Handle approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $course_id = (int)($_POST['course_id'] ?? 0);

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE courses SET approval_status = 'approved', approved_by = ?, approved_at = NOW() WHERE course_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
        $stmt->execute();
        $_SESSION['success'] = 'Course approved successfully!';
    } elseif ($action === 'reject') {
        $rejection_reason = sanitizeInput($_POST['rejection_reason'] ?? 'Not specified');
        $stmt = $conn->prepare("UPDATE courses SET approval_status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE course_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $rejection_reason, $course_id);
        $stmt->execute();
        $_SESSION['success'] = 'Course rejected.';
    }

    redirect('views/admin/course_approvals.php');
}

// Get filter
$filter = $_GET['filter'] ?? 'pending';

// Get courses based on filter
$sql = "SELECT c.*, cat.category_name, u.full_name as instructor_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrollment_count
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.category_id 
        LEFT JOIN users u ON c.instructor_id = u.user_id";

if ($filter === 'pending') {
    $sql .= " WHERE c.approval_status = 'pending'";
} elseif ($filter === 'approved') {
    $sql .= " WHERE c.approval_status = 'approved'";
} elseif ($filter === 'rejected') {
    $sql .= " WHERE c.approval_status = 'rejected'";
}

$sql .= " ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Count by status
$result = $conn->query("SELECT approval_status, COUNT(*) as count FROM courses GROUP BY approval_status");
$status_counts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['approval_status']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Approvals - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
        }
        .filter-tab {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            text-decoration: none;
        }
        .filter-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        .course-card {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .course-actions {
            display: flex;
            gap: 0.5rem;
        }
        .rejection-form {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light-color);
            border-radius: 0.5rem;
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="course_approvals.php">Approvals</a></li>
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
        <h1>Course Approvals</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="filter-tabs">
            <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                Pending (<?php echo $status_counts['pending'] ?? 0; ?>)
            </a>
            <a href="?filter=approved" class="filter-tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">
                Approved (<?php echo $status_counts['approved'] ?? 0; ?>)
            </a>
            <a href="?filter=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">
                Rejected (<?php echo $status_counts['rejected'] ?? 0; ?>)
            </a>
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Courses
            </a>
        </div>

        <?php if (empty($courses)): ?>
            <p style="text-align: center; color: var(--text-secondary); padding: 3rem;">
                No courses found in this category.
            </p>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-header">
                        <div>
                            <h3 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p style="color: var(--text-secondary); margin: 0;">
                                <strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?> |
                                <strong>Category:</strong> <?php echo htmlspecialchars($course['category_name']); ?> |
                                <strong>Price:</strong> $<?php echo number_format($course['price'], 2); ?> |
                                <strong>Status:</strong> 
                                <span class="badge badge-<?php echo $course['status'] === 'published' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="badge badge-<?php 
                                echo $course['approval_status'] === 'approved' ? 'success' : 
                                    ($course['approval_status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($course['approval_status']); ?>
                            </span>
                        </div>
                    </div>

                    <p style="margin: 1rem 0;"><?php echo htmlspecialchars(substr($course['description'], 0, 200)) . '...'; ?></p>

                    <p style="font-size: 0.875rem; color: var(--text-secondary);">
                        <strong>Created:</strong> <?php echo formatDate($course['created_at']); ?> |
                        <strong>Enrollments:</strong> <?php echo $course['enrollment_count']; ?>
                    </p>

                    <?php if ($course['rejection_reason']): ?>
                        <div class="alert alert-danger" style="margin-top: 1rem;">
                            <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($course['rejection_reason']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($course['approval_status'] === 'pending'): ?>
                        <div class="course-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this course?')">
                                    ✓ Approve
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" onclick="toggleRejectForm(<?php echo $course['course_id']; ?>)">
                                ✗ Reject
                            </button>
                        </div>

                        <div id="reject-form-<?php echo $course['course_id']; ?>" class="rejection-form">
                            <form method="POST">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <div class="form-group">
                                    <label>Rejection Reason</label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Explain why this course is being rejected..."></textarea>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleRejectForm(<?php echo $course['course_id']; ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function toggleRejectForm(courseId) {
            const form = document.getElementById('reject-form-' + courseId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
