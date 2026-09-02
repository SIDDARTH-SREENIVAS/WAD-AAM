# Course Feedback Management System

A decoupled, responsive, and secure Web Application Development (WAD) project designed for students to submit course evaluations and administrators to moderate feedback and manage course curricula.

---

## 🏛️ Clean Architecture & Separation of Concerns

The project is structured into distinct, dedicated layers where **HTML**, **CSS**, **JavaScript**, and **PHP** reside in separate files:

```
wad/
├── index.html                  # Pure HTML: User sign-in interface
├── register.html               # Pure HTML: Student registration interface
├── student_dashboard.html      # Pure HTML: Student evaluation portal
├── admin_dashboard.html        # Pure HTML: Administrator control panel
├── css/
│   └── style.css               # Pure CSS: Design system, dark/light themes, animations & components
├── js/
│   ├── app.js                  # Pure JS: Theme switcher, modal manager & toast alerts
│   ├── auth.js                 # Pure JS: Login & registration handlers with validation
│   ├── student.js              # Pure JS: Dynamic course fetching, feedback CRUD & modals
│   └── admin.js                # Pure JS: Admin analytics, course CRUD & feedback moderation
├── php/
│   ├── config.php              # Pure PHP: Database abstraction & MySQL PDO connection
│   ├── auth.php                # Pure PHP: Authentication API (login, register, check_session, logout)
│   ├── courses.php             # Pure PHP: Course API (list, create, update, delete)
│   ├── feedback.php            # Pure PHP: Feedback API (list, create, update, delete)
│   └── stats.php               # Pure PHP: Admin analytics and statistics API
├── schema.sql                  # Setup script for MySQL tables & sample records
└── README.md                   # Documentation & setup guide
```

---

## ✨ Key Features

- **Decoupled Frontend & Backend**: Pure HTML views communicating with PHP REST endpoints via JSON `fetch()` requests.
- **Dual-role Authentication**: Supports Student and Administrator dashboards with session verification.
- **Student Dashboard**:
  - Dynamic course selection dropdown (filters out previously reviewed courses).
  - Interactive 5-star rating widget with hover and click support.
  - Complete CRUD: Students can submit, view, edit, and delete their own feedback.
- **Administrator Control Panel**:
  - Live statistics summary counters (Total Courses, Total Reviews, Overall Average Rating).
  - Course CRUD operations: Administrators can add, edit, and delete courses.
  - Feedback moderation: Real-time search/filter and deletion of inappropriate feedback.
- **Modern UI & Aesthetic**:
  - Glassmorphism styling, clean modern typography (Inter), and CSS micro-animations.
  - Dark Mode and Light Mode theme switcher with `localStorage` persistence.
- **Relational Database**:
  - Backed directly by MySQL with relational foreign keys and cascaded deletions.

---

## 🚀 Installation & Setup

1. **Start MySQL**:
   Ensure your local MySQL service (via XAMPP, MAMP, or standalone MySQL) is running.

2. **Import Database Schema**:
   Import [`schema.sql`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/schema.sql) into MySQL using phpMyAdmin or terminal:
   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Database Configuration**:
   - Open [`php/config.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/php/config.php).
   - Update `$user` and `$pass` if your local MySQL has a specific password configured.

4. **Launch Web Server**:
   - Start the PHP development server:
     ```bash
     php -S 127.0.0.1:8000
     ```
   - Open `http://127.0.0.1:8000/index.html` in your browser.

---

## 👥 Seeded User Accounts (from `schema.sql`)

- **Administrator**:
  - **Username**: `admin`
  - **Password**: `admin123`
- **Student**:
  - **Username**: `john_doe`
  - **Password**: `student123`
- **Student**:
  - **Username**: `jane_smith`
  - **Password**: `student123`
