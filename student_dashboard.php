<?php
require_once 'config.php';

DB::init();

// Redirect to login if not authenticated or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['username'];

// Get all courses and student's feedbacks
$all_courses = DB::getCourses();
$my_feedbacks = DB::getFeedbacks(['student_id' => $student_id]);

// Filter courses that this student hasn't reviewed yet
$submitted_course_ids = array_column($my_feedbacks, 'course_id');
$pending_courses = array_filter($all_courses, function($course) use ($submitted_course_ids) {
    return !in_array($course['id'], $submitted_course_ids);
});

// Messages from actions
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Feedback Loop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="animate-fade-in">
    <div class="app-container">
        <!-- Dashboard Header -->
        <header>
            <div class="logo-section">
                <h1>Feedback Loop</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($student_name); ?></strong> <span class="badge badge-student">Student</span></p>
            </div>
            <div class="header-controls">
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Theme"></button>
                <a href="logout.php" class="btn btn-secondary btn-sm">Sign Out</a>
            </div>
        </header>

        <!-- Status Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (DB::getMode() === 'demo'): ?>
            <div class="alert alert-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>System running in offline <strong>Demo Mode</strong>. Your changes will be saved to your session context.</span>
            </div>
        <?php endif; ?>

        <!-- Dashboard Body Grid -->
        <div class="dashboard-grid">
            <!-- Left Side: Submit Course Feedback (Create Operation) -->
            <div class="card-panel">
                <h3>Submit Feedback</h3>
                <?php if (empty($pending_courses)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎉</div>
                        <p>You have submitted feedback for all available courses! Outstanding job.</p>
                    </div>
                <?php else: ?>
                    <form id="feedback-submission-form" action="feedback_actions.php?action=create" method="POST">
                        <div class="form-group">
                            <label for="course_id">Select Course</label>
                            <select id="course_id" name="course_id" class="input-control" required>
                                <option value="" disabled selected>-- Choose Course --</option>
                                <?php foreach ($pending_courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php unset($course); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Rating</label>
                            <div class="star-rating-widget">
                                <input type="radio" id="star5" name="rating" value="5">
                                <label for="star5" title="5 Stars">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 Stars">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" title="3 Stars">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 Stars">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 Star">★</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comments">Comments / Review</label>
                            <textarea id="comments" name="comments" class="input-control" rows="5" required placeholder="What did you think of the course structure, instructor, assessments?"></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-full">Submit Feedback</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Right Side: Feedback History (Read, Update, Delete Operations) -->
            <div>
                <div class="records-header">
                    <h2>My Feedback History</h2>
                    <div><?php echo count($my_feedbacks); ?> reviews submitted</div>
                </div>

                <?php if (empty($my_feedbacks)): ?>
                    <div class="card-panel empty-state">
                        <div class="empty-state-icon">📝</div>
                        <p>You haven't submitted any feedback yet. Choose a course on the left to start!</p>
                    </div>
                <?php else: ?>
                    <div class="records-container">
                        <?php foreach ($my_feedbacks as $fb): ?>
                            <div class="feedback-card">
                                <div>
                                    <div class="feedback-meta">
                                        <div>
                                            <span class="course-code-tag"><?php echo htmlspecialchars($fb['course_code']); ?></span>
                                            <h4><?php echo htmlspecialchars($fb['course_name']); ?></h4>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.15rem;">
                                                Instructor: <?php echo htmlspecialchars($fb['instructor']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="stars-display">
                                                <?php 
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $fb['rating']) {
                                                        echo '★';
                                                    } else {
                                                        echo '<span class="star-empty">★</span>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 1rem;" class="feedback-comment">
                                        <?php echo htmlspecialchars($fb['comments']); ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="feedback-student-meta">
                                        Submitted on: <?php echo date('M d, Y h:i A', strtotime($fb['created_at'])); ?>
                                    </div>
                                    <div class="record-actions">
                                        <!-- Edit button triggers modal -->
                                        <button class="btn btn-secondary btn-sm btn-edit-feedback" 
                                                data-id="<?php echo $fb['id']; ?>"
                                                data-course-name="<?php echo htmlspecialchars($fb['course_code'] . ' - ' . $fb['course_name']); ?>"
                                                data-rating="<?php echo $fb['rating']; ?>"
                                                data-comments="<?php echo htmlspecialchars($fb['comments']); ?>">
                                            Edit
                                        </button>
                                        <!-- Delete button triggers verification -->
                                        <a href="feedback_actions.php?action=delete&id=<?php echo $fb['id']; ?>" 
                                           class="btn btn-danger btn-sm btn-delete-confirm"
                                           data-confirm-message="Are you sure you want to delete this feedback review?">
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php unset($fb); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
       Edit Feedback Modal Overlay
       ========================================================================== -->
    <div id="edit-feedback-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Feedback</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form action="feedback_actions.php?action=update" method="POST">
                <input type="hidden" id="edit-feedback-id" name="id">
                
                <div class="form-group">
                    <label>Course</label>
                    <div id="edit-feedback-course-title" style="font-weight: 600; margin-bottom: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label>Rating</label>
                    <div class="star-rating-widget">
                        <input type="radio" id="edit-star5" name="rating" value="5">
                        <label for="edit-star5" title="5 Stars">★</label>
                        <input type="radio" id="edit-star4" name="rating" value="4">
                        <label for="edit-star4" title="4 Stars">★</label>
                        <input type="radio" id="edit-star3" name="rating" value="3">
                        <label for="edit-star3" title="3 Stars">★</label>
                        <input type="radio" id="edit-star2" name="rating" value="2">
                        <label for="edit-star2" title="2 Stars">★</label>
                        <input type="radio" id="edit-star1" name="rating" value="1">
                        <label for="edit-star1" title="1 Star">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit-feedback-comments">Comments / Review</label>
                    <textarea id="edit-feedback-comments" name="comments" class="input-control" rows="5" required></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-close-modal" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
