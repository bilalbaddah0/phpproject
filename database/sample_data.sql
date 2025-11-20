-- Sample Data for Testing
-- Run this AFTER importing schema.sql

USE lms_database;

-- Sample Instructor Account
-- Email: instructor@test.com
-- Password: instructor123
INSERT INTO users (email, password, full_name, role, bio) VALUES 
('instructor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Instructor', 'instructor', 'Experienced web developer and educator with 10+ years in the industry.');

-- Sample Student Account
-- Email: student@test.com
-- Password: student123
INSERT INTO users (email, password, full_name, role) VALUES 
('student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Student', 'student');

-- Sample Courses
SET @instructor_id = (SELECT user_id FROM users WHERE email = 'instructor@test.com');

INSERT INTO courses (instructor_id, category_id, title, description, price, level, status) VALUES
(@instructor_id, 1, 'Complete Web Development Bootcamp', 'Learn HTML, CSS, JavaScript, PHP, and MySQL from scratch. Build real-world projects and become a full-stack web developer.', 0.00, 'beginner', 'published'),
(@instructor_id, 2, 'Data Science with Python', 'Master data analysis, visualization, and machine learning with Python. Work with real datasets and build predictive models.', 0.00, 'intermediate', 'published'),
(@instructor_id, 1, 'Advanced JavaScript Masterclass', 'Deep dive into modern JavaScript, ES6+, async programming, design patterns, and advanced concepts.', 0.00, 'advanced', 'published'),
(@instructor_id, 3, 'Digital Marketing Fundamentals', 'Learn SEO, social media marketing, content marketing, and analytics to grow your business online.', 0.00, 'beginner', 'published');

-- Sample Modules for Course 1 (Web Development)
SET @course1_id = (SELECT course_id FROM courses WHERE title = 'Complete Web Development Bootcamp');

INSERT INTO modules (course_id, module_title, module_order, description) VALUES
(@course1_id, 'Introduction to Web Development', 1, 'Get started with the basics of web development and set up your environment.'),
(@course1_id, 'HTML Fundamentals', 2, 'Learn HTML structure, tags, and semantic markup.'),
(@course1_id, 'CSS Styling and Layouts', 3, 'Master CSS for beautiful and responsive designs.'),
(@course1_id, 'JavaScript Basics', 4, 'Introduction to programming with JavaScript.');

-- Sample Lessons for Module 1
SET @module1_id = (SELECT module_id FROM modules WHERE course_id = @course1_id AND module_order = 1);

INSERT INTO lessons (module_id, lesson_title, lesson_order, content_type, content_text, content_url) VALUES
(@module1_id, 'Welcome to the Course', 1, 'text', 'Welcome to the Complete Web Development Bootcamp!\n\nIn this comprehensive course, you will learn everything you need to become a professional web developer. We will cover:\n\n- HTML and CSS for structure and styling\n- JavaScript for interactivity\n- PHP for server-side programming\n- MySQL for database management\n- Building real-world projects\n\nNo prior experience required! Let''s get started on your journey to becoming a web developer.', NULL),
(@module1_id, 'What is Web Development?', 2, 'text', 'Web development is the process of creating websites and web applications. It involves three main components:\n\n1. Frontend (Client-side):\n   - HTML: Structure\n   - CSS: Styling\n   - JavaScript: Interactivity\n\n2. Backend (Server-side):\n   - PHP, Python, Node.js\n   - Database management\n   - Server configuration\n\n3. Full-Stack:\n   - Combining frontend and backend skills\n   - Understanding the complete web architecture\n\nAs a web developer, you can work on various projects from simple websites to complex web applications.', NULL),
(@module1_id, 'Setting Up Your Development Environment', 3, 'text', 'To start web development, you need to set up your development environment:\n\n1. Text Editor/IDE:\n   - VS Code (Recommended)\n   - Sublime Text\n   - Atom\n\n2. Web Browser:\n   - Chrome (with DevTools)\n   - Firefox\n   - Edge\n\n3. Local Server:\n   - XAMPP (for PHP and MySQL)\n   - Node.js (for JavaScript)\n\n4. Version Control:\n   - Git\n   - GitHub account\n\nDownload and install these tools to follow along with the course.', NULL),
(@module1_id, 'Introduction to HTML', 4, 'video', NULL, 'https://www.youtube.com/watch?v=UB1O30fR-EE');

-- Sample Lessons for Module 2 (HTML Fundamentals)
SET @module2_id = (SELECT module_id FROM modules WHERE course_id = @course1_id AND module_order = 2);

