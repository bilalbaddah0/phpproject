# 🚀 QUICK REFERENCE CARD

## 📋 Essential Information At A Glance

### 🔗 Access URLs
```
Login Page:        http://localhost/php/lms/
Admin Dashboard:   http://localhost/php/lms/views/admin/dashboard.php
Instructor:        http://localhost/php/lms/views/instructor/dashboard.php
Student:           http://localhost/php/lms/views/student/dashboard.php
```

### 👤 Default Accounts
```
Admin:
  Email: admin@lms.com
  Password: admin123

Test Instructor (if sample data loaded):
  Email: instructor@test.com
  Password: instructor123

Test Student (if sample data loaded):
  Email: student@test.com
  Password: student123
```

### 🗄️ Database
```
Database Name: lms_database
MySQL User: root
MySQL Password: (empty)
Host: localhost
Port: 3306
```

### 📁 Important Files
```
Configuration:
  - config/config.php (Settings)
  - config/database.php (DB Connection)

Models:
  - models/User.php (Users)
  - models/Course.php (Courses)
  - models/Enrollment.php (Enrollments)
  - models/Quiz.php (Quizzes)

Entry Point:
  - index.php (Login)

Upload Directory:
  - assets/uploads/courses/
```

### 🎯 Quick Actions

**To Change Database Settings:**
Edit: `config/database.php`

**To Change Base URL:**
Edit: `config/config.php` → `BASE_URL`

**To Add Admin User:**
Run SQL: `INSERT INTO users (email, password, full_name, role) VALUES (...)`
Password hash for "admin123": `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

**To Import Database:**
phpMyAdmin → Import → `database/schema.sql`

**To Add Sample Data:**
phpMyAdmin → Import → `database/sample_data.sql`

### 🔒 Security Notes
```
✅ Passwords are hashed with bcrypt
✅ SQL injection protected (prepared statements)
✅ XSS protected (htmlspecialchars)
✅ Session-based authentication
✅ Role-based access control
✅ File upload validation
```

### 🎨 Content Types
```
Text:     Plain text lessons
Video:    YouTube URLs (auto-embed)
PDF:      Upload .pdf files
Link:     External resource URLs
```

### 🔧 Troubleshooting
```
Issue: Can't connect to database
Fix: Check MySQL is running in XAMPP

Issue: Page not found
Fix: Verify BASE_URL in config/config.php

Issue: Can't upload files
Fix: Check folder permissions on assets/uploads/

Issue: Styles not loading
Fix: Clear browser cache

Issue: Login not working
Fix: Check database exists and admin user imported
```

### 📊 Database Tables
```
users              - User accounts
categories         - Course categories
courses            - Course information
modules            - Course modules
lessons            - Lesson content
enrollments        - Student enrollments
lesson_progress    - Completion tracking
quizzes            - Quiz info
quiz_questions     - Questions
quiz_options       - Answer options
quiz_attempts      - Student attempts
quiz_answers       - Answers submitted
```

### 📱 User Capabilities

**Students Can:**
- Browse courses
- Search & filter
- Enroll in courses
- View lessons
- Track progress
- Complete lessons

**Instructors Can:**
- Create courses
- Add modules
- Add lessons
- Upload files
- Publish courses
- View enrollments

**Admins Can:**
- Manage all users
- Create categories
- View all courses
- Change user status
- Delete users
- Monitor platform

### 🎓 File Naming Pattern
```
Models:      PascalCase (User.php, Course.php)
Views:       snake_case (course_view.php, dashboard.php)
Functions:   camelCase (getUserById, createCourse)
Database:    snake_case (user_id, course_id)
CSS:         kebab-case (btn-primary, course-card)
```

### 📝 Common Tasks

**Register New User:**
1. Go to registration page
2. Fill form
3. Select role (Student/Instructor)
4. Submit

**Create Course:**
1. Login as Instructor
2. Click "Create New Course"
3. Fill details
4. Add modules
5. Add lessons
6. Publish

**Enroll Student:**
1. Login as Student
2. Browse courses
3. Click "Enroll Now"
4. Start learning

**Manage Users:**
1. Login as Admin
2. Go to Users page
3. Filter by role
4. Update status or delete

### 🔄 Workflow Summary
```
Student:   Register → Browse → Enroll → Learn → Complete
Instructor: Register → Create → Content → Publish → Monitor
Admin:     Login → Manage → Monitor → Control
```

### 💾 Backup Checklist
```
□ Database (Export from phpMyAdmin)
□ Uploaded files (assets/uploads/)
□ Config files (if customized)
□ .htaccess (if modified)
```

### 🚀 Production Checklist
```
□ Change database password
□ Update BASE_URL
□ Enable HTTPS
□ Set error_reporting(0)
□ Secure upload directory
□ Regular backups
□ Monitor logs
```

### 📞 Support Resources
```
Installation:     SETUP.md
Features:         README.md
Testing:          TESTING.md
Architecture:     PROJECT_OVERVIEW.md
File Structure:   FILE_STRUCTURE.md
```

### ⚡ Performance Tips
```
- Use indexes on frequently queried columns
- Optimize images before upload
- Enable browser caching
- Compress CSS/JS for production
- Use CDN for static assets (optional)
- Regular database optimization
```

### 🎯 Success Metrics
```
✅ All XAMPP services running
✅ Database imported successfully
✅ Can login with admin account
✅ Students can enroll
✅ Instructors can create courses
✅ Progress tracking works
✅ No PHP errors
```

---

## 📄 Document Quick Links

- **Full Setup:** SETUP.md
- **All Features:** README.md  
- **Test Cases:** TESTING.md
- **Code Guide:** FILE_STRUCTURE.md
- **Project Info:** PROJECT_OVERVIEW.md
- **Completion:** PROJECT_COMPLETE.md

---

**Keep this card handy for quick reference!**

**Status: ✅ SYSTEM READY**
