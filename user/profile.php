<?php
$pageTitle = "My Profile";
include 'header.php';
include 'sidebar.php';

// Fetch user data from database
require_once '../config/database.php';
$pdo = getDB();

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone_number, bio, profile_image, created_at FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $currentUser['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        // Fallback to session data if database fetch fails
        $userData = [
            'first_name' => explode(' ', $currentUser['name'])[0] ?? '',
            'last_name' => explode(' ', $currentUser['name'])[1] ?? '',
            'email' => $currentUser['email'],
            'phone_number' => '',
            'bio' => '',
            'profile_image' => '',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
} catch (PDOException $e) {
    // Fallback to session data if database error
    $userData = [
        'first_name' => explode(' ', $currentUser['name'])[0] ?? '',
        'last_name' => explode(' ', $currentUser['name'])[1] ?? '',
        'email' => $currentUser['email'],
        'phone_number' => '',
        'bio' => '',
        'profile_image' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];
}
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
                            <h2 class="profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></h2>
                            <p class="profile-role">Job Portal User</p>
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
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['first_name']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lastName" class="form-label">
                                            <i class="bi bi-person"></i>
                                            Last Name
                                        </label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['last_name']); ?>" readonly>
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
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">
                                            <i class="bi bi-telephone"></i>
                                            Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone_number']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="joinDate" class="form-label">
                                            <i class="bi bi-calendar"></i>
                                            Join Date
                                        </label>
                                        <input type="text" class="form-control" id="joinDate" name="joinDate" value="<?php echo date('F j, Y', strtotime($userData['created_at'])); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bio" class="form-label">
                                    <i class="bi bi-file-text"></i>
                                    Bio
                                </label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" readonly><?php echo htmlspecialchars($userData['bio']); ?></textarea>
                            </div>

                            <!-- Password Change Section -->
                            <div class="password-section mt-4">
                                <div class="section-header">
                                    <h5 class="section-title">
                                        <i class="bi bi-shield-lock"></i>
                                        Change Password
                                    </h5>
                                    <button type="button" class="btn btn-back btn-sm" id="togglePasswordSection">
                                        <i class="bi bi-key"></i>
                                        Change Password
                                    </button>
                                </div>
                                
                                <div class="password-form" id="passwordForm" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currentPassword" class="form-label">
                                                    <i class="bi bi-lock"></i>
                                                    Current Password
                                                </label>
                                                <div class="password-input-wrapper">
                                                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Enter current password">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="newPassword" class="form-label">
                                                    <i class="bi bi-key"></i>
                                                    New Password
                                                </label>
                                                <div class="password-input-wrapper">
                                                    <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter new password">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="password-strength" id="passwordStrength"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="confirmPassword" class="form-label">
                                                    <i class="bi bi-check-circle"></i>
                                                    Confirm New Password
                                                </label>
                                                <div class="password-input-wrapper">
                                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="password-match" id="passwordMatch"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="password-actions">
                                        <button type="button" class="btn btn-back" id="savePasswordBtn">
                                            <i class="bi bi-check-circle"></i>
                                            Update Password
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="cancelPasswordBtn">
                                            <i class="bi bi-x-circle"></i>
                                            Cancel
                                        </button>
                                    </div>
                                </div>
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
                                <div class="stat-icon jobs">
                                    <i class="bi bi-briefcase-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="totalJobsCount">0</div>
                                    <div class="stat-label">Total Jobs</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon vendors">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="totalVendorsCount">0</div>
                                    <div class="stat-label">Vendors Added by Me</div>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon activity">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number" id="completedJobsCount">0</div>
                                    <div class="stat-label">Jobs Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="assets/js/profile.js"></script>

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
    background: rgba(255, 255, 255, 0.2);
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: var(--shadow-medium);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    transition: all 0.3s ease;
}

.avatar-icon:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
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

<!-- Profile JavaScript -->


