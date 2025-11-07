// User Profile Management JavaScript
let originalValues = {}; // Global variable for original form values

document.addEventListener('DOMContentLoaded', function() {
    // Initialize profile functionality
    initializeProfileForm();
    initializePasswordForm();
    initializeNotifications();
    loadProfileStats();
});

// Profile Form Management
function initializeProfileForm() {
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');
    const profileForm = document.getElementById('profileForm');
    const formInputs = profileForm.querySelectorAll('input, textarea');
    
    // Store original values
    formInputs.forEach(input => {
        originalValues[input.name] = input.value;
    });
    
    // Edit mode
    editBtn.addEventListener('click', function() {
        formInputs.forEach(input => {
            input.removeAttribute('readonly');
            input.style.backgroundColor = 'white';
        });
        
        editBtn.style.display = 'none';
        cancelBtn.style.display = 'inline-flex';
        saveBtn.style.display = 'inline-flex';
    });
    
    // Cancel edit
    cancelBtn.addEventListener('click', function() {
        formInputs.forEach(input => {
            input.setAttribute('readonly', 'readonly');
            input.style.backgroundColor = 'var(--bg-light)';
            input.value = originalValues[input.name];
        });
        
        editBtn.style.display = 'inline-flex';
        cancelBtn.style.display = 'none';
        saveBtn.style.display = 'none';
    });
    
    // Save changes
    saveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleProfileUpdate();
    });
}

// Handle Profile Update
async function handleProfileUpdate() {
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const profileForm = document.getElementById('profileForm');
    const formInputs = profileForm.querySelectorAll('input, textarea');
    
    // Show loading state
    saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Saving...';
    saveBtn.disabled = true;
    
    // Collect form data
    const formData = {
        firstName: document.getElementById('firstName').value.trim(),
        lastName: document.getElementById('lastName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phoneNumber: document.getElementById('phone').value.trim(),
        bio: document.getElementById('bio').value.trim()
    };
    
    // Validate form data
    if (!validateProfileForm(formData)) {
        resetSaveButton(saveBtn, editBtn, cancelBtn);
        return;
    }
    
    try {
        // Send update request
        const response = await fetch('assets/api/update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update original values
            formInputs.forEach(input => {
                originalValues[input.name] = input.value;
                input.setAttribute('readonly', 'readonly');
                input.style.backgroundColor = 'var(--bg-light)';
            });
            
            editBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'none';
            saveBtn.style.display = 'none';
            
            // Update localStorage if user data is provided
            if (result.user) {
                localStorage.setItem('userData', JSON.stringify(result.user));
            }
            
            showNotification('Profile updated successfully!', 'success');
        } else {
            showNotification(result.message || 'Failed to update profile. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
    } finally {
        resetSaveButton(saveBtn, editBtn, cancelBtn);
    }
}

// Reset Save Button
function resetSaveButton(saveBtn, editBtn, cancelBtn) {
    saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
    saveBtn.disabled = false;
}

// Validate Profile Form
function validateProfileForm(data) {
    let isValid = true;
    
    // Validate first name
    if (!data.firstName) {
        showFieldError('firstName', 'First name is required');
        isValid = false;
    } else {
        clearFieldError('firstName');
    }
    
    // Validate last name
    if (!data.lastName) {
        showFieldError('lastName', 'Last name is required');
        isValid = false;
    } else {
        clearFieldError('lastName');
    }
    
    // Validate email
    if (!data.email) {
        showFieldError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(data.email)) {
        showFieldError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearFieldError('email');
    }
    
    return isValid;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show field error
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');
    
    // Remove existing error
    const existingError = formGroup.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error class
    field.classList.add('is-invalid');
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-danger mt-1';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.textContent = message;
    formGroup.appendChild(errorDiv);
    
    // Shake animation
    field.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        field.style.animation = '';
    }, 500);
}

// Clear field error
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');
    
    field.classList.remove('is-invalid');
    
    const existingError = formGroup.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}


// Notification System
function initializeNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notificationContainer')) {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
}

