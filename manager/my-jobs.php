<?php $pageTitle = 'All Jobs'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Dashboard Content -->
        <main class="dashboard-content">

            <!-- Metrics Cards -->
            <div class="metrics-row" id="metricsRow">
                <div class="metric-card clickable-card" id="metricCardTotalJobs" onclick="document.getElementById('userJobStatusFilter').value=''; filterJobs();" title="View all jobs">
                    <div class="metric-icon jobs">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="totalJobsCount">0</h3>
                        <p class="metric-label">TOTAL JOBS</p>
                        <span class="metric-status text-success">All jobs created</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardSlaReminders" onclick="window.location.href='my-jobs.php'" title="View jobs with SLA reminders">
                    <div class="metric-icon sla">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="slaRemindersCount">0</h3>
                        <p class="metric-label">SLA REMINDERS</p>
                        <span class="metric-status text-warning">Under 2 days</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardCompletedJobs" onclick="document.getElementById('userJobStatusFilter').value='completed'; filterJobs();" title="View completed jobs">
                    <div class="metric-icon completed">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="completedJobsCount">0</h3>
                        <p class="metric-label">COMPLETED</p>
                        <span class="metric-status text-success">Successfully finished</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardInProgressJobs" onclick="document.getElementById('userJobStatusFilter').value='in_progress'; filterJobs();" title="View in-progress jobs">
                    <div class="metric-icon active">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="inProgressJobsCount">0</h3>
                        <p class="metric-label">IN PROGRESS</p>
                        <span class="metric-status text-info">Currently running</span>
                    </div>
                </div>
            </div>

            <!-- Jobs List Section -->
            <div class="users-section">

                <div class="section-header">
                    <div>
                        <h3>My Jobs</h3>
                        <p class="text-muted mb-0">Your assigned and created jobs</p>
                    </div>
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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobModal">
                            <i class="bi bi-plus-circle"></i> Add Job
                        </button>
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
                                    <input type="text" class="search-input" id="userJobSearchInput" placeholder="Search jobs...">
                                </div>
                        </div>
                    </div>
                    
                        <div class="col-lg-4 col-md-5 col-12">
                    <div class="filter-dropdowns">
                        <div class="dropdown-wrapper">
                            <label>Status</label>
                            <select class="filter-dropdown" id="userJobStatusFilter">
                                        <option value="">All Jobs</option>
                                        <option value="added">Job Created</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                            </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs Table -->
                <div class="jobs-table-container">
                    <div class="table-responsive">
                        <table class="table jobs-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllJobs" class="form-check-input">
                                    </th>
                                    <th>STORE NAME</th>
                                    <th>JOB TYPE</th>
                                    <th>ADDRESS</th>
                                    <th>SLA DEADLINE</th>
                                    <th>STATUS</th>
                                    <th>ASSIGNED TO</th>
                                    <th>VENDORS</th>
                                    <th>CREATED</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                <!-- Dynamic jobs will be loaded here -->
                            </tbody>
                        </table>
                </div>
                </div>

                
            </div>
           
        </main>
    </div>

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
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

    <?php include 'footer.php'; ?>

    <!-- My Jobs JavaScript -->
    <script src="assets/js/my-jobs.js"></script>
