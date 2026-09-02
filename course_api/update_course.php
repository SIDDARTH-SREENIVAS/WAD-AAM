<?php
/**
 * Course Feedback Management System
 * Action: Update Course Details (Admin Only)
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
$id = intval($input['id'] ?? 0);
$code = strtoupper(trim($input['course_code'] ?? ''));
$name = trim($input['course_name'] ?? '');
$instructor = trim($input['instructor'] ?? '');

if ($id <= 0 || empty($code) || empty($name) || empty($instructor)) {
    jsonResponse(false, 'All course fields and a valid ID are required.', 400);
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("UPDATE courses SET course_code = ?, course_name = ?, instructor = ? WHERE id = ?");
    $stmt->execute([$code, $name, $instructor, $id]);

    jsonResponse(true, ['message' => 'Course updated successfully!']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(false, 'Course code already exists.', 400);
    }
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
