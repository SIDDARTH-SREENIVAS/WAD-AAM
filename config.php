<?php
/**
 * Course Feedback Management System
 * Database Configuration and Data Access Layer
 */

class DB {
    private static $pdo = null;
    private static $mode = 'mysql'; // 'mysql' or 'demo'

    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (self::$pdo !== null) {
            return;
        }

        // Database connection details
        $host = '127.0.0.1';
        $db   = 'course_feedback';
        $user = 'root';
        $pass = ''; // Leave blank for standard local setups (XAMPP/MAMP)
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$mode = 'mysql';
        } catch (PDOException $e) {
            // Gracefully fall back to Session Demo Mode if database is offline
            self::$mode = 'demo';
            self::initDemoData();
        }
    }

    public static function getMode() {
        self::init();
        return self::$mode;
    }

    private static function initDemoData() {
        // Populate Session arrays if not exists
        if (!isset($_SESSION['demo_users'])) {
            $_SESSION['demo_users'] = [
                1 => ['id' => 1, 'username' => 'admin', 'password' => password_hash('admin123', PASSWORD_BCRYPT), 'role' => 'admin'],
                2 => ['id' => 2, 'username' => 'john_doe', 'password' => password_hash('student123', PASSWORD_BCRYPT), 'role' => 'student'],
                3 => ['id' => 3, 'username' => 'jane_smith', 'password' => password_hash('student123', PASSWORD_BCRYPT), 'role' => 'student']
            ];
            $_SESSION['user_id_counter'] = 3;
        }

        if (!isset($_SESSION['demo_courses'])) {
            $_SESSION['demo_courses'] = [
                1 => ['id' => 1, 'course_code' => 'CS101', 'course_name' => 'Introduction to Computer Science', 'instructor' => 'Dr. Alan Turing'],
                2 => ['id' => 2, 'course_code' => 'CS202', 'course_name' => 'Database Management Systems', 'instructor' => 'Prof. Edgar Codd'],
                3 => ['id' => 3, 'course_code' => 'CS303', 'course_name' => 'Web Application Development', 'instructor' => 'Dr. Tim Berners-Lee'],
                4 => ['id' => 4, 'course_code' => 'MA101', 'course_name' => 'Discrete Mathematics', 'instructor' => 'Prof. Ada Lovelace']
            ];
            $_SESSION['course_id_counter'] = 4;
        }

        if (!isset($_SESSION['demo_feedback'])) {
            $_SESSION['demo_feedback'] = [
                1 => ['id' => 1, 'student_id' => 2, 'course_id' => 1, 'rating' => 5, 'comments' => 'Absolutely loved the course! The professor made complex concepts very intuitive to understand.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
                2 => ['id' => 2, 'student_id' => 2, 'course_id' => 3, 'rating' => 4, 'comments' => 'Very practical assignments. A bit fast-paced but learned a lot of modern frontend techniques.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
                3 => ['id' => 3, 'student_id' => 3, 'course_id' => 2, 'rating' => 4, 'comments' => 'Great course structure. The SQL optimization assignments were challenging but highly rewarding.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
                4 => ['id' => 4, 'student_id' => 3, 'course_id' => 3, 'rating' => 5, 'comments' => 'Best course ever. Learned PHP, CSS layouts, and built a fully functional project.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            ];
            $_SESSION['feedback_id_counter'] = 4;
        }
    }

    // --------------------------------------------------------
    // AUTHENTICATION OPERATIONS
    // --------------------------------------------------------

    public static function login($username, $password) {
        self::init();
        $username = trim($username);

        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
        } else {
            foreach ($_SESSION['demo_users'] as $user) {
                if (strcasecmp($user['username'], $username) === 0) {
                    if (password_verify($password, $user['password'])) {
                        return $user;
                    }
                }
            }
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

        if (self::$mode === 'mysql') {
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
        } else {
            foreach ($_SESSION['demo_users'] as $user) {
                if (strcasecmp($user['username'], $username) === 0) {
                    return "Username already exists.";
                }
            }
            $_SESSION['user_id_counter']++;
            $new_id = $_SESSION['user_id_counter'];
            $_SESSION['demo_users'][$new_id] = [
                'id' => $new_id,
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role
            ];
            return true;
        }
    }

    public static function getUserById($id) {
        self::init();
        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } else {
            return $_SESSION['demo_users'][$id] ?? null;
        }
    }

    // --------------------------------------------------------
    // COURSE CRUD OPERATIONS
    // --------------------------------------------------------

    public static function getCourses() {
        self::init();
        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->query("SELECT * FROM courses ORDER BY course_code ASC");
            return $stmt->fetchAll();
        } else {
            $courses = array_values($_SESSION['demo_courses']);
            usort($courses, function($a, $b) {
                return strcmp($a['course_code'], $b['course_code']);
            });
            return $courses;
        }
    }

    public static function getCourseById($id) {
        self::init();
        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } else {
            return $_SESSION['demo_courses'][$id] ?? null;
        }
    }

    public static function createCourse($code, $name, $instructor) {
        self::init();
        $code = strtoupper(trim($code));
        $name = trim($name);
        $instructor = trim($instructor);

        if (empty($code) || empty($name) || empty($instructor)) {
            return "All course fields are required.";
        }

        if (self::$mode === 'mysql') {
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
        } else {
            foreach ($_SESSION['demo_courses'] as $course) {
                if ($course['course_code'] === $code) {
                    return "Course code already exists.";
                }
            }
            $_SESSION['course_id_counter']++;
            $new_id = $_SESSION['course_id_counter'];
            $_SESSION['demo_courses'][$new_id] = [
                'id' => $new_id,
                'course_code' => $code,
                'course_name' => $name,
                'instructor' => $instructor
            ];
            return true;
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

        if (self::$mode === 'mysql') {
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
        } else {
            foreach ($_SESSION['demo_courses'] as $cid => $course) {
                if ($course['course_code'] === $code && $cid != $id) {
                    return "Course code already exists.";
                }
            }
            if (isset($_SESSION['demo_courses'][$id])) {
                $_SESSION['demo_courses'][$id]['course_code'] = $code;
                $_SESSION['demo_courses'][$id]['course_name'] = $name;
                $_SESSION['demo_courses'][$id]['instructor'] = $instructor;
                return true;
            }
            return "Course not found.";
        }
    }

    public static function deleteCourse($id) {
        self::init();
        if (self::$mode === 'mysql') {
            try {
                $stmt = self::$pdo->prepare("DELETE FROM courses WHERE id = ?");
                $stmt->execute([$id]);
                return true;
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        } else {
            if (isset($_SESSION['demo_courses'][$id])) {
                unset($_SESSION['demo_courses'][$id]);
                // Delete associated feedback cascade
                foreach ($_SESSION['demo_feedback'] as $fid => $fb) {
                    if ($fb['course_id'] == $id) {
                        unset($_SESSION['demo_feedback'][$fid]);
                    }
                }
                return true;
            }
            return "Course not found.";
        }
    }

    // --------------------------------------------------------
    // FEEDBACK CRUD OPERATIONS
    // --------------------------------------------------------

    public static function getFeedbacks($filters = []) {
        self::init();
        if (self::$mode === 'mysql') {
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
        } else {
            $results = [];
            foreach ($_SESSION['demo_feedback'] as $fb) {
                $student = $_SESSION['demo_users'][$fb['student_id']] ?? ['username' => 'Unknown Student'];
                $course = $_SESSION['demo_courses'][$fb['course_id']] ?? ['course_code' => 'N/A', 'course_name' => 'Unknown Course', 'instructor' => 'N/A'];
                
                $fb_extended = array_merge($fb, [
                    'student_name' => $student['username'],
                    'course_code' => $course['course_code'],
                    'course_name' => $course['course_name'],
                    'instructor' => $course['instructor']
                ]);

                // Filter by Student ID
                if (!empty($filters['student_id']) && $fb['student_id'] != $filters['student_id']) {
                    continue;
                }

                // Filter by Search Query
                if (!empty($filters['search'])) {
                    $search = strtolower($filters['search']);
                    $match = strpos(strtolower($fb_extended['course_code']), $search) !== false ||
                             strpos(strtolower($fb_extended['course_name']), $search) !== false ||
                             strpos(strtolower($fb_extended['instructor']), $search) !== false ||
                             strpos(strtolower($fb_extended['comments']), $search) !== false ||
                             strpos(strtolower($fb_extended['student_name']), $search) !== false;
                    if (!$match) continue;
                }

                $results[] = $fb_extended;
            }
            // Sort by created_at DESC (simulated by sorting by ID desc)
            usort($results, function($a, $b) {
                return $b['id'] - $a['id'];
            });
            return $results;
        }
    }

    public static function getFeedbackById($id) {
        self::init();
        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->prepare("SELECT * FROM feedback WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } else {
            return $_SESSION['demo_feedback'][$id] ?? null;
        }
    }

    public static function getFeedbackForCourse($student_id, $course_id) {
        self::init();
        if (self::$mode === 'mysql') {
            $stmt = self::$pdo->prepare("SELECT * FROM feedback WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student_id, $course_id]);
            return $stmt->fetch();
        } else {
            foreach ($_SESSION['demo_feedback'] as $fb) {
                if ($fb['student_id'] == $student_id && $fb['course_id'] == $course_id) {
                    return $fb;
                }
            }
            return null;
        }
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

        if (self::$mode === 'mysql') {
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
        } else {
            // Check uniqueness
            foreach ($_SESSION['demo_feedback'] as $fb) {
                if ($fb['student_id'] == $student_id && $fb['course_id'] == $course_id) {
                    return "You have already submitted feedback for this course.";
                }
            }

            $_SESSION['feedback_id_counter']++;
            $new_id = $_SESSION['feedback_id_counter'];
            $_SESSION['demo_feedback'][$new_id] = [
                'id' => $new_id,
                'student_id' => intval($student_id),
                'course_id' => intval($course_id),
                'rating' => $rating,
                'comments' => $comments,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            return true;
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

        if (self::$mode === 'mysql') {
            try {
                $stmt = self::$pdo->prepare("UPDATE feedback SET rating = ?, comments = ? WHERE id = ?");
                $stmt->execute([$rating, $comments, $id]);
                return true;
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        } else {
            if (isset($_SESSION['demo_feedback'][$id])) {
                $_SESSION['demo_feedback'][$id]['rating'] = $rating;
                $_SESSION['demo_feedback'][$id]['comments'] = $comments;
                $_SESSION['demo_feedback'][$id]['updated_at'] = date('Y-m-d H:i:s');
                return true;
            }
            return "Feedback record not found.";
        }
    }

    public static function deleteFeedback($id) {
        self::init();
        if (self::$mode === 'mysql') {
            try {
                $stmt = self::$pdo->prepare("DELETE FROM feedback WHERE id = ?");
                $stmt->execute([$id]);
                return true;
            } catch (PDOException $e) {
                return $e->getMessage();
            }
        } else {
            if (isset($_SESSION['demo_feedback'][$id])) {
                unset($_SESSION['demo_feedback'][$id]);
                return true;
            }
            return "Feedback record not found.";
        }
    }
}
?>
