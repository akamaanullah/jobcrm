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
                    <h3>My Assigned Jobs</h3>
                    <p class="text-muted mb-0">Jobs assigned to you by admin</p>
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
                                    <th>STORE NAME</th>
                                    <th>JOB TYPE</th>
                                    <th>ADDRESS</th>
                                    <th>SLA DEADLINE</th>
                                    <th>STATUS</th>
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


    <?php include 'footer.php'; ?>

    <!-- My Jobs JavaScript -->
    <script src="assets/js/my-jobs.js"></script>
