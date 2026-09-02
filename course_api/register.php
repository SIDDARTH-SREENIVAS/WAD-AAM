<?php
/**
 * Course Feedback Management System
 * Action: Student Registration
 */

require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

$input = getJsonInput();
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

if (empty($username) || empty($password) || empty($confirm_password)) {
    jsonResponse(false, 'Please fill in all fields.', 400);
}

if (strlen($username) < 3) {
    jsonResponse(false, 'Username must be at least 3 characters long.', 400);
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Password must be at least 6 characters long.', 400);
}

if ($password !== $confirm_password) {
    jsonResponse(false, 'Passwords do not match.', 400);
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
    $stmt->execute([$username, $hashed_password]);

    jsonResponse(true, [
        'message' => 'Registration successful! You can now log in.'
    ], 201);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(false, 'Username already exists. Please choose a different username.', 400);
    }
    jsonResponse(false, 'Registration error: ' . $e->getMessage(), 500);
}
?>
