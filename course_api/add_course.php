<?php
/**
 * Course Feedback Management System
 * Action: Add New Course (Admin Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized. Administrator access required.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

$input = getJsonInput();
$code = strtoupper(trim($input['course_code'] ?? ''));
$name = trim($input['course_name'] ?? '');
$instructor = trim($input['instructor'] ?? '');

if (empty($code) || empty($name) || empty($instructor)) {
    jsonResponse(false, 'All course fields (code, name, instructor) are required.', 400);
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, instructor) VALUES (?, ?, ?)");
    $stmt->execute([$code, $name, $instructor]);

    jsonResponse(true, ['message' => 'Course added successfully!'], 201);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(false, 'Course code already exists.', 400);
    }
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
