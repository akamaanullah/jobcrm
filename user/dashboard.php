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
                    <p>Here's your job portal overview and important updates</p>
                </div>
            </div>

            <!-- Notification Permission Banner -->
            <div id="notificationPermissionBanner" class="notification-permission-banner" style="display: none;">
                <div class="banner-content">
                    <div class="banner-icon">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="banner-text">
                        <h4>Enable Notifications</h4>
                        <p>Get instant alerts for SLA deadlines, new messages, and important updates.</p>
                    </div>
                    <div class="banner-actions">
                        <button class="btn btn-primary btn-sm" onclick="enableNotifications()">
                            <i class="bi bi-bell"></i> Enable
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="dismissNotificationBanner()">
                            <i class="bi bi-x"></i> Dismiss
                        </button>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="metrics-row" id="metricsRow">
                <div class="metric-card clickable-card" id="metricCardTotalJobs" onclick="window.location.href='my-jobs.php'" title="View all jobs">
                    <div class="metric-icon jobs">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="dashboardTotalJobsCount">0</h3>
                        <p class="metric-label">TOTAL JOBS</p>
                        <span class="metric-status text-success">All jobs created</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardTotalVendors" onclick="window.location.href='vendors.php'" title="View all vendors">
                    <div class="metric-icon vendors">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="dashboardTotalVendorsCount">0</h3>
                        <p class="metric-label">TOTAL VENDORS</p>
                        <span class="metric-status text-info">All vendors</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardSlaReminders" onclick="window.location.href='my-jobs.php'" title="View jobs with SLA reminders">
                    <div class="metric-icon sla">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="dashboardSlaRemindersCount">0</h3>
                        <p class="metric-label">SLA REMINDERS</p>
                        <span class="metric-status text-warning">Under 2 days</span>
                    </div>
                </div>

                <div class="metric-card clickable-card" id="metricCardCompletedJobs" onclick="window.location.href='my-jobs.php?status=completed'" title="View completed jobs">
                    <div class="metric-icon completed">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="metric-content">
                        <h3 id="dashboardCompletedJobsCount">0</h3>
                        <p class="metric-label">COMPLETED</p>
                        <span class="metric-status text-success">Successfully done</span>
                    </div>
                </div>
            </div>

            <!-- SLA Reminders Section -->
            <div class="sla-reminders-section">
                <div class="content-card">
                    <div class="card-header">
                        <h4>
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            SLA Reminders
                        </h4>
                        <span class="badge bg-warning" id="slaRemindersBadge">0</span>
                    </div>
                    <div class="card-body" id="slaRemindersBody">
                        <!-- Dynamic SLA reminders will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="content-row">
                <!-- My Recent Jobs -->
                <div class="content-card" id="recentJobsCard">
                    <div class="card-header">
                        <h4>My Recent Jobs</h4>
                        <button class="btn btn-sm btn-outline-primary" id="viewAllJobsBtn">View All</button>
                    </div>
                    <div class="card-body" id="recentJobsBody">
                        <!-- Dynamic recent jobs will be loaded here -->

                    </div>
                </div>

                <!-- My Notifications -->
                <div class="content-card" id="systemNotificationsCard">
                    <div class="card-header">
                        <h4>My Notifications</h4>
                        <button class="btn btn-sm btn-outline-primary" id="viewAllNotificationsBtn">View All</button>
                    </div>
                    <div class="card-body" id="systemNotificationsBody">
                        <!-- Dynamic notifications will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>


    <?php include 'footer.php'; ?>

<style>
/* SLA Reminders Styling */
.sla-reminders-section {
    margin-bottom: 2rem;
}

.sla-reminders-section .content-card {
    min-height: auto;
    height: auto;
}

.sla-reminders-section .card-body {
    min-height: auto;
    height: auto;
    padding: 1rem;
}

/* Auto height for all dashboard sections */
.recent-jobs-section .content-card,
.recent-notifications-section .content-card {
    min-height: auto;
    height: auto;
}

.recent-jobs-section .card-body,
.recent-notifications-section .card-body {
    min-height: auto;
    height: auto;
    padding: 1rem;
}

.sla-reminder-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: var(--radius-md);
    border-left: 4px solid;
    background: var(--bg-light);
    transition: all 0.3s ease;
}

.sla-reminder-item:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-light);
}

.sla-reminder-item.urgent {
    border-left-color: #EF4444;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
}

.sla-reminder-item.warning {
    border-left-color: #F59E0B;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.02) 100%);
}

.sla-reminder-item.info {
    border-left-color: #3B82F6;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
}

.sla-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.sla-reminder-item.urgent .sla-icon {
    background: #EF4444;
    color: white;
}

.sla-reminder-item.warning .sla-icon {
    background: #F59E0B;
    color: white;
}

.sla-reminder-item.info .sla-icon {
    background: #3B82F6;
    color: white;
}

.sla-content {
    flex: 1;
}

.sla-content h6 {
    margin: 0 0 0.25rem 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-dark);
}

.sla-content p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-medium);
}

.sla-status {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 0.25rem;
}

.sla-status.urgent {
    background: #FEE2E2;
    color: #DC2626;
}

.sla-status.warning {
    background: #FEF3C7;
    color: #D97706;
}

.sla-status.info {
    background: #DBEAFE;
    color: #2563EB;
}

.sla-actions {
    flex-shrink: 0;
}


/* Responsive Design */
@media (max-width: 768px) {
    .sla-reminder-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .sla-actions {
        align-self: stretch;
    }
    
    .sla-actions .btn {
        width: 100%;
    }
}
</style>

<script src="assets/js/dashboard.js"></script>
