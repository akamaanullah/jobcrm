<?php $pageTitle = 'Manage Vendors'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Dashboard Content -->
        <main class="dashboard-content">


            <!-- Users List Section -->
            <div class="users-section">
                
                <div class="section-header">
                    <h3>Manage Vendors</h3>
                    <p>View and manage all registered vendors</p>
                    
                </div>

                <!-- Search and Filter Section -->
                <div class="search-filter-section">
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-12 col-12">
                            <div class="search-box">
                                <label>Search</label>
                                <div class="search-input-wrapper">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" class="search-input" id="vendorSearchInput" placeholder="Search vendors by name, email, or specialty...">
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="filter-dropdowns">
                                <div class="dropdown-wrapper">
                                    <label>Sort By</label>
                                    <select class="filter-dropdown" id="vendorSortBy">
                                        <option value="created_at_desc">Newest First</option>
                                        <option value="created_at_asc">Oldest First</option>
                                        <option value="vendor_name_asc">Name A-Z</option>
                                        <option value="vendor_name_desc">Name Z-A</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="users-grid" id="vendorsGrid">
                    <!-- Dynamic vendor cards will be loaded here -->
                    <div class="text-center py-5" id="vendorsLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading vendors...</p>
                    </div>
                </div>
            </div>
           
        </main>
    </div>

    <!-- Add User Modal -->
    <!-- <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createUserBtn">Create User</button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Edit User Modal -->
    <!-- <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                                    <label for="editFullName" class="form-label">
                                       </i> Full Name
                                    </label>
                                    <input type="text" class="form-control" id="editFullName" name="fullName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editUsername" class="form-label">
                                        </i> Username
                                    </label>
                                    <input type="text" class="form-control" id="editUsername" name="username" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">
                                </i> Email Address
                            </label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editRole" class="form-label">
                                        Role
                                    </label>
                                    <select class="form-select" id="editRole" name="role" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editStatus" class="form-label">
                                        </i> Status
                                    </label>
                                    <select class="form-select" id="editStatus" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">
                                </i> New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="editPassword" name="password" placeholder="Leave blank to keep current password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Leave empty to keep the current password unchanged</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="updateUserBtn">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                </div>
            </div>
        </div>
    </div> -->

    <script src="assets/js/vendors.js"></script>
    <?php include 'footer.php'; ?>
