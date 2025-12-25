<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            exit;
        case 'instructor':
            header("Location: ../instructor/dashboard.php");
            exit;
        case 'student':
            header("Location: ../student/dashboard.php");
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #F9FAFB; } /* light-color */
        .card {
            width: 400px;
            border: none;
            box-shadow: 0 10px 15px rgba(0,0,0,0.1); /* shadow-lg */
            padding: 30px;
            border-radius: 10px;
        }
        .btn-primary { background-color: #4F46E5; border: none; font-weight: bold; } /* primary-color */
        .btn-primary:hover { background-color: #4338CA; } /* primary-dark */
        .text-primary-color { color: #4F46E5; font-weight: bold; } /* primary-color */
        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: bold;
            color: #4F46E5; /* primary-color */
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 5px rgba(79,70,229,0.2); /* primary-color tint */
        }
        .alert { text-align: center; font-size: 14px; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg rounded">
        <div class="brand-name">LMS</div>
        <h2 class="text-center mb-4 text-primary-color">Login</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="loginProcess.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-3">
            <p>Don't have an account? <a href="register.php" class="text-primary-color">Register here</a></p>
        </div>
    </div>
</body>
</html>
