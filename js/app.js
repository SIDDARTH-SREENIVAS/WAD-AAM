/**
 * Course Feedback Management System
 * Client-side Interaction Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // --------------------------------------------------------
    // THEME TOGGLER LOGIC
    // --------------------------------------------------------
    const themeToggleBtn = document.getElementById('theme-toggle');
    const rootBody = document.body;

    // Load saved theme preference
    if (localStorage.getItem('theme') === 'light') {
        rootBody.classList.add('light-theme');
        updateThemeIcon(true);
    } else {
        updateThemeIcon(false);
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const isLight = rootBody.classList.toggle('light-theme');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            updateThemeIcon(isLight);
        });
    }

    function updateThemeIcon(isLight) {
        if (!themeToggleBtn) return;
        themeToggleBtn.innerHTML = isLight 
            ? `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>` // Moon icon
            : `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`; // Sun icon
    }

    // --------------------------------------------------------
    // DYNAMIC MODAL TRIGGERS (Course Add/Edit, Feedback Edit)
    // --------------------------------------------------------
    const modals = document.querySelectorAll('.modal-overlay');
    const closeButtons = document.querySelectorAll('.modal-close, .btn-close-modal');

    // Close modalls on click outside or on X buttons
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            modals.forEach(modal => modal.classList.remove('active'));
        });
    });

    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Handle ESC key to close active modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modals.forEach(modal => modal.classList.remove('active'));
        }
    });

    // --------------------------------------------------------
    // ADMIN COURSE EDIT MODAL POPULATOR
    // --------------------------------------------------------
    const editCourseButtons = document.querySelectorAll('.btn-edit-course');
    const editCourseModal = document.getElementById('edit-course-modal');

    if (editCourseModal && editCourseButtons.length > 0) {
        editCourseButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const code = btn.getAttribute('data-code');
                const name = btn.getAttribute('data-name');
                const instructor = btn.getAttribute('data-instructor');

                // Populate input fields in the Edit Course Modal
                document.getElementById('edit-course-id').value = id;
                document.getElementById('edit-course-code').value = code;
                document.getElementById('edit-course-name').value = name;
                document.getElementById('edit-course-instructor').value = instructor;

                // Open modal
                editCourseModal.classList.add('active');
            });
        });
    }

    // --------------------------------------------------------
    // STUDENT FEEDBACK EDIT MODAL POPULATOR
    // --------------------------------------------------------
    const editFeedbackButtons = document.querySelectorAll('.btn-edit-feedback');
    const editFeedbackModal = document.getElementById('edit-feedback-modal');

    if (editFeedbackModal && editFeedbackButtons.length > 0) {
        editFeedbackButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const courseName = btn.getAttribute('data-course-name');
                const rating = btn.getAttribute('data-rating');
                const comments = btn.getAttribute('data-comments');

                // Populate elements in the Edit Feedback Modal
                document.getElementById('edit-feedback-id').value = id;
                document.getElementById('edit-feedback-course-title').innerText = courseName;
                document.getElementById('edit-feedback-comments').value = comments;

                // Set star rating input value
                const radioStar = document.getElementById(`edit-star${rating}`);
                if (radioStar) {
                    radioStar.checked = true;
                }

                // Open modal
                editFeedbackModal.classList.add('active');
            });
        });
    }

    // --------------------------------------------------------
    // FORM VALIDATORS AND ACTION CONFIRMATIONS
    // --------------------------------------------------------
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.getAttribute('data-confirm-message') || "Are you sure you want to delete this record?";
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Client-side validation for Feedback submission star ratings
    const feedbackForm = document.getElementById('feedback-submission-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', (e) => {
            const ratingInputs = feedbackForm.querySelectorAll('input[name="rating"]');
            let checked = false;
            for (const input of ratingInputs) {
                if (input.checked) {
                    checked = true;
                    break;
                }
            }

            if (!checked) {
                e.preventDefault();
                alert("Please select a star rating (1 to 5 stars) before submitting your feedback.");
            }
        });
    }
});
