<?php
$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dbname = "lms_database";
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";

if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->select_db($dbname);

$table1 = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('student','instructor','admin') NOT NULL,
    admin_status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    instructor_status ENUM('pending','accepted','rejected') DEFAULT 'pending'
)";


$table2 = "CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
)";

$table3 = "CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    rejection_reason TEXT NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
)";

$table4 = "CREATE TABLE IF NOT EXISTS lessons (
    lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lesson_title VARCHAR(255) NOT NULL,
    content TEXT,
    lesson_order INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
)";

$table5 = "CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
)";

if ($conn->query($table1) === TRUE) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

if ($conn->query($table2) === TRUE) {
    echo "Categories table created successfully\n";
} else {
    echo "Error creating categories table: " . $conn->error . "\n";
}

if ($conn->query($table3) === TRUE) {
    echo "Courses table created successfully\n";
} else {
    echo "Error creating courses table: " . $conn->error . "\n";
}

if ($conn->query($table4) === TRUE) {
    echo "Lessons table created successfully\n";
} else {
    echo "Error creating lessons table: " . $conn->error . "\n";
}

if ($conn->query($table5) === TRUE) {
    echo "Enrollments table created successfully\n";
} else {
    echo "Error creating enrollments table: " . $conn->error . "\n";
}

$table6 = "CREATE TABLE IF NOT EXISTS lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    lesson_id INT NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    UNIQUE (enrollment_id, lesson_id),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
)";

if ($conn->query($table6) === TRUE) {
    echo "Lesson progress table created successfully\n";
} else {
    echo "Error creating lesson_progress table: " . $conn->error . "\n";
}

$conn->close();
?>
