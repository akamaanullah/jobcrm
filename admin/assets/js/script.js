// Job System Portal - JavaScript Functions
// Modern Interactive Admin Panel

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeSidebar();
    initializeTopbar();
    initializeDashboard();
    initializeNotifications();
    initializeResponsive();
    
    // Load initial notification badges
    updateNotificationBadges();
});

// Sidebar Management
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');
    
    // Mobile sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Active navigation highlighting
    const navLinks = document.querySelectorAll('[id^="navLink"]');
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    
    // Remove active class from all nav items first
    document.querySelectorAll('[id^="nav"]').forEach(item => {
        item.classList.remove('active');
    });
    
    // Set active class based on current page
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.parentElement.classList.add('active');
        }
        
        // Add click animation
        link.addEventListener('click', function(e) {
            // Remove active class from all nav items
            document.querySelectorAll('[id^="nav"]').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            this.parentElement.classList.add('active');
            
            // Add loading state
            this.style.opacity = '0.6';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 300);
        });
    });
}

// Topbar Management
function initializeTopbar() {
    const userProfile = document.querySelector('.user-profile');
    const logoutBtns = document.querySelectorAll('.logout-btn');
    
    // Bootstrap dropdown will handle the functionality automatically
    
    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLogoutConfirmation();
        });
    }
    
    // Notification and message icons
    const notificationIcon = document.getElementById('notificationIcon');
    const messageIcon = document.getElementById('messageIcon');
    
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function() {
            // Open notifications panel
            openNotificationsPanel();
        });
    }
    
    if (messageIcon) {
        messageIcon.addEventListener('click', function() {
            // Open messages panel
            openMessagesPanel();
        });
    }
}

// Dashboard Management
function initializeDashboard() {
    // Animate metric cards on load
    const metricCards = document.querySelectorAll('[id^="metricCard"]');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects to metric cards
    metricCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Job item interactions
    const jobViewIcons = document.querySelectorAll('[id^="viewJob"]');
    jobViewIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            // Get the job item from the icon
            const jobItem = this.closest('[id^="jobItem"]');
            if (jobItem) {
                openJobDetails(jobItem);
            }
        });
    });
    
    // Notification item interactions
    const notificationItems = document.querySelectorAll('[id^="notificationItem"]');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Mark as read and open details
            markNotificationAsRead(item);
        });
    });
    
    // View All buttons
    const viewAllJobsBtn = document.getElementById('viewAllJobsBtn');
    const viewAllNotificationsBtn = document.getElementById('viewAllNotificationsBtn');
    
    if (viewAllJobsBtn) {
        viewAllJobsBtn.addEventListener('click', function() {
            // Navigate to jobs page or open jobs modal
        });
    }
    
    if (viewAllNotificationsBtn) {
        viewAllNotificationsBtn.addEventListener('click', function() {
            // Navigate to notifications page or open notifications modal
        });
    }
    
    // Message icon click event
    const messageIcon = document.getElementById('messageIcon');
    if (messageIcon) {
        messageIcon.addEventListener('click', function() {
            // Navigate to jobs page to access chat functionality
            window.location.href = 'jobs.php';
        });
    }
}

// Notification Management
function initializeNotifications() {
    // Real-time notification updates (placeholder for WebSocket implementation)
    updateNotificationBadges();
    updateMessageBadges();
    
    // Auto-refresh notifications every 10 seconds
    setInterval(updateNotificationBadges, 3000);
    
    // Auto-refresh message badges every 10 seconds
    setInterval(updateMessageBadges, 3000);
}

// Update notification badges with real data
async function updateNotificationBadges() {
    try {
        const response = await fetch('assets/api/get_notifications.php?stats_only=true');
        const result = await response.json();

        if (result.success && result.stats) {
            const notificationBadge = document.getElementById('notificationBadge');
            const sidebarNotificationBadge = document.getElementById('sidebarNotificationBadge');
            
            // Update header notification badge
            if (notificationBadge) {
                const unreadCount = result.stats.unread_notifications || 0;
                if (unreadCount > 0) {
                    notificationBadge.textContent = unreadCount;
                    notificationBadge.style.display = 'inline-block';
                } else {
                    notificationBadge.style.display = 'none';
                }
            }
            
            // Update sidebar notification badge
            if (sidebarNotificationBadge) {
                const unreadCount = result.stats.unread_notifications || 0;
                if (unreadCount > 0) {
                    sidebarNotificationBadge.textContent = unreadCount;
                    sidebarNotificationBadge.style.display = 'inline-block';
                } else {
                    sidebarNotificationBadge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Update Notification Badges Error:', error);
    }
}

// Update message badges with real data
async function updateMessageBadges() {
    try {
        const response = await fetch('assets/api/get_total_unread_messages.php');
        const result = await response.json();

        if (result.success && result.data) {
            const messageBadge = document.getElementById('messageBadge');
            
            // Update header message badge
            if (messageBadge) {
                const unreadCount = result.data.total_unread_messages || 0;
                if (unreadCount > 0) {
                    messageBadge.textContent = unreadCount;
                    messageBadge.style.display = 'inline-block';
                } else {
                    messageBadge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating message badges:', error);
    }
}

// Responsive Management
function initializeResponsive() {
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            mainContent.style.marginLeft = 'var(--sidebar-width)';
        } else {
            mainContent.style.marginLeft = '0';
        }
    });
    
    // Initial responsive setup
    handleResponsiveLayout();
}

function handleResponsiveLayout() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        mainContent.style.marginLeft = '0';
    } else {
        mainContent.style.marginLeft = 'var(--sidebar-width)';
    }
}

// Utility Functions
function showLogoutConfirmation() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear localStorage data
        localStorage.removeItem('userData');
        localStorage.removeItem('isLoggedIn');
        
        // Redirect to logout API
        window.location.href = '../assets/api/logout.php';
    }
}

