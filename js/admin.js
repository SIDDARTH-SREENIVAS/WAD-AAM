/**
 * Course Feedback Management System
 * Admin Dashboard Client Logic
 */

let currentAdmin = null;
let allCourses = [];
let allFeedbacks = [];
let searchQuery = '';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Verify Admin Authentication
    const isAuthed = await verifyAdminAuth();
    if (!isAuthed) return;

    // 2. Load Dashboard Data
    await loadAdminDashboardData();

    // 3. Bind Event Handlers
    setupCourseModalsAndForms();
    setupSearchFilter();
});

async function verifyAdminAuth() {
    try {
        const response = await fetch('php/auth.php?action=check_session');
        const data = await response.json();

        if (!data.success || !data.data || !data.data.authenticated || data.data.user.role !== 'admin') {
            window.location.href = 'index.html';
            return false;
        }

        currentAdmin = data.data.user;
        const adminNameElem = document.getElementById('admin-display-name');
        if (adminNameElem) {
            adminNameElem.textContent = currentAdmin.username;
        }

        return true;
    } catch (e) {
        console.error('Auth verification failed', e);
        window.location.href = 'index.html';
        return false;
    }
}

async function loadAdminDashboardData() {
    try {
        const [statsRes, coursesRes, feedbacksRes] = await Promise.all([
            fetch('php/stats.php'),
            fetch('php/courses.php?action=list'),
            fetch(`php/feedback.php?action=list${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`)
        ]);

        const statsData = await statsRes.json();
        const coursesData = await coursesRes.json();
        const feedbacksData = await feedbacksRes.json();

        if (statsData.success && statsData.data.stats) {
            renderStats(statsData.data.stats);
        }

        allCourses = (coursesData.success && coursesData.data.courses) ? coursesData.data.courses : [];
        allFeedbacks = (feedbacksData.success && feedbacksData.data.feedbacks) ? feedbacksData.data.feedbacks : [];

        renderCoursesGrid();
        renderFeedbacksTable();
    } catch (err) {
        console.error('Error loading admin dashboard data', err);
        showToast('Error loading dashboard data', 'error');
    }
}

function renderStats(stats) {
    const totalCoursesElem = document.getElementById('stat-total-courses');
    const totalFeedbacksElem = document.getElementById('stat-total-feedbacks');
    const avgRatingElem = document.getElementById('stat-avg-rating');

    if (totalCoursesElem) totalCoursesElem.textContent = stats.total_courses || '0';
    if (totalFeedbacksElem) totalFeedbacksElem.textContent = stats.total_feedbacks || '0';
    if (avgRatingElem) {
        avgRatingElem.textContent = stats.average_rating ? `${stats.average_rating} ★` : 'N/A';
    }
}

function renderCoursesGrid() {
    const grid = document.getElementById('courses-grid');
    const emptyState = document.getElementById('no-courses-state');

    if (!grid) return;

    if (allCourses.length === 0) {
        grid.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }

    if (emptyState) emptyState.style.display = 'none';

    grid.innerHTML = allCourses.map(course => `
        <div class="card-panel course-item-card">
            <div class="card-header-flex">
                <span class="badge badge-course">${escapeHtml(course.course_code)}</span>
                <div class="action-buttons-group">
                    <button class="btn-icon btn-edit-course" 
                        data-id="${course.id}" 
                        data-code="${escapeHtml(course.course_code)}" 
                        data-name="${escapeHtml(course.course_name)}" 
                        data-instructor="${escapeHtml(course.instructor)}"
                        title="Edit Course">
                        ✏️
                    </button>
                    <button class="btn-icon btn-delete-course" 
                        data-id="${course.id}" 
                        data-name="${escapeHtml(course.course_name)}"
                        title="Delete Course">
                        🗑️
                    </button>
                </div>
            </div>
            <h4 class="course-card-title">${escapeHtml(course.course_name)}</h4>
            <p class="course-card-instructor">Instructor: <strong>${escapeHtml(course.instructor)}</strong></p>
        </div>
    `).join('');

    // Attach listeners to course actions
    grid.querySelectorAll('.btn-edit-course').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const code = btn.getAttribute('data-code');
            const name = btn.getAttribute('data-name');
            const instructor = btn.getAttribute('data-instructor');

            document.getElementById('edit-course-id').value = id;
            document.getElementById('edit-course-code').value = code;
            document.getElementById('edit-course-name').value = name;
            document.getElementById('edit-course-instructor').value = instructor;

            openModal('edit-course-modal');
        });
    });

    grid.querySelectorAll('.btn-delete-course').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            if (confirm(`Are you sure you want to delete course "${name}"? This will also remove all associated feedback reviews.`)) {
                await deleteCourseRecord(id);
            }
        });
    });
}

