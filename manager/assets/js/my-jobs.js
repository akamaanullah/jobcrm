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
            
            // Initialize bulk assignment after rendering
            initializeBulkAssignment();
            
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
                <td colspan="10" class="text-center py-4">
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
                <input type="checkbox" class="job-checkbox form-check-input" data-job-id="${job.id}">
            </td>
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
                <div class="assigned-user-info">
                    ${job.assigned_to_name ? `
                        <div class="user-assigned">
                            <i class="bi bi-person-check text-success"></i>
                            <span class="assigned-user-name">${escapeHtml(job.assigned_to_name)}</span>
                        </div>
                    ` : `
                        <div class="user-not-assigned">
                            <i class="bi bi-person-x text-muted"></i>
                            <span class="text-muted">Not assigned</span>
                        </div>
                    `}
                </div>
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
            // Reinitialize bulk assignment after rendering
            initializeBulkAssignment();
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
    // Add Job Button Handler
    const createJobBtn = document.getElementById('createJobBtn');
    const addJobModal = document.getElementById('addJobModal');
    
    if (createJobBtn) {
        createJobBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleAddJob();
        });
    }
    
    // File input change handler for job pictures
    const attachedPicturesInput = document.getElementById('attachedPictures');
    if (attachedPicturesInput) {
        attachedPicturesInput.addEventListener('change', function() {
            previewJobPictures(this);
        });
    }
    
    // Reset form when modal is closed
    if (addJobModal) {
        addJobModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addJobForm');
            if (form) {
                form.reset();
                clearPicturePreview();
            }
        });
        
    }
    
    // Initialize bulk assignment
    initializeBulkAssignment();
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

