<?php
/**
 * Course Feedback Management System
 * Database Configuration, Data Access Layer & API Utilities (MySQL Only)
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jsonResponse($success, $payload = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    if ($success) {
        echo json_encode([
            'success' => true,
            'data' => $payload
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => is_string($payload) ? $payload : ($payload['message'] ?? 'An error occurred'),
            'error' => $payload
        ]);
    }
    exit;
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

class DB {
    private static $pdo = null;

    public static function init() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Database connection details
        $host = '127.0.0.1';
        $db   = 'course_feedback';
        $user = 'root';
        $pass = ''; // Default local password (adjust if your MySQL root has a password)
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return self::$pdo;
        } catch (PDOException $e) {
            jsonResponse(false, "Database Connection Failed: " . $e->getMessage(), 500);
        }
    }

    // --------------------------------------------------------
    // AUTHENTICATION OPERATIONS
    // --------------------------------------------------------

    public static function login($username, $password) {
        self::init();
        $username = trim($username);

        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public static function register($username, $password, $role = 'student') {
        self::init();
        $username = trim($username);
        if (empty($username) || empty($password)) {
            return "Username and password cannot be empty.";
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = self::$pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $role]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "Username already exists.";
            }
            return "Registration error: " . $e->getMessage();
        }
    }

    public static function getUserById($id) {
        self::init();
        $stmt = self::$pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // --------------------------------------------------------
    // COURSE CRUD OPERATIONS
    // --------------------------------------------------------

    public static function getCourses() {
        self::init();
        $stmt = self::$pdo->query("SELECT * FROM courses ORDER BY course_code ASC");
        return $stmt->fetchAll();
    }

    public static function getCourseById($id) {
        self::init();
        $stmt = self::$pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function createCourse($code, $name, $instructor) {
        self::init();
        $code = strtoupper(trim($code));
        $name = trim($name);
        $instructor = trim($instructor);

        if (empty($code) || empty($name) || empty($instructor)) {
            return "All course fields are required.";
        }

        try {
            $stmt = self::$pdo->prepare("INSERT INTO courses (course_code, course_name, instructor) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $instructor]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "Course code already exists.";
            }
            return $e->getMessage();
        }
    }

    public static function updateCourse($id, $code, $name, $instructor) {
        self::init();
        $code = strtoupper(trim($code));
        $name = trim($name);
        $instructor = trim($instructor);

        if (empty($code) || empty($name) || empty($instructor)) {
            return "All course fields are required.";
        }

        try {
            $stmt = self::$pdo->prepare("UPDATE courses SET course_code = ?, course_name = ?, instructor = ? WHERE id = ?");
            $stmt->execute([$code, $name, $instructor, $id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "Course code already exists.";
            }
            return $e->getMessage();
        }
    }

    public static function deleteCourse($id) {
        self::init();
        try {
            $stmt = self::$pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    // --------------------------------------------------------
    // FEEDBACK CRUD OPERATIONS
    // --------------------------------------------------------

    public static function getFeedbacks($filters = []) {
        self::init();
        $sql = "SELECT f.*, c.course_code, c.course_name, c.instructor, u.username as student_name 
                FROM feedback f
                JOIN courses c ON f.course_id = c.id
                JOIN users u ON f.student_id = u.id";
        
        $where = [];
        $params = [];
        
        if (!empty($filters['student_id'])) {
            $where[] = "f.student_id = ?";
            $params[] = $filters['student_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(c.course_code LIKE ? OR c.course_name LIKE ? OR c.instructor LIKE ? OR f.comments LIKE ? OR u.username LIKE ?)";
            $searchWild = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchWild, $searchWild, $searchWild, $searchWild, $searchWild]);
        }
        
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getFeedbackById($id) {
        self::init();
        $stmt = self::$pdo->prepare("SELECT * FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getFeedbackForCourse($student_id, $course_id) {
        self::init();
        $stmt = self::$pdo->prepare("SELECT * FROM feedback WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$student_id, $course_id]);
        return $stmt->fetch();
    }

    public static function createFeedback($student_id, $course_id, $rating, $comments) {
        self::init();
        $rating = intval($rating);
        $comments = trim($comments);

        if ($rating < 1 || $rating > 5) {
            return "Rating must be between 1 and 5.";
        }
        if (empty($comments)) {
            return "Comments cannot be empty.";
        }

        try {
            $stmt = self::$pdo->prepare("INSERT INTO feedback (student_id, course_id, rating, comments) VALUES (?, ?, ?, ?)");
            $stmt->execute([$student_id, $course_id, $rating, $comments]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "You have already submitted feedback for this course.";
            }
            return $e->getMessage();
        }
    }

    public static function updateFeedback($id, $rating, $comments) {
        self::init();
        $rating = intval($rating);
        $comments = trim($comments);

        if ($rating < 1 || $rating > 5) {
            return "Rating must be between 1 and 5.";
        }
        if (empty($comments)) {
            return "Comments cannot be empty.";
        }

        try {
            $stmt = self::$pdo->prepare("UPDATE feedback SET rating = ?, comments = ? WHERE id = ?");
            $stmt->execute([$rating, $comments, $id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public static function deleteFeedback($id) {
        self::init();
        try {
            $stmt = self::$pdo->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
?>
