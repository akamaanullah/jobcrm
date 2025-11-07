// Job System Portal - JavaScript Functions
// Modern Interactive Admin Panel

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeSidebar();
    initializeTopbar();
    initializeDashboard();
    initializeNotifications();
    initializeResponsive();
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
}

// Notification Management
function initializeNotifications() {
    // Real-time notification updates (placeholder for WebSocket implementation)
    updateNotificationBadges();
    
    // Auto-refresh notifications every 10 seconds
    setInterval(updateNotificationBadges, 10000);
}

function updateNotificationBadges() {
    // Load notification badge count from server
    loadNotificationBadgeCount();
}

// Load notification badge count from API
async function loadNotificationBadgeCount() {
    try {
        const response = await fetch('assets/api/get_notifications.php?stats_only=true');
        const result = await response.json();
        
        if (result.success && result.stats) {
            const unreadCount = result.stats.unread_notifications || 0;
            updateNotificationBadge(unreadCount);
        }
    } catch (error) {
        console.error('Load Notification Badge Error:', error);
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
    
    // Check if elements exist before accessing their properties
    if (mainContent) {
        if (window.innerWidth <= 768) {
            mainContent.style.marginLeft = '0';
        } else {
            mainContent.style.marginLeft = 'var(--sidebar-width)';
        }
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
            <div class="panel-body">
                <div class="text-center py-3" id="notificationsLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading notifications...</p>
                </div>
                <div id="notificationsList"></div>
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
    
    // Add mark all read functionality
    panel.querySelector('.mark-all-read-btn').addEventListener('click', () => {
        markAllNotificationsRead();
    });
    
    // Load notifications when panel is created
    loadHeaderNotifications(panel);
    
    return panel;
}

function closePanel(panel) {
    panel.classList.remove('show');
    setTimeout(() => {
        document.body.removeChild(panel);
    }, 300);
}

// Load notifications for header panel
async function loadHeaderNotifications(panel) {
    try {
        const response = await fetch('assets/api/get_notifications.php?limit=10');
        const result = await response.json();
        
        if (result.success) {
            displayHeaderNotifications(panel, result.data);
        } else {
            showHeaderError(panel, result.message);
        }
    } catch (error) {
        console.error('Load Header Notifications Error:', error);
        showHeaderError(panel, 'Failed to load notifications');
    }
}

// Display notifications in header panel
function displayHeaderNotifications(panel, notifications) {
    const loadingElement = panel.querySelector('#notificationsLoading');
    const notificationsList = panel.querySelector('#notificationsList');
    
    // Hide loading
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    if (notifications.length === 0) {
        notificationsList.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No notifications yet</p>
            </div>
        `;
        return;
    }
    
    notificationsList.innerHTML = notifications.map(notification => {
        const icon = getNotificationIcon(notification.type);
        const statusClass = getNotificationStatusClass(notification.type, notification.action_required);
        const statusText = getNotificationStatusText(notification.type, notification.action_required);
        const timeAgo = getTimeAgo(notification.created_at);
        
        return `
            <div class="notification-item ${notification.is_read ? '' : 'unread'}">
                <div class="system-notification-icon ${statusClass}">
                    <i class="${icon}"></i>
                </div>
                <div class="notification-content">
                    <h6>${getNotificationTitle(notification.type)}</h6>
                    <p>${notification.message}</p>
                </div>
                <div class="notification-meta">
                    <span class="notification-time">${timeAgo}</span>
                </div>
            </div>
        `;
    }).join('');
}

// Show error in header panel
function showHeaderError(panel, message) {
    const loadingElement = panel.querySelector('#notificationsLoading');
    const notificationsList = panel.querySelector('#notificationsList');
    
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    notificationsList.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <h6 class="text-danger mt-2">Error</h6>
            <p class="text-muted">${message}</p>
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
            // Update notification badges using proper API call
            if (typeof updateUnreadNotificationCount === 'function') {
                await updateUnreadNotificationCount();
            }
            
            // Show success message
            showNotification('All notifications marked as read', 'success');
            
            // Reload notifications if panel is open
            const panel = document.querySelector('.notifications-panel');
            if (panel) {
                loadHeaderNotifications(panel);
            }
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Mark All Read Error:', error);
        showNotification('Failed to mark all notifications as read', 'error');
    }
}

// Update notification badge count
function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
    
    // Also update sidebar notification badge
    const sidebarBadge = document.getElementById('sidebarNotificationBadge');
    if (sidebarBadge) {
        sidebarBadge.textContent = count;
        sidebarBadge.style.display = count > 0 ? 'block' : 'none';
    }
}

// Get notification icon based on type
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
        default:
            return 'bi bi-bell';
    }
}

// Get notification status class
function getNotificationStatusClass(type, actionRequired) {
    if (actionRequired) {
        return 'status-pending';
    }
    
    switch (type) {
        case 'request_visit_accepted':
        case 'final_visit_request_accepted':
        case 'vendor_payment_accepted':
            return 'status-accepted';
        case 'visit_request_rejected':
        case 'final_visit_request_rejected':
        case 'vendor_payment_rejected':
            return 'status-rejected';
        case 'job_completed':
            return 'status-completed';
        default:
            return 'status-submitted';
    }
}

// Get notification status text
function getNotificationStatusText(type, actionRequired) {
    if (actionRequired) {
        return 'PENDING';
    }
    
    switch (type) {
        case 'request_visit_accepted':
        case 'final_visit_request_accepted':
        case 'vendor_payment_accepted':
            return 'ACCEPTED';
        case 'visit_request_rejected':
        case 'final_visit_request_rejected':
        case 'vendor_payment_rejected':
            return 'REJECTED';
        case 'job_completed':
            return 'COMPLETED';
        default:
            return 'SUBMITTED';
    }
}

// Get notification title
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
        default:
            return 'Notification';
    }
}

// Get time ago string
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

// Show notification message
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
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
    if (typeof updateUnreadNotificationCount === 'function') {
        updateUnreadNotificationCount();
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
