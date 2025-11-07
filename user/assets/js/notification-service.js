// Push Notification Service for User Side
if (typeof window.NotificationService === 'undefined') {
class NotificationService {
    constructor() {
        this.permission = null;
        this.previousSlaReminders = [];
        this.previousUnreadCount = 0;
        this.previousNotificationCount = 0;
        this.isInitialized = false;
        
        this.init();
    }

    async     init() {
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            return;
        }

        // Check current permission status
        this.permission = Notification.permission;
        
        // If permission is not granted, show permission request
        if (this.permission === 'default') {
            await this.requestPermission();
        }

        this.isInitialized = true;
        
        // Start global notification monitoring
        this.startGlobalMonitoring();
    }

    // Start global monitoring for notifications across all pages
    startGlobalMonitoring() {
        if (this.permission !== 'granted') return;

        // Check for SLA reminders every 2 minutes
        this.slaCheckInterval = setInterval(() => {
            this.checkGlobalSlaReminders();
        }, 120000); // 2 minutes

        // Check for messages every 10 seconds
        this.messageCheckInterval = setInterval(() => {
            this.checkGlobalMessages();
        }, 10000); // 10 seconds

        // Check for notifications every 10 seconds
        this.notificationCheckInterval = setInterval(() => {
            this.checkGlobalNotifications();
        }, 10000); // 10 seconds

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            // Handle page visibility changes
        });
    }

    // Stop global monitoring (call when leaving page)
    stopGlobalMonitoring() {
        if (this.slaCheckInterval) {
            clearInterval(this.slaCheckInterval);
            this.slaCheckInterval = null;
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
            
            if (this.permission === 'granted') {
                this.showNotification('Notifications Enabled', 'You will now receive important updates about your jobs and SLA reminders.', 'success');
                this.saveNotificationPreference(true);
            } else {
                this.saveNotificationPreference(false);
            }
            
            return this.permission;
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return 'denied';
        }
    }

    showNotification(title, body, type = 'info', options = {}) {
        if (this.permission !== 'granted') {
            return null;
        }

        // Default notification options
        const defaultOptions = {
            body: body,
            icon: this.getNotificationIcon(type),
            badge: '/assets/images/icons/logo.png',
            tag: 'job-portal-notification',
            requireInteraction: false,
            silent: false,
            timestamp: Date.now()
        };

        // Merge with custom options
        const notificationOptions = { ...defaultOptions, ...options };

        try {
            const notification = new Notification(title, notificationOptions);
            
            // Auto close after 5 seconds unless requireInteraction is true
            if (!notificationOptions.requireInteraction) {
                setTimeout(() => {
                    notification.close();
                }, 5000);
            }

            // Handle click on notification
            notification.onclick = () => {
                window.focus();
                notification.close();
                
                // Navigate based on notification type
                this.handleNotificationClick(type, options);
            };

            return notification;
        } catch (error) {
            console.error('Error showing notification:', error);
            return null;
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'success': '/assets/images/icons/logo.png',
            'warning': '/assets/images/icons/logo.png',
            'error': '/assets/images/icons/logo.png',
            'info': '/assets/images/icons/logo.png',
            'sla': '/assets/images/icons/logo.png',
            'message': '/assets/images/icons/logo.png'
        };
        
        // Fallback to a default icon or data URI
        return icons[type] || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiMzQjgyRjYiLz4KPHN2Zz4K';
    }

    handleNotificationClick(type, options) {
        switch (type) {
            case 'sla':
            case 'sla_urgent':
                if (options.jobId) {
                    window.location.href = `my-jobs.php?id=${options.jobId}`;
                } else {
                    window.location.href = 'my-jobs.php';
                }
                break;
            case 'message':
                window.location.href = 'my-jobs.php';
                break;
            case 'notification':
                window.location.href = 'notifications.php';
                break;
            default:
                window.location.href = 'Dashboard.php';
                break;
        }
    }

    // SLA Reminder Notifications
    checkSlaReminders(slaReminders) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        const currentUrgent = slaReminders.filter(r => r.reminder_type === 'urgent');
        const previousUrgent = this.previousSlaReminders.filter(r => r.reminder_type === 'urgent');

        // Check for new urgent SLA reminders
        const newUrgentReminders = currentUrgent.filter(current => 
            !previousUrgent.some(previous => previous.id === current.id)
        );

        // Show notifications for new urgent reminders
        newUrgentReminders.forEach(reminder => {
            this.showNotification(
                '🚨 URGENT: SLA Deadline Approaching!',
                `${reminder.job_name} (${reminder.job_number}) - ${reminder.time_remaining}`,
                'sla_urgent',
                {
                    requireInteraction: true,
                    jobId: reminder.id
                }
            );
        });

        // Update previous reminders
        this.previousSlaReminders = [...slaReminders];
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

    // Notification Count Updates
    checkNotifications(unreadNotificationCount) {
        if (!this.isInitialized || this.permission !== 'granted') return;

        // Show notification when unread count increases (including first notification)
        if (unreadNotificationCount > this.previousNotificationCount) {
            const newNotifications = unreadNotificationCount - this.previousNotificationCount;
            
            // Only show notification if there are actually new notifications
            if (newNotifications > 0) {
                this.showNotification(
                    '🔔 New Notification',
                    `You have ${newNotifications} new notification${newNotifications > 1 ? 's' : ''}.`,
                    'info',
                    {
                        requireInteraction: false,
                        tag: 'new-notification'
                    }
                );
            }
        }

        this.previousNotificationCount = unreadNotificationCount;
    }

    // Save notification preference to localStorage
    saveNotificationPreference(enabled) {
        try {
            localStorage.setItem('notifications_enabled', enabled.toString());
        } catch (error) {
            console.error('Error saving notification preference:', error);
        }
    }

    // Get notification preference from localStorage
    getNotificationPreference() {
        try {
            const preference = localStorage.getItem('notifications_enabled');
            return preference === 'true';
        } catch (error) {
            console.error('Error getting notification preference:', error);
            return false;
        }
    }

    // Test notification
    showTestNotification() {
        this.showNotification(
            'Test Notification',
            'This is a test notification to verify that push notifications are working correctly.',
            'info',
            {
                requireInteraction: true
            }
        );
    }

    // Clear all notifications
    clearAllNotifications() {
        // This will close all notifications with the same tag
        // Note: This is limited by browser implementation
    }

    // Check if notifications are supported and enabled
    isSupported() {
        return 'Notification' in window;
    }

    isEnabled() {
        return this.permission === 'granted';
    }

    // Global SLA reminders check
    async checkGlobalSlaReminders() {
        try {
            const response = await fetch('assets/api/get_dashboard_data.php');
            const result = await response.json();
            
            if (result.success && result.data.sla_reminders_details) {
                this.checkSlaReminders(result.data.sla_reminders_details);
            }
        } catch (error) {
            console.error('Error checking global SLA reminders:', error);
        }
    }

    // Global messages check
    async checkGlobalMessages() {
        try {
            const response = await fetch('assets/api/get_unread_messages_count.php');
            const result = await response.json();
            
            if (result.success) {
                const unreadCount = result.data.unread_count || 0;
                this.checkUnreadMessages(unreadCount);
            }
        } catch (error) {
            console.error('Error checking global messages:', error);
        }
    }

    // Global notifications check
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

    // Initialize notification service globally
    window.NotificationService = NotificationService;
    window.notificationService = new NotificationService();

    // Export for module usage
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = NotificationService;
    }
}
