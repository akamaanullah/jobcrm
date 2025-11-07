<?php
$pageTitle = "My Profile";
include 'header.php';
include 'sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Profile Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="profile-header mt-4">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar">
                            <div class="avatar-icon">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                        <div class="profile-info">
                            <h2 class="profile-name" id="profileName">Loading...</h2>
                            <p class="profile-role">System Administrator</p>
                            <span class="profile-status">
                                <i class="bi bi-circle-fill"></i>
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-primary" id="editProfileBtn">
                            <i class="bi bi-pencil-square"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="row">
            <!-- Personal Information -->
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-person-fill"></i>
                            Personal Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="profileForm" class="profile-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="firstName" class="form-label">
                                            <i class="bi bi-person"></i>
                                            First Name
                                        </label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="Loading..." readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lastName" class="form-label">
                                            <i class="bi bi-person"></i>
                                            Last Name
                                        </label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="Loading..." readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope"></i>
                                            Email Address
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" value="Loading..." readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">
                                            <i class="bi bi-telephone"></i>
                                            Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="Loading..." readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department" class="form-label">
                                            <i class="bi bi-building"></i>
                                            Department
                                        </label>
                                        <input type="text" class="form-control" id="department" name="department" value="Administration" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinDate" class="form-label">
                                            <i class="bi bi-calendar"></i>
                                            Join Date
                                        </label>
                                        <input type="text" class="form-control" id="joinDate" name="joinDate" value="Loading..." readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bio" class="form-label">
                                    <i class="bi bi-file-text"></i>
                                    Bio
                                </label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" readonly>Loading...</textarea>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;">
                                <i class="bi bi-x-circle"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-back" id="saveBtn" style="display: none;">
                                <i class="bi bi-check-circle"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Stats & Quick Actions -->
            <div class="col-lg-4">
                <!-- Profile Stats -->
                <div class="content-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-graph-up"></i>
                            Profile Statistics
                        </h3>
                        <div class="card-subtitle">Your performance overview</div>
                    </div>
                    <div class="card-body">
                        <div class="stats-container">
                            <div class="stat-item">
                                <div class="stat-icon users">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="usersManaged">0</div>
                                    <div class="stat-label">Users Managed</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon jobs">
                                    <i class="bi bi-briefcase-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="jobsPosted">0</div>
                                    <div class="stat-label">Jobs Posted</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon vendors">
                                    <i class="bi bi-building-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="vendors">0</div>
                                    <div class="stat-label">Vendors</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon activity">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="daysActive">0</div>
                                    <div class="stat-label">Days Active</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
/* Profile Page Specific Styles */
.profile-header {
    background: linear-gradient(135deg, var(--danger-color) 0%, #B91C1C 100%);
    border-radius: var(--radius-lg);
    padding: 2rem;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-medium);
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
}

.avatar-icon {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: var(--shadow-medium);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.9);
    transition: all 0.3s ease;
}

.avatar-icon:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%);
    transform: scale(1.05);
    color: white;
}

.profile-info h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.profile-role {
    margin: 0.5rem 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.profile-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.profile-status i {
    color: #10B981;
    font-size: 0.7rem;
}

.profile-actions .btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    transition: all 0.3s ease;
}

.profile-actions .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.profile-form .form-group {
    margin-bottom: 1.5rem;
}