function openNotificationsPanel() {
    // Create and show notifications panel
    const panel = createNotificationsPanel();
    document.body.appendChild(panel);
    
    // Animate in
    setTimeout(() => {
        panel.classList.add('show');
    }, 10);
}

function createNotificationsPanel() {
    const panel = document.createElement('div');
    panel.className = 'notifications-panel';
    panel.innerHTML = `
        <div class="panel-overlay"></div>
        <div class="panel-content">
            <div class="panel-header">
                <h4>Notifications</h4>
                <div class="panel-actions">
                    <button class="btn btn-sm btn-outline-primary mark-all-read-btn">Mark All Read</button>
                    <button class="close-panel">&times;</button>
                </div>
            </div>
            <div class="panel-body" id="notificationsList">
                <div class="text-center py-4" id="notificationsLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading notifications...</p>
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    panel.querySelector('.close-panel').addEventListener('click', () => {
        closePanel(panel);
    });
    
    panel.querySelector('.panel-overlay').addEventListener('click', () => {
        closePanel(panel);
    });
    
    // Mark all read button
    panel.querySelector('.mark-all-read-btn').addEventListener('click', () => {
        markAllNotificationsRead();
    });
    
    // Load notifications when panel is created
    loadHeaderNotifications();
    
    // Update badges when panel is opened
    updateNotificationBadges();
    
    return panel;
}

function closePanel(panel) {
    panel.classList.remove('show');
    setTimeout(() => {
        document.body.removeChild(panel);
    }, 300);
}

// Load header notifications
async function loadHeaderNotifications() {
    try {
        const response = await fetch('assets/api/get_notifications.php?limit=10');
        const result = await response.json();

        if (result.success) {
            displayHeaderNotifications(result.data);
        } else {
            showHeaderNotificationError(result.message);
        }
    } catch (error) {
        console.error('Load Header Notifications Error:', error);
        showHeaderNotificationError('Failed to load notifications');
    }
}

function displayHeaderNotifications(notifications) {
    const notificationsList = document.getElementById('notificationsList');
    const loadingElement = document.getElementById('notificationsLoading');
    
    if (loadingElement) {
        loadingElement.remove();
    }
    
    if (!notifications || notifications.length === 0) {
        notificationsList.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <h6 class="text-muted mt-3">No Notifications</h6>
                <p class="text-muted">You're all caught up!</p>
            </div>
        `;
        return;
    }
    
    const notificationsHTML = notifications.map(notification => createHeaderNotificationItem(notification)).join('');
    notificationsList.innerHTML = notificationsHTML;
}

function createHeaderNotificationItem(notification) {
    const iconClass = getNotificationIcon(notification.type);
    const isRead = notification.is_read ? '' : 'unread';
    const clickAction = getNotificationClickAction(notification);
    
    return `
        <div class="notification-item ${isRead} clickable-notification" data-notification-id="${notification.id}" ${clickAction} title="Click to view details">
            <div class="system-notification-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content">
                <h6>${getNotificationTitle(notification.type)}</h6>
                <p>${notification.message}</p>
            </div>
            <span class="notification-time">${notification.time_ago}</span>
        </div>
    `;
}

