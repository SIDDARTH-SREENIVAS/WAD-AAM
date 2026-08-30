<?php
require_once 'config.php';

DB::init();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        header("Location: student_dashboard.php");
        exit;
    }
}

$error_message = '';
$success_message = '';

// Check if redirected with registration success
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success_message = "Registration successful! You can now log in.";
}

// Handle login post request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        $user = DB::login($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Course Feedback System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="animate-fade-in">
    <div class="app-container">
        <!-- Header area with theme toggle -->
        <header>
            <div class="logo-section">
                <h1>Feedback Loop</h1>
                <p>Course Feedback Management System</p>
            </div>
            <div class="header-controls">
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Theme"></button>
            </div>
        </header>

        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Enter your credentials to access your dashboard</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (DB::getMode() === 'demo'): ?>
                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Running in <strong>Demo Mode</strong>. Use default users: Admin: <code>admin</code> (pwd: <code>admin123</code>) or Student: <code>john_doe</code> (pwd: <code>student123</code>).</span>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="input-control" required placeholder="e.g. john_doe" autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="input-control" required placeholder="••••••••" autocomplete="current-password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="login" class="btn btn-primary btn-full">Sign In</button>
                    </div>
                </form>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Create Student Account</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