.profile-form .form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.profile-form .form-control {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.profile-form .form-control:focus {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.profile-form .form-control[readonly] {
    background-color: var(--bg-light);
    color: var(--text-medium);
}

.card-footer {
    background: var(--bg-light);
    border-top: 1px solid var(--border-color);
    padding: 1.5rem;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.card-subtitle {
    font-size: 0.85rem;
    color: var(--text-medium);
    margin-top: 0.25rem;
    font-weight: 400;
}

.stats-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, rgba(220, 53, 69, 0.05) 100%);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(220, 53, 69, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-item:hover {
    background: linear-gradient(135deg, var(--danger-color) 0%, #B91C1C 100%);
    color: white;
    transform: translateY(-3px);
    box-shadow: var(--shadow-medium);
    border-color: transparent;
}

.stat-item:hover::before {
    background: white;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.stat-icon.users {
    background: linear-gradient(135deg, var(--danger-color) 0%, #B91C1C 100%);
    color: white;
}

.stat-icon.jobs {
    background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
    color: white;
}

.stat-icon.vendors {
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    color: white;
}

.stat-icon.activity {
    background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
    color: white;
}

.stat-item:hover .stat-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.stat-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-medium);
    font-weight: 500;
    margin: 0.25rem 0;
}

.stat-item:hover .stat-number,
.stat-item:hover .stat-label {
    color: white;
}


/* Responsive Design */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');
    const profileForm = document.getElementById('profileForm');
    const formInputs = profileForm.querySelectorAll('input, textarea');
    
    let originalValues = {};
    
    // Load profile data and stats
    loadProfileData();
    loadProfileStats();
    
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
    saveBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Show loading state
        saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Saving...';
        saveBtn.disabled = true;
        
        try {
             // Prepare form data
             const formData = new FormData();
             formData.append('firstName', document.getElementById('firstName').value);
             formData.append('lastName', document.getElementById('lastName').value);
             formData.append('email', document.getElementById('email').value);
             formData.append('phoneNumber', document.getElementById('phone').value);
             formData.append('bio', document.getElementById('bio').value);
            
            // Make API call
            const response = await fetch('assets/api/update_profile.php', {
                method: 'POST',
                body: formData
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
            
                // Update profile name in header
                const profileName = document.querySelector('.profile-name');
                if (profileName) {
                    profileName.textContent = document.getElementById('firstName').value + ' ' + document.getElementById('lastName').value;
                }
                
                showNotification(result.message || 'Profile updated successfully!', 'success');
            } else {
                showNotification(result.message || 'Failed to update profile', 'error');
            }
        } catch (error) {
            console.error('Profile update error:', error);
            showNotification('Network error. Please try again.', 'error');
        } finally {
            // Reset button
            saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
            saveBtn.disabled = false;
        }
    });
    
    // Avatar icon is now static - no edit functionality needed
    
    // Load profile data
    async function loadProfileData() {
        try {
            const response = await fetch('assets/api/get_profile_data.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const data = result.data;
                
                // Update profile header
                document.getElementById('profileName').textContent = `${data.first_name || 'Admin'} ${data.last_name || 'User'}`;
                
                // Update form fields
                document.getElementById('firstName').value = data.first_name || 'Admin';
                document.getElementById('lastName').value = data.last_name || 'User';
                document.getElementById('email').value = data.email || 'admin@jobsystem.com';
                document.getElementById('phone').value = data.phone_number || '+1 (555) 123-4567';
                document.getElementById('joinDate').value = data.join_date || 'January 15, 2024';
                document.getElementById('bio').value = data.bio || 'Experienced system administrator with expertise in managing job portal systems, user management, and system maintenance.';
                
                // Update original values
                formInputs.forEach(input => {
                    originalValues[input.name] = input.value;
                });
            } else {
                console.error('Failed to load profile data:', result.message);
                showNotification('Failed to load profile data', 'error');
            }
        } catch (error) {
            console.error('Error loading profile data:', error);
            showNotification('Error loading profile data', 'error');
        }
    }
    
    // Load profile stats
    async function loadProfileStats() {
        try {
            const response = await fetch('assets/api/get_profile_stats.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const stats = result.data;
                
                // Update statistics
                document.getElementById('usersManaged').textContent = stats.users_managed ? stats.users_managed.toLocaleString() : '0';
                document.getElementById('jobsPosted').textContent = stats.jobs_posted ? stats.jobs_posted.toLocaleString() : '0';
                document.getElementById('vendors').textContent = stats.vendors ? stats.vendors.toLocaleString() : '0';
                document.getElementById('daysActive').textContent = stats.days_active ? stats.days_active.toLocaleString() : '0';
            } else {
                console.error('Failed to load profile stats:', result.message);
            }
        } catch (error) {
            console.error('Error loading profile stats:', error);
        }
    }
    
    // Notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        let iconClass = 'info-circle';
        if (type === 'success') iconClass = 'check-circle';
        if (type === 'error') iconClass = 'exclamation-circle';
        
        notification.innerHTML = `
            <i class="bi bi-${iconClass}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});
</script>

<style>
/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: var(--radius-md);
    padding: 1rem 1.5rem;
    box-shadow: var(--shadow-medium);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 1000;
    transform: translateX(400px);
    transition: all 0.3s ease;
    border-left: 4px solid var(--accent-blue);
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left-color: var(--success-color);
}

.notification-success i {
    color: var(--success-color);
}

.notification-info {
    border-left-color: var(--danger-color);
}

.notification-info i {
    color: var(--danger-color);
}

.notification-error {
    border-left-color: #EF4444;
}

.notification-error i {
    color: #EF4444;
}
</style>