function getNotificationClickAction(notification) {
    // Determine where to redirect based on notification type
    if (notification.job_id) {
        // If has job_id, redirect to view-jobs page
        return `onclick="window.location.href='view-job.php?id=${notification.job_id}'"`;
    } else if (notification.type === 'visit_request' || notification.type === 'final_visit_request' || notification.type === 'request_vendor_payment') {
        // For action-required notifications, go to requests page with filter
        const filterMap = {
            'visit_request': 'visit',
            'final_visit_request': 'approval',
            'request_vendor_payment': 'payment'
        };
        const filter = filterMap[notification.type] || 'all';
        return `onclick="window.location.href='requests.php?filter=${filter}'"`;
    } else {
        // Default: go to requests page
        return `onclick="window.location.href='requests.php'"`;
    }
}

function getNotificationIcon(type) {
    const iconMap = {
        'visit_request': 'bi bi-eye',
        'final_visit_request': 'bi bi-calendar-check',
        'job_completed': 'bi bi-check-circle',
        'request_vendor_payment': 'bi bi-credit-card',
        'vendor_added': 'bi bi-person-plus',
        'request_visit_accepted': 'bi bi-check-circle-fill',
        'final_visit_request_accepted': 'bi bi-check-circle-fill',
        'vendor_payment_accepted': 'bi bi-check-circle-fill'
    };
    return iconMap[type] || 'bi bi-bell';
}

function getNotificationTitle(type) {
    const titleMap = {
        'visit_request': 'Visit Request',
        'final_visit_request': 'Final Visit Request',
        'job_completed': 'Job Completed',
        'request_vendor_payment': 'Payment Request',
        'vendor_added': 'Vendor Added',
        'request_visit_accepted': 'Visit Accepted',
        'final_visit_request_accepted': 'Final Visit Accepted',
        'vendor_payment_accepted': 'Payment Accepted'
    };
    return titleMap[type] || 'Notification';
}

function showHeaderNotificationError(message) {
    const notificationsList = document.getElementById('notificationsList');
    const loadingElement = document.getElementById('notificationsLoading');
    
    if (loadingElement) {
        loadingElement.remove();
    }
    
    notificationsList.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <h6 class="text-danger mt-3">Error</h6>
            <p class="text-muted">${message}</p>
            <button class="btn btn-primary btn-sm" onclick="loadHeaderNotifications()">
                <i class="bi bi-arrow-clockwise"></i> Retry
            </button>
        </div>
    `;
}

// Mark all notifications as read
async function markAllNotificationsRead() {
    try {
        const response = await fetch('assets/api/mark_all_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update UI to show all notifications as read
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.classList.remove('unread');
            });
            
            // Update notification badges
            updateNotificationBadges();
            
            // Show success message
            showNotification('All notifications marked as read', 'success');
        } else {
            showNotification(result.message || 'Failed to mark notifications as read', 'error');
        }
    } catch (error) {
        console.error('Mark All Read Error:', error);
        showNotification('Error marking notifications as read', 'error');
    }
}

function openMessagesPanel() {
    // Similar to notifications panel but for messages
}

function openJobDetails(jobItem) {
    // Create and show job details modal
    const modal = createJobDetailsModal(jobItem);
    document.body.appendChild(modal);
    
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

function createJobDetailsModal(jobItem) {
    const modal = document.createElement('div');
    modal.className = 'job-details-modal';
    modal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h4>Job Details</h4>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="job-detail-item">
                    <label>Job Title:</label>
                    <span>${jobItem.querySelector('h5').textContent}</span>
                </div>
                <div class="job-detail-item">
                    <label>Status:</label>
                    <span class="badge bg-warning">PAYMENT REQUESTED</span>
                </div>
                <div class="job-detail-item">
                    <label>Client:</label>
                    <span>admin</span>
                </div>
                <div class="job-detail-item">
                    <label>Vendors Assigned:</label>
                    <span>1 vendor assigned</span>
                </div>
                <div class="job-detail-item">
                    <label>Created:</label>
                    <span>16 hours ago</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">View Full Details</button>
                <button class="btn btn-secondary close-modal">Close</button>
            </div>
        </div>
    `;
    
    // Add event listeners
    modal.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(modal);
        });
    });
    
    modal.querySelector('.modal-overlay').addEventListener('click', () => {
        closeModal(modal);
    });
    
    return modal;
}

function closeModal(modal) {
    modal.classList.remove('show');
    setTimeout(() => {
        document.body.removeChild(modal);
    }, 300);
}

function markNotificationAsRead(notificationItem) {
    // Mark notification as read
    notificationItem.style.opacity = '0.6';
    notificationItem.style.backgroundColor = 'var(--bg-tertiary)';
    
    // Update badge count using the proper API call
    if (typeof updateNotificationBadges === 'function') {
        updateNotificationBadges();
    }
}

// CSS styles moved to assets/css/style.css for better organization

// Export functions for global access
window.JobSystemPortal = {
    showLogoutConfirmation,
    openNotificationsPanel,
    openMessagesPanel,
    openJobDetails,
    markNotificationAsRead
};
