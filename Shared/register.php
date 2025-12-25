<?php
// register.php — simple registration form
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <form method="post" action="registerProcess.php">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Create account</button>
    </form>
    <p><a href="login.php">Login</a></p>
</body>
</html>
