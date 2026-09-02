/**
 * Course Feedback Management System
 * Authentication Scripts (Login, Register, Logout)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Check if user is already logged in on index or register page
    const isAuthPage = document.getElementById('login-form') || document.getElementById('register-form');
    
    if (isAuthPage) {
        checkAuthAndRedirect();
    }

    // Login Form Handler
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('auth-alert');
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                showAlert(alertBox, 'Please enter both username and password.', 'danger');
                return;
            }

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Signing in...';
                }

                const response = await fetch('php/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.success && data.data && data.data.user) {
                    const role = data.data.user.role;
                    if (role === 'admin') {
                        window.location.href = 'admin_dashboard.html';
                    } else {
                        window.location.href = 'student_dashboard.html';
                    }
                } else {
                    showAlert(alertBox, data.message || 'Invalid login credentials.', 'danger');
                }
            } catch (err) {
                showAlert(alertBox, 'Server communication error. Please try again.', 'danger');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            }
        });
    }

    // Register Form Handler
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('auth-alert');

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;

            if (!username || !password || !confirm_password) {
                showAlert(alertBox, 'Please fill in all fields.', 'danger');
                return;
            }

            if (username.length < 3) {
                showAlert(alertBox, 'Username must be at least 3 characters long.', 'danger');
                return;
            }

            if (password.length < 6) {
                showAlert(alertBox, 'Password must be at least 6 characters long.', 'danger');
                return;
            }

            if (password !== confirm_password) {
                showAlert(alertBox, 'Passwords do not match.', 'danger');
                return;
            }

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Creating account...';
                }

                const response = await fetch('php/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, confirm_password })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'index.html?registered=success';
                } else {
                    showAlert(alertBox, data.message || 'Failed to create account.', 'danger');
                }
            } catch (err) {
                showAlert(alertBox, 'Server communication error. Please try again.', 'danger');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                }
            }
        });
    }
});

async function checkAuthAndRedirect() {
    try {
        const response = await fetch('php/auth.php?action=check_session');
        const data = await response.json();
        if (data.success && data.data && data.data.authenticated) {
            const role = data.data.user.role;
            if (role === 'admin') {
                window.location.href = 'admin_dashboard.html';
            } else {
                window.location.href = 'student_dashboard.html';
            }
        }
    } catch (e) {
        // Silently proceed if network or server is initialising
    }
}

async function logoutUser() {
    try {
        await fetch('php/auth.php?action=logout');
    } catch (e) {
        console.error('Logout error:', e);
    }
    window.location.href = 'index.html';
}

function showAlert(container, message, type = 'danger') {
    if (!container) return;
    container.className = `alert alert-${type}`;
    container.style.display = 'flex';
    
    let iconSvg = type === 'success' 
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

    container.innerHTML = `
        ${iconSvg}
        <span>${escapeHtml(message)}</span>
    `;
}
