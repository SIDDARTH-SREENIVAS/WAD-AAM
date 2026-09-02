/**
 * Course Feedback Management System
 * Student Portal Client Logic
 */

let currentStudent = null;
let allCourses = [];
let myFeedbacks = [];

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Check Student Authentication
    const isAuthed = await verifyStudentAuth();
    if (!isAuthed) return;

    // 2. Load Dashboard Data
    await loadStudentData();

    // 3. Bind Event Listeners
    setupStarRatingInputs();
    setupFeedbackSubmissionForm();
    setupFeedbackEditForm();
    setupDeleteModal();
});

async function verifyStudentAuth() {
    try {
        const response = await fetch('php/auth.php?action=check_session');
        const data = await response.json();

        if (!data.success || !data.data || !data.data.authenticated || data.data.user.role !== 'student') {
            window.location.href = 'index.html';
            return false;
        }

        currentStudent = data.data.user;
        const studentNameElem = document.getElementById('student-display-name');
        if (studentNameElem) {
            studentNameElem.textContent = currentStudent.username;
        }

        return true;
    } catch (e) {
        console.error('Auth verification failed', e);
        window.location.href = 'index.html';
        return false;
    }
}

async function loadStudentData() {
    try {
        // Fetch courses and feedbacks in parallel
        const [coursesRes, feedbacksRes] = await Promise.all([
            fetch('php/courses.php?action=list'),
            fetch('php/feedback.php?action=list&my_feedback=1')
        ]);

        const coursesData = await coursesRes.json();
        const feedbacksData = await feedbacksRes.json();

        allCourses = (coursesData.success && coursesData.data.courses) ? coursesData.data.courses : [];
        myFeedbacks = (feedbacksData.success && feedbacksData.data.feedbacks) ? feedbacksData.data.feedbacks : [];

        renderCourseOptions();
        renderFeedbackTable();
    } catch (err) {
        console.error('Error loading student dashboard data', err);
        showToast('Error loading dashboard records', 'error');
    }
}

function renderCourseOptions() {
    const select = document.getElementById('course_id');
    const formPanel = document.getElementById('feedback-form-container');
    const emptyState = document.getElementById('all-completed-state');

    if (!select) return;

    // Filter out courses already reviewed
    const reviewedCourseIds = myFeedbacks.map(f => parseInt(f.course_id));
    const pendingCourses = allCourses.filter(c => !reviewedCourseIds.includes(parseInt(c.id)));

    if (pendingCourses.length === 0) {
        if (formPanel) formPanel.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
    } else {
        if (formPanel) formPanel.style.display = 'block';
        if (emptyState) emptyState.style.display = 'none';

        select.innerHTML = '<option value="" disabled selected>-- Choose Course --</option>';
        pendingCourses.forEach(course => {
            const opt = document.createElement('option');
            opt.value = course.id;
            opt.textContent = `${course.course_code} - ${course.course_name} (${course.instructor})`;
            select.appendChild(opt);
        });
    }
}

function renderFeedbackTable() {
    const tbody = document.getElementById('my-feedbacks-tbody');
    const emptyFeedbackState = document.getElementById('no-feedback-state');
    const feedbackTableContainer = document.getElementById('feedbacks-table-wrapper');

    if (!tbody) return;

    if (myFeedbacks.length === 0) {
        if (tbody) tbody.innerHTML = '';
        if (feedbackTableContainer) feedbackTableContainer.style.display = 'none';
        if (emptyFeedbackState) emptyFeedbackState.style.display = 'block';
        return;
    }

    if (feedbackTableContainer) feedbackTableContainer.style.display = 'block';
    if (emptyFeedbackState) emptyFeedbackState.style.display = 'none';

    tbody.innerHTML = myFeedbacks.map(fb => `
        <tr>
            <td>
                <strong>${escapeHtml(fb.course_code)}</strong><br>
                <span class="text-muted" style="font-size: 0.85rem;">${escapeHtml(fb.course_name)}</span>
            </td>
            <td>
                ${generateStarsHtml(fb.rating)}
            </td>
            <td>
                <div class="feedback-comment-preview">${escapeHtml(fb.comments)}</div>
            </td>
            <td>
                <span class="text-muted" style="font-size: 0.85rem;">${escapeHtml(fb.created_at || 'Just now')}</span>
            </td>
            <td class="text-right">
                <button class="btn btn-secondary btn-sm btn-edit-fb" 
                    data-id="${fb.id}" 
                    data-course-name="${escapeHtml(fb.course_name)}" 
                    data-rating="${fb.rating}" 
                    data-comments="${escapeHtml(fb.comments)}">
                    Edit
                </button>
                <button class="btn btn-danger btn-sm btn-delete-fb" 
                    data-id="${fb.id}"
                    data-course-name="${escapeHtml(fb.course_name)}">
                    Delete
                </button>
            </td>
        </tr>
    `).join('');

    // Attach listeners to dynamically created edit and delete buttons
    tbody.querySelectorAll('.btn-edit-fb').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const courseName = btn.getAttribute('data-course-name');
            const rating = parseInt(btn.getAttribute('data-rating')) || 5;
            const comments = btn.getAttribute('data-comments');

            document.getElementById('edit-feedback-id').value = id;
            document.getElementById('edit-modal-course-name').textContent = courseName;
            document.getElementById('edit-feedback-comments').value = comments;

            // Set radio button star rating
            const starRadio = document.querySelector(`input[name="edit_rating"][value="${rating}"]`);
            if (starRadio) starRadio.checked = true;

            openModal('edit-feedback-modal');
        });
    });

    tbody.querySelectorAll('.btn-delete-fb').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const courseName = btn.getAttribute('data-course-name') || 'this course';
            
            document.getElementById('delete-feedback-id').value = id;
            document.getElementById('delete-modal-course-name').textContent = courseName;
            
            openModal('delete-feedback-modal');
        });
    });
}

