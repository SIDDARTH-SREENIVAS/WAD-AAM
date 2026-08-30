# Course Feedback Management System

A modern, responsive, and secure web application designed for students to submit course feedback and administrators to manage the system. Built using HTML, CSS, JavaScript, PHP, and MySQL.

---

## Key Features

- **Dual-role Authentication**: Supports Student and Administrator dashboards.
- **Student Dashboard**:
  - List of courses pending review.
  - Interactive 5-star rating widget.
  - CRUD operations: Students can submit, view, edit, and delete their own feedback.
- **Admin Dashboard**:
  - Statistics summary counters (total courses, reviews, average rating).
  - Course CRUD operations: Administrators can add, edit, and delete courses.
  - Feedback moderation: Search/filter feedback records and delete inappropriate posts.
- **Visual Design**:
  - Sleek glassmorphism elements with responsive layouts.
  - Smooth micro-animations (e.g. entry fades, interactive stars, and hover states).
  - Dark Mode and Light Mode theme switcher with local storage persistence.
- **Robust Fail-safe Connection**:
  - If MySQL is offline, the backend seamlessly falls back to a **Demo Mode** using Session-based storage, allowing full review of CRUD operations instantly without setup.

---

## Installation & Setup

### Option 1: Live MySQL Database Setup (Recommended)

1. **Start MySQL**: Make sure your local MySQL instance (e.g., via XAMPP, MAMP, or native MySQL) is active.
2. **Import Database Schema**:
   - Run the SQL statements inside [`schema.sql`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/schema.sql) in your database administration tool (like phpMyAdmin) or run the command:
     ```bash
     mysql -u root -p < schema.sql
     ```
3. **Database Configuration**:
   - Open [`config.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/config.php).
   - If your MySQL user password is not empty, update the config connection block:
     ```php
     $user = 'root';
     $pass = 'your_mysql_password';
     ```
4. **Launch Web Server**:
   - Place this project directory in your web server root (e.g., `htdocs` for XAMPP).
   - Alternatively, navigate to the folder in your shell and start the PHP built-in server:
     ```bash
     php -S localhost:8000
     ```
   - Visit `http://localhost:8000` in your web browser.

### Option 2: Running in Offline Demo Mode (Instant Preview)

If you don't have MySQL or PHP configured yet, the app automatically transitions to **Demo Mode** upon failure to connect to MySQL. 
- You can run it on any PHP server (like MAMP or PHP built-in server) without database configuration.
- Standard default users are pre-created inside session memory:
  - **Administrator account**:
    - **Username**: `admin`
    - **Password**: `admin123`
  - **Student account**:
    - **Username**: `john_doe`
    - **Password**: `student123`

---

## File Structure

- [`schema.sql`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/schema.sql) — Setup script for MySQL tables and initial mock data.
- [`config.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/config.php) — Database connection configuration with fallback mode.
- [`index.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/index.php) — Landing page & authentication (Login / Signup).
- [`register.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/register.php) — Student registration.
- [`logout.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/logout.php) — Standard session termination.
- [`student_dashboard.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/student_dashboard.php) — Student interface.
- [`admin_dashboard.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/admin_dashboard.php) — Admin dashboard.
- [`course_actions.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/course_actions.php) — Course Create/Update/Delete handlers.
- [`feedback_actions.php`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/feedback_actions.php) — Feedback Create/Update/Delete handlers.
- [`css/style.css`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/css/style.css) — Visual custom styling.
- [`js/app.js`](file:///Users/siddarthsreenivas/Desktop/new_folder/SIDDARTHSREENIVAS/wad/js/app.js) — Theme toggling, modal bindings, and rating validators.
