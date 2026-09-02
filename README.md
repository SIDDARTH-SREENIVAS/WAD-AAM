# 🎓 Course Feedback Management System

A decoupled, responsive, and secure web application designed for students to submit course feedback and for administrators to manage courses and review feedback analytics.

Built using a multi-tier architecture separating **HTML**, **CSS**, **JavaScript**, and **PHP** into isolated files with a **MySQL** relational database.

---

## 📋 Prerequisites

Before running the project locally, make sure you have the following installed on your system:

- **Git**: [Download Git](https://git-scm.com/downloads)
- **PHP** (v7.4 or v8.x+): [Download PHP](https://www.php.net/downloads)
- **MySQL Database Server**:
  - Standalone MySQL: [Download MySQL](https://dev.mysql.com/downloads/mysql/)
  - **OR** an all-in-one local stack like **XAMPP** / **MAMP** / **WampServer** (which includes PHP, Apache, and MySQL).

---

## 🚀 Getting Started: Step-by-Step Setup from Cloning

### Step 1: Clone the Repository

Open your terminal or command prompt and clone the repository:

```bash
git clone https://github.com/SIDDARTH-SREENIVAS/wad.git
cd wad
```

---

### Step 2: Set Up the MySQL Database

Make sure your MySQL server is running (e.g., start MySQL service via terminal or click **Start** next to MySQL in the XAMPP/MAMP Control Panel).

#### Option A: Using MySQL Command Line (Recommended)

Run the following command in the project root to create the `course_feedback` database, tables, and seed sample data:

```bash
# If your MySQL root user has no password (default XAMPP/Homebrew/MAMP):
mysql -u root < schema.sql

# If your MySQL root user has a password:
mysql -u root -p < schema.sql
```

#### Option B: Using phpMyAdmin (GUI)

1. Open your browser and navigate to `http://localhost/phpmyadmin`.
2. Click **New** on the left sidebar and create a database named `course_feedback` (with collation `utf8mb4_general_ci`).
3. Select the newly created `course_feedback` database.
4. Click the **Import** tab in the top navigation.
5. Click **Choose File**, select [`schema.sql`](schema.sql) from the cloned project folder, and click **Import** (or **Go**) at the bottom.

---

### 🔍 How to Inspect & Verify the Database in MySQL CLI

To verify that the database and tables are populated correctly, enter the MySQL monitor:

```bash
# Connect to MySQL prompt
mysql -u root
# (or 'mysql -u root -p' if password-protected)
```

Inside the `mysql>` prompt, run:

```sql
-- 1. View all databases
SHOW DATABASES;

-- 2. Select the course feedback database
USE course_feedback;

-- 3. View all created tables (courses, feedback, users)
SHOW TABLES;

-- 4. View table schema and column types
DESC courses;
DESC users;
DESC feedback;

-- 5. View sample data records
SELECT * FROM courses;
SELECT * FROM users;
SELECT * FROM feedback;

-- 6. Exit MySQL monitor
EXIT;
```

---

### Step 3: Configure Database Credentials

Open [`course_api/db_connect.php`](course_api/db_connect.php) in your code editor and verify your local database connection settings:

```php
// Database connection details inside course_api/db_connect.php
$host    = '127.0.0.1';        // Database host (default: localhost or 127.0.0.1)
$db      = 'course_feedback';  // Database name in phpMyAdmin
$user    = 'root';             // Database username (default: root)
$pass    = '';                 // Database password (default: empty for XAMPP, 'root' for MAMP)
$charset = 'utf8mb4';
```

> **Note:** If your MySQL/phpMyAdmin setup uses a password (e.g., `root`, `admin`, or a custom password), update `$pass = 'your_password';` accordingly.

---

### Step 4: Run the Application Locally

#### Method 1: Using PHP's Built-in Development Server (Fastest)

In your terminal, from the project root directory (`wad/`), run:

```bash
php -S 127.0.0.1:8000
```

Open your browser and navigate to:
👉 **`http://127.0.0.1:8000/index.html`** (or `http://localhost:8000/index.html`)

---

#### Method 2: Using XAMPP / WAMP / MAMP

1. Move or copy the cloned `wad/` folder into your web server's public directory:
   - **XAMPP (Windows/Linux/macOS)**: `htdocs/wad/`
   - **MAMP (macOS)**: `/Applications/MAMP/htdocs/wad/`
   - **WampServer (Windows)**: `www/wad/`
2. Start **Apache** and **MySQL** services from your control panel.
3. Open your browser and navigate to:
   👉 **`http://localhost/wad/index.html`**

---

## 👥 Default Login Credentials

The [`schema.sql`](schema.sql) script pre-populates default accounts for testing:

| Role | Username | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Full access to add/edit/delete courses, view statistics, and review all feedback |
| **Student** | `john_doe` | `student123` | Submit course feedback, rate with 5 stars, edit/delete own reviews |
| **Student** | `jane_smith` | `student123` | Submit course feedback, rate with 5 stars, edit/delete own reviews |

> You can also click **"Create Student Account"** on [`register.html`](register.html) to register a brand new student account.

---

## 🏛️ Project Directory Structure

```
wad/
├── index.html                  # Pure HTML: Login & authentication portal
├── register.html               # Pure HTML: Student registration view
├── student_dashboard.html      # Pure HTML: Student evaluation & review portal
├── admin_dashboard.html        # Pure HTML: Administrator control panel
├── css/
│   └── style.css               # Pure CSS: Design system, dark/light themes, animations & components
├── js/
│   ├── app.js                  # Pure JS: Theme switcher (dark/light), modal manager & toast alerts
│   ├── auth.js                 # Pure JS: Login & registration handlers with validation
│   ├── student.js              # Pure JS: Dynamic course feed, interactive star rating & feedback CRUD
│   └── admin.js                # Pure JS: Analytics metrics & course CRUD
├── course_api/
│   ├── db_connect.php          # Database connection (PDO MySQL / phpMyAdmin)
│   ├── login.php               # Authenticate credentials & initialize session
│   ├── register.php            # Register new student account in MySQL
│   ├── check_session.php       # Verify active session and role
│   ├── logout.php              # Destroy session and log out
│   ├── get_courses.php         # Fetch courses list
│   ├── add_course.php          # Insert new course (Admin only)
│   ├── update_course.php       # Update existing course (Admin only)
│   ├── delete_course.php       # Delete course & cascading reviews (Admin only)
│   ├── get_feedback.php        # Fetch feedback reviews joined with courses & students
│   ├── submit_feedback.php     # Submit course evaluation rating & comments (Student only)
│   ├── update_feedback.php     # Update feedback review (Student only)
│   ├── delete_feedback.php     # Delete feedback review (Student only)
│   └── get_stats.php           # Calculate metrics & averages (Admin only)
├── schema.sql                  # Database schema with tables and seeded records
└── README.md                   # Project documentation & setup guide
```

---

## 🔌 API Endpoints Reference

All frontend files interact with dedicated backend PHP scripts via asynchronous JSON requests (`fetch`):

### 1. Authentication Endpoints
- **`POST course_api/login.php`** &rarr; Authenticates user credentials (`{username, password}`).
- **`POST course_api/register.php`** &rarr; Registers a new student account (`{username, password, confirm_password}`).
- **`GET course_api/check_session.php`** &rarr; Checks active login session and user role.
- **`GET/POST course_api/logout.php`** &rarr; Destroys active session and logs out user.

### 2. Course Management Endpoints
- **`GET course_api/get_courses.php`** &rarr; Retrieves all available courses.
- **`POST course_api/add_course.php`** *(Admin only)* &rarr; Adds a new course (`{course_code, course_name, instructor}`).
- **`POST course_api/update_course.php`** *(Admin only)* &rarr; Updates course details (`{id, course_code, course_name, instructor}`).
- **`POST course_api/delete_course.php?id=X`** *(Admin only)* &rarr; Deletes a course and cascades to remove its feedbacks.

### 3. Feedback Management Endpoints
- **`GET course_api/get_feedback.php`** &rarr; Retrieves feedback reviews (supports `?search=keyword` or `?my_feedback=1`).
- **`POST course_api/submit_feedback.php`** *(Student only)* &rarr; Submits a review (`{course_id, rating, comments}`).
- **`POST course_api/update_feedback.php`** *(Student only)* &rarr; Updates author's review (`{id, rating, comments}`).
- **`POST course_api/delete_feedback.php?id=X`** *(Author only)* &rarr; Deletes author's review.

### 4. Admin Statistics Endpoint
- **`GET course_api/get_stats.php`** *(Admin only)* &rarr; Calculates total courses, total reviews, and overall average star rating.

---

## 🔧 Troubleshooting

- **`Database Connection Failed: Access denied for user 'root'@'localhost'`**:
  Open [`course_api/db_connect.php`](course_api/db_connect.php) and check your MySQL password `$pass`.
- **`SQLSTATE[HY000] [2002] Connection refused`**:
  Make sure your MySQL database service is running on port 3306.
- **Port 8000 already in use**:
  Start PHP server on a different port:
  ```bash
  php -S 127.0.0.1:8080
  ```
  Then visit `http://127.0.0.1:8080/index.html`.
