<?php
/**
 * Course Feedback Management System
 * Feedback API (List, Create, Update, Delete)
 */

require_once __DIR__ . '/config.php';

DB::init();

if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized. Please log in.', 401);
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$action = $_GET['action'] ?? 'list';
$input = getJsonInput();

switch ($action) {
    case 'list':
        $filters = [];
        
        // If student requests their own reviews or specified student_id
        if ($user_role === 'student' || isset($_GET['my_feedback'])) {
            $filters['student_id'] = $user_id;
        } elseif (!empty($_GET['student_id'])) {
            $filters['student_id'] = intval($_GET['student_id']);
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }

        $feedbacks = DB::getFeedbacks($filters);
        jsonResponse(true, ['feedbacks' => $feedbacks]);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $feedback = DB::getFeedbackById($id);
        if ($feedback) {
            jsonResponse(true, ['feedback' => $feedback]);
        } else {
            jsonResponse(false, 'Feedback record not found.', 404);
        }
        break;

    case 'create':
        if ($user_role !== 'student') {
            jsonResponse(false, 'Only students can submit feedback.', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

        $course_id = intval($input['course_id'] ?? 0);
        $rating = intval($input['rating'] ?? 0);
        $comments = trim($input['comments'] ?? '');

        if ($course_id <= 0 || $rating < 1 || $rating > 5 || empty($comments)) {
            jsonResponse(false, 'All fields are required and rating must be between 1 and 5.', 400);
        }

        $result = DB::createFeedback($user_id, $course_id, $rating, $comments);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Feedback submitted successfully!'], 201);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    case 'update':
        if ($user_role !== 'student') {
            jsonResponse(false, 'Only students can update feedback.', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

        $id = intval($input['id'] ?? 0);
        $rating = intval($input['rating'] ?? 0);
        $comments = trim($input['comments'] ?? '');

        $feedback = DB::getFeedbackById($id);
        if (!$feedback || $feedback['student_id'] != $user_id) {
            jsonResponse(false, 'Unauthorized to edit this feedback.', 403);
        }

        if ($rating < 1 || $rating > 5 || empty($comments)) {
            jsonResponse(false, 'All fields are required and rating must be between 1 and 5.', 400);
        }

        $result = DB::updateFeedback($id, $rating, $comments);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Feedback updated successfully!']);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    case 'delete':
        $id = intval($_GET['id'] ?? ($input['id'] ?? 0));
        if ($id <= 0) {
            jsonResponse(false, 'Invalid feedback record ID.', 400);
        }

        $feedback = DB::getFeedbackById($id);

        if (!$feedback) {
            jsonResponse(false, 'Feedback record not found.', 404);
        }

        // Authorization check: Only the author student can delete their own feedback
        if (intval($feedback['student_id']) !== intval($user_id)) {
            jsonResponse(false, 'Unauthorized to delete this feedback.', 403);
        }

        $result = DB::deleteFeedback($id);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Feedback deleted successfully!']);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    default:
        jsonResponse(false, 'Unknown feedback action.', 400);
        break;
}
?>
