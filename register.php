<?php
require_once 'config.php';

DB::init();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error_message = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $result = DB::register($username, $password, 'student');
        if ($result === true) {
            header("Location: index.php?registered=success");
            exit;
        } else {
            $error_message = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Course Feedback System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="animate-fade-in">
    <div class="app-container">
        <!-- Header -->
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
                    <h2>Create Account</h2>
                    <p>Register as a student to submit course feedback</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="username">Choose Username</label>
                        <input type="text" id="username" name="username" class="input-control" required placeholder="e.g. alex_stone" minlength="3" autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="input-control" required placeholder="Min 6 characters" minlength="6" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="input-control" required placeholder="Repeat password" minlength="6" autocomplete="new-password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="register" class="btn btn-primary btn-full">Create Account</button>
                    </div>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="index.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
