<?php
/**
 * Course Feedback Management System
 * Authentication API (Login, Register, Check Session, Logout)
 */

require_once __DIR__ . '/config.php';

DB::init();

$action = $_GET['action'] ?? '';
$input = getJsonInput();

switch ($action) {
    case 'check_session':
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            jsonResponse(true, [
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ]);
        } else {
            jsonResponse(true, [
                'authenticated' => false
            ]);
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            jsonResponse(false, 'Please provide both username and password.', 400);
        }

        $user = DB::login($username, $password);
        if ($user) {
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
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', 405);
        }

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

        $result = DB::register($username, $password, 'student');
        if ($result === true) {
            jsonResponse(true, [
                'message' => 'Registration successful! You can now log in.'
            ], 201);
        } else {
            jsonResponse(false, $result, 400);
        }
        break;

    case 'logout':
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        jsonResponse(true, ['message' => 'Logged out successfully']);
        break;

    default:
        jsonResponse(false, 'Unknown authentication action.', 400);
        break;
}
?>