// Handle Add Job
async function handleAddJob() {
    const form = document.getElementById('addJobForm');
    const createJobBtn = document.getElementById('createJobBtn');
    const addJobModal = document.getElementById('addJobModal');
    
    if (!form || !createJobBtn) {
        showNotification('Form elements not found', 'error');
        return;
    }

    // Get form values
    const jobData = {
        storeName: document.getElementById('storeName').value.trim(),
        address: document.getElementById('address').value.trim(),
        jobType: document.getElementById('jobType').value.trim(),
        jobSLA: document.getElementById('jobSLA').value,
        jobDetails: document.getElementById('jobDetails').value.trim(),
        additionalNotes: document.getElementById('additionalNotes').value.trim()
    };

    // Validate form
    if (!validateAddJobForm(jobData)) {
        return;
    }

    // Show loading state
    createJobBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing Images...';
    createJobBtn.disabled = true;

    try {
        // Get job pictures as base64 with progress
        createJobBtn.innerHTML = '<i class="bi bi-image"></i> Compressing Images...';
        jobData.jobPictures = await getJobPicturesAsync();

        createJobBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Creating Job...';

        // Send API request
        const response = await fetch('assets/api/add_job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(jobData)
        });

        const data = await response.json();

        if (data.success) {
            // Show success message
            showNotification('Job created successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(addJobModal);
            if (modal) {
                modal.hide();
            }
            
            // Refresh jobs list
            loadJobs();
            loadMyJobsStats();

        } else {
            console.error('Add Job API Error:', data.message);
            showNotification(data.message || 'Failed to create job', 'error');
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred while creating job', 'error');
    } finally {
        // Reset button state
        createJobBtn.innerHTML = 'Create Job';
        createJobBtn.disabled = false;
    }
}

// Validate Add Job Form
function validateAddJobForm(jobData) {
    if (!jobData.storeName) {
        showNotification('Store name is required', 'error');
        return false;
    }

    if (!jobData.address) {
        showNotification('Address is required', 'error');
        return false;
    }

    if (!jobData.jobType) {
        showNotification('Job type is required', 'error');
        return false;
    }

    if (!jobData.jobSLA) {
        showNotification('Job SLA is required', 'error');
        return false;
    }

    if (!jobData.jobDetails) {
        showNotification('Job details are required', 'error');
        return false;
    }

    return true;
}

// Get Job Pictures as Base64 (Async version)
function getJobPicturesAsync() {
    return new Promise((resolve) => {
        const attachedPicturesInput = document.getElementById('attachedPictures');
        if (!attachedPicturesInput || !attachedPicturesInput.files.length) {
            resolve([]);
            return;
        }

        const pictures = [];
        const files = Array.from(attachedPicturesInput.files);
        let processedFiles = 0;

        if (files.length === 0) {
            resolve([]);
            return;
        }

        // Validate file sizes first
        const maxSize = 5 * 1024 * 1024; // 5MB per file
        const validFiles = files.filter(file => {
            if (file.size > maxSize) {
                showNotification(`File ${file.name} is too large. Maximum size is 5MB.`, 'error');
                return false;
            }
            return file.type.startsWith('image/');
        });

        if (validFiles.length === 0) {
            resolve([]);
            return;
        }

        // Process files with compression
        validFiles.forEach(file => {
            compressAndConvertToBase64(file).then(compressedData => {
                pictures.push(compressedData);
                processedFiles++;

                if (processedFiles === validFiles.length) {
                    resolve(pictures);
                }
            }).catch(error => {
                console.error('Error processing file:', error);
                processedFiles++;
                if (processedFiles === validFiles.length) {
                    resolve(pictures);
                }
            });
        });
    });
}

// Compress and convert image to base64
function compressAndConvertToBase64(file) {
    return new Promise((resolve, reject) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = function () {
            // Calculate new dimensions (max 1920x1080)
            const maxWidth = 1920;
            const maxHeight = 1080;
            let { width, height } = img;

            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height);
                width *= ratio;
                height *= ratio;
            }

            // Set canvas dimensions
            canvas.width = width;
            canvas.height = height;

            // Draw and compress
            ctx.drawImage(img, 0, 0, width, height);

            // Convert to base64 with compression
            const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.8);
            resolve(compressedDataUrl);
        };

        img.onerror = function () {
            reject(new Error('Failed to load image'));
        };

        // Load image
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
        };
        reader.onerror = function () {
            reject(new Error('Failed to read file'));
        };
        reader.readAsDataURL(file);
    });
}

// Preview Job Pictures
function previewJobPictures(input) {
    const files = input.files;
    const fileCountInfo = document.getElementById('fileCountInfo');

    // Update file count info
    if (files.length > 0) {
        if (fileCountInfo) {
            fileCountInfo.style.display = 'block';
            fileCountInfo.querySelector('span').textContent = `${files.length} file${files.length > 1 ? 's' : ''} selected`;
        }
    } else {
        if (fileCountInfo) {
            fileCountInfo.style.display = 'none';
        }
    }
}

// Clear picture preview
function clearPicturePreview() {
    const fileCountInfo = document.getElementById('fileCountInfo');
    if (fileCountInfo) {
        fileCountInfo.style.display = 'none';
    }
    
    const attachedPicturesInput = document.getElementById('attachedPictures');
    if (attachedPicturesInput) {
        attachedPicturesInput.value = '';
    }
}

// Bulk Assignment Functions
async function initializeBulkAssignment() {
    // Load users for assignment dropdown
    await loadUsersForAssignment();

    // Initialize event listeners after jobs are rendered
    // This will be called after renderJobs completes
    setTimeout(() => {
        setupBulkAssignmentListeners();
    }, 100);
}

