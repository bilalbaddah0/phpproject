<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Shared/login.php');
    exit;
}

require_once __DIR__ . '/../Shared/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) {
        $_SESSION['error'] = 'User not found';
    } else {
        $role = $target['role'];
        if ($role === 'admin') {
            $allowed = ['pending','accepted','rejected'];
            if (!in_array($status, $allowed, true)) {
                $_SESSION['error'] = 'Invalid status';
            } else {
                $u = $pdo->prepare("UPDATE users SET admin_status = ? WHERE user_id = ?");
                if ($u->execute([$status, $user_id])) {
                    $_SESSION['success'] = 'User status updated successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to update user status';
                }
            }
        } elseif ($role === 'instructor') {
            $allowed = ['pending','accepted','rejected'];
            if (!in_array($status, $allowed, true)) {
                $_SESSION['error'] = 'Invalid status';
            } else {
                $u = $pdo->prepare("UPDATE users SET instructor_status = ? WHERE user_id = ?");
                if ($u->execute([$status, $user_id])) {
                    $_SESSION['success'] = 'User status updated successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to update user status';
                }
            }
        } else {
            $_SESSION['error'] = 'Cannot change status for students here.';
        }
    }    header('Location: users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot delete your own account';
    } else {
        $d = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        if ($d->execute([$user_id])) {
            $_SESSION['success'] = 'User deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
    }
    header('Location: users.php');
    exit;
}

$role_filter = isset($_GET['role']) && in_array($_GET['role'], ['student','instructor','admin']) ? $_GET['role'] : null;
if ($role_filter) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, role, admin_status, instructor_status FROM users WHERE role = ? ORDER BY user_id DESC");
    $stmt->execute([$role_filter]);
} else {
    $stmt = $pdo->query("SELECT user_id, full_name, email, role, admin_status, instructor_status FROM users ORDER BY user_id DESC");
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('formatDate')) {
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
    <title>Manage Users - LMS</title>
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
        <h1>Manage Users</h1>

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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <a href="?role=" class="btn btn-sm <?php echo !$role_filter ? 'btn-primary' : 'btn-outline'; ?>">All</a>
                    <a href="?role=student" class="btn btn-sm <?php echo $role_filter === 'student' ? 'btn-primary' : 'btn-outline'; ?>">Students</a>
                    <a href="?role=instructor" class="btn btn-sm <?php echo $role_filter === 'instructor' ? 'btn-primary' : 'btn-outline'; ?>">Instructors</a>
                    <a href="?role=admin" class="btn btn-sm <?php echo $role_filter === 'admin' ? 'btn-primary' : 'btn-outline'; ?>">Admins</a>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span></td>
                            <td>
    <?php if ($user['role'] === 'admin'): ?>
        <?php if ($user['admin_status'] === 'pending'): ?>
            <span class="badge badge-warning">Pending Approval</span>
        <?php elseif ($user['admin_status'] === 'accepted'): ?>
            <span class="badge badge-success">Approved</span>
        <?php else: ?>
            <span class="badge badge-danger">Rejected</span>
        <?php endif; ?>

    <?php elseif ($user['role'] === 'instructor'): ?>
        <?php if ($user['instructor_status'] === 'pending'): ?>
            <span class="badge badge-warning">Pending Approval</span>
        <?php elseif ($user['instructor_status'] === 'accepted'): ?>
            <span class="badge badge-success">Approved</span>
        <?php else: ?>
            <span class="badge badge-danger">Rejected</span>
        <?php endif; ?>

    <?php else: ?>
        <span class="badge badge-primary">Active</span>
    <?php endif; ?>
</td>

                            <td>
    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>

        <?php if (
            ($user['role'] === 'admin' && $user['admin_status'] === 'pending') ||
            ($user['role'] === 'instructor' && $user['instructor_status'] === 'pending')
        ): ?>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                <input type="hidden" name="status" value="accepted">
                <input type="hidden" name="update_status" value="1">
                <button class="btn btn-sm btn-primary">Approve</button>
            </form>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                <input type="hidden" name="status" value="rejected">
                <input type="hidden" name="update_status" value="1">
                <button class="btn btn-sm btn-danger">Reject</button>
            </form>

        <?php endif; ?>

        <a href="?delete=<?php echo $user['user_id']; ?>"
           class="btn btn-sm btn-outline"
           onclick="return confirm('Delete this user?')">
            Delete
        </a>

    <?php else: ?>
        <span class="badge badge-success">You</span>
    <?php endif; ?>
</td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>