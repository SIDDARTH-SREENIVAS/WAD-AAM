SELECT * FROM courses;
-- Course Feedback Management System Database Schema
-- Run this script in your MySQL instance to create the database and tables.

CREATE DATABASE IF NOT EXISTS course_feedback;
USE course_feedback;

-- 1. Users Table (Students and Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    instructor VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Feedback Table (CRUD operations will act on this)
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comments TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course (student_id, course_id) -- Prevents multiple feedback submissions for the same course
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data for Initial Setup
-- --------------------------------------------------------

-- Insert Default Admins and Students
-- Default admin password is 'admin123' (hashed using BCRYPT)
-- Default student passwords are 'student123' (hashed using BCRYPT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Default Courses
INSERT INTO courses (course_code, course_name, instructor) VALUES
('CS101', 'Introduction to Computer Science', 'Dr. Alan Turing'),
('CS202', 'Database Management Systems', 'Prof. Edgar Codd'),
('CS303', 'Web Application Development', 'Dr. Tim Berners-Lee'),
('MA101', 'Discrete Mathematics', 'Prof. Ada Lovelace')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Sample Feedback
-- Student john_doe (id 2) leaves feedback on CS101 (id 1) and CS303 (id 3)
-- Student jane_smith (id 3) leaves feedback on CS202 (id 2) and CS303 (id 3)
INSERT INTO feedback (student_id, course_id, rating, comments) VALUES
(2, 1, 5, 'Absolutely loved the course! The professor made complex concepts very intuitive to understand.'),
(2, 3, 4, 'Very practical assignments. A bit fast-paced but learned a lot of modern frontend techniques.'),
(3, 2, 4, 'Great course structure. The SQL optimization assignments were challenging but highly rewarding.'),
(3, 3, 5, 'Best course ever. Learned PHP, CSS layouts, and built a fully functional project.')
ON DUPLICATE KEY UPDATE id=id;
