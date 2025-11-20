<?php
// Script to update admin password
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

// New password
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin user
$sql = "UPDATE users SET password = ? WHERE email = 'admin@lms.com'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "✅ Admin password updated successfully!<br><br>";
    echo "You can now login with:<br>";
    echo "Email: admin@lms.com<br>";
    echo "Password: admin123<br><br>";
    echo "<a href='index.php'>Go to Login Page</a>";
} else {
    echo "❌ Error updating password: " . $conn->error;
}

// DELETE THIS FILE AFTER USE FOR SECURITY
echo "<br><br><strong style='color: red;'>⚠️ IMPORTANT: Delete this file (update_admin_password.php) after use!</strong>";
?>
