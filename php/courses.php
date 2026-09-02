<?php
/**
 * Course Feedback Management System
 * Course API (List, Create, Update, Delete)
 */

require_once __DIR__ . '/config.php';

DB::init();

$action = $_GET['action'] ?? 'list';
$input = getJsonInput();

// List courses can be accessed by authenticated users (or anyone to render form options)
if ($action === 'list') {
    $courses = DB::getCourses();
    jsonResponse(true, ['courses' => $courses]);
}

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    $course = DB::getCourseById($id);
    if ($course) {
        jsonResponse(true, ['course' => $course]);
    } else {
        jsonResponse(false, 'Course not found.', 404);
    }
}

// Protected Actions (Admin Only)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized. Admin privileges required.', 403);
}

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

        $code = trim($input['course_code'] ?? '');
        $name = trim($input['course_name'] ?? '');
        $instructor = trim($input['instructor'] ?? '');

        if (empty($code) || empty($name) || empty($instructor)) {
            jsonResponse(false, 'All fields are required.', 400);
        }

        $result = DB::createCourse($code, $name, $instructor);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Course created successfully!'], 201);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

        $id = intval($input['id'] ?? 0);
        $code = trim($input['course_code'] ?? '');
        $name = trim($input['course_name'] ?? '');
        $instructor = trim($input['instructor'] ?? '');

        if ($id <= 0 || empty($code) || empty($name) || empty($instructor)) {
            jsonResponse(false, 'All fields are required.', 400);
        }

        $result = DB::updateCourse($id, $code, $name, $instructor);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Course updated successfully!']);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    case 'delete':
        $id = intval($_GET['id'] ?? ($input['id'] ?? 0));
        if ($id <= 0) {
            jsonResponse(false, 'Invalid course ID.', 400);
        }

        $result = DB::deleteCourse($id);
        if ($result === true) {
            jsonResponse(true, ['message' => 'Course and associated feedback deleted successfully!']);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    default:
        jsonResponse(false, 'Unknown course action.', 400);
        break;
}
?>
