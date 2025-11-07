// Job System Portal - Login Page JavaScript
// Modern Interactive Login Form

document.addEventListener('DOMContentLoaded', function() {
    // Initialize login functionality
    initializeLoginForm();
    initializePasswordToggle();
    initializeFormValidation();
    initializeAnimations();
});

// Login Form Management
function initializeLoginForm() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoading = loginBtn.querySelector('.btn-loading');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(loginForm);
        const username = formData.get('username');
        const password = formData.get('password');
        
        // Validate form
        if (!validateForm(username, password)) {
            return;
        }
        
        // Show loading state
        showLoadingState(loginBtn, btnText, btnLoading);
        
        // Call login API
        loginUser(username, password)
            .then(response => {
                hideLoadingState(loginBtn, btnText, btnLoading);
                handleLoginSuccess(response);
            })
            .catch(error => {
                hideLoadingState(loginBtn, btnText, btnLoading);
                handleLoginError(error);
            });
    });
}

// Password Toggle Functionality
function initializePasswordToggle() {
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        
        // Add visual feedback without breaking positioning
        passwordToggle.style.transform = 'translateY(-50%) scale(0.95)';
        setTimeout(() => {
            passwordToggle.style.transform = 'translateY(-50%) scale(1)';
        }, 150);
    });
}

// Form Validation
function initializeFormValidation() {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    // Initial validation if fields have values
    if (usernameInput.value.trim()) {
        validateUsername(usernameInput);
    }
    if (passwordInput.value) {
        validatePassword(passwordInput);
    }
    
    // Real-time validation
    usernameInput.addEventListener('blur', function() {
        validateUsername(this);
    });
    
    passwordInput.addEventListener('blur', function() {
        validatePassword(this);
    });
    
    // Real-time validation on input
    usernameInput.addEventListener('input', function() {
        if (this.value.trim()) {
            validateUsername(this);
        } else {
            clearValidation(this);
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value) {
            validatePassword(this);
        } else {
            clearValidation(this);
        }
    });
}

// Validation Functions
function validateForm(username, password) {
    let isValid = true;
    
    // Validate username
    if (!validateUsername(document.getElementById('username'))) {
        isValid = false;
    }
    
    // Validate password
    if (!validatePassword(document.getElementById('password'))) {
        isValid = false;
    }
    
    return isValid;
}

function validateUsername(input) {
    const username = input.value.trim();
    const usernameRegex = /^[a-zA-Z0-9_.]{3,20}$/;
    
    if (!username) {
        showFieldError(input, 'Username is required');
        return false;
    } else if (!usernameRegex.test(username)) {
        showFieldError(input, 'Username must be 3-20 characters and contain only letters, numbers, underscores, and dots');
        return false;
    } else {
        showFieldSuccess(input);
        return true;
    }
}

function validatePassword(input) {
    const password = input.value;
    
    if (!password) {
        showFieldError(input, 'Password is required');
        return false;
    } else if (password.length < 6) {
        showFieldError(input, 'Password must be at least 6 characters');
        return false;
    } else {
        showFieldSuccess(input);
        return true;
    }
}

function showFieldError(input, message) {
    clearValidation(input);
    
    input.classList.add('is-invalid');
    input.style.borderColor = 'var(--danger-color)';
    
    // Shake animation
    input.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        input.style.animation = '';
    }, 500);
}

function showFieldSuccess(input) {
    clearValidation(input);
    
    input.classList.add('is-valid');
    input.style.borderColor = 'var(--success-color)';
}

function clearValidation(input) {
    input.classList.remove('is-invalid', 'is-valid');
    input.style.borderColor = '';
}

// Loading State Management
function showLoadingState(btn, btnText, btnLoading) {
    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'flex';
    btn.classList.add('loading');
}

function hideLoadingState(btn, btnText, btnLoading) {
    btn.disabled = false;
    btnText.style.display = 'block';
    btnLoading.style.display = 'none';
    btn.classList.remove('loading');
}

// Login API Call
function loginUser(username, password) {
    return fetch('assets/api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            return data;
        } else {
            throw new Error(data.message || 'Login failed');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        if (error.message.includes('Network')) {
            throw {
                type: 'network',
                message: 'Network error. Please check your connection and try again.'
            };
        } else {
            throw {
                type: 'credentials',
                message: error.message || 'Login failed. Please try again.'
            };
        }
    });
}

// Login Simulation (Keep for testing purposes)
function simulateLogin(email, password) {
    return new Promise((resolve, reject) => {
        // Simulate network delay
        setTimeout(() => {
            // Simulate different scenarios
            const random = Math.random();
            
            if (random < 0.1) {
                // 10% chance of network error
                reject({
                    type: 'network',
                    message: 'Network error. Please check your connection and try again.'
                });
            } else if (random < 0.2) {
                // 10% chance of invalid credentials
                reject({
                    type: 'credentials',
                    message: 'Invalid username or password. Please try again.'
                });
            } else {
                // 80% chance of success
                resolve({
                    type: 'success',
                    message: 'Login successful! Redirecting...',
                    user: {
                        username: username,
                        name: username,
                        role: 'admin'
                    }
                });
            }
        }, 2000); // 2 second delay
    });
}

// Success Handler
function handleLoginSuccess(response) {
    showAlert('success', response.message);
    
    // Store user data in localStorage for future use
    if (response.user) {
        localStorage.setItem('userData', JSON.stringify(response.user));
        localStorage.setItem('isLoggedIn', 'true');
    }
    
    // Redirect after delay
    setTimeout(() => {
        const redirectUrl = response.redirect_url || 'admin/dashboard.php';
        window.location.href = redirectUrl;
    }, 1500);
}

// Error Handler
function handleLoginError(error) {
    let alertType = 'danger';
    let message = error.message;
    
    if (error.type === 'network') {
        alertType = 'warning';
    }
    
    showAlert(alertType, message);
    
    // Focus on username field for retry
    document.getElementById('username').focus();
}

// Alert System
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-${getAlertIcon(type)}"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="closeAlert(this)" style="margin-left: auto; background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    `;
    
    // Add to container
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            closeAlert(alert.querySelector('.btn-close'));
        }
    }, 5000);
}

function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function closeAlert(closeBtn) {
    const alert = closeBtn.closest('.alert');
    if (alert) {
        alert.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

// Animations
function initializeAnimations() {
    // Animate login card on load
    const loginCard = document.querySelector('.login-card');
    loginCard.style.opacity = '0';
    loginCard.style.transform = 'translateY(30px)';
    
    setTimeout(() => {
        loginCard.style.transition = 'all 0.6s ease-out';
        loginCard.style.opacity = '1';
        loginCard.style.transform = 'translateY(0)';
    }, 100);
    
    // Animate form elements
    const formElements = document.querySelectorAll('.form-group');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.4s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateX(0)';
        }, 300 + (index * 100));
    });
    
}


// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .is-invalid {
        border-color: var(--danger-color) !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }
    
    .is-valid {
        border-color: var(--success-color) !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }
`;
document.head.appendChild(style);

// Logout functionality
function logout() {
    // Clear localStorage
    localStorage.removeItem('userData');
    localStorage.removeItem('isLoggedIn');
    
    // Redirect to login page
    window.location.href = '../index.php';
}

// Export functions for global access
window.LoginSystem = {
    showAlert,
    closeAlert,
    validateForm,
    loginUser,
    simulateLogin,
    logout
};
