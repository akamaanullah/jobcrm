// Users Management JavaScript
document.addEventListener('DOMContentLoaded', function () {

    // Initialize DOM elements
    const searchInput = document.getElementById('userSearchInput');
    const sortFilter = document.getElementById('userSortFilter');
    const usersGrid = document.getElementById('usersGrid');

    // Load users on page load
    loadUsers();

    // Add User Form Handler
    const addUserForm = document.getElementById('addUserForm');
    const createUserBtn = document.getElementById('createUserBtn');
    const addUserModal = document.getElementById('addUserModal');

    // Prevent form submission
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddUser();
        });
    }

    if (createUserBtn) {
        createUserBtn.addEventListener('click', function (e) {
            e.preventDefault();
            handleAddUser();
        });
    }

    // Handle Add User
    function handleAddUser() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);

        // Get form values with safety checks
        const roleElement = document.getElementById('role');
        const selectedRole = roleElement ? roleElement.value : '';
        
        const userData = {
            firstName: document.getElementById('firstName')?.value?.trim() || '',
            lastName: document.getElementById('lastName')?.value?.trim() || '',
            username: document.getElementById('username')?.value?.trim() || '',
            password: document.getElementById('password')?.value || '',
            role: selectedRole, // Don't default to 'user' - let validation catch it
            bio: document.getElementById('bio')?.value?.trim() || '',
            profileImage: '' // Add if needed
        };

        // Debug log to check role value
        console.log('Role selected:', selectedRole);
        console.log('UserData being sent:', userData);

        // Validate form
        if (!validateAddUserForm(userData)) {
            return;
        }

        // Show loading state
        createUserBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
        createUserBtn.disabled = true;

        // Send API request
        fetch('assets/api/add_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('User created successfully!', 'success');

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(addUserModal);
                    modal.hide();

                    // Reset form
                    form.reset();

                    // Refresh users list
                    loadUsers();

                } else {
                    showNotification(data.message || 'Failed to create user', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while creating user', 'error');
            })
            .finally(() => {
                // Reset button state
                createUserBtn.innerHTML = 'Create User';
                createUserBtn.disabled = false;
            });
    }

    // Validate Add User Form
    function validateAddUserForm(userData) {
        if (!userData.firstName) {
            showNotification('First name is required', 'error');
            return false;
        }

        if (!userData.lastName) {
            showNotification('Last name is required', 'error');
            return false;
        }

        if (!userData.username) {
            showNotification('Username is required', 'error');
            return false;
        }

        if (!userData.password) {
            showNotification('Password is required', 'error');
            return false;
        }

        if (!userData.role) {
            showNotification('Role is required', 'error');
            return false;
        }

        // Validate role is one of the allowed values
        const validRoles = ['user', 'manager', 'admin'];
        if (!validRoles.includes(userData.role)) {
            showNotification('Invalid role selected. Please select User, Manager, or Admin', 'error');
            return false;
        }

        // Username validation (alphanumeric, underscore, and dot allowed)
        const usernameRegex = /^[a-zA-Z0-9_.]{3,20}$/;
        if (!usernameRegex.test(userData.username)) {
            showNotification('Username must be 3-20 characters and contain only letters, numbers, underscores, and dots', 'error');
            return false;
        }

        // Password validation
        if (userData.password.length < 6) {
            showNotification('Password must be at least 6 characters long', 'error');
            return false;
        }

        return true;
    }

    // Load Users (for refreshing the list)
    function loadUsers() {
        const searchTerm = searchInput ? searchInput.value : '';
        const sortValue = sortFilter ? sortFilter.value : 'name';

        // Build query parameters
        const params = new URLSearchParams();
        if (searchTerm) params.append('search', searchTerm);
        if (sortValue) params.append('sort', sortValue);

        // Show loading state
        if (usersGrid) {
            usersGrid.innerHTML = '<div class="text-center p-4"><i class="bi bi-hourglass-split"></i> Loading users...</div>';
        }

        // Fetch users
        fetch(`assets/api/get_users.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsers(data.users);
                    updateUserMetrics(data.stats);
                } else {
                    // console.error('API Error:', data.message); // Debug log
                    showNotification(data.message || 'Failed to load users', 'error');
                    if (usersGrid) {
                        usersGrid.innerHTML = '<div class="text-center p-4 text-danger">Failed to load users</div>';
                    }
                }
            })
            .catch(error => {
                // console.error('Error:', error);
                showNotification('An error occurred while loading users', 'error');
                if (usersGrid) {
                    usersGrid.innerHTML = '<div class="text-center p-4 text-danger">Error loading users</div>';
                }
            });
    }

    // Render Users
    function renderUsers(users) {
        if (!usersGrid) return;

        if (users.length === 0) {
            usersGrid.innerHTML = '<div class="text-center p-4">No users found</div>';
            return;
        }

        let html = '';
        users.forEach(user => {
            let roleClass = 'badge-primary';
            if (user.role === 'admin') {
                roleClass = 'badge-danger';
            } else if (user.role === 'manager') {
                roleClass = 'badge-warning';
            }

            html += `
                <div class="user-card" data-user-id="${user.id}">
                    <div class="user-avatar">
                        <span>${user.avatar_initials}</span>
                    </div>
                    <div class="user-info">
                        <h4>${user.full_name}</h4>
                        <p class="user-email">@${user.username}</p>
                        <div class="user-badges">
                            <span class="badge ${roleClass}">${user.role.toUpperCase()}</span>
                        </div>
                        <div class="user-details">
                            <p><i class="bi bi-calendar"></i> Joining Date: ${user.formatted_date}</p>
                            <p><i class="bi bi-briefcase"></i> Jobs Completed: ${user.jobs_completed}</p>
                        </div>
                    </div>
                    <div class="user-actions">
                        <button class="action-btn edit-btn" title="Edit User" data-bs-toggle="modal"
                            data-bs-target="#editUserModal" 
                            data-user-id="${user.id}" 
                            data-user-first-name="${user.first_name}"
                            data-user-last-name="${user.last_name}"
                            data-user-username="${user.username}" 
                            data-user-role="${user.role}"
                            data-user-bio="${user.bio || ''}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="action-btn delete-btn" title="Delete User" 
                            data-user-id="${user.id}" 
                            data-user-name="${user.full_name}"
                            data-user-username="${user.username}" 
                            data-user-role="${user.role}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        usersGrid.innerHTML = html;

        // Re-attach event listeners
        attachUserEventListeners();
    }

    // Update User Metrics
    function updateUserMetrics(stats) {
        // Check if stats object exists
        if (!stats) {
            // console.warn('No stats provided to updateUserMetrics');
            return;
        }

        // Update total users
        const totalUsersElement = document.querySelector('#metricCardTotalUsers h3');
        if (totalUsersElement && stats.total_users !== undefined) {
            totalUsersElement.textContent = stats.total_users;
        }

        // Update active users
        const activeUsersElement = document.querySelector('#metricCardActiveUsers h3');
        if (activeUsersElement && stats.active_users !== undefined) {
            activeUsersElement.textContent = stats.active_users;
        }

        // Update regular users
        const regularUsersElement = document.querySelector('#metricCardRegularUsers h3');
        if (regularUsersElement && stats.regular_users !== undefined) {
            regularUsersElement.textContent = stats.regular_users;
        }

        // Update admin users
        const adminUsersElement = document.querySelector('#metricCardAdmins h3');
        if (adminUsersElement && stats.admin_users !== undefined) {
            adminUsersElement.textContent = stats.admin_users;
        }
    }

    // Attach Event Listeners to User Cards
    function attachUserEventListeners() {
        // Edit User Handler
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-user-id');
                const userFirstName = this.getAttribute('data-user-first-name');
                const userLastName = this.getAttribute('data-user-last-name');
                const userUsername = this.getAttribute('data-user-username');
                const userBio = this.getAttribute('data-user-bio');

                // Store current user ID for editing
                window.currentEditUserId = userId;

                // Populate edit form
                const editFirstName = document.getElementById('editFirstName');
                const editLastName = document.getElementById('editLastName');
                const editUsername = document.getElementById('editUsername');
                const editRole = document.getElementById('editRole');
                const editBio = document.getElementById('editBio');
                const userRole = this.getAttribute('data-user-role');

                if (editFirstName) editFirstName.value = userFirstName || '';
                if (editLastName) editLastName.value = userLastName || '';
                if (editUsername) editUsername.value = userUsername;
                if (editRole) editRole.value = userRole || 'user';
                if (editBio) editBio.value = userBio;

            });
        });

        // Delete User Handler
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                const userUsername = this.getAttribute('data-user-username');
                const userRole = this.getAttribute('data-user-role');

                // Store current delete user ID
                window.currentDeleteUserId = userId;

                // Populate delete modal with user details
                if (deleteUserDetails) {
                    deleteUserDetails.innerHTML = `
                        <div class="row">
                            <div class="col-6"><strong>Name:</strong></div>
                            <div class="col-6">${userName}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Username:</strong></div>
                            <div class="col-6">@${userUsername}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Role:</strong></div>
                            <div class="col-6">${userRole.toUpperCase()}</div>
                        </div>
                    `;
                }

                // Show delete confirmation modal
                const modal = new bootstrap.Modal(deleteUserModal);
                modal.show();
            });
        });
    }

    // Edit User Form Handler
    const editUserForm = document.getElementById('editUserForm');
    const updateUserBtn = document.getElementById('updateUserBtn');
    const editUserModal = document.getElementById('editUserModal');

    // Initialize password functionality
    initializePasswordForm();

    // Delete User Modal Handler
    const deleteUserModal = document.getElementById('deleteUserModal');
    const confirmDeleteUserBtn = document.getElementById('confirmDeleteUserBtn');
    const deleteUserDetails = document.getElementById('deleteUserDetails');

    if (updateUserBtn) {
        updateUserBtn.addEventListener('click', function () {
            handleEditUser();
        });
    }

    if (confirmDeleteUserBtn) {
        confirmDeleteUserBtn.addEventListener('click', function () {
            handleDeleteUser();
        });
    }

    // Handle Edit User
    function handleEditUser() {
        if (!window.currentEditUserId) {
            showNotification('No user selected for editing', 'error');
            return;
        }

        // Get form elements safely
                const editFirstNameEl = document.getElementById('editFirstName');
                const editLastNameEl = document.getElementById('editLastName');
                const editUsernameEl = document.getElementById('editUsername');
                const editRoleEl = document.getElementById('editRole');
                const editBioEl = document.getElementById('editBio');
        
        const userData = {
            userId: window.currentEditUserId,
            firstName: editFirstNameEl?.value?.trim() || '',
            lastName: editLastNameEl?.value?.trim() || '',
            username: editUsernameEl?.value?.trim() || '',
            role: editRoleEl?.value || 'user',
            bio: editBioEl?.value?.trim() || ''
        };

        // Validate form
        if (!validateEditUserForm(userData)) {
            return;
        }

        // Show loading state
        updateUserBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
        updateUserBtn.disabled = true;

        // Send API request
        fetch('assets/api/edit_user.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('User updated successfully!', 'success');

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(editUserModal);
                    modal.hide();

                    // Reset form
                    editUserForm.reset();

                    // Clear current edit user ID
                    window.currentEditUserId = null;

                    // Refresh users list
                    loadUsers();

                } else {
                    showNotification(data.message || 'Failed to update user', 'error');
                }
            })
            .catch(error => {
                // console.error('Error:', error);
                showNotification('An error occurred while updating user', 'error');
            })
            .finally(() => {
                // Reset button state
                updateUserBtn.innerHTML = '<i class="bi bi-check-circle"></i> Update User';
                updateUserBtn.disabled = false;
            });
    }

    // Validate Edit User Form
    function validateEditUserForm(userData) {
        if (!userData.firstName) {
            showNotification('Full name is required', 'error');
            return false;
        }

        if (!userData.username) {
            showNotification('Username is required', 'error');
            return false;
        }

        if (!userData.role) {
            showNotification('Role is required', 'error');
            return false;
        }

        // Username validation (alphanumeric, underscore, and dot allowed)
        const usernameRegex = /^[a-zA-Z0-9_.]{3,20}$/;
        if (!usernameRegex.test(userData.username)) {
            showNotification('Username must be 3-20 characters and contain only letters, numbers, underscores, and dots', 'error');
            return false;
        }


        return true;
    }

    // Handle Delete User
    function handleDeleteUser() {
        if (!window.currentDeleteUserId) {
            showNotification('No user selected for deletion', 'error');
            return;
        }

        // Show loading state
        confirmDeleteUserBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting...';
        confirmDeleteUserBtn.disabled = true;

        // Send API request
        fetch('assets/api/delete_user.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                userId: window.currentDeleteUserId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('User deleted successfully!', 'success');

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(deleteUserModal);
                    modal.hide();

                    // Clear current delete user ID
                    window.currentDeleteUserId = null;

                    // Refresh users list
                    loadUsers();

                } else {
                    showNotification(data.message || 'Failed to delete user', 'error');
                }
            })
            .catch(error => {
                // console.error('Error:', error);
                showNotification('An error occurred while deleting user', 'error');
            })
            .finally(() => {
                // Reset button state
                confirmDeleteUserBtn.innerHTML = '<i class="bi bi-trash"></i> Delete User';
                confirmDeleteUserBtn.disabled = false;
            });
    }

    // Show Notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add to body
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Search and Filter Handlers
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterUsers();
        });
    }



    if (sortFilter) {
        sortFilter.addEventListener('change', function () {
            sortUsers();
        });
    }

    // Filter Users
    function filterUsers() {
        // Reload users with current filters
        loadUsers();
    }

    // Sort Users
    function sortUsers() {
        // Reload users with current sort
        loadUsers();
    }

    // Password Form Management
    function initializePasswordForm() {
        const togglePasswordSection = document.getElementById('togglePasswordSection');
        const passwordForm = document.getElementById('passwordForm');
        const savePasswordBtn = document.getElementById('savePasswordBtn');
        const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
        const newPasswordInput = document.getElementById('editNewPassword');
        const confirmPasswordInput = document.getElementById('editConfirmPassword');

        // Toggle password section
        if (togglePasswordSection) {
            togglePasswordSection.addEventListener('click', function () {
                passwordForm.style.display = passwordForm.style.display === 'none' ? 'block' : 'none';
                this.style.display = passwordForm.style.display === 'block' ? 'none' : 'inline-block';
            });
        }

        // Cancel password change
        if (cancelPasswordBtn) {
            cancelPasswordBtn.addEventListener('click', function () {
                resetPasswordForm();
                passwordForm.style.display = 'none';
                togglePasswordSection.style.display = 'inline-block';
            });
        }

        // Save password
        if (savePasswordBtn) {
            savePasswordBtn.addEventListener('click', handleAdminPasswordUpdate);
        }

        // Password strength indicator
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', checkPasswordStrength);
        }

        // Password confirmation
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }

        // Password toggle buttons
        const passwordToggleButtons = document.querySelectorAll('.password-toggle');
        passwordToggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                togglePassword(targetId);
            });
        });
    }

    // Password validation functions
    function checkPasswordStrength() {
        const password = document.getElementById('editNewPassword').value;
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
        const newPassword = document.getElementById('editNewPassword').value;
        const confirmPassword = document.getElementById('editConfirmPassword').value;
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
        document.getElementById('editNewPassword').value = '';
        document.getElementById('editConfirmPassword').value = '';
        document.getElementById('passwordStrength').innerHTML = '';
        document.getElementById('passwordMatch').innerHTML = '';
    }

    async function handleAdminPasswordUpdate() {
        const newPassword = document.getElementById('editNewPassword').value;
        const confirmPassword = document.getElementById('editConfirmPassword').value;

        // Validation
        if (!newPassword || !confirmPassword) {
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

        if (!window.currentEditUserId) {
            showNotification('No user selected for password update', 'error');
            return;
        }

        try {
            const response = await fetch('assets/api/admin_update_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    userId: window.currentEditUserId,
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
});