// Show Notification
function showNotification(message, type) {
    const container = document.getElementById('notificationContainer');
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    let iconClass = 'info-circle';
    if (type === 'success') iconClass = 'check-circle';
    if (type === 'error') iconClass = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="bi bi-${iconClass}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .is-invalid {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    .notification {
        background: white;
        border-radius: var(--radius-md);
        padding: 1rem 1.5rem;
        box-shadow: var(--shadow-medium);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        transform: translateX(400px);
        transition: all 0.3s ease;
        border-left: 4px solid var(--accent-blue);
        pointer-events: auto;
        max-width: 350px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-success {
        border-left-color: #10B981;
    }
    
    .notification-success i {
        color: #10B981;
    }
    
    .notification-error {
        border-left-color: #EF4444;
    }
    
    .notification-error i {
        color: #EF4444;
    }
    
    .notification-info {
        border-left-color: var(--danger-color);
    }
    
    .notification-info i {
        color: var(--danger-color);
    }
`;
document.head.appendChild(style);

// Load Profile Statistics
async function loadProfileStats() {
    try {
        const response = await fetch('assets/api/get_profile_stats.php');
        const result = await response.json();
        
        if (result.success) {
            updateProfileStats(result.data);
        } else {
            console.error('Profile stats error:', result.message);
        }
    } catch (error) {
        console.error('Load Profile Stats Error:', error);
    }
}

// Update Profile Statistics
function updateProfileStats(stats) {
    // Update total jobs count
    const totalJobsElement = document.getElementById('totalJobsCount');
    if (totalJobsElement) {
        totalJobsElement.textContent = stats.total_jobs;
    }
    
    // Update total vendors count
    const totalVendorsElement = document.getElementById('totalVendorsCount');
    if (totalVendorsElement) {
        totalVendorsElement.textContent = stats.total_vendors;
    }
    
    // Update completed jobs count
    const completedJobsElement = document.getElementById('completedJobsCount');
    if (completedJobsElement) {
        completedJobsElement.textContent = stats.completed_jobs;
    }
}

// Password Form Management
function initializePasswordForm() {
    const togglePasswordSection = document.getElementById('togglePasswordSection');
    const passwordForm = document.getElementById('passwordForm');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    // Toggle password section
    if (togglePasswordSection) {
        togglePasswordSection.addEventListener('click', function() {
            passwordForm.style.display = passwordForm.style.display === 'none' ? 'block' : 'none';
            this.style.display = passwordForm.style.display === 'block' ? 'none' : 'inline-block';
        });
    }
    
    // Cancel password change
    if (cancelPasswordBtn) {
        cancelPasswordBtn.addEventListener('click', function() {
            resetPasswordForm();
            passwordForm.style.display = 'none';
            togglePasswordSection.style.display = 'inline-block';
        });
    }
    
    // Save password
    if (savePasswordBtn) {
        savePasswordBtn.addEventListener('click', handlePasswordUpdate);
    }
    
    // Password strength indicator
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', checkPasswordStrength);
    }
    
    // Password confirmation
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
}

// Password validation functions
function checkPasswordStrength() {
    const password = document.getElementById('newPassword').value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    if (!password) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    let strength = 0;
    let feedback = [];
    
    // Length check
    if (password.length >= 8) strength += 1;
    else feedback.push('At least 8 characters');
    
    // Lowercase check
    if (/[a-z]/.test(password)) strength += 1;
    else feedback.push('Lowercase letter');
    
    // Uppercase check
    if (/[A-Z]/.test(password)) strength += 1;
    else feedback.push('Uppercase letter');
    
    // Number check
    if (/\d/.test(password)) strength += 1;
    else feedback.push('Number');
    
    // Special character check
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
    else feedback.push('Special character');
    
    let strengthText = '';
    let strengthClass = '';
    
    if (strength <= 2) {
        strengthText = 'Weak';
        strengthClass = 'weak';
    } else if (strength <= 3) {
        strengthText = 'Medium';
        strengthClass = 'medium';
    } else if (strength <= 4) {
        strengthText = 'Strong';
        strengthClass = 'strong';
    } else {
        strengthText = 'Very Strong';
        strengthClass = 'very-strong';
    }
    
    strengthDiv.innerHTML = `
        <div class="password-strength-indicator">
            <span class="strength-text ${strengthClass}">${strengthText}</span>
            ${feedback.length > 0 ? `<small class="text-muted">Missing: ${feedback.join(', ')}</small>` : ''}
        </div>
    `;
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (!confirmPassword) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i> Passwords match</small>';
    } else {
        matchDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i> Passwords do not match</small>';
    }
}

function resetPasswordForm() {
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordStrength').innerHTML = '';
    document.getElementById('passwordMatch').innerHTML = '';
}

async function handlePasswordUpdate() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('Please fill in all password fields', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('New password must be at least 8 characters long', 'error');
        return;
    }
    
    try {
        const response = await fetch('assets/api/update_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Password updated successfully!', 'success');
            resetPasswordForm();
            document.getElementById('passwordForm').style.display = 'none';
            document.getElementById('togglePasswordSection').style.display = 'inline-block';
        } else {
            showNotification(result.message || 'Failed to update password', 'error');
        }
    } catch (error) {
        console.error('Password update error:', error);
        showNotification('An error occurred while updating password', 'error');
    }
}

// Password toggle function (global)
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Export functions for global access
window.ProfileSystem = {
    showNotification,
    validateProfileForm,
    handleProfileUpdate,
    loadProfileStats,
    togglePassword,
    handlePasswordUpdate
};
