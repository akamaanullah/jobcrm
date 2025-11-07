// User My Jobs Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize jobs functionality
    initializeJobsPage();
    initializeSearchAndFilter();
    initializeEventListeners();
    loadMyJobsStats();
});

// Initialize Jobs Page
function initializeJobsPage() {
    // Load jobs on page load
    loadJobs();
}

// Load Jobs from API
async function loadJobs() {
    try {
        // Show loading state
        showLoadingState();
        
        // Fetch jobs and unread counts in parallel
        const [jobsResponse, unreadResponse] = await Promise.all([
            fetch('assets/api/get_jobs.php'),
            fetch('assets/api/get_job_unread_messages.php')
        ]);
        
        const jobsResult = await jobsResponse.json();
        const unreadResult = await unreadResponse.json();
        
        if (jobsResult.success) {
            // Get unread counts
            const unreadCounts = unreadResult.success ? unreadResult.data : {};
            
            // Render jobs with unread counts
            renderJobs(jobsResult.jobs, unreadCounts);
            
            // Update metrics
            updateJobMetrics(jobsResult.stats);
            
            // Hide loading state
            hideLoadingState();
        } else {
            showNotification(jobsResult.message || 'Failed to load jobs', 'error');
            hideLoadingState();
        }
    } catch (error) {
        console.error('Load jobs error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
        hideLoadingState();
    }
}

// Render Jobs in Table
function renderJobs(jobs, unreadCounts = {}) {
    const tbody = document.getElementById('jobsTableBody');
    
    if (!tbody) {
        console.error('Jobs table tbody not found');
        return;
    }
    
    if (jobs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="empty-state">
                        <i class="bi bi-briefcase" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h5>No jobs found</h5>
                        <p class="text-muted">No jobs match your current search criteria.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = jobs.map(job => `
        <tr>
            <td>
                <div class="store-info">
                    <div class="store-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="store-details">
                        <div class="store-name">${escapeHtml(job.store_name)}</div>
                        <div class="job-id">JOB-${job.id.toString().padStart(4, '0')}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="job-type">${escapeHtml(job.job_type)}</span>
            </td>
            <td>
                <div class="address-info">
                    <i class="bi bi-geo-alt"></i>
                    <span>${escapeHtml(job.address)}</span>
                </div>
            </td>
            <td>
                <div class="deadline-info">
                    <i class="bi bi-calendar"></i>
                    <div class="deadline-details">
                        <div>${job.sla_formatted.date}</div>
                        <div class="time">${job.sla_formatted.time}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="status-badge ${job.status_class}">${job.status_display}</span>
            </td>
            <td>
                <div class="vendor-info">
                    <span class="vendor-count">${job.vendor_count}</span>
                    ${unreadCounts[job.id] > 0 ? `
                        <span class="unread-messages-badge" title="Unread messages">
                            <i class="bi bi-chat-dots"></i>
                            <span class="badge-count">${unreadCounts[job.id]}</span>
                        </span>
                    ` : ''}
                </div>
            </td>
            <td>
                <div class="created-info">
                    <i class="bi bi-clock"></i>
                    <span>${job.created_ago}</span>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <a href="view-job.php?id=${job.id}">
                        <button class="action-btn view-btn" title="View Job">
                            <i class="bi bi-eye"></i>
                        </button>
                    </a>
                </div>
            </td>
        </tr>
    `).join('');
}

// Update Job Metrics
function updateJobMetrics(stats) {
    const metrics = {
        total: document.querySelector('#metricCardTotalJobs h3'),
        pending: document.querySelector('#metricCardPendingJobs h3'),
        active: document.querySelector('#metricCardActiveJobs h3'),
        completed: document.querySelector('#metricCardCompletedJobs h3')
    };
    
    if (metrics.total) metrics.total.textContent = stats.total_jobs;
    if (metrics.pending) metrics.pending.textContent = stats.pending_jobs;
    if (metrics.active) metrics.active.textContent = stats.active_jobs;
    if (metrics.completed) metrics.completed.textContent = stats.completed_jobs;
}

// Initialize Search and Filter
function initializeSearchAndFilter() {
    const searchInput = document.getElementById('userJobSearchInput');
    const statusFilter = document.getElementById('userJobStatusFilter');
    
    // Search functionality
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterJobs();
            }, 500); // Debounce search
        });
    }
    
    // Status filter
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterJobs();
        });
    }
}

