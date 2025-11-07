// Admin Dashboard Management
class AdminDashboard {
    constructor() {
        this.stats = {};
        this.recentJobs = [];
        this.recentNotifications = [];
        this.slaReminders = [];
        this.paymentReminders = [];
        this.invoiceReminders = [];
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.initializeNotifications();

        // Auto-refresh every 30 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, 30000);
    }

    async loadDashboardData() {
        try {
            const response = await fetch('assets/api/get_dashboard_stats.php');
            const data = await response.json();

            if (data.success) {
                this.stats = data.data.stats;
                this.recentJobs = data.data.recent_jobs;
                this.recentNotifications = data.data.recent_notifications;
                this.slaReminders = data.data.sla_reminders_details || [];
                this.paymentReminders = data.data.payment_reminders_details || [];
                this.invoiceReminders = data.data.invoice_reminders_details || [];

                this.updateStats();
                this.updateRecentJobs();
                this.updateRecentNotifications();
                this.updateSlaMonitoring();
                this.updatePaymentReminders();
                this.updateInvoiceReminders();
                this.updateSystemNotifications();

                // Check for notification triggers
                this.checkNotificationTriggers(data.data);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Load Dashboard Data Error:', error);
            this.showError('Failed to load dashboard data');
            // Ensure SLA monitoring is still updated even on error
            this.slaReminders = [];
            this.updateSlaMonitoring();
        }
    }

    // Initialize notifications
    async initializeNotifications() {
        // Wait for notification service to be ready
        if (window.adminNotificationService) {
            // Show notification permission banner if notifications are supported but not enabled
            const notificationBanner = document.getElementById('notificationPermissionBanner');
            if (notificationBanner && window.adminNotificationService.isSupported() && !window.adminNotificationService.isEnabled()) {
                notificationBanner.style.display = 'block';
            }
        }
    }

    // Check notification triggers
    checkNotificationTriggers(data) {
        if (window.adminNotificationService && window.adminNotificationService.isEnabled()) {
            // Check SLA reminders for notifications
            if (data.sla_reminders_details) {
                window.adminNotificationService.checkSlaReminders(data.sla_reminders_details);
            }

            // Check payment reminders for notifications
            if (data.payment_reminders_details && data.payment_reminders_details.length > 0) {
                window.adminNotificationService.checkPaymentReminders(data.payment_reminders_details);
            }

            // Check invoice reminders for notifications
            if (data.invoice_reminders_details && data.invoice_reminders_details.length > 0) {
                window.adminNotificationService.checkInvoiceReminders(data.invoice_reminders_details);
            }

            // Check unread messages for notifications
            if (data.total_unread_messages) {
                window.adminNotificationService.checkUnreadMessages(data.total_unread_messages);
            }

            // Check unread notifications for notifications
            if (data.total_unread_notifications) {
                window.adminNotificationService.checkNotifications(data.total_unread_notifications);
            }
        }
    }

    // Enable notifications
    async enableNotifications() {
        if (window.adminNotificationService) {
            const permission = await window.adminNotificationService.requestPermission();
            if (permission === 'granted') {
                // Hide the banner
                const banner = document.getElementById('notificationPermissionBanner');
                if (banner) {
                    banner.style.display = 'none';
                }

                // Save preference
                window.adminNotificationService.saveNotificationPreference(true);

                // Show success message
                this.showNotification('Notifications enabled successfully!', 'success');
            } else {
                this.showNotification('Notifications permission denied', 'error');
            }
        }
    }

    // Dismiss notification banner
    dismissNotificationBanner() {
        const banner = document.getElementById('notificationPermissionBanner');
        if (banner) {
            banner.style.display = 'none';
        }

        // Save preference to not show again
        if (window.adminNotificationService) {
            window.adminNotificationService.saveNotificationPreference(false);
        }
    }

    updateStats() {
        // Update Total Users
        const totalUsersElement = document.querySelector('#metricCardUsers h3');
        if (totalUsersElement) {
            totalUsersElement.textContent = this.stats.total_users || 0;
        }

        // Update Total Jobs
        const totalJobsElement = document.querySelector('#metricCardJobs h3');
        if (totalJobsElement) {
            totalJobsElement.textContent = this.stats.total_jobs || 0;
        }

        // Update Pending Jobs
        const pendingJobsElement = document.querySelector('#metricCardPendingJobs h3');
        if (pendingJobsElement) {
            pendingJobsElement.textContent = this.stats.pending_jobs || 0;
        }

        // Update Pending Approvals
        const pendingApprovalsElement = document.querySelector('#metricCardApprovals h3');
        if (pendingApprovalsElement) {
            pendingApprovalsElement.textContent = this.stats.pending_approvals || 0;
        }
    }

        updateRecentJobs() {
            const recentJobsBody = document.getElementById('recentJobsBody');
            if (!recentJobsBody) return;

            if (this.recentJobs.length === 0) {
                recentJobsBody.innerHTML = `
                    <div class="text-center py-3">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No recent jobs</p>
                    </div>
                `;
                return;
            }

            recentJobsBody.innerHTML = this.recentJobs.map(job => `
                <div class="job-item">
                    <div class="job-info">
                        <h5>${job.store_name}</h5>
                        <span class="badge ${job.status_badge.class}">${job.status_badge.text}</span>
                    </div>
                    <div class="job-details">
                        <p>${job.vendor_count} vendor${job.vendor_count !== 1 ? 's' : ''} added</p>
                        <small class="text-muted">${job.time_ago}</small>
                    </div>
                </div>
            `).join('');
        }

    updateRecentNotifications() {
        const recentNotificationsBody = document.getElementById('recentNotificationsBody');
        if (!recentNotificationsBody) return;

        if (this.recentNotifications.length === 0) {
            recentNotificationsBody.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-bell text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No recent notifications</p>
                </div>
            `;
            return;
        }

        recentNotificationsBody.innerHTML = this.recentNotifications.map(notification => `
            <div class="notification-item">
                <div class="notification-icon">
                    <i class="bi ${notification.icon}"></i>
                </div>
                <div class="notification-content">
                    <h6>${notification.title}</h6>
                    <p>${notification.message}</p>
                </div>
                <span class="notification-time">${notification.time_ago}</span>
            </div>
        `).join('');
    }

    updateInvoiceReminders() {
        const invoiceRemindersBody = document.getElementById('invoiceRemindersBody');
        const invoiceRemindersBadge = document.getElementById('invoiceRemindersBadge');

        if (!invoiceRemindersBody) return;

        if (this.invoiceReminders.length === 0) {
            invoiceRemindersBody.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-1">No pending invoice reminders</p>
                    <small class="text-muted">All invoices are up to date!</small>
                </div>
            `;
            if (invoiceRemindersBadge) {
                invoiceRemindersBadge.textContent = '0 Pending';
                invoiceRemindersBadge.className = 'badge bg-success';
            }
            return;
        }

        // Update badge - count ALL invoice reminders
        const totalCount = this.invoiceReminders.length;
        const urgentCount = this.invoiceReminders.filter(r => r.reminder_type === 'urgent' || r.reminder_type === 'overdue').length;
        if (invoiceRemindersBadge) {
            invoiceRemindersBadge.textContent = `${totalCount} Pending`;
            invoiceRemindersBadge.className = urgentCount > 0 ? 'badge bg-danger' : 'badge bg-warning';
        }

        // Render invoice reminders
        invoiceRemindersBody.innerHTML = this.invoiceReminders.map(reminder => {
            const statusClass = reminder.reminder_type === 'overdue' ? 'overdue' :
                reminder.reminder_type === 'urgent' ? 'urgent' : 'pending';
            const statusText = reminder.reminder_type === 'overdue' ? 'OVERDUE' :
                reminder.reminder_type === 'urgent' ? 'URGENT' : 'PENDING';
            const btnClass = reminder.reminder_type === 'overdue' ? 'btn-outline-danger' :
                reminder.reminder_type === 'urgent' ? 'btn-outline-danger' : 'btn-outline-primary';
            
            // Add sent indicator
            const sentIndicator = reminder.notification_sent ? 
                `<small class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Notification sent</small>` : 
                `<small class="text-warning"><i class="bi bi-clock-fill"></i> Pending notification</small>`;

            return `
                <div class="invoice-reminder-item ${statusClass}">
                    <div class="d-flex align-items-center gap-1rem">
                        <div class="invoice-icon">
                            <i class="bi ${reminder.reminder_type === 'overdue' ? 'bi-exclamation-triangle-fill' :
                        reminder.reminder_type === 'urgent' ? 'bi-receipt-cutoff' :
                            'bi-receipt'}"></i>
                        </div>
                        <div class="invoice-content">
                            <h6>${reminder.job_name} - ${reminder.job_number}</h6>
                            <p><strong>${reminder.vendor_name}</strong> • Payment accepted ${reminder.time_since_acceptance}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="invoice-status ${statusClass}">${statusText}</span>
                                ${sentIndicator}
                            </div>
                        </div>
                    </div>
                    <div class="invoice-actions">
                        <button class="btn btn-sm ${btnClass}" onclick="viewInvoiceGenerator(${reminder.job_id || ''})">Generate Invoice</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    updateSystemNotifications() {
        const systemNotificationsBody = document.getElementById('systemNotificationsBody');
        if (!systemNotificationsBody) return;

        if (this.recentNotifications.length === 0) {
            systemNotificationsBody.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No system notifications</p>
                </div>
            `;
            return;
        }

        systemNotificationsBody.innerHTML = this.recentNotifications.map(notification => `
            <div class="notification-item">
                <div class="system-notification-icon">
                    <i class="bi ${notification.icon}"></i>
                </div>
                <div class="notification-content">
                    <h6>${notification.title}</h6>
                    <p>${notification.message}</p>
                </div>
                <span class="notification-time">${notification.time_ago}</span>
            </div>
        `).join('');
    }

    updateSlaMonitoring() {
        const slaMonitoringBody = document.getElementById('slaMonitoringBody');
        const slaMonitoringBadge = document.getElementById('slaMonitoringBadge');

        if (!slaMonitoringBody) return;

        if (this.slaReminders.length === 0) {
            slaMonitoringBody.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-1">No SLA reminders</p>
                    <small class="text-muted">All jobs are on track!</small>
                </div>
            `;
            if (slaMonitoringBadge) {
                slaMonitoringBadge.textContent = '0 Urgent';
                slaMonitoringBadge.className = 'badge bg-success';
            }
            return;
        }

        // Update badge
        const urgentCount = this.slaReminders.filter(r => r.reminder_type === 'urgent').length;
        if (slaMonitoringBadge) {
            slaMonitoringBadge.textContent = `${urgentCount} Urgent`;
            slaMonitoringBadge.className = urgentCount > 0 ? 'badge bg-danger' : 'badge bg-warning';
        }

        // Render SLA reminders
        slaMonitoringBody.innerHTML = this.slaReminders.map(reminder => {
            const statusClass = reminder.reminder_type === 'urgent' ? 'urgent' :
                reminder.reminder_type === 'warning' ? 'warning' : 'normal';
            const statusText = reminder.reminder_type === 'urgent' ? 'URGENT' :
                reminder.reminder_type === 'warning' ? 'WARNING' : 'NORMAL';
            const btnClass = reminder.reminder_type === 'urgent' ? 'btn-outline-danger' :
                reminder.reminder_type === 'warning' ? 'btn-outline-warning' : 'btn-outline-primary';

            // Add sent indicator
            const sentIndicator = reminder.notification_sent ?
                `<small class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Notification sent</small>` :
                `<small class="text-warning"><i class="bi bi-clock-fill"></i> Pending notification</small>`;

            return `
                <div class="sla-reminder-item ${statusClass}">
                    <div class="sla-icon">
                        <i class="bi ${reminder.reminder_type === 'urgent' ? 'bi-clock-fill' :
                    reminder.reminder_type === 'warning' ? 'bi-hourglass-split' : 'bi-clock'}"></i>
                    </div>
                    <div class="sla-content">
                        <h6>${reminder.job_name} - ${reminder.job_number}</h6>
                        <p>SLA Deadline: <strong>${reminder.time_remaining}</strong></p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="sla-status ${statusClass}">${statusText}</span>
                            ${sentIndicator}
                        </div>
                    </div>
                    <div class="sla-actions">
                        <button class="btn btn-sm ${btnClass}" onclick="viewJob(${reminder.id})">View Job</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    updatePaymentReminders() {
        const paymentRemindersBody = document.getElementById('paymentRemindersBody');
        const paymentRemindersBadge = document.getElementById('paymentRemindersBadge');

        if (!paymentRemindersBody) return;

        if (this.paymentReminders.length === 0) {
            paymentRemindersBody.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-1">No pending payment requests</p>
                    <small class="text-muted">All payments are up to date!</small>
                </div>
            `;
            if (paymentRemindersBadge) {
                paymentRemindersBadge.textContent = '0 Pending';
                paymentRemindersBadge.className = 'badge bg-success';
            }
            return;
        }

        // Update badge - count ALL payment reminders
        const totalCount = this.paymentReminders.length;
        const urgentCount = this.paymentReminders.filter(r => r.reminder_type === 'urgent' || r.reminder_type === 'overdue').length;
        if (paymentRemindersBadge) {
            paymentRemindersBadge.textContent = `${totalCount} Pending`;
            paymentRemindersBadge.className = urgentCount > 0 ? 'badge bg-danger' : 'badge bg-warning';
        }

        // Render payment reminders
        paymentRemindersBody.innerHTML = this.paymentReminders.map(reminder => {
            const statusClass = reminder.reminder_type === 'overdue' ? 'overdue' :
                reminder.reminder_type === 'urgent' ? 'urgent' :
                    reminder.reminder_type === 'warning' ? 'warning' :
                    reminder.reminder_type === 'pending' ? 'pending' : 'normal';
            const statusText = reminder.reminder_type === 'overdue' ? 'OVERDUE' :
                reminder.reminder_type === 'urgent' ? 'URGENT' :
                    reminder.reminder_type === 'warning' ? 'WARNING' :
                    reminder.reminder_type === 'pending' ? 'PENDING' : 'NORMAL';
            const btnClass = reminder.reminder_type === 'overdue' ? 'btn-outline-danger' :
                reminder.reminder_type === 'urgent' ? 'btn-outline-danger' :
                    reminder.reminder_type === 'warning' ? 'btn-outline-warning' :
                    reminder.reminder_type === 'pending' ? 'btn-outline-info' : 'btn-outline-primary';

            // Add sent indicator
            const sentIndicator = reminder.notification_sent ?
                `<small class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Notification sent</small>` :
                `<small class="text-warning"><i class="bi bi-clock-fill"></i> Pending notification</small>`;

            return `
                <div class="payment-reminder-item ${statusClass}">
                    <div class="d-flex align-items-center gap-1rem">
                        <div class="payment-icon">
                            <i class="bi ${reminder.reminder_type === 'overdue' ? 'bi-exclamation-triangle-fill' :
                        reminder.reminder_type === 'urgent' ? 'bi-credit-card-fill' :
                            reminder.reminder_type === 'warning' ? 'bi-hourglass-split' :
                            reminder.reminder_type === 'pending' ? 'bi-clock-fill' : 'bi-credit-card'}"></i>
                        </div>
                        <div class="payment-content">
                            <h6>${reminder.job_name} - ${reminder.job_number}</h6>
                            <p><strong>${reminder.vendor_name}</strong> • Requested by: ${reminder.user_name}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="payment-status ${statusClass}">${statusText}</span>
                                ${sentIndicator}
                            </div>
                        </div>
                    </div>
                    <div class="payment-actions">
                        <button class="btn btn-sm ${btnClass}" onclick="viewPaymentRequest(${reminder.id})">View Request</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    showError(message) {
        console.error('Dashboard Error:', message);
        // You can implement a toast notification here if needed
    }

    showNotification(message, type = 'info') {
        // You can implement a toast notification here if needed
        console.log(`Notification (${type}):`, message);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    window.adminDashboard = new AdminDashboard();
});

// Global functions for button clicks
function viewJob(jobId) {
    window.location.href = `view-job.php?id=${jobId}`;
}

function viewPaymentRequest(requestId) {
    // Redirect to requests page with payment request filter
    window.location.href = `requests.php?filter=payment&request_id=${requestId}`;
}

function enableNotifications() {
    if (window.adminDashboard) {
        window.adminDashboard.enableNotifications();
    }
}

function dismissNotificationBanner() {
    if (window.adminDashboard) {
        window.adminDashboard.dismissNotificationBanner();
    }
}

// View Invoice Generator
function viewInvoiceGenerator(jobId) {
    // Check if jobId is valid
    if (!jobId || jobId === 'undefined' || jobId === '') {
        console.warn('Invalid job ID provided:', jobId);
        // Redirect without job_id parameter
        window.location.href = 'invoices-generator.php';
        return;
    }
    
    // Redirect to invoice generator page with valid job ID
    window.location.href = `invoices-generator.php?job_id=${jobId}`;
}