function setupBulkAssignmentListeners() {
    const selectAllCheckbox = document.getElementById('selectAllJobs');
    const jobCheckboxes = document.querySelectorAll('.job-checkbox');
    const bulkAssignmentControls = document.getElementById('bulkAssignmentControls');
    const selectedJobsCount = document.getElementById('selectedJobsCount');
    const assignUserSelect = document.getElementById('assignUserSelect');
    const assignSelectedBtn = document.getElementById('assignSelectedBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');

    // Select All checkbox handler
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            jobCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBulkAssignmentUI();
        });
    }

    // Individual job checkbox handlers
    jobCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            updateSelectAllState();
            updateBulkAssignmentUI();
        });
    });

    // Assign button handler
    if (assignSelectedBtn) {
        assignSelectedBtn.addEventListener('click', assignSelectedJobs);
    }

    // Clear selection handler
    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', clearAllSelections);
    }

    function updateSelectAllState() {
        if (selectAllCheckbox) {
            const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
            const totalBoxes = document.querySelectorAll('.job-checkbox');

            selectAllCheckbox.checked = checkedBoxes.length === totalBoxes.length && totalBoxes.length > 0;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalBoxes.length;
        }
    }

    function updateBulkAssignmentUI() {
        const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
        const count = checkedBoxes.length;

        if (count > 0) {
            if (bulkAssignmentControls) {
                bulkAssignmentControls.style.display = 'flex';
            }
            if (selectedJobsCount) {
                selectedJobsCount.textContent = count;
            }
            if (assignSelectedBtn) {
                assignSelectedBtn.disabled = !assignUserSelect || !assignUserSelect.value;
            }
        } else {
            if (bulkAssignmentControls) {
                bulkAssignmentControls.style.display = 'none';
            }
        }
    }

    // Enable/disable assign button based on user selection
    if (assignUserSelect) {
        assignUserSelect.addEventListener('change', function () {
            const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
            if (assignSelectedBtn) {
                assignSelectedBtn.disabled = checkedBoxes.length === 0 || !this.value;
            }
        });
    }
}

// Load Users for Assignment
async function loadUsersForAssignment() {
    try {
        const response = await fetch('assets/api/get_users_for_assignment.php');
        const data = await response.json();

        if (data.success) {
            const assignUserSelect = document.getElementById('assignUserSelect');
            if (assignUserSelect) {
                assignUserSelect.innerHTML = '<option value="">Select User to Assign</option>';
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.first_name} ${user.last_name}`;
                    assignUserSelect.appendChild(option);
                });
            }
        } else {
            console.error('Error loading users:', data.message);
        }
    } catch (error) {
        console.error('Error loading users for assignment:', error);
    }
}

async function assignSelectedJobs() {
    const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
    const assignUserSelect = document.getElementById('assignUserSelect');

    if (checkedBoxes.length === 0 || !assignUserSelect.value) {
        showNotification('Please select jobs and a user to assign', 'error');
        return;
    }

    const jobIds = Array.from(checkedBoxes).map(checkbox => checkbox.dataset.jobId);
    const userId = assignUserSelect.value;
    const userName = assignUserSelect.options[assignUserSelect.selectedIndex].text;

    try {
        const response = await fetch('assets/api/bulk_assign_jobs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                job_ids: jobIds,
                user_id: userId
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(`Successfully assigned ${jobIds.length} jobs to ${userName}`, 'success');
            loadJobs(); // Reload jobs to show updated assignments
            clearAllSelections();
            if (assignUserSelect) {
                assignUserSelect.value = '';
            }
        } else {
            throw new Error(data.message || 'Failed to assign jobs');
        }
    } catch (error) {
        console.error('Error assigning jobs:', error);
        showNotification(error.message || 'Failed to assign jobs', 'error');
    }
}

// Clear all selections function
function clearAllSelections() {
    const jobCheckboxes = document.querySelectorAll('.job-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllJobs');

    jobCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    // Hide bulk assignment controls
    const bulkAssignmentControls = document.getElementById('bulkAssignmentControls');
    if (bulkAssignmentControls) {
        bulkAssignmentControls.style.display = 'none';
    }
}

// Export functions for global access
window.MyJobsSystem = {
    loadJobs,
    filterJobs,
    showNotification,
    loadMyJobsStats,
    loadUsersForAssignment
};