INSERT INTO lessons (module_id, lesson_title, lesson_order, content_type, content_text) VALUES
(@module2_id, 'HTML Document Structure', 1, 'text', 'Every HTML document follows a basic structure:\n\n<!DOCTYPE html>\n<html>\n<head>\n    <meta charset="UTF-8">\n    <title>Page Title</title>\n</head>\n<body>\n    <!-- Content goes here -->\n</body>\n</html>\n\nKey Components:\n- DOCTYPE: Declares HTML version\n- <html>: Root element\n- <head>: Metadata and links\n- <body>: Visible content\n\nThis structure is the foundation of every web page you create.'),
(@module2_id, 'HTML Tags and Elements', 2, 'text', 'HTML uses tags to define elements:\n\nHeadings:\n<h1>Main Heading</h1>\n<h2>Subheading</h2>\n\nParagraphs:\n<p>This is a paragraph.</p>\n\nLinks:\n<a href="https://example.com">Click here</a>\n\nImages:\n<img src="image.jpg" alt="Description">\n\nLists:\n<ul>\n    <li>Item 1</li>\n    <li>Item 2</li>\n</ul>\n\nPractice creating these elements to build your first web page!'),
(@module2_id, 'Semantic HTML', 3, 'text', 'Semantic HTML uses meaningful tags that describe content:\n\n<header>: Page header\n<nav>: Navigation menu\n<main>: Main content\n<article>: Independent content\n<section>: Grouped content\n<aside>: Sidebar content\n<footer>: Page footer\n\nBenefits:\n- Better SEO\n- Improved accessibility\n- Cleaner code structure\n- Easier maintenance\n\nAlways use semantic tags when appropriate!');

-- Sample Lessons for Module 3 (CSS)
SET @module3_id = (SELECT module_id FROM modules WHERE course_id = @course1_id AND module_order = 3);

INSERT INTO lessons (module_id, lesson_title, lesson_order, content_type, content_text) VALUES
(@module3_id, 'Introduction to CSS', 1, 'text', 'CSS (Cascading Style Sheets) is used to style HTML elements.\n\nThree Ways to Add CSS:\n\n1. Inline:\n<p style="color: blue;">Text</p>\n\n2. Internal:\n<style>\n    p { color: blue; }\n</style>\n\n3. External (Recommended):\n<link rel="stylesheet" href="style.css">\n\nCSS Syntax:\nselector {\n    property: value;\n}\n\nExample:\np {\n    color: blue;\n    font-size: 16px;\n}'),
(@module3_id, 'CSS Selectors', 2, 'text', 'CSS selectors target HTML elements:\n\nElement Selector:\np { color: red; }\n\nClass Selector:\n.highlight { background: yellow; }\n\nID Selector:\n#header { font-size: 24px; }\n\nDescendant Selector:\ndiv p { margin: 10px; }\n\nPseudo-classes:\na:hover { color: red; }\n\nMastering selectors is key to efficient CSS!'),
(@module3_id, 'CSS Box Model', 3, 'text', 'Every HTML element is a box with:\n\n1. Content: The actual content\n2. Padding: Space inside the border\n3. Border: Surrounds the padding\n4. Margin: Space outside the border\n\nExample:\ndiv {\n    width: 300px;\n    padding: 20px;\n    border: 2px solid black;\n    margin: 10px;\n}\n\nTotal width = width + padding + border + margin\n\nUnderstanding the box model is essential for layouts!');

-- More sample data for testing...

-- Sample Student Enrollment
SET @student_id = (SELECT user_id FROM users WHERE email = 'student@test.com');

INSERT INTO enrollments (student_id, course_id) VALUES
(@student_id, @course1_id);

-- Sample Lesson Progress
SET @enrollment_id = (SELECT enrollment_id FROM enrollments WHERE student_id = @student_id AND course_id = @course1_id);
SET @lesson1_id = (SELECT lesson_id FROM lessons WHERE module_id = @module1_id AND lesson_order = 1);
SET @lesson2_id = (SELECT lesson_id FROM lessons WHERE module_id = @module1_id AND lesson_order = 2);

INSERT INTO lesson_progress (enrollment_id, lesson_id, is_completed, completed_at) VALUES
(@enrollment_id, @lesson1_id, TRUE, NOW()),
(@enrollment_id, @lesson2_id, TRUE, NOW());

-- Update enrollment progress
UPDATE enrollments SET progress_percentage = 20.00 WHERE enrollment_id = @enrollment_id;

-- Display success message
SELECT 'Sample data imported successfully!' AS message,
       COUNT(*) AS total_courses FROM courses WHERE status = 'published' 
UNION ALL
SELECT 'Test accounts created:', COUNT(*) FROM users WHERE role != 'admin';
