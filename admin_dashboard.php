<?php
require_once 'config.php';

DB::init();

// Redirect to login if not authenticated or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['username'];

// Search Query
$search = $_GET['search'] ?? '';

// Fetch Data
$all_courses = DB::getCourses();
$all_feedbacks = DB::getFeedbacks(['search' => $search]);

// Calculate statistics
$total_courses = count($all_courses);
$total_feedbacks_count = count(DB::getFeedbacks()); // Total regardless of search
$average_rating = 0;
if ($total_feedbacks_count > 0) {
    $sum = array_sum(array_column(DB::getFeedbacks(), 'rating'));
    $average_rating = round($sum / $total_feedbacks_count, 1);
}

// Generate map of course feedback counts
$course_feedback_counts = [];
foreach (DB::getFeedbacks() as $fb) {
    $cid = $fb['course_id'];
    $course_feedback_counts[$cid] = ($course_feedback_counts[$cid] ?? 0) + 1;
}

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
    <title>Admin Dashboard - Feedback Loop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional dashboard specific statistics styles */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-premium);
        }

        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.5rem;
            background-color: var(--accent-glow);
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-details h4 {
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-details .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 0.25rem;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
        }

        .section-header h2 {
            font-size: 1.35rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="animate-fade-in">
    <div class="app-container">
        <!-- Dashboard Header -->
        <header>
            <div class="logo-section">
                <h1>Feedback Loop</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($admin_name); ?></strong> <span class="badge badge-admin">Admin</span></p>
            </div>
            <div class="header-controls">
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Theme"></button>
                <a href="logout.php" class="btn btn-secondary btn-sm">Sign Out</a>
            </div>
        </header>

        <!-- Status Alerts -->
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
                <span>Running in offline <strong>Demo Mode</strong>. Your changes will be saved to your session context.</span>
            </div>
        <?php endif; ?>

        <!-- Statistics counters -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <h4>Total Courses</h4>
                    <div class="stat-number"><?php echo $total_courses; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-details">
                    <h4>Total Feedbacks</h4>
                    <div class="stat-number"><?php echo $total_feedbacks_count; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-details">
                    <h4>Average Rating</h4>
                    <div class="stat-number"><?php echo $average_rating; ?> / 5.0</div>
                </div>
            </div>
        </div>

        <div class="admin-layout">
            <!-- 1. Manage Courses Section (CRUD) -->
            <div class="card-panel">
                <div class="section-header">
                    <h2>Manage Course Catalog</h2>
                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-course-modal').classList.add('active')">+ Add New Course</button>
                </div>

                <?php if (empty($all_courses)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p>No courses registered in catalog yet. Click "+ Add New Course" to add one.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-courses-grid">
                        <?php foreach ($all_courses as $course): ?>
                            <?php $reviews_count = $course_feedback_counts[$course['id']] ?? 0; ?>
                            <div class="course-card">
                                <div>
                                    <div class="course-meta">
                                        <span class="course-code-tag"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                        <span style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo $reviews_count; ?> feedback(s)</span>
                                    </div>
                                    <h4 style="margin-top: 0.5rem;"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                        Instructor: <?php echo htmlspecialchars($course['instructor']); ?>
                                    </div>
                                </div>

                                <div class="record-actions" style="margin-top: auto;">
                                    <button class="btn btn-secondary btn-sm btn-edit-course"
                                            data-id="<?php echo $course['id']; ?>"
                                            data-code="<?php echo htmlspecialchars($course['course_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($course['course_name']); ?>"
                                            data-instructor="<?php echo htmlspecialchars($course['instructor']); ?>">
                                        Edit
                                    </button>
                                    <a href="course_actions.php?action=delete&id=<?php echo $course['id']; ?>"
                                       class="btn btn-danger btn-sm btn-delete-confirm"
                                       data-confirm-message="Are you sure you want to delete this course? Doing so will permanently delete all associated student feedbacks (Cascading Delete).">
                                        Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php unset($course); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. Manage Feedbacks Section (Read & Delete operations) -->
            <div class="card-panel">
                <div class="section-header">
                    <h2>Student Feedback Database</h2>
                </div>

                <div class="records-header">
                    <!-- Search Bar -->
                    <div class="search-wrapper">
                        <form action="admin_dashboard.php" method="GET" style="display: flex; gap: 0.5rem; width: 100%;">
                            <input type="text" name="search" class="input-control" placeholder="Search by course, student, comment..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="admin_dashboard.php" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; justify-content: center;">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div>
                        Showing <?php echo count($all_feedbacks); ?> record(s)
                    </div>
                </div>

                <?php if (empty($all_feedbacks)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🔍</div>
                        <p>No feedback records found matching search queries or no reviews exist.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
                        <?php foreach ($all_feedbacks as $fb): ?>
                            <div class="feedback-card">
                                <div>
                                    <div class="feedback-meta">
                                        <div>
                                            <span class="course-code-tag"><?php echo htmlspecialchars($fb['course_code']); ?></span>
                                            <h4><?php echo htmlspecialchars($fb['course_name']); ?></h4>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.15rem;">
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
                                        <span>Student: <strong><?php echo htmlspecialchars($fb['student_name']); ?></strong></span>
                                        <span>•</span>
                                        <span><?php echo date('M d, Y', strtotime($fb['created_at'])); ?></span>
                                    </div>
                                    <div class="record-actions">
                                        <a href="feedback_actions.php?action=delete&id=<?php echo $fb['id']; ?>"
                                           class="btn btn-danger btn-sm btn-delete-confirm"
                                           data-confirm-message="Are you sure you want to delete this feedback review? This cannot be undone.">
                                            Delete Review
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
       Add Course Modal Overlay
       ========================================================================== -->
    <div id="add-course-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Course</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form action="course_actions.php?action=create" method="POST">
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code" class="input-control" required placeholder="e.g. CS303" style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label for="course_name">Course Title</label>
                    <input type="text" id="course_name" name="course_name" class="input-control" required placeholder="e.g. Web Application Development">
                </div>

                <div class="form-group">
                    <label for="instructor">Instructor Name</label>
                    <input type="text" id="instructor" name="instructor" class="input-control" required placeholder="e.g. Dr. Tim Berners-Lee">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Add Course</button>
                    <button type="button" class="btn btn-secondary btn-close-modal" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="edit-course-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Course</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form action="course_actions.php?action=update" method="POST">
                <input type="hidden" id="edit-course-id" name="id">
                
                <div class="form-group">
                    <label for="edit-course-code">Course Code</label>
                    <input type="text" id="edit-course-code" name="course_code" class="input-control" required style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label for="edit-course-name">Course Title</label>
                    <input type="text" id="edit-course-name" name="course_name" class="input-control" required>
                </div>

                <div class="form-group">
                    <label for="edit-course-instructor">Instructor Name</label>
                    <input type="text" id="edit-course-instructor" name="instructor" class="input-control" required>
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
