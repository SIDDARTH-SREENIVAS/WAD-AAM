<?php
/**
 * Course Feedback Management System
 * Course Operations (Create, Update, Delete) - Admin Only
 */

require_once 'config.php';
DB::init();

// Security: User must be logged in and must be an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: index.php");
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['course_code'] ?? '');
            $name = trim($_POST['course_name'] ?? '');
            $instructor = trim($_POST['instructor'] ?? '');

            if (empty($code) || empty($name) || empty($instructor)) {
                $_SESSION['error_message'] = "All fields are required.";
            } else {
                $result = DB::createCourse($code, $name, $instructor);
                if ($result === true) {
                    $_SESSION['success_message'] = "Course added successfully!";
                } else {
                    $_SESSION['error_message'] = $result;
                }
            }
        }
        header("Location: admin_dashboard.php");
        exit;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $code = trim($_POST['course_code'] ?? '');
            $name = trim($_POST['course_name'] ?? '');
            $instructor = trim($_POST['instructor'] ?? '');

            if ($id <= 0 || empty($code) || empty($name) || empty($instructor)) {
                $_SESSION['error_message'] = "All fields are required.";
            } else {
                $result = DB::updateCourse($id, $code, $name, $instructor);
                if ($result === true) {
                    $_SESSION['success_message'] = "Course details updated successfully!";
                } else {
                    $_SESSION['error_message'] = $result;
                }
            }
        }
        header("Location: admin_dashboard.php");
        exit;

    case 'delete':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error_message'] = "Invalid course record ID.";
        } else {
            $result = DB::deleteCourse($id);
            if ($result === true) {
                $_SESSION['success_message'] = "Course deleted successfully along with all related student reviews.";
            } else {
                $_SESSION['error_message'] = $result;
            }
        }
        header("Location: admin_dashboard.php");
        exit;

    default:
        $_SESSION['error_message'] = "Unknown course action requested.";
        header("Location: admin_dashboard.php");
        exit;
}
?>