function renderFeedbacksTable() {
    const tbody = document.getElementById('admin-feedbacks-tbody');
    const emptyFeedbackState = document.getElementById('no-feedback-state');
    const feedbackTableContainer = document.getElementById('admin-feedbacks-table-wrapper');

    if (!tbody) return;

    if (allFeedbacks.length === 0) {
        tbody.innerHTML = '';
        if (feedbackTableContainer) feedbackTableContainer.style.display = 'none';
        if (emptyFeedbackState) emptyFeedbackState.style.display = 'block';
        return;
    }

    if (feedbackTableContainer) feedbackTableContainer.style.display = 'block';
    if (emptyFeedbackState) emptyFeedbackState.style.display = 'none';

    tbody.innerHTML = allFeedbacks.map(fb => `
        <tr>
            <td>
                <strong>${escapeHtml(fb.course_code)}</strong><br>
                <span class="text-muted" style="font-size: 0.85rem;">${escapeHtml(fb.course_name)}</span>
            </td>
            <td>
                <span class="badge badge-student">${escapeHtml(fb.student_name || 'Student')}</span>
            </td>
            <td>
                ${generateStarsHtml(fb.rating)}
            </td>
            <td>
                <div class="feedback-comment-preview">${escapeHtml(fb.comments)}</div>
            </td>
            <td>
                <span class="text-muted" style="font-size: 0.85rem;">${escapeHtml(fb.created_at || 'N/A')}</span>
            </td>
        </tr>
    `).join('');
}

function setupCourseModalsAndForms() {
    // Add Course Button trigger
    const addCourseBtn = document.getElementById('btn-add-course-modal');
    if (addCourseBtn) {
        addCourseBtn.addEventListener('click', () => {
            document.getElementById('add-course-form').reset();
            openModal('add-course-modal');
        });
    }

    // Add Course Form Submit
    const addForm = document.getElementById('add-course-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = addForm.querySelector('button[type="submit"]');

            const course_code = document.getElementById('add-course-code').value.trim();
            const course_name = document.getElementById('add-course-name').value.trim();
            const instructor = document.getElementById('add-course-instructor').value.trim();

            if (!course_code || !course_name || !instructor) {
                showToast('Please fill in all course fields.', 'error');
                return;
            }

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';
                }

                const res = await fetch('php/courses.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ course_code, course_name, instructor })
                });

                const data = await res.json();

                if (data.success) {
                    closeModal('add-course-modal');
                    addForm.reset();
                    showToast(data.data.message || 'Course added successfully!', 'success');
                    await loadAdminDashboardData();
                } else {
                    showToast(data.message || 'Failed to add course.', 'error');
                }
            } catch (err) {
                showToast('Network error while adding course.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Course';
                }
            }
        });
    }

    // Edit Course Form Submit
    const editForm = document.getElementById('edit-course-form');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = editForm.querySelector('button[type="submit"]');

            const id = document.getElementById('edit-course-id').value;
            const course_code = document.getElementById('edit-course-code').value.trim();
            const course_name = document.getElementById('edit-course-name').value.trim();
            const instructor = document.getElementById('edit-course-instructor').value.trim();

            if (!id || !course_code || !course_name || !instructor) {
                showToast('Please fill in all course fields.', 'error');
                return;
            }

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Updating...';
                }

                const res = await fetch('php/courses.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(id), course_code, course_name, instructor })
                });

                const data = await res.json();

                if (data.success) {
                    closeModal('edit-course-modal');
                    showToast(data.data.message || 'Course updated successfully!', 'success');
                    await loadAdminDashboardData();
                } else {
                    showToast(data.message || 'Failed to update course.', 'error');
                }
            } catch (err) {
                showToast('Network error while updating course.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Update Course';
                }
            }
        });
    }
}

function setupSearchFilter() {
    const searchForm = document.getElementById('feedback-search-form');
    const searchInput = document.getElementById('feedback-search-input');
    const clearBtn = document.getElementById('btn-clear-search');

    if (searchForm) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            searchQuery = searchInput.value.trim();
            await loadFeedbacksOnly();
        });
    }

    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(async () => {
                searchQuery = searchInput.value.trim();
                await loadFeedbacksOnly();
            }, 300);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (searchInput) searchInput.value = '';
            searchQuery = '';
            await loadFeedbacksOnly();
        });
    }
}

async function loadFeedbacksOnly() {
    try {
        const res = await fetch(`php/feedback.php?action=list${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`);
        const data = await res.json();
        allFeedbacks = (data.success && data.data.feedbacks) ? data.data.feedbacks : [];
        renderFeedbacksTable();
    } catch (err) {
        console.error('Error searching feedbacks', err);
    }
}

async function deleteCourseRecord(id) {
    try {
        const res = await fetch(`php/courses.php?action=delete&id=${id}`, {
            method: 'POST'
        });
        const data = await res.json();

        if (data.success) {
            showToast('Course and reviews deleted successfully!', 'success');
            await loadAdminDashboardData();
        } else {
            showToast(data.message || 'Failed to delete course.', 'error');
        }
    } catch (err) {
        showToast('Network error while deleting course.', 'error');
    }
}
