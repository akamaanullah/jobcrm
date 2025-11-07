<?php $pageTitle = 'Manage Users'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotalUsers">
                <div class="metric-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="metric-content">
                    <h3>3</h3>
                    <p class="metric-label">TOTAL USERS</p>
                    <span class="metric-status text-success">All registered users</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardActiveUsers">
                <div class="metric-icon active-users">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="metric-content">
                    <h3>3</h3>
                    <p class="metric-label">ACTIVE USERS</p>
                    <span class="metric-status text-success">Currently active</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardRegularUsers">
                <div class="metric-icon regular-users">
                    <i class="bi bi-person"></i>
                </div>
                <div class="metric-content">
                    <h3>2</h3>
                    <p class="metric-label">USERS</p>
                    <span class="metric-status text-warning">Regular users</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardAdmins">
                <div class="metric-icon admins">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div class="metric-content">
                    <h3>1</h3>
                    <p class="metric-label">ADMINS</p>
                    <span class="metric-status text-danger">Administrators</span>
                </div>
            </div>
        </div>

        <!-- Users List Section -->
        <div class="users-section">

            <div class="section-header">
                <h3>All Users</h3>
                <div class="section-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-lg-8 col-md-8 col-12">
                        <div class="search-box">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="search-input" id="userSearchInput"
                                    placeholder="Search users by name or username...">
                            </div>
                        </div>
                    </div>



                    <div class="col-lg-4 col-md-4 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Sort By</label>
                                <select class="filter-dropdown" id="userSortFilter">
                                    <option value="name">Name</option>
                                    <option value="username">Username</option>
                                    <option value="date">Joining Date</option>
                                    <option value="jobs">Jobs Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="users-grid" id="usersGrid">
                <!-- Users will be loaded dynamically via JavaScript -->
                <div class="text-center p-4">
                    <i class="bi bi-hourglass-split"></i> Loading users...
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>


                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="user">User</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio (Optional)</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createUserBtn">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="lastName" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="">Select Role</option>
                            <option value="user">User</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editBio" class="form-label">
                            <i class="bi bi-person-lines-fill"></i> Bio
                        </label>
                        <textarea class="form-control" id="editBio" name="bio" rows="3"
                            placeholder="Enter user bio"></textarea>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section mt-4">
                        <div class="section-header">
                            <h6 class="section-title">
                                <i class="bi bi-shield-lock"></i>
                                Change Password
                            </h6>
                            <button type="button" class="btn btn-back btn-sm" id="togglePasswordSection">
                                <i class="bi bi-key"></i>
                                Change Password
                            </button>
                        </div>

                        <div class="password-form" id="passwordForm" style="display: none;">
                            <div class="mb-3">
                                <label for="editNewPassword" class="form-label">
                                    <i class="bi bi-key"></i>
                                    New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="editNewPassword" name="newPassword"
                                        placeholder="Enter new password">
                                    <button type="button" class="password-toggle" data-target="editNewPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>

                            <div class="mb-3">
                                <label for="editConfirmPassword" class="form-label">
                                    <i class="bi bi-check-circle"></i>
                                    Confirm New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="editConfirmPassword"
                                        name="confirmPassword" placeholder="Confirm new password">
                                    <button type="button" class="password-toggle" data-target="editConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-match" id="passwordMatch"></div>
                            </div>

                            <div class="password-actions">
                                <button type="button" class="btn btn-success btn-sm" id="savePasswordBtn">
                                    <i class="bi bi-check-circle"></i>
                                    Update Password
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="cancelPasswordBtn">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-back" id="updateUserBtn">
                    <i class="bi bi-check-circle"></i> Update User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Delete User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-x text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Are you sure you want to delete this user?</h5>
                    <p class="text-muted mb-3">
                        This action cannot be undone. All user data will be permanently removed.
                    </p>
                    <div class="alert alert-warning">
                        <strong>User Details:</strong>
                        <div id="deleteUserDetails" class="mt-2">
                            <!-- User details will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">
                    <i class="bi bi-trash"></i> Delete User
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Users JavaScript -->
<script src="assets/js/users.js"></script>