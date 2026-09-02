<?php
/**
 * Course Feedback Management System
 * Action: User Login
 */

require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

$input = getJsonInput();
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(false, 'Please enter both username and password.', 400);
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        jsonResponse(true, [
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
    } else {
        jsonResponse(false, 'Invalid username or password.', 401);
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