function setupStarRatingInputs() {
    // Enable keyboard accessibility or star tooltips if needed
}

function setupFeedbackSubmissionForm() {
    const form = document.getElementById('feedback-submission-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');

        const course_id = document.getElementById('course_id').value;
        const ratingInput = form.querySelector('input[name="rating"]:checked');
        const comments = document.getElementById('comments').value.trim();

        if (!course_id) {
            showToast('Please select a course.', 'error');
            return;
        }

        if (!ratingInput) {
            showToast('Please select a star rating.', 'error');
            return;
        }

        if (!comments) {
            showToast('Please provide feedback comments.', 'error');
            return;
        }

        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            const res = await fetch('php/feedback.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    course_id: parseInt(course_id),
                    rating: parseInt(ratingInput.value),
                    comments
                })
            });

            const data = await res.json();

            if (data.success) {
                showToast(data.data.message || 'Feedback submitted successfully!', 'success');
                form.reset();
                await loadStudentData();
            } else {
                showToast(data.message || 'Failed to submit feedback.', 'error');
            }
        } catch (err) {
            showToast('Network error while submitting feedback.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Feedback';
            }
        }
    });
}

function setupFeedbackEditForm() {
    const form = document.getElementById('edit-feedback-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');

        const id = document.getElementById('edit-feedback-id').value;
        const ratingInput = form.querySelector('input[name="edit_rating"]:checked');
        const comments = document.getElementById('edit-feedback-comments').value.trim();

        if (!ratingInput || !comments) {
            showToast('Please fill in all feedback fields.', 'error');
            return;
        }

        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }

            const res = await fetch('php/feedback.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: parseInt(id),
                    rating: parseInt(ratingInput.value),
                    comments
                })
            });

            const data = await res.json();

            if (data.success) {
                closeModal('edit-feedback-modal');
                showToast(data.data.message || 'Feedback updated successfully!', 'success');
                await loadStudentData();
            } else {
                showToast(data.message || 'Failed to update feedback.', 'error');
            }
        } catch (err) {
            showToast('Network error while updating feedback.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
            }
        }
    });
}

function setupDeleteModal() {
    const confirmDeleteBtn = document.getElementById('btn-confirm-delete-feedback');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async () => {
            const id = document.getElementById('delete-feedback-id').value;
            if (!id) return;

            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.textContent = 'Deleting...';

            try {
                await deleteFeedbackRecord(id);
                closeModal('delete-feedback-modal');
            } finally {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Delete Review';
            }
        });
    }
}

async function deleteFeedbackRecord(id) {
    try {
        const res = await fetch(`php/feedback.php?action=delete&id=${id}`, {
            method: 'POST'
        });
        const data = await res.json();

        if (data.success) {
            showToast('Feedback deleted successfully!', 'success');
            await loadStudentData();
        } else {
            showToast(data.message || 'Failed to delete feedback.', 'error');
        }
    } catch (err) {
        showToast('Network error while deleting feedback.', 'error');
    }
}
