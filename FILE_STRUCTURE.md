# FILE STRUCTURE

```
📁 lms/                                    # Root directory
│
├── 📄 index.php                           # Login page (Entry point)
├── 📄 .htaccess                           # Apache configuration
├── 📄 README.md                           # Complete documentation
├── 📄 SETUP.md                            # Quick setup guide
└── 📄 PROJECT_OVERVIEW.md                 # Project details
│
├── 📁 config/                             # Configuration files
│   ├── 📄 config.php                     # General settings & helper functions
│   └── 📄 database.php                   # Database connection class
│
├── 📁 controllers/                        # Request handlers
│   └── 📄 AuthController.php             # Login, register, logout logic
│
├── 📁 models/                             # Business logic & database operations
│   ├── 📄 User.php                       # User management (CRUD)
│   ├── 📄 Course.php                     # Course operations
│   ├── 📄 Enrollment.php                 # Enrollment & progress tracking
│   └── 📄 Quiz.php                       # Quiz functionality (ready for use)
│
├── 📁 views/                              # User interface pages
│   │
│   ├── 📁 auth/                          # Authentication pages
│   │   └── 📄 register.php               # User registration form
│   │
│   ├── 📁 student/                       # Student portal
│   │   ├── 📄 dashboard.php              # Student dashboard & enrolled courses
│   │   ├── 📄 browse.php                 # Browse & search all courses
│   │   └── 📄 course_view.php            # Course learning interface
│   │
│   ├── 📁 instructor/                    # Instructor portal
│   │   ├── 📄 dashboard.php              # Instructor dashboard & stats
│   │   ├── 📄 courses.php                # Redirect to dashboard
│   │   ├── 📄 create_course.php          # New course creation form
│   │   ├── 📄 edit_course.php            # Edit course details
│   │   └── 📄 manage_content.php         # Manage modules & lessons
│   │
│   └── 📁 admin/                         # Admin portal
│       ├── 📄 dashboard.php              # Admin dashboard & statistics
│       ├── 📄 users.php                  # User management interface
│       ├── 📄 courses.php                # All courses overview
│       └── 📄 categories.php             # Category management
│
├── 📁 assets/                             # Static files
│   │
│   ├── 📁 css/                           # Stylesheets
│   │   └── 📄 style.css                  # Main stylesheet (responsive)
│   │
│   ├── 📁 js/                            # JavaScript files
│   │   └── 📄 main.js                    # Main JavaScript functions
│   │
│   └── 📁 uploads/                       # User uploaded files
│       └── 📁 courses/                   # Course materials (PDFs, etc.)
│
└── 📁 database/                           # Database related files
    └── 📄 schema.sql                     # Complete database structure
```

## 📊 File Count Summary

| Category | Count | Details |
|----------|-------|---------|
| **Configuration** | 2 | config.php, database.php |
| **Controllers** | 1 | AuthController.php |
| **Models** | 4 | User, Course, Enrollment, Quiz |
| **Views - Auth** | 2 | Login (index.php), Register |
| **Views - Student** | 3 | Dashboard, Browse, Course View |
| **Views - Instructor** | 5 | Dashboard, Create, Edit, Content, Courses |
| **Views - Admin** | 4 | Dashboard, Users, Courses, Categories |
| **Assets - CSS** | 1 | style.css |
| **Assets - JS** | 1 | main.js |
| **Database** | 1 | schema.sql |
| **Documentation** | 3 | README, SETUP, OVERVIEW |
| **Total PHP Files** | 22 | |
| **Total Files** | 30+ | |

## 🎯 File Responsibilities

### Core Application Files

**index.php**
- Application entry point
- Login page
- Session validation
- Role-based redirect

**config/config.php**
- Site configuration
- Helper functions
- Constants definition
- Session management

**config/database.php**
- Database connection class
- Query methods
- Error handling

### Controller Layer

**controllers/AuthController.php**
- Process login requests
- Handle registration
- Manage sessions
- Logout functionality

### Model Layer

**models/User.php**
- User registration
- User authentication
- Profile management
- User CRUD operations
- Admin user management

**models/Course.php**
- Course creation & editing
- Module management
- Lesson operations
- Course statistics
- Category handling

**models/Enrollment.php**
- Student enrollment
- Progress tracking
- Lesson completion
- Course progress calculation

**models/Quiz.php**
- Quiz creation
- Question management
- Answer handling
- Attempt tracking
- Score calculation

### View Layer

**Student Views:**
1. `dashboard.php` - Shows enrolled courses & progress
2. `browse.php` - Course catalog with search
3. `course_view.php` - Learning interface with sidebar

**Instructor Views:**
1. `dashboard.php` - Course overview & statistics
2. `create_course.php` - New course form
3. `edit_course.php` - Edit course details
4. `manage_content.php` - Module & lesson management

**Admin Views:**
1. `dashboard.php` - Platform statistics
2. `users.php` - User management table
3. `courses.php` - All courses overview
4. `categories.php` - Category management

**Auth Views:**
1. `index.php` - Login form (root)
2. `register.php` - Registration form

### Asset Files

**assets/css/style.css**
- Responsive layout
- Component styles
- Color scheme
- Animations
- Media queries

**assets/js/main.js**
- Modal handling
- Form validation
- Alert auto-hide
- Progress animation
- Helper functions

### Database

**database/schema.sql**
- 13 table definitions
- Foreign key relationships
- Indexes for performance
- Sample data (admin user, categories)

### Documentation

**README.md**
- Complete project documentation
- Installation guide
- Feature list
- Troubleshooting
- API reference

**SETUP.md**
- Quick start guide
- 5-minute setup
- Verification checklist
- Common issues

**PROJECT_OVERVIEW.md**
- Architecture overview
- Technical details
- Statistics
- Future enhancements

## 🔗 File Dependencies

### Student Flow
```
index.php → AuthController.php → student/dashboard.php
                                     ↓
                                  browse.php
                                     ↓
                                  course_view.php
```

### Instructor Flow
```
index.php → AuthController.php → instructor/dashboard.php
                                     ↓
                                  create_course.php
                                     ↓
                                  manage_content.php
```

### Admin Flow
```
index.php → AuthController.php → admin/dashboard.php
                                     ↓
                    ┌───────────────┼───────────────┐
                    ↓               ↓               ↓
                users.php      courses.php    categories.php
```

## 📦 Module Relationships

```
config/
  ↓
models/ ← controllers/ → views/
  ↓
database.php
```

Every view file includes:
1. `config/config.php` (settings & helpers)
2. Relevant model(s) (User, Course, etc.)
3. Session validation (requireRole)

## 🎨 Naming Conventions

- **PHP Files**: PascalCase for classes (User.php), snake_case for views (course_view.php)
- **Functions**: camelCase (getUserById, createCourse)
- **Database**: snake_case (user_id, course_id)
- **CSS Classes**: kebab-case (btn-primary, course-card)

## 📏 File Sizes (Approximate)

| File | Lines | Size |
|------|-------|------|
| style.css | 500+ | 15 KB |
| Course.php | 400+ | 12 KB |
| User.php | 300+ | 10 KB |
| schema.sql | 250+ | 8 KB |
| dashboard pages | 150-250 | 5-8 KB |
| README.md | 400+ | 12 KB |

## 🔐 Protected Files

These files should NOT be directly accessible:
- `config/*.php`
- `models/*.php`
- `controllers/*.php`
- `database/schema.sql`

Protected by .htaccess configuration.

---

**Total Project Size: ~1-2 MB (excluding uploads)**