// Filter Jobs
async function filterJobs() {
    const searchInput = document.getElementById('userJobSearchInput');
    const statusFilter = document.getElementById('userJobStatusFilter');
    
    const search = searchInput ? searchInput.value.trim() : '';
    const status = statusFilter ? statusFilter.value : '';
    
    try {
        // Show loading state
        showLoadingState();
        
        // Build query parameters
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        
        // Fetch filtered jobs
        const response = await fetch(`assets/api/get_jobs.php?${params.toString()}`);
        const result = await response.json();
        
        if (result.success) {
            // Also load unread counts for filtered jobs
            try {
                const unreadResponse = await fetch('assets/api/get_job_unread_messages.php');
                const unreadResult = await unreadResponse.json();
                const unreadCounts = unreadResult.success ? unreadResult.data : {};
                renderJobs(result.jobs, unreadCounts);
            } catch (error) {
                console.error('Error loading unread counts:', error);
                renderJobs(result.jobs);
            }
            updateJobMetrics(result.stats);
        } else {
            showNotification(result.message || 'Failed to filter jobs', 'error');
        }
        
        hideLoadingState();
    } catch (error) {
        console.error('Filter jobs error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
        hideLoadingState();
    }
}

// Initialize Event Listeners
function initializeEventListeners() {
    // Users can only view their assigned jobs, cannot add jobs
    // All job additions are done by admin only
}

// Show Loading State
function showLoadingState() {
    const tbody = document.querySelector('.jobs-table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading jobs...</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Hide Loading State
function hideLoadingState() {
    // Loading state will be replaced by actual data
}


// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Notification System
function showNotification(message, type) {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notificationContainer')) {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
    
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    let iconClass = 'info-circle';
    if (type === 'success') iconClass = 'check-circle';
    if (type === 'error') iconClass = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="bi bi-${iconClass}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        background: white;
        border-radius: var(--radius-md);
        padding: 1rem 1.5rem;
        box-shadow: var(--shadow-medium);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        transform: translateX(400px);
        transition: all 0.3s ease;
        border-left: 4px solid var(--accent-blue);
        pointer-events: auto;
        max-width: 350px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-success {
        border-left-color: #10B981;
    }
    
    .notification-success i {
        color: #10B981;
    }
    
    .notification-error {
        border-left-color: #EF4444;
    }
    
    .notification-error i {
        color: #EF4444;
    }
    
    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
`;
document.head.appendChild(style);

// Load My Jobs Stats
async function loadMyJobsStats() {
    try {
        const response = await fetch('assets/api/get_my_jobs_stats.php');
        const result = await response.json();
        
        if (result.success) {
            updateMyJobsStatsCards(result.data);
        } else {
            console.error('My Jobs Stats error:', result.message);
        }
    } catch (error) {
        console.error('Load My Jobs Stats Error:', error);
    }
}

// Update My Jobs Stats Cards
function updateMyJobsStatsCards(stats) {
    // Update total jobs count
    const totalJobsElement = document.querySelector('#totalJobsCount');
    if (totalJobsElement) {
        totalJobsElement.textContent = stats.total_jobs;
    }
    
    // Update SLA reminders count
    const slaRemindersElement = document.querySelector('#slaRemindersCount');
    if (slaRemindersElement) {
        slaRemindersElement.textContent = stats.sla_reminders;
    }
    
    // Update completed jobs count
    const completedJobsElement = document.querySelector('#completedJobsCount');
    if (completedJobsElement) {
        completedJobsElement.textContent = stats.completed_jobs;
    }
    
    // Update in progress jobs count
    const inProgressJobsElement = document.querySelector('#inProgressJobsCount');
    if (inProgressJobsElement) {
        inProgressJobsElement.textContent = stats.in_progress_jobs;
    }
}

// Export functions for global access
window.MyJobsSystem = {
    loadJobs,
    filterJobs,
    showNotification,
    loadMyJobsStats
};
