// User Dashboard Management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification service
    initializeNotifications();
    
    // Load dashboard data
    loadDashboardData();
    
    // Auto refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
    
    // Setup event listeners
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // View All Jobs button
    const viewAllJobsBtn = document.getElementById('viewAllJobsBtn');
    if (viewAllJobsBtn) {
        viewAllJobsBtn.addEventListener('click', function() {
            window.location.href = 'my-jobs.php';
        });
    }
    
    // View All Notifications button
    const viewAllNotificationsBtn = document.getElementById('viewAllNotificationsBtn');
    if (viewAllNotificationsBtn) {
        viewAllNotificationsBtn.addEventListener('click', function() {
            window.location.href = 'notifications.php';
        });
    }
}

// Load dashboard data from API
async function loadDashboardData() {
    try {
        const response = await fetch('assets/api/get_dashboard_data.php');
        const result = await response.json();
        
        if (result.success) {
            updateJobStats(result.data);
            updateSlaReminders(result.data.sla_reminders_details);
            updateRecentJobs(result.data.recent_jobs);
            updateRecentNotifications(result.data.recent_notifications);
            
            // Check for notification triggers
            checkNotificationTriggers(result.data);
        } else {
            console.error('Dashboard data error:', result.message);
        }
    } catch (error) {
        console.error('Load Dashboard Error:', error);
    }
}

// Update dashboard statistics cards
function updateJobStats(data) {
    // Update total jobs
    const totalJobsElement = document.querySelector('#dashboardTotalJobsCount');
    if (totalJobsElement) {
        totalJobsElement.textContent = data.total_jobs;
    }
    
    // Update total vendors
    const totalVendorsElement = document.querySelector('#dashboardTotalVendorsCount');
    if (totalVendorsElement) {
        totalVendorsElement.textContent = data.total_vendors;
    }
    
    // Update SLA reminders
    const slaRemindersElement = document.querySelector('#dashboardSlaRemindersCount');
    if (slaRemindersElement) {
        slaRemindersElement.textContent = data.sla_reminders;
    }
    
    // Update completed jobs
    const completedJobsElement = document.querySelector('#dashboardCompletedJobsCount');
    if (completedJobsElement) {
        completedJobsElement.textContent = data.completed_jobs;
    }
}

// Update SLA reminders section
function updateSlaReminders(reminders) {
    const slaSection = document.querySelector('.sla-reminders-section');
    if (!slaSection) return;
    
    const cardBody = slaSection.querySelector('.card-body');
    const badge = slaSection.querySelector('.badge');
    
    if (reminders.length === 0) {
        cardBody.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-1">No SLA reminders</p>
                <small class="text-muted">All jobs are on track!</small>
            </div>
        `;
        if (badge) {
            badge.textContent = '0 Urgent';
            badge.className = 'badge bg-success';
        }
        return;
    }
    
    // Update badge
    const urgentCount = reminders.filter(r => r.reminder_type === 'urgent').length;
    if (badge) {
        badge.textContent = `${urgentCount} Urgent`;
        badge.className = urgentCount > 0 ? 'badge bg-danger' : 'badge bg-warning';
    }
    
    // Render reminders
    cardBody.innerHTML = reminders.map(reminder => {
        const statusClass = reminder.reminder_type === 'urgent' ? 'urgent' : 
                           reminder.reminder_type === 'warning' ? 'warning' : 'normal';
        const statusText = reminder.reminder_type === 'urgent' ? 'URGENT' : 
                          reminder.reminder_type === 'warning' ? 'WARNING' : 'NORMAL';
        const btnClass = reminder.reminder_type === 'urgent' ? 'btn-outline-danger' : 
                        reminder.reminder_type === 'warning' ? 'btn-outline-warning' : 'btn-outline-primary';
        
        return `
            <div class="sla-reminder-item ${statusClass}">
                <div class="sla-icon">
                    <i class="bi ${reminder.reminder_type === 'urgent' ? 'bi-clock-fill' : 
                                  reminder.reminder_type === 'warning' ? 'bi-hourglass-split' : 'bi-clock'}"></i>
                </div>
                <div class="sla-content">
                    <h6>${reminder.job_name} - Job #${reminder.job_number}</h6>
                    <p>SLA Deadline: <strong>${reminder.time_remaining}</strong></p>
                    <span class="sla-status ${statusClass}">${statusText}</span>
                </div>
                <div class="sla-actions">
                    <button class="btn btn-sm ${btnClass}" onclick="viewJob(${reminder.id})">View Job</button>
                </div>
            </div>
        `;
    }).join('');
}

// Update recent jobs section
function updateRecentJobs(jobs) {
    const cardBody = document.getElementById('recentJobsBody');
    if (!cardBody) return;
    
    if (jobs.length === 0) {
        cardBody.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-briefcase text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-1">No jobs found</p>
            </div>
        `;
        return;
    }
    
    cardBody.innerHTML = jobs.map(job => {
        const statusClass = getJobStatusClass(job.status);
        const statusBadge = getJobStatusBadge(job.status);
        const timeAgo = getTimeAgo(job.created_at);
        
        return `
            <div class="job-item">
                <div class="job-info">
                    <h5>${job.job_name}</h5>
                    <span class="badge ${statusBadge}">${job.status.toUpperCase()}</span>
                </div>
                <div class="job-details">
                    <p>Job #${job.job_number}</p>
                    <span class="job-time">Created ${timeAgo}</span>
                </div>
                <div class="job-actions">
                    <i class="bi bi-eye" onclick="viewJob(${job.id})" style="cursor: pointer;"></i>
                </div>
            </div>
        `;
    }).join('');
}

