# E-Learning Management System (LMS)

A comprehensive online course platform built with **pure PHP** and **MySQL**, similar to Udemy. This platform enables instructors to create and manage courses while students can enroll, learn, and track their progress.

## 🎯 Features

### For Students
- **User Registration & Login** - Secure authentication system
- **Course Catalog** - Browse and search available courses with filters
- **Course Enrollment** - Enroll in courses with one click
- **Learning Interface** - Access course content including videos, PDFs, and text
- **Progress Tracking** - Automatically track lesson completion and course progress
- **Dashboard** - View enrolled courses and learning statistics

### For Instructors
- **Course Creation** - Create structured courses with modules and lessons
- **Content Management** - Upload PDFs, embed videos (YouTube), add text content, and external links
- **Course Publishing** - Control course status (draft, published, archived)
- **Student Analytics** - View enrollment counts and course statistics
- **Multiple Content Types** - Support for video, PDF, text, and external links

### For Administrators
- **User Management** - Manage all users, update statuses, and delete accounts
- **Course Oversight** - View all courses across the platform
- **Category Management** - Create and manage course categories
- **Platform Statistics** - Dashboard with key metrics

## 🛠️ Technology Stack

- **Backend:** Pure PHP (no frameworks)
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, vanilla JavaScript
- **Server:** XAMPP (Apache + MySQL)

## 📋 Prerequisites

- XAMPP installed on your system
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, etc.)

## 🚀 Installation & Setup

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### Step 2: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `lms/database/schema.sql`
4. Click "Go" to import

**Or create manually:**
```sql
CREATE DATABASE lms_database;
```
Then import the `schema.sql` file.

### Step 3: Configure Database Connection
Edit `lms/config/database.php` if needed (default settings):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lms_database');
```

### Step 4: Set Base URL
Edit `lms/config/config.php` and update `BASE_URL`:
```php
define('BASE_URL', 'http://localhost/php/lms/');
```

### Step 5: Set Permissions
Ensure the uploads folder is writable:
- Right-click `lms/assets/uploads/` folder
- Properties → Security → Edit
- Give "Full Control" to your user

### Step 6: Access the Application
Open your browser and navigate to:
```
http://localhost/php/lms/
```

## 👤 Default Login Credentials

### Administrator
- **Email:** admin@lms.com
- **Password:** admin123

### Test Accounts
You can register new accounts for Students and Instructors through the registration page.

## 📁 Project Structure

```
lms/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/                    # JavaScript files
│   └── uploads/
│       └── courses/           # Uploaded course files
├── config/
│   ├── config.php             # General configuration
│   └── database.php           # Database connection
├── controllers/
│   └── AuthController.php     # Authentication logic
├── database/
│   └── schema.sql             # Database schema
├── models/
│   ├── User.php               # User model
│   ├── Course.php             # Course model
│   ├── Enrollment.php         # Enrollment model
│   └── Quiz.php               # Quiz model
├── views/
│   ├── admin/                 # Admin pages
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   ├── courses.php
│   │   └── categories.php
│   ├── instructor/            # Instructor pages
│   │   ├── dashboard.php
│   │   ├── create_course.php
│   │   ├── edit_course.php
│   │   └── manage_content.php
│   ├── student/               # Student pages
│   │   ├── dashboard.php
│   │   ├── browse.php
│   │   └── course_view.php
│   └── auth/                  # Authentication pages
│       └── register.php
└── index.php                  # Login page
```

## 🗃️ Database Schema

The system uses 13 tables:

- **users** - User accounts (students, instructors, admins)
- **categories** - Course categories
- **courses** - Course information
- **modules** - Course modules/sections
- **lessons** - Individual lessons
- **quizzes** - Quiz information
- **quiz_questions** - Quiz questions
- **quiz_options** - Answer options
- **enrollments** - Student enrollments
- **lesson_progress** - Lesson completion tracking
- **quiz_attempts** - Quiz attempt records
- **quiz_answers** - Student quiz answers

## 🎓 Usage Guide

### For Students
1. Register an account with "Student" role
2. Browse available courses from the dashboard
3. Click "Enroll Now" on any published course
4. Access course content from "My Learning"
5. Complete lessons to track progress

### For Instructors
1. Register an account with "Instructor" role
2. Create a new course from the dashboard
3. Add modules to organize content
4. Add lessons (videos, PDFs, text, links)
5. Publish course when ready

### For Administrators
1. Login with admin credentials
2. Manage users (activate/suspend/delete)
3. Create and manage categories
4. Monitor platform statistics

## ✨ Key Features Explained

### Course Content Types
- **Text Content** - Rich text lessons
- **Video** - YouTube embeds or video URLs
- **PDF Documents** - Uploadable PDF files
- **External Links** - Links to external resources

### Progress Tracking
- Automatic calculation based on completed lessons
- Visual progress bars
- Course completion status
- Individual lesson tracking

### Role-Based Access Control
- **Students** - Can only view and enroll in courses
- **Instructors** - Can create and manage their own courses
- **Admins** - Full system access

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Input sanitization
- Session-based authentication
- Role-based access control
- XSS protection with `htmlspecialchars()`

## 🐛 Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `lms_database` exists

### Upload Not Working
- Check folder permissions on `assets/uploads/`
- Verify PHP `upload_max_filesize` in `php.ini`
- Ensure `post_max_size` is sufficient

### Pages Not Loading
- Verify `BASE_URL` in `config/config.php`
- Check Apache is running in XAMPP
- Clear browser cache

### CSS Not Loading
- Check file path in HTML files
- Verify `BASE_URL` configuration
- Check browser developer console for 404 errors

## 📝 Future Enhancements

Potential features to add:
- Quiz functionality (models are ready)
- Course ratings and reviews
- Discussion forums
- Certificate generation
- Payment integration
- Email notifications
- Course preview/demo lessons
- Mobile responsive improvements
- Advanced search filters
- Instructor earnings dashboard

## 🤝 Contributing

This is an educational project demonstrating:
- Pure PHP development without frameworks
- MVC-like architecture
- Database design and relationships
- User authentication and authorization
- File upload handling
- Session management
- Responsive web design

## 📄 License

This project is created for educational purposes.

## 👨‍💻 Developer Notes

### Code Organization
- **Models** - Database operations and business logic
- **Views** - HTML presentation layer
- **Controllers** - Request handling and routing
- **Config** - Configuration and helper functions

### Best Practices Used
- Separation of concerns
- Prepared statements for security
- Consistent naming conventions
- Reusable functions
- Responsive design patterns

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Verify your XAMPP configuration
3. Review PHP error logs in `xampp/apache/logs/`

---

**Built with ❤️ using Pure PHP and MySQL**
