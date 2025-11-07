<?php $pageTitle = 'All Jobs'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotalJobs">
                <div class="metric-icon jobs">
                    <i class="bi bi-briefcase"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalJobsCount">0</h3>
                    <p class="metric-label">TOTAL JOBS</p>
                    <span class="metric-status text-success">All jobs created</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardPendingJobs">
                <div class="metric-icon pending">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="pendingJobsCount">0</h3>
                    <p class="metric-label">ADDED</p>
                    <span class="metric-status text-info">New jobs</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardActiveJobs">
                <div class="metric-icon active">
                    <i class="bi bi-play-circle"></i>
                </div>
                <div class="metric-content">
                    <h3 id="activeJobsCount">0</h3>
                    <p class="metric-label">IN PROGRESS</p>
                    <span class="metric-status text-warning">Currently running</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardCompletedJobs">
                <div class="metric-icon completed">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-content">
                    <h3 id="completedJobsCount">0</h3>
                    <p class="metric-label">COMPLETED</p>
                    <span class="metric-status text-success">Successfully finished</span>
                </div>
            </div>
        </div>

        <!-- Jobs List Section -->
        <div class="users-section">

            <div class="section-header">
                <h3>All Jobs</h3>
                <div>
                <div class="section-actions">
                    <!-- Bulk Assignment Controls (Hidden by default) -->
                    <div class="bulk-assignment-controls" id="bulkAssignmentControls" style="display: none;">
                        <div class="bulk-selection-info">
                            <span id="selectedJobsCount">0</span> jobs selected
                        </div>
                        <select class="form-select form-select-sm me-2" id="assignUserSelect" style="width: 200px;">
                            <option value="">Select User to Assign</option>
                        </select>
                        <button class="btn btn-success btn-sm me-2" id="assignSelectedBtn" disabled>
                            <i class="bi bi-person-check"></i> Assign Selected
                        </button>
                        
                        <button class="btn btn-secondary btn-sm" id="clearSelectionBtn">
                            <i class="bi bi-x-circle"></i> Clear Selection
                        </button>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addjobsModal">
                        <i class="bi bi-plus-circle"></i> Add Job
                    </button>
                </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-lg-8 col-md-7 col-12">
                        <div class="search-box">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="search-input" id="jobSearchInput"
                                    placeholder="Search jobs...">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-5 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Status</label>
                                <select class="filter-dropdown" id="jobStatusFilter">
                                    <option value="">All Jobs</option>
                                    <option value="added">Added</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="jobs-table-container" id="jobsTableContainer">
                <div class="text-center py-5" id="jobsLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading jobs...</p>
                </div>
            </div>


        </div>

    </main>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addjobsModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addJobModalLabel">Add New Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addJobForm">
                    <div class="mb-3">
                        <label for="storeName" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="storeName" name="storeName" placeholder="Store Name"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Address"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="jobType" class="form-label">Job Type</label>
                        <input type="text" class="form-control" id="jobType" name="jobType"
                            placeholder="e.g. Delivery, Pickup, Service, Maintenance" required>
                    </div>

                    <div class="mb-3">
                        <label for="jobSLA" class="form-label">Job SLA</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control" id="jobSLA" name="jobSLA" required>
                            <button class="btn btn-outline-secondary" type="button" id="jobSLACalendarBtn">
                                <i class="bi bi-calendar"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jobDetails" class="form-label">Job Details</label>
                        <textarea class="form-control" id="jobDetails" name="jobDetails" rows="4"
                            placeholder="Describe the job requirements, specifications, and any important details..."
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="attachedPictures" class="form-label">Attached Pictures</label>
                        <input type="file" class="form-control" id="attachedPictures" name="attachedPictures" multiple
                            accept="image/*">
                        <div class="file-info mt-2">
                            <i class="bi bi-info-circle text-info"></i>
                            <span class="text-muted">You can select up to 10 images (JPG, PNG, GIF, WebP). Max 5MB per file.</span>
                        </div>
                        <div id="fileCountInfo" class="file-count-info mt-2" style="display: none;">
                            <i class="bi bi-images text-primary"></i>
                            <span class="text-primary fw-medium"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="additionalNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="additionalNotes" name="additionalNotes" rows="3"
                            placeholder="Any additional information, special instructions, or notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="createJobBtn">Create Job</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editjobsModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editJobModalLabel">Edit Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editJobForm">
                    <div class="mb-3">
                        <label for="editStoreName" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="editStoreName" name="storeName"
                            placeholder="Store Name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="address" placeholder="Address"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="editJobType" class="form-label">Job Type</label>
                        <input type="text" class="form-control" id="editJobType" name="jobType"
                            placeholder="e.g. Delivery, Pickup, Service, Maintenance" required>
                    </div>

                    <div class="mb-3">
                        <label for="editJobSLA" class="form-label">Job SLA</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control" id="editJobSLA" name="jobSLA" required>
                            <button class="btn btn-outline-secondary" type="button" id="editJobSLACalendarBtn">
                                <i class="bi bi-calendar"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editJobDetails" class="form-label">Job Details</label>
                        <textarea class="form-control" id="editJobDetails" name="jobDetails" rows="4"
                            placeholder="Describe the job requirements, specifications, and any important details..."
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editAttachedPictures" class="form-label">Attached Pictures</label>
                        <input type="file" class="form-control" id="editAttachedPictures" name="attachedPictures"
                            multiple accept="image/*">
                        <div class="file-info">
                            <i class="bi bi-info-circle"></i>
                            <span>You can select up to 10 images (JPG, PNG, GIF, WebP). Max 5MB per file.</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editAdditionalNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="editAdditionalNotes" name="additionalNotes" rows="3"
                            placeholder="Any additional information, special instructions, or notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateJobBtn">Update Job</button>
            </div>
        </div>
    </div>
</div>

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

<!-- Delete Job Confirmation Modal -->
<div class="modal fade" id="deleteJobModal" tabindex="-1" aria-labelledby="deleteJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteJobModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Delete Job Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-trash3-fill text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Are you sure you want to delete this job?</h6>
                <p class="text-muted text-center mb-3" id="deleteJobName"></p>

                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        This action will permanently delete:
                    </h6>
                    <ul class="mb-0">
                        <li>The job and all its details</li>
                        <li>All assigned vendors</li>
                        <li>All chat messages and attachments</li>
                        <li>All job pictures and files</li>
                        <li>All completion forms and documents</li>
                        <li>All payment requests</li>
                        <li>All related notifications</li>
                    </ul>
                </div>

                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteJobBtn">
                    <i class="bi bi-trash3-fill me-1"></i>
                    Delete Job
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Jobs JavaScript -->
<script src="assets/js/jobs.js"></script>