// Update recent notifications section
function updateRecentNotifications(notifications) {
    const cardBody = document.getElementById('systemNotificationsBody');
    if (!cardBody) return;
    
    if (notifications.length === 0) {
        cardBody.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-1">No notifications</p>
            </div>
        `;
        return;
    }
    
    cardBody.innerHTML = notifications.map(notification => {
        const icon = getNotificationIcon(notification.type);
        const timeAgo = getTimeAgo(notification.created_at);
        const unreadClass = notification.is_read ? '' : 'unread';
        const iconClass = getNotificationIconClass(notification.type);
        
        return `
            <div class="notification-item ${unreadClass}">
                <div class="system-notification-icon">
                    <i class="${icon} ${iconClass}"></i>
                </div>
                <div class="notification-content">
                    <h6>${getNotificationTitle(notification.type)}</h6>
                    <p>${notification.message}</p>
                </div>
                <span class="notification-time">${timeAgo}</span>
            </div>
        `;
    }).join('');
}

// Helper functions
function getJobStatusClass(status) {
    switch (status) {
        case 'active': return 'text-info';
        case 'completed': return 'text-success';
        case 'pending': return 'text-warning';
        default: return 'text-muted';
    }
}

function getJobStatusBadge(status) {
    switch (status) {
        case 'in_progress': return 'bg-warning';
        case 'completed': return 'bg-success';
        case 'added': return 'bg-info';
        default: return 'bg-secondary';
    }
}

function getJobStatusIcon(status) {
    switch (status) {
        case 'active': return 'bi bi-play-circle';
        case 'completed': return 'bi bi-check-circle';
        case 'pending': return 'bi bi-clock';
        default: return 'bi bi-question-circle';
    }
}

function getNotificationIcon(type) {
    switch (type) {
        case 'request_visit_accepted':
        case 'final_visit_request_accepted':
            return 'bi bi-check-circle';
        case 'visit_request_rejected':
        case 'final_visit_request_rejected':
        case 'vendor_payment_rejected':
            return 'bi bi-x-circle';
        case 'request_vendor_payment':
        case 'vendor_payment_accepted':
            return 'bi bi-credit-card';
        case 'final_visit_request':
            return 'bi bi-eye';
        case 'job_completed':
            return 'bi bi-check-circle';
        case 'sla_reminder':
            return 'bi bi-clock-history';
        default:
            return 'bi bi-bell';
    }
}

function getNotificationTitle(type) {
    switch (type) {
        case 'request_visit_accepted':
            return 'Visit Request Accepted';
        case 'final_visit_request_accepted':
            return 'Final Visit Approved';
        case 'visit_request_rejected':
            return 'Visit Request Rejected';
        case 'final_visit_request_rejected':
            return 'Final Visit Rejected';
        case 'request_vendor_payment':
            return 'Payment Request';
        case 'vendor_payment_accepted':
            return 'Payment Ready';
        case 'vendor_payment_rejected':
            return 'Payment Request Rejected';
        case 'final_visit_request':
            return 'Final Visit Request';
        case 'job_completed':
            return 'Job Completed';
        case 'sla_reminder':
            return 'SLA Reminder';
        default:
            return 'Notification';
    }
}

function getNotificationIconClass(type) {
    // All icons will be white
    return 'text-white';
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
}

// View job function
function viewJob(jobId) {
    window.location.href = `my-jobs.php?id=${jobId}`;
}

// Initialize notifications
async function initializeNotifications() {
    // Wait for notification service to be ready
    await new Promise(resolve => {
        const checkService = () => {
            if (window.notificationService && window.notificationService.isInitialized) {
                resolve();
            } else {
                setTimeout(checkService, 100);
            }
        };
        checkService();
    });

    // Show notification permission banner if needed
    if (window.notificationService.isSupported() && !window.notificationService.isEnabled()) {
        const banner = document.getElementById('notificationPermissionBanner');
        if (banner) {
            banner.style.display = 'block';
        }
    }
}

// Check for notification triggers
function checkNotificationTriggers(data) {
    if (!window.notificationService || !window.notificationService.isEnabled()) {
        return;
    }

    // Check SLA reminders for urgent notifications
    if (data.sla_reminders_details) {
        window.notificationService.checkSlaReminders(data.sla_reminders_details);
    }

    // Check unread messages count
    // Note: This would need to be added to the dashboard data API
    // For now, we'll use a placeholder
    const unreadMessageCount = data.total_unread_messages || 0;
    window.notificationService.checkUnreadMessages(unreadMessageCount);

    // Check unread notifications count
    const unreadNotificationCount = data.total_unread_notifications || 0;
    window.notificationService.checkNotifications(unreadNotificationCount);
}

// Enable notifications function
async function enableNotifications() {
    if (window.notificationService) {
        const permission = await window.notificationService.requestPermission();
        
        if (permission === 'granted') {
            // Hide the banner
            dismissNotificationBanner();
            
            // Show success message
            window.notificationService.showNotification(
                'Notifications Enabled!',
                'You will now receive important updates about your jobs and SLA reminders.',
                'success',
                { requireInteraction: true }
            );
        }
    }
}

// Dismiss notification banner
function dismissNotificationBanner() {
    const banner = document.getElementById('notificationPermissionBanner');
    if (banner) {
        banner.style.display = 'none';
        // Save preference to not show again
        localStorage.setItem('notification_banner_dismissed', 'true');
    }
}

// Test notification function (for debugging)
function testNotification() {
    if (window.notificationService) {
        window.notificationService.showTestNotification();
    }
}
