    // Push Notification Service for Admin Side
if (typeof window.NotificationService === 'undefined') {
class NotificationService {
    constructor() {
        this.permission = null;
        this.previousUnreadCount = 0;
        this.previousNotificationCount = 0;
        this.messageCheckInterval = null;
        this.notificationCheckInterval = null;
        this.isInitialized = false;
        
        this.init();
    }

    async init() {
        if (!('Notification' in window)) {
            return;
        }
        this.permission = Notification.permission;
        if (this.permission === 'default') {
            await this.requestPermission();
        }
        this.isInitialized = true;
        this.startGlobalMonitoring();
    }

    startGlobalMonitoring() {
        if (this.permission !== 'granted') return;

        this.slaCheckInterval = setInterval(() => {
            this.checkGlobalSlaReminders();
        }, 1800000); // 30 minutes

        this.paymentReminderInterval = setInterval(() => {
            this.checkGlobalPaymentReminders();
        }, 1800000); // 30 minutes

        this.invoiceReminderInterval = setInterval(() => {
            this.checkGlobalInvoiceReminders();
        }, 1800000); // 30 minutes

        this.messageCheckInterval = setInterval(() => {
            this.checkGlobalMessages();
        }, 3000); // 10 seconds

        this.notificationCheckInterval = setInterval(() => {
            this.checkGlobalNotifications();
        }, 3000); // 10 seconds

        document.addEventListener('visibilitychange', () => {
            // Handle page visibility changes
        });
    }

    stopGlobalMonitoring() {
        if (this.slaCheckInterval) {
            clearInterval(this.slaCheckInterval);
            this.slaCheckInterval = null;
        }
        if (this.paymentReminderInterval) {
            clearInterval(this.paymentReminderInterval);
            this.paymentReminderInterval = null;
        }
        if (this.invoiceReminderInterval) {
            clearInterval(this.invoiceReminderInterval);
            this.invoiceReminderInterval = null;
        }
        if (this.messageCheckInterval) {
            clearInterval(this.messageCheckInterval);
            this.messageCheckInterval = null;
        }
        if (this.notificationCheckInterval) {
            clearInterval(this.notificationCheckInterval);
            this.notificationCheckInterval = null;
        }
    }

    async requestPermission() {
        try {
            this.permission = await Notification.requestPermission();
            return this.permission;
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return 'denied';
        }
    }

    showNotification(title, body, type = 'info', options = {}) {
        if (this.permission !== 'granted') {
            return;
        }

        const defaultOptions = {
            body: body,
            icon: '../assets/images/logo.png', // Default icon
            badge: '../assets/images/logo.png', // Default badge
            vibrate: [200, 100, 200],
            tag: type, // Group notifications by type
            renotify: true, // Show notification again if tag is same
            requireInteraction: false // Auto-dismiss by default
        };

        // Custom icons based on type
        switch (type) {
            case 'sla_urgent':
                defaultOptions.icon = 'assets/images/icons/logo.png';
                defaultOptions.requireInteraction = true; // Urgent notifications require interaction
                break;
            case 'payment':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                defaultOptions.requireInteraction = true;
                break;
            case 'invoice':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                defaultOptions.requireInteraction = true;
                break;
            case 'message':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                break;
            case 'notification':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                break;
            case 'success':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                break;
            case 'error':
                defaultOptions.icon = '../assets/images/icons/logo.png';
                break;
        }

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const notification = new Notification(title, finalOptions);

            notification.onclick = (event) => {
                event.preventDefault();
                notification.close();
                if (options.jobId) {
                    window.focus();
                    window.location.href = `view-job.php?id=${options.jobId}`;
                } else if (type === 'message') {
                    window.focus();
                    window.location.href = 'jobs.php'; // Redirect to jobs page for messages
                } else if (type === 'notification') {
                    window.focus();
                    window.location.href = 'requests.php'; // Redirect to requests page
                }
            };

            // Auto-close non-persistent notifications after 5 seconds
            if (!finalOptions.requireInteraction) {
                setTimeout(() => notification.close(), 5000);
            }

        } catch (error) {
            console.error('Error showing notification:', error);
        }
    }

    // SLA Reminder Notifications - One Time Only
    async checkSlaReminders(slaReminders) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        try {
            // First, check and create new reminders in database
            const createResponse = await fetch('assets/api/sla_reminder_manager.php?action=check_and_create');
            const createResult = await createResponse.json();
            
            if (createResult.success && createResult.reminders.length > 0) {
                // Get pending reminders that haven't been sent yet
                const pendingResponse = await fetch('assets/api/sla_reminder_manager.php?action=get_pending');
                const pendingResult = await pendingResponse.json();
                
                if (pendingResult.success) {
                    // Show notifications only for new pending reminders
                    pendingResult.reminders.forEach(reminder => {
                        if (reminder.reminder_type === 'urgent') {
            this.showNotification(
                '🚨 URGENT: SLA Deadline Approaching!',
                `${reminder.job_name} (${reminder.job_number}) - ${reminder.time_remaining}`,
                'sla_urgent',
                {
                    requireInteraction: true,
                                    jobId: reminder.job_id,
                                    reminderType: reminder.reminder_type
                                }
                            );
                            
                            // Mark as sent immediately
                            this.markReminderAsSent(reminder.job_id, reminder.reminder_type);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error checking SLA reminders:', error);
        }
    }

    // Mark reminder as sent in database
    async markReminderAsSent(jobId, reminderType) {
        try {
            const formData = new FormData();
            formData.append('job_id', jobId);
            formData.append('reminder_type', reminderType);
            
            const response = await fetch('assets/api/sla_reminder_manager.php?action=mark_sent', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                // SLA reminder marked as sent
            }
        } catch (error) {
            console.error('Error marking reminder as sent:', error);
        }
    }

    // Invoice Reminder Notifications - One Time Only
    async checkInvoiceReminders(invoiceReminders) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        try {
            // First, check and create new invoice reminders in database
            const createResponse = await fetch('assets/api/invoice_reminder_manager.php?action=check_and_create');
            const createResult = await createResponse.json();
            
            if (createResult.success && createResult.reminders.length > 0) {
                // Get pending reminders that haven't been sent yet
                const pendingResponse = await fetch('assets/api/invoice_reminder_manager.php?action=get_pending');
                const pendingResult = await pendingResponse.json();
                
                if (pendingResult.success) {
                    const unsentReminders = pendingResult.invoice_reminders.filter(reminder => !reminder.notification_sent);
                    
                    for (const reminder of unsentReminders) {
                        // Show notification
                        this.showNotification(
                            `Invoice Reminder: ${reminder.job_name}`,
                            `Generate invoice for ${reminder.vendor_name} - Payment accepted ${reminder.time_since_acceptance}`,
                            'invoice'
                        );
                        
                        // Mark as sent
                        await fetch(`assets/api/invoice_reminder_manager.php?action=mark_sent&reminder_id=${reminder.id}`);
                    }
                }
            }
        } catch (error) {
            console.error('Error checking invoice reminders:', error);
        }
    }

    // Payment Reminder Notifications - One Time Only
    async checkPaymentReminders(paymentReminders) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        try {
            // First, check and create new payment reminders in database
            const createResponse = await fetch('assets/api/payment_reminder_manager.php?action=check_and_create');
            const createResult = await createResponse.json();
            
            if (createResult.success && createResult.reminders.length > 0) {
                // Get pending reminders that haven't been sent yet
                const pendingResponse = await fetch('assets/api/payment_reminder_manager.php?action=get_pending');
                const pendingResult = await pendingResponse.json();
                
                if (pendingResult.success) {
                    // Show notifications for all reminder types including 'pending'
                    pendingResult.reminders.forEach(reminder => {
                        let title, icon, requireInteraction = false;
                        
                        switch (reminder.reminder_type) {
                            case 'overdue':
                                title = '🚨 OVERDUE: Payment Request!';
                                icon = '🚨';
                                requireInteraction = true;
                                break;
                            case 'urgent':
                                title = '⚠️ URGENT: Payment Request Reminder!';
                                icon = '⚠️';
                                requireInteraction = true;
                                break;
                            case 'warning':
                                title = '⏰ Payment Request Warning!';
                                icon = '⏰';
                                requireInteraction = false;
                                break;
                            case 'pending':
                                title = '💰 New Payment Request!';
                                icon = '💰';
                                requireInteraction = false;
                                break;
                            default:
                                title = '💰 Payment Request Update!';
                                icon = '💰';
                                requireInteraction = false;
                        }
            
            this.showNotification(
                title,
                            `${reminder.job_name} (${reminder.job_number}) - Requested by ${reminder.user_name}`,
                'payment_reminder',
                {
                                requireInteraction: requireInteraction,
                    jobId: reminder.job_id,
                                requestId: reminder.request_payment_id,
                                reminderType: reminder.reminder_type,
                    icon: icon
                }
            );
                        
                        // Mark as sent immediately
                        this.markPaymentReminderAsSent(reminder.request_payment_id, reminder.reminder_type);
                    });
                }
            }
        } catch (error) {
            console.error('Error checking payment reminders:', error);
        }
    }

    // Mark payment reminder as sent in database
    async markPaymentReminderAsSent(requestId, reminderType) {
        try {
            const formData = new FormData();
            formData.append('request_id', requestId);
            formData.append('reminder_type', reminderType);
            
            const response = await fetch('assets/api/payment_reminder_manager.php?action=mark_sent', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                // Payment reminder marked as sent
            }
        } catch (error) {
            console.error('Error marking payment reminder as sent:', error);
        }
    }

    // Message Notifications
    checkUnreadMessages(unreadCount) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        // Show notification when unread count increases (including first message)
        if (unreadCount > this.previousUnreadCount) {
            const newMessages = unreadCount - this.previousUnreadCount;
            
            // Only show notification if there are actually new messages
            if (newMessages > 0) {
                this.showNotification(
                    '💬 New Message Received',
                    `You have ${newMessages} new message${newMessages > 1 ? 's' : ''} from vendors.`,
                    'message',
                    {
                        requireInteraction: false,
                        tag: 'new-message'
                    }
                );
            }
        }

        this.previousUnreadCount = unreadCount;
    }

    // Notification Alerts - One Time Only
    async checkNotifications(unreadNotificationCount) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        try {
            // First, check and create new notification alerts
            const createResponse = await fetch('assets/api/notification_alert_manager.php?action=check_and_create');
            const createResult = await createResponse.json();
            
            if (createResult.success && createResult.alerts.length > 0) {
                // Get pending alerts that haven't been sent yet
                const pendingResponse = await fetch('assets/api/notification_alert_manager.php?action=get_pending');
                const pendingResult = await pendingResponse.json();
                
                if (pendingResult.success) {
                    // Show notifications only for new pending alerts
                    pendingResult.alerts.forEach(alert => {
                        const title = this.getNotificationTitle(alert.type);
                        const icon = this.getNotificationIcon(alert.type);
                        const isUrgent = this.isUrgentNotification(alert.type);
                        
                this.showNotification(
                            title,
                            alert.message,
                    'notification',
                    {
                                requireInteraction: isUrgent,
                                notificationId: alert.id,
                                jobId: alert.job_id,
                                vendorId: alert.vendor_id,
                                tag: `notification-${alert.id}` // Unique tag per notification
                            }
                        );
                        
                        // Mark as sent immediately
                        this.markNotificationAlertAsSent(alert.id);
                    });
                }
            }
        } catch (error) {
            console.error('Error checking notification alerts:', error);
        }
    }

    // Mark notification alert as sent in database
    async markNotificationAlertAsSent(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            const response = await fetch('assets/api/notification_alert_manager.php?action=mark_sent', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                // Notification alert marked as sent
            }
        } catch (error) {
            console.error('Error marking notification alert as sent:', error);
        }
    }

    // Get notification title based on type
    getNotificationTitle(type) {
        const titles = {
            'visit_request': '🔍 Visit Request',
            'final_visit_request': '✅ Final Visit Request',
            'request_vendor_payment': '💰 Payment Request',
            'requested_vendor_payment': '💳 Payment Requested',
            'job_completed': '✅ Job Completed',
            'new_job_added': '📋 New Job Added',
            'request_visit_accepted': '✅ Visit Accepted',
            'visit_request_rejected': '❌ Visit Rejected',
            'final_visit_request_accepted': '✅ Final Visit Accepted',
            'final_visit_request_rejected': '❌ Final Visit Rejected',
            'vendor_payment_accepted': '✅ Payment Accepted',
            'vendor_payment_rejected': '❌ Payment Rejected',
            'sla_reminder': '⏰ SLA Reminder'
        };
        return titles[type] || '🔔 New Notification';
    }

    // Get notification icon based on type
    getNotificationIcon(type) {
        const icons = {
            'visit_request': '🔍',
            'final_visit_request': '✅',
            'request_vendor_payment': '💰',
            'requested_vendor_payment': '💳',
            'job_completed': '✅',
            'new_job_added': '📋',
            'request_visit_accepted': '✅',
            'visit_request_rejected': '❌',
            'final_visit_request_accepted': '✅',
            'final_visit_request_rejected': '❌',
            'vendor_payment_accepted': '✅',
            'vendor_payment_rejected': '❌',
            'sla_reminder': '⏰'
        };
        return icons[type] || '🔔';
    }

    // Check if notification type is urgent
    isUrgentNotification(type) {
        const urgentTypes = ['visit_request', 'final_visit_request', 'request_vendor_payment'];
        return urgentTypes.includes(type);
    }

    // Save notification preference to localStorage
    saveNotificationPreference(enabled) {
        try {
            localStorage.setItem('admin_notifications_enabled', enabled.toString());
            return true;
        } catch (error) {
            console.error('Error saving notification preference:', error);
            return false;
        }
    }

    // Load notification preference from localStorage
    loadNotificationPreference() {
        try {
            const enabled = localStorage.getItem('admin_notifications_enabled');
            return enabled === 'true';
        } catch (error) {
            console.error('Error loading notification preference:', error);
            return false;
        }
    }

    isSupported() {
        return 'Notification' in window;
    }

    isEnabled() {
        return this.permission === 'granted';
    }

    // Global monitoring functions
    async checkGlobalSlaReminders() {
        try {
            const response = await fetch('assets/api/get_dashboard_stats.php');
            const result = await response.json();
            
            if (result.success && result.data.sla_reminders_details) {
                this.checkSlaReminders(result.data.sla_reminders_details);
            }
        } catch (error) {
            console.error('Error checking global SLA reminders:', error);
        }
    }

    async checkGlobalPaymentReminders() {
        try {
            const response = await fetch('assets/api/get_payment_reminders.php');
            const result = await response.json();
            
            if (result.success && result.data.payment_reminders) {
                this.checkPaymentReminders(result.data.payment_reminders);
            }
        } catch (error) {
            console.error('Error checking global payment reminders:', error);
        }
    }

    async checkGlobalInvoiceReminders() {
        try {
            const response = await fetch('assets/api/get_dashboard_stats.php');
            const result = await response.json();
            
            if (result.success && result.data.invoice_reminders_details) {
                this.checkInvoiceReminders(result.data.invoice_reminders_details);
            }
        } catch (error) {
            console.error('Error checking global invoice reminders:', error);
        }
    }

    async checkGlobalMessages() {
        try {
            const response = await fetch('assets/api/get_total_unread_messages.php');
            const result = await response.json();
            
            if (result.success) {
                const unreadCount = result.data.total_unread_messages || 0;
                this.checkUnreadMessages(unreadCount);
            }
        } catch (error) {
            console.error('Error checking global messages:', error);
        }
    }

    async checkGlobalNotifications() {
        try {
            const response = await fetch('assets/api/get_notifications.php?stats_only=true');
            const result = await response.json();
            
            if (result.success) {
                const unreadCount = result.stats.unread_notifications || 0;
                this.checkNotifications(unreadCount);
            }
        } catch (error) {
            console.error('Error checking global notifications:', error);
        }
    }
}

window.NotificationService = NotificationService;
window.adminNotificationService = new NotificationService();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationService;
}
}
