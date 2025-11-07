<?php $pageTitle = 'Dashboard'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>Welcome back, <?php echo htmlspecialchars($currentUser['name']); ?>!</h2>
                <p>Here's what's happening with your system today</p>
            </div>
        </div>

        <!-- Notification Permission Banner -->
        <div class="notification-permission-banner" id="notificationPermissionBanner" style="display: none;">
            <div class="notification-banner-content">
                <div class="notification-banner-icon">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="notification-banner-text">
                    <h6>Enable Browser Notifications</h6>
                    <p>Get instant alerts for urgent SLA deadlines, new messages, and important notifications.</p>
                </div>
                <div class="notification-banner-actions">
                    <button class="btn btn-primary btn-sm" onclick="enableNotifications()">Enable Notifications</button>
                    <button class="btn btn-link btn-sm text-muted" onclick="dismissNotificationBanner()">Dismiss</button>
                </div>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card clickable-card" id="metricCardUsers" onclick="window.location.href='users.php'" title="View all users">
                <div class="metric-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="metric-content">
                    <h3>127</h3>
                    <p class="metric-label">Total Users</p>
                    <span class="metric-status text-success">Registered users</span>
                </div>
            </div>

            <div class="metric-card clickable-card" id="metricCardJobs" onclick="window.location.href='jobs.php'" title="View all jobs">
                <div class="metric-icon jobs">
                    <i class="bi bi-briefcase"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalJobsCount">0</h3>
                    <p class="metric-label">Total Jobs</p>
                    <span class="metric-status text-success">All jobs created</span>
                </div>
            </div>

            <div class="metric-card clickable-card" id="metricCardPendingJobs" onclick="window.location.href='jobs.php?status=added'" title="View pending jobs">
                <div class="metric-icon pending">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="pendingJobsCount">0</h3>
                    <p class="metric-label">Pending Jobs</p>
                    <span class="metric-status text-warning">Awaiting start</span>
                </div>
            </div>

            <div class="metric-card clickable-card" id="metricCardApprovals" onclick="window.location.href='requests.php?filter=pending'" title="View pending approvals">
                <div class="metric-icon approvals">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3>8</h3>
                    <p class="metric-label">Pending Approvals</p>
                    <span class="metric-status text-muted">Awaiting review</span>
                </div>
            </div>
        </div>

        <!-- Monitoring & Reminders Row -->
        <div class="monitoring-reminders-row">
            <div class="row g-2">
                <!-- SLA Monitoring -->
                <div class="col-lg-4">
                    <div class="content-card monitoring-card">
                        <div class="card-header">
                            <h4>
                                <i class="bi bi-clock-history me-2"></i>
                                Job SLA Monitoring
                            </h4>
                            <span class="badge bg-warning" id="slaMonitoringBadge">0 Urgent</span>
                        </div>
                        <div class="card-body" id="slaMonitoringBody">
                            <!-- Dynamic SLA reminders will be loaded here -->
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading SLA reminders...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Reminders -->
                <div class="col-lg-4">
                    <div class="content-card monitoring-card">
                        <div class="card-header">
                            <h4>
                                <i class="bi bi-credit-card me-2"></i>
                                Payment Reminders
                            </h4>
                            <span class="badge bg-danger" id="paymentRemindersBadge">0 Pending</span>
                        </div>
                        <div class="card-body" id="paymentRemindersBody">
                            <!-- Dynamic payment reminders will be loaded here -->
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading payment reminders...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Reminders -->
                <div class="col-lg-4">
                    <div class="content-card monitoring-card">
                        <div class="card-header">
                            <h4>
                                <i class="bi bi-receipt me-2"></i>
                                Invoice Reminders
                            </h4>
                            <span class="badge bg-success" id="invoiceRemindersBadge">0 Pending</span>
                        </div>
                        <div class="card-body" id="invoiceRemindersBody">
                            <!-- Dynamic invoice reminders will be loaded here -->
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading invoice reminders...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="content-row">
            <!-- Recent Jobs -->
            <div class="content-card" id="recentJobsCard">
                <div class="card-header">
                    <h4>Recent Jobs</h4>
                    <button class="btn btn-sm btn-outline-primary" id="viewAllJobsBtn">View All</button>
                </div>
                <div class="card-body" id="recentJobsBody">
                    <div class="job-item" id="jobItem1">
                        <div class="job-info">
                            <h5>Website Development</h5>
                            <span class="badge bg-warning">PAYMENT REQUESTED</span>
                        </div>
                        <div class="job-details">
                            <p>Client: John Smith | 2 vendors assigned</p>
                            <span class="job-time">2 hours ago</span>
                        </div>
                        <div class="job-actions">
                            <i class="bi bi-eye" id="viewJob1"></i>
                        </div>
                    </div>

                    <div class="job-item" id="jobItem2">
                        <div class="job-info">
                            <h5>Mobile App Design</h5>
                            <span class="badge bg-success">IN PROGRESS</span>
                        </div>
                        <div class="job-details">
                            <p>Client: Sarah Johnson | 1 vendor assigned</p>
                            <span class="job-time">1 day ago</span>
                        </div>
                        <div class="job-actions">
                            <i class="bi bi-eye" id="viewJob2"></i>
                        </div>
                    </div>

                    <div class="job-item" id="jobItem3">
                        <div class="job-info">
                            <h5>Logo Design</h5>
                            <span class="badge bg-info">COMPLETED</span>
                        </div>
                        <div class="job-details">
                            <p>Client: Mike Wilson | 1 vendor assigned</p>
                            <span class="job-time">3 days ago</span>
                        </div>
                        <div class="job-actions">
                            <i class="bi bi-eye" id="viewJob3"></i>
                        </div>
                    </div>

                    <div class="job-item" id="jobItem4">
                        <div class="job-info">
                            <h5>Content Writing</h5>
                            <span class="badge bg-secondary">PENDING</span>
                        </div>
                        <div class="job-details">
                            <p>Client: Emily Davis | No vendor assigned</p>
                            <span class="job-time">5 days ago</span>
                        </div>
                        <div class="job-actions">
                            <i class="bi bi-eye" id="viewJob4"></i>
                        </div>
                    </div>

                    <div class="job-item" id="jobItem5">
                        <div class="job-info">
                            <h5>Content Writing</h5>
                            <span class="badge bg-secondary">PENDING</span>
                        </div>
                        <div class="job-details">
                            <p>Client: Emily Davis | No vendor assigned</p>
                            <span class="job-time">5 days ago</span>
                        </div>
                        <div class="job-actions">
                            <i class="bi bi-eye" id="viewJob5"></i>
                        </div>
                    </div>

                </div>
            </div>


            <!-- System Notifications -->
            <div class="content-card" id="systemNotificationsCard">
                <div class="card-header">
                    <h4>System Notifications</h4>
                    <button class="btn btn-sm btn-outline-primary" id="viewAllNotificationsBtn">View All</button>
                </div>
                <div class="card-body" id="systemNotificationsBody">
                    <!-- Dynamic notifications will be loaded here -->
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Loading notifications...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>


<script src="assets/js/dashboard.js"></script>
<?php include 'footer.php'; ?>