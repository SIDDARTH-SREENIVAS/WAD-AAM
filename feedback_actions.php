<?php
/**
 * Course Feedback Management System
 * Feedback Operations (Create, Update, Delete)
 */

require_once 'config.php';
DB::init();

// Security: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        // Only students can create feedback
        if ($user_role !== 'student') {
            $_SESSION['error_message'] = "Unauthorized access.";
            header("Location: index.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id = intval($_POST['course_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $comments = trim($_POST['comments'] ?? '');

            if ($course_id <= 0 || $rating < 1 || $rating > 5 || empty($comments)) {
                $_SESSION['error_message'] = "All fields are required and rating must be between 1 and 5.";
            } else {
                $result = DB::createFeedback($user_id, $course_id, $rating, $comments);
                if ($result === true) {
                    $_SESSION['success_message'] = "Feedback submitted successfully!";
                } else {
                    $_SESSION['error_message'] = $result;
                }
            }
        }
        header("Location: student_dashboard.php");
        exit;

    case 'update':
        // Only students can update their feedback
        if ($user_role !== 'student') {
            $_SESSION['error_message'] = "Unauthorized access.";
            header("Location: index.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $feedback_id = intval($_POST['id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $comments = trim($_POST['comments'] ?? '');

            // Verify feedback ownership
            $feedback = DB::getFeedbackById($feedback_id);
            if (!$feedback || $feedback['student_id'] != $user_id) {
                $_SESSION['error_message'] = "You are not authorized to update this feedback.";
                header("Location: student_dashboard.php");
                exit;
            }

            if ($rating < 1 || $rating > 5 || empty($comments)) {
                $_SESSION['error_message'] = "All fields are required and rating must be between 1 and 5.";
            } else {
                $result = DB::updateFeedback($feedback_id, $rating, $comments);
                if ($result === true) {
                    $_SESSION['success_message'] = "Feedback updated successfully!";
                } else {
                    $_SESSION['error_message'] = $result;
                }
            }
        }
        header("Location: student_dashboard.php");
        exit;

    case 'delete':
        $feedback_id = intval($_GET['id'] ?? 0);
        $feedback = DB::getFeedbackById($feedback_id);

        if (!$feedback) {
            $_SESSION['error_message'] = "Feedback record not found.";
            $redirect = ($user_role === 'admin') ? "admin_dashboard.php" : "student_dashboard.php";
            header("Location: $redirect");
            exit;
        }

        // Verify authorization: either admin, or the student who wrote it
        if ($user_role === 'admin') {
            $result = DB::deleteFeedback($feedback_id);
            if ($result === true) {
                $_SESSION['success_message'] = "Feedback record deleted successfully by administrator.";
            } else {
                $_SESSION['error_message'] = $result;
            }
            header("Location: admin_dashboard.php");
            exit;
        } elseif ($user_role === 'student' && $feedback['student_id'] == $user_id) {
            $result = DB::deleteFeedback($feedback_id);
            if ($result === true) {
                $_SESSION['success_message'] = "Feedback deleted successfully.";
            } else {
                $_SESSION['error_message'] = $result;
            }
            header("Location: student_dashboard.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Unauthorized access.";
            header("Location: index.php");
            exit;
        }

    default:
        $_SESSION['error_message'] = "Unknown feedback action.";
        header("Location: index.php");
        exit;
}
?>
