// View Job Page JavaScript

// Get job ID from URL
function getJobIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

document.addEventListener('DOMContentLoaded', function() {
    // Get job ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const jobId = urlParams.get('id');

    if (!jobId) {
        showNotification('Job ID not found', 'error');
        return;
    }

    // Store job ID globally
    window.currentJobId = jobId;

    // Load job details, vendors, timeline, and comments
    loadJobDetails(jobId);
    loadJobVendors(jobId);
    loadJobTimeline(jobId);
    loadComments(jobId);
    loadPaymentMetrics(jobId);

    // Initialize Add Vendor form
    initializeAddVendorForm();

    // Initialize Final Visit Approval form
    initializeFinalVisitApprovalForm();

    // Initialize Complete Job form
    initializeCompleteJobForm();

    // Initialize Request Payment form
    initializeRequestPaymentForm();

    // Initialize Partial Payment form
    initializePartialPaymentForm();

    // Initialize Show Vendors modal
    initializeShowVendorsModal();

    // Initialize Comments functionality
    initializeComments();

    // Initialize Edit Vendor functionality
    initializeEditVendorForm();

    // Add modal event listeners to store vendor ID
    const requestPaymentModal = document.getElementById('requestPaymentModal');
    if (requestPaymentModal) {
        requestPaymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button) {
                const vendorId = button.getAttribute('data-vendor-id');
                const vendorName = button.getAttribute('data-vendor-name');
                window.currentVendorId = vendorId;
                window.currentVendorName = vendorName;
            }
        });
    }
});

// Load job details
async function loadJobDetails(jobId) {
    try {
        const response = await fetch(`assets/api/get_job_details.php?job_id=${jobId}`);
        const result = await response.json();

        if (result.success) {
            displayJobDetails(result.data);
        } else {
            showNotification(result.message || 'Failed to load job details', 'error');
        }
    } catch (error) {
        console.error('Error loading job details:', error);
        showNotification('Error loading job details', 'error');
    }
}

// Display job details
function displayJobDetails(job) {
    // Update welcome banner
    document.getElementById('jobTitle').textContent = `${job.store_name} - JOB-${job.id}`;
    document.getElementById('jobSubtitle').textContent = `${job.job_type} • ${job.address}`;

    // Update metrics cards (only SLA Deadline now, payment metrics are loaded separately)
    document.getElementById('slaDeadline').textContent = job.sla_formatted.date + ' at ' + job.sla_formatted.time;

    // Update job description
    document.getElementById('jobDetail').textContent = job.job_detail || 'No job details provided.';

    // Update additional notes
    if (job.additional_notes && job.additional_notes.trim()) {
        document.getElementById('additionalNotes').textContent = job.additional_notes;
        document.getElementById('additionalNotesSection').style.display = 'block';
    }

    // Update attachments
    displayAttachments(job.attachments);
}

// Display attachments
function displayAttachments(attachments) {
    const attachedFilesCard = document.getElementById('attachedFilesCard');
    const attachedFilesGrid = document.getElementById('attachedFilesGrid');
    const noAttachments = document.getElementById('noAttachments');
    const attachmentCount = document.getElementById('attachmentCount');


    if (attachments && attachments.length > 0) {
        attachedFilesCard.style.display = 'block';
        attachmentCount.textContent = `${attachments.length} file${attachments.length > 1 ? 's' : ''}`;

        attachedFilesGrid.innerHTML = '';

        attachments.forEach(attachment => {
            const fileItem = createFileItem(attachment);
            attachedFilesGrid.appendChild(fileItem);
        });

        noAttachments.style.display = 'none';
    } else {
        attachedFilesCard.style.display = 'block';
        attachmentCount.textContent = '0 files';
        attachedFilesGrid.innerHTML = '';
        noAttachments.style.display = 'block';
    }
}

// Create file item element
function createFileItem(attachment) {
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';

    const fileExtension = attachment.picture_name.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension);


    let previewHtml = '';
    if (isImage) {
        previewHtml = `
            <div class="file-preview">
                <img src="${attachment.picture_path}" alt="${attachment.picture_name}" class="file-image" 
                     onerror="this.src='https://via.placeholder.com/400x300/f8f9fa/6c757d?text=Image+Not+Found'">
            </div>
        `;
    } else {
        const iconClass = getFileIconClass(fileExtension);
        previewHtml = `
            <div class="file-preview">
                <div class="file-icon-preview">
                    <i class="${iconClass}" style="font-size: 4rem; color: #6c757d;"></i>
                </div>
            </div>
        `;
    }

    fileItem.innerHTML = `
        
            <div class="file-preview">
                ${isImage ? `
                    <img src="${attachment.picture_path}" alt="${attachment.picture_name}" class="file-image" 
                         onclick="viewImage('${attachment.picture_path}', '${attachment.picture_name}')"
                         onerror="this.src='https://via.placeholder.com/400x300/f8f9fa/6c757d?text=Image+Not+Found'">
                    <div class="file-actions-overlay">
                        <button class="btn-file-action" onclick="downloadFile('${attachment.picture_path}', '${attachment.picture_name}')" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                ` : `
                    <div class="file-icon-preview">
                        <i class="${getFileIconClass(fileExtension)}" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <div class="file-actions-overlay">
                        <button class="btn-file-action" onclick="downloadFile('${attachment.picture_path}', '${attachment.picture_name}')" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                `}
            </div>
        
    `;

    return fileItem;
}

// Get file icon class based on extension
function getFileIconClass(extension) {
    const iconMap = {
        'pdf': 'bi bi-file-earmark-pdf text-danger',
        'doc': 'bi bi-file-earmark-word text-primary',
        'docx': 'bi bi-file-earmark-word text-primary',
        'txt': 'bi bi-file-earmark-text text-secondary',
        'zip': 'bi bi-file-earmark-zip text-warning',
        'rar': 'bi bi-file-earmark-zip text-warning',
        'xls': 'bi bi-file-earmark-excel text-success',
        'xlsx': 'bi bi-file-earmark-excel text-success'
    };

    return iconMap[extension] || 'bi bi-file-earmark text-muted';
}

// Get file type text
function getFileTypeText(extension) {
    const typeMap = {
        'jpg': 'Image',
        'jpeg': 'Image',
        'png': 'Image',
        'gif': 'Image',
        'bmp': 'Image',
        'webp': 'Image',
        'pdf': 'PDF Document',
        'doc': 'Word Document',
        'docx': 'Word Document',
        'txt': 'Text File',
        'zip': 'ZIP Archive',
        'rar': 'RAR Archive',
        'xls': 'Excel Spreadsheet',
        'xlsx': 'Excel Spreadsheet'
    };

    return typeMap[extension] || 'File';
}

// Load job timeline
async function loadJobTimeline(jobId) {
    try {
        const response = await fetch(`assets/api/get_job_timeline.php?job_id=${jobId}`);
        const result = await response.json();

        if (result.success) {
            displayJobTimeline(result.data);
        } else {
            console.error('Timeline API Error:', result.message);
            showTimelineError(result.message);
        }
    } catch (error) {
        console.error('Load Timeline Error:', error);
        showTimelineError('Failed to load timeline');
    }
}

function displayJobTimeline(timelineEvents) {
    const timelineContainer = document.getElementById('jobTimeline');
    const loadingElement = document.getElementById('timelineLoading');

    if (loadingElement) {
        loadingElement.remove();
    }

    if (!timelineEvents || timelineEvents.length === 0) {
        timelineContainer.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No Timeline Events</h5>
                <p class="text-muted">No timeline events found for this job.</p>
            </div>
        `;
        return;
    }

    const timelineHTML = timelineEvents.map(event => createTimelineItem(event)).join('');
    timelineContainer.innerHTML = timelineHTML;
}

function createTimelineItem(event) {
    const statusClass = getTimelineStatusClass(event.status);
    const iconClass = event.icon || 'bi-circle';

    return `
        <div class="timeline-item ${statusClass}">
            <div class="timeline-marker">
                <i class="${iconClass}"></i>
            </div>
            <div class="timeline-content">
                <h4>
                    <i class="${iconClass}"></i>
                    ${event.title}
                </h4>
                <p>${event.description}</p>
                <span class="timeline-time">${event.time_ago}</span>
            </div>
        </div>
    `;
}

function getTimelineStatusClass(status) {
    switch (status) {
        case 'completed':
            return 'completed';
        case 'active':
            return 'active';
        case 'pending':
            return 'pending';
        default:
            return '';
    }
}

function showTimelineError(message) {
    const timelineContainer = document.getElementById('jobTimeline');
    const loadingElement = document.getElementById('timelineLoading');

    if (loadingElement) {
        loadingElement.remove();
    }

    timelineContainer.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
            <h5 class="text-danger mt-3">Timeline Error</h5>
            <p class="text-muted">${message}</p>
            <button class="btn btn-primary btn-sm" onclick="loadJobTimeline(window.currentJobId)">
                <i class="bi bi-arrow-clockwise"></i> Retry
            </button>
        </div>
    `;
}

// Load job vendors
async function loadJobVendors(jobId) {
    try {
        const response = await fetch(`assets/api/get_job_vendors.php?job_id=${jobId}`);
        const result = await response.json();
        if (result.success) {
            displayVendors(result.data);
        } else {
            showNotification(result.message || 'Failed to load vendors', 'error');
        }
    } catch (error) {
        console.error('Error loading vendors:', error);
        showNotification('Error loading vendors', 'error');
    }
}

// Display vendors
async function displayVendors(vendors) {
    const vendorsGrid = document.getElementById('vendorsGrid');
    const noVendors = document.getElementById('noVendors');
    const loadingVendors = document.getElementById('loadingVendors');
    const chatWithVendorsBtn = document.getElementById('chatWithVendorsBtn');

    // Hide loading
    loadingVendors.style.display = 'none';

    if (vendors && vendors.length > 0) {
        vendorsGrid.innerHTML = '';

        // Get unread counts for all vendors
        const jobId = getJobIdFromUrl();
        let unreadCounts = {};

        if (jobId) {
            try {
                const response = await fetch(`assets/api/get_vendor_unread_messages.php?job_id=${jobId}&t=${Date.now()}`);
                const result = await response.json();
                if (result.success && result.data) {
                    unreadCounts = result.data;
                }
            } catch (error) {
                console.error('Error fetching unread counts for vendor cards:', error);
            }
        }

        vendors.forEach((vendor, index) => {
            const vendorCard = createVendorCard(vendor, index, unreadCounts[vendor.id] || 0);
            vendorsGrid.appendChild(vendorCard);
        });

        // Show chat button if vendors exist
        chatWithVendorsBtn.style.display = 'inline-block';

        noVendors.style.display = 'none';
    } else {
        vendorsGrid.innerHTML = '';
        noVendors.style.display = 'block';
        chatWithVendorsBtn.style.display = 'none';
    }
}

// Create vendor card element
function createVendorCard(vendor, index, unreadCount = 0) {
    const vendorCard = document.createElement('div');
    vendorCard.className = 'user-card';
    vendorCard.setAttribute('data-vendor-id', vendor.id);

    const actionButton = getVendorActionButton(vendor, index);
    const showUnreadBadge = unreadCount > 0;


    vendorCard.innerHTML = `
        <div class="vendor-edit-icon">
            <button class="btn btn-outline-secondary btn-sm" onclick="editVendor(${vendor.id})" title="Edit Vendor">
                <i class="bi bi-pencil"></i>
            </button>
        </div>
        <div class="user-avatar">
            <span>${vendor.avatar}</span>
            ${showUnreadBadge ? `<div class="unread-badge" id="vendorUnreadBadge${vendor.id}">${unreadCount}</div>` : ''}
        </div>
        <div class="user-info">
            <div class="user-name-section">
                <h4>${vendor.vendor_name}</h4>
                ${getStatusBadge(vendor.status)}
            </div>
            <p class="user-email">${vendor.phone}</p>
            <div class="user-details">
                <p><i class="bi bi-calendar"></i> Joined: ${vendor.created_ago}</p>
                <p><i class="bi bi-briefcase"></i> Platform: ${vendor.vendor_platform || 'Not specified'}</p>
                ${vendor.location ? `<p><i class="bi bi-geo-alt"></i> Location: ${vendor.location}</p>` : ''}
                <p><i class="bi bi-currency-dollar"></i> ${vendor.quote_display}</p>
            </div>
            <div class="user-status-actions vendor-action-buttons">
                ${actionButton}
            </div>
        </div>
    `;

    return vendorCard;
}

// Get status badge based on vendor status
function getStatusBadge(status) {
    const statusMap = {
        'added': {
            text: 'Added',
            class: 'badge-secondary'
        },
        'visit_requested': {
            text: 'Visit Requested',
            class: 'badge-warning'
        },
        'visit_request_rejected': {
            text: 'Visit Rejected',
            class: 'badge-danger'
        },
        'final_visit_requested': {
            text: 'Final Visit Requested',
            class: 'badge-info'
        },
        'final_visit_request_rejected': {
            text: 'Final Visit Rejected',
            class: 'badge-danger'
        },
        'job_completed': {
            text: 'Job Completed',
            class: 'badge-success'
        },
        'requested_vendor_payment': {
            text: 'Payment Requested',
            class: 'badge-primary'
        },
        'payment_request_rejected': {
            text: 'Payment Rejected',
            class: 'badge-danger'
        },
        'request_visit_accepted': {
            text: 'Visit Accepted',
            class: 'badge-success'
        },
        'final_visit_request_accepted': {
            text: 'Final Visit Accepted',
            class: 'badge-success'
        },
        'vendor_payment_accepted': {
            text: 'Payment Accepted',
            class: 'badge-success'
        }
    };

    const statusInfo = statusMap[status] || {
        text: status,
        class: 'badge-secondary'
    };

    return `<span class="badge ${statusInfo.class} vendor-status-badge">${statusInfo.text}</span>`;
}

// Get vendor action button based on status
function getVendorActionButton(vendor, index) {
    // Use the action button data from API
    const actionButton = vendor.action_button;

    if (!actionButton.show) {
        return ''; // No button to show
    }

    // Map action types to modal IDs (except request_visit which is direct)
    const modalMap = {
        'request_final_visit': 'finalVisitApprovalModal',
        'complete_job': 'completeJobModal',
        'request_payment': 'requestPaymentModal',
        'request_partial_payment': 'partialPaymentModal'
    };

    const modalId = modalMap[actionButton.action] || '';

    // Check if button should be disabled
    const disabledAttr = actionButton.disabled ? 'disabled' : '';
    const disabledClass = actionButton.disabled ? 'disabled' : '';

    // For request_visit, use direct click handler instead of modal
    if (actionButton.action === 'request_visit') {
        return `
            <button class="btn ${actionButton.class} btn-sm ${disabledClass}" 
                    ${disabledAttr}
                    onclick="${actionButton.disabled ? '' : `handleDirectVisitRequest(${vendor.id}, '${vendor.vendor_name}')`}"
                    data-vendor-id="${vendor.id}"
                    data-vendor-name="${vendor.vendor_name}">
                <i class="${actionButton.icon}"></i> ${actionButton.text}
            </button>
        `;
    }

    // For disabled buttons, don't add modal trigger
    if (actionButton.disabled) {
        return `
            <button class="btn ${actionButton.class} btn-sm disabled" 
                    disabled
                    data-vendor-id="${vendor.id}"
                    data-vendor-name="${vendor.vendor_name}">
                <i class="${actionButton.icon}"></i> ${actionButton.text}
            </button>
        `;
    }

    let buttonsHTML = `<div class="d-flex gap-2 flex-wrap">`;

    // Primary button
    buttonsHTML += `
        <button class="btn ${actionButton.class} btn-sm" 
                data-bs-toggle="modal" 
                data-bs-target="#${modalId}"
                data-vendor-id="${vendor.id}"
                data-vendor-name="${vendor.vendor_name}">
            <i class="${actionButton.icon}"></i> ${actionButton.text}
        </button>
    `;

    // Add secondary button if exists
    if (actionButton.secondary) {
        const secondaryModalId = modalMap[actionButton.secondary.action] || '';
        buttonsHTML += `
        <button class="btn ${actionButton.secondary.class} btn-sm" 
                data-bs-toggle="modal" 
                data-bs-target="#${secondaryModalId}"
                data-vendor-id="${vendor.id}"
                data-vendor-name="${vendor.vendor_name}">
            <i class="${actionButton.secondary.icon || 'bi-check-circle'}"></i> ${actionButton.secondary.text}
        </button>
        `;
    }

    buttonsHTML += `</div>`;

    return buttonsHTML;
}

// Handle direct visit request (no modal)
async function handleDirectVisitRequest(vendorId, vendorName) {
    if (!confirm(`Are you sure you want to request a visit from ${vendorName}?`)) {
        return;
    }

    try {
        const response = await fetch('assets/api/request_visit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                vendor_id: vendorId,
                job_id: window.currentJobId
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Visit request sent successfully!', 'success');
            // Reload vendors and timeline to update status
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
            loadPaymentMetrics(window.currentJobId);
        } else {
            showNotification(result.message || 'Failed to send visit request', 'error');
        }
    } catch (error) {
        console.error('Error requesting visit:', error);
        showNotification('Error requesting visit', 'error');
    }
}

// Make function globally accessible
window.handleDirectVisitRequest = handleDirectVisitRequest;

// File actions
function viewFile(filePath) {
    window.open(filePath, '_blank');
}

function downloadFile(filePath, fileName) {
    const link = document.createElement('a');
    link.href = filePath;
    link.download = fileName;
    link.click();
}

// Helper function to get time ago (simplified version)
function getTimeAgo(datetime) {
    if (!datetime) return 'Unknown';

    const now = new Date();
    const past = new Date(datetime);
    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
    if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
    if (diffInSeconds < 31536000) return Math.floor(diffInSeconds / 2592000) + ' months ago';
    return Math.floor(diffInSeconds / 31536000) + ' years ago';
}

// Initialize Add Vendor Form
function initializeAddVendorForm() {
    const submitBtn = document.getElementById('submitAddVendor');
    const form = document.getElementById('addVendorForm');
    const appointmentInput = document.getElementById('appointmentDateTime');

    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates

    // Handle form submission
    if (submitBtn && form) {
        submitBtn.addEventListener('click', handleAddVendor);
    }
}

// Initialize Final Visit Approval Form
function initializeFinalVisitApprovalForm() {
    const submitBtn = document.getElementById('submitFinalVisitApproval');
    const form = document.getElementById('finalVisitApprovalForm');
    const visitDateTimeInput = document.getElementById('finalVisitDateTime');
    const modal = document.getElementById('finalVisitApprovalModal');

    // Set minimum date to current date/time
    if (visitDateTimeInput) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        visitDateTimeInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Handle form submission
    if (submitBtn && form) {
        submitBtn.addEventListener('click', handleFinalVisitApproval);
    }

    // Store vendor data when modal is shown
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button) {
                const vendorId = button.getAttribute('data-vendor-id');
                const vendorName = button.getAttribute('data-vendor-name');

                // Store in modal data attributes
                modal.setAttribute('data-vendor-id', vendorId);
                modal.setAttribute('data-vendor-name', vendorName);
            }
        });
    }
}

// Initialize Complete Job Form
function initializeCompleteJobForm() {
    const submitBtn = document.getElementById('submitCompleteJob');
    const form = document.getElementById('completeJobForm');
    const modal = document.getElementById('completeJobModal');
    const jobPicturesInput = document.getElementById('jobPictures');
    const invoiceFileInput = document.getElementById('invoiceFile');

    // Handle form submission
    if (submitBtn && form) {
        submitBtn.addEventListener('click', handleCompleteJob);
    }

    // Store vendor data when modal is shown
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button) {
                const vendorId = button.getAttribute('data-vendor-id');
                const vendorName = button.getAttribute('data-vendor-name');

                // Store in modal data attributes
                modal.setAttribute('data-vendor-id', vendorId);
                modal.setAttribute('data-vendor-name', vendorName);
            }
        });
    }

    // Handle job pictures preview
    if (jobPicturesInput) {
        jobPicturesInput.addEventListener('change', function(e) {
            handleJobPicturesPreview(e);
        });
    }

    // Handle invoice file preview
    if (invoiceFileInput) {
        invoiceFileInput.addEventListener('change', function(e) {
            handleInvoicePreview(e);
        });
    }
}

// Toggle Quote Amount field
window.toggleQuoteAmount = function() {
    const quoteType = document.getElementById('quoteType').value;
    const quoteAmountSection = document.getElementById('quoteAmountSection');
    const quoteAmountInput = document.getElementById('quoteAmount');

    if (quoteType === 'paid_quote') {
        quoteAmountSection.style.display = 'block';
        quoteAmountInput.required = true;
    } else {
        quoteAmountSection.style.display = 'none';
        quoteAmountInput.required = false;
        quoteAmountInput.value = '';
    }
};

// Handle Add Vendor form submission
async function handleAddVendor() {
    const form = document.getElementById('addVendorForm');
    const submitBtn = document.getElementById('submitAddVendor');

    if (!form || !submitBtn) return;

    // Collect form data
    const formData = new FormData(form);
    const vendorData = {
        vendor_name: formData.get('vendorName'),
        phone: formData.get('vendorPhone'),
        quote_type: formData.get('quoteType'),
        quote_amount: formData.get('quoteAmount') || 0,
        vendor_platform: formData.get('vendorPlatform') || null,
        location: formData.get('vendorLocation') || null,
        appointment_date_time: formData.get('appointmentDateTime'),
        notes: formData.get('vendorNotes') || null,
        job_id: window.currentJobId
    };

    // Validate form
    if (!validateVendorForm(vendorData)) {
        return;
    }

    // Show loading state
    submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Adding Vendor...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('assets/api/add_vendor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(vendorData)
        });

        const result = await response.json();

        if (result.success) {
            // Reset form
            form.reset();
            toggleQuoteAmount(); // Hide quote amount field

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addVendorModal'));
            modal.hide();

            // Show success message
            showNotification('Vendor added successfully!', 'success');

            // Reload vendors list and timeline
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
        } else {
            showNotification(result.message || 'Failed to add vendor', 'error');
        }
    } catch (error) {
        console.error('Error adding vendor:', error);
        showNotification('Error adding vendor', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Vendor';
        submitBtn.disabled = false;
    }
}

// Validate vendor form
function validateVendorForm(data) {
    if (!data.vendor_name || !data.vendor_name.trim()) {
        showNotification('Please enter vendor name', 'error');
        return false;
    }

    if (!data.phone || !data.phone.trim()) {
        showNotification('Please enter phone number', 'error');
        return false;
    }

    if (!data.quote_type) {
        showNotification('Please select quote type', 'error');
        return false;
    }

    if (data.quote_type === 'paid_quote' && (!data.quote_amount || data.quote_amount <= 0)) {
        showNotification('Please enter valid quote amount', 'error');
        return false;
    }

    if (!data.appointment_date_time) {
        showNotification('Please select appointment date and time', 'error');
        return false;
    }

    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates

    return true;
}

// Handle Final Visit Approval form submission
async function handleFinalVisitApproval() {
    const form = document.getElementById('finalVisitApprovalForm');
    const submitBtn = document.getElementById('submitFinalVisitApproval');

    if (!form || !submitBtn) return;

    // Get vendor data from modal
    const modal = document.getElementById('finalVisitApprovalModal');
    const vendorId = modal.getAttribute('data-vendor-id');
    const vendorName = modal.getAttribute('data-vendor-name');

    if (!vendorId) {
        showNotification('Vendor ID not found', 'error');
        return;
    }

    // Collect form data
    const formData = new FormData(form);
    const requestData = {
        vendor_id: vendorId,
        job_id: window.currentJobId,
        estimated_amount: formData.get('estimatedAmount'),
        visit_date_time: formData.get('visitDateTime'),
        payment_mode: formData.get('paymentMode'),
        additional_notes: formData.get('additionalNotes') || null
    };

    // Validate form
    if (!validateFinalVisitApprovalForm(requestData)) {
        return;
    }

    // Show loading state
    submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Submitting Request...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('assets/api/request_final_visit_approval.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });

        const result = await response.json();

        if (result.success) {
            // Reset form
            form.reset();

            // Close modal
            const modalInstance = bootstrap.Modal.getInstance(modal);
            modalInstance.hide();

            // Show success message
            showNotification('Final visit approval request submitted successfully!', 'success');

            // Reload vendors list and timeline to update status
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
        } else {
            showNotification(result.message || 'Failed to submit request', 'error');
        }
    } catch (error) {
        console.error('Error submitting final visit approval:', error);
        showNotification('Error submitting request', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Request';
        submitBtn.disabled = false;
    }
}

// Validate final visit approval form
function validateFinalVisitApprovalForm(data) {
    if (!data.estimated_amount || data.estimated_amount <= 0) {
        showNotification('Please enter a valid estimated amount', 'error');
        return false;
    }

    if (!data.visit_date_time) {
        showNotification('Please select visit date and time', 'error');
        return false;
    }

    // Date restrictions removed for historical data entry
    // Allow any date/time including past dates

    if (!data.payment_mode) {
        showNotification('Please select payment mode', 'error');
        return false;
    }

    return true;
}

// Handle Complete Job form submission
async function handleCompleteJob() {
    const form = document.getElementById('completeJobForm');
    const submitBtn = document.getElementById('submitCompleteJob');

    if (!form || !submitBtn) return;

    // Get vendor data from modal
    const modal = document.getElementById('completeJobModal');
    const vendorId = modal.getAttribute('data-vendor-id');
    const vendorName = modal.getAttribute('data-vendor-name');

    if (!vendorId) {
        showNotification('Vendor ID not found', 'error');
        return;
    }

    // Collect form data
    const formData = new FormData(form);
    const jobPicturesInput = document.getElementById('jobPictures');
    const invoiceFileInput = document.getElementById('invoiceFile');

    // Prepare W9 information (required)
    const vendorBusinessName = document.getElementById('vendorBusinessName').value;
    const vendorAddress = document.getElementById('vendorAddress').value;
    const vendorEINSSN = document.getElementById('vendorEINSSN').value;
    const entityType = document.getElementById('entityType').value;

    const w9Info = {
        vendor_business_name: vendorBusinessName,
        address: vendorAddress,
        ein_ssn: vendorEINSSN,
        entity_type: entityType
    };

    // Prepare attachments
    const attachments = [];

    // Handle job pictures
    if (jobPicturesInput && jobPicturesInput.files.length > 0) {
        for (let i = 0; i < jobPicturesInput.files.length; i++) {
            const file = jobPicturesInput.files[i];
            if (file.type.startsWith('image/')) {
                const base64Data = await fileToBase64(file);
                attachments.push({
                    type: 'picture',
                    name: file.name,
                    data: base64Data
                });
            }
        }
    }

    // Handle invoice file
    if (invoiceFileInput && invoiceFileInput.files.length > 0) {
        const file = invoiceFileInput.files[0];
        const base64Data = await fileToBase64(file);
        attachments.push({
            type: 'invoice',
            name: file.name,
            data: base64Data
        });
    }

    // Prepare request data
    const requestData = {
        job_id: window.currentJobId,
        vendor_id: vendorId,
        w9_info: w9Info,
        attachments: attachments
    };

    // Validate form
    if (!validateCompleteJobForm(requestData)) {
        return;
    }

    // Show loading state
    submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Completing Job...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('assets/api/complete_job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });

        const result = await response.json();

        if (result.success) {
            // Reset form
            form.reset();
            clearCompleteJobPreviews();

            // Close modal
            const modalInstance = bootstrap.Modal.getInstance(modal);
            modalInstance.hide();

            // Show success message
            showNotification('Job completed successfully!', 'success');

            // Reload vendors list and timeline to update status
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
        } else {
            showNotification(result.message || 'Failed to complete job', 'error');
        }
    } catch (error) {
        console.error('Error completing job:', error);
        showNotification('Error completing job', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Complete Job';
        submitBtn.disabled = false;
    }
}

// Validate complete job form
function validateCompleteJobForm(data) {
    // W9 information is required for job completion
    if (!data.w9_info) {
        showNotification('W9 information is required for job completion', 'error');
        return false;
    }

    // Check if at least one attachment is provided
    if (!data.attachments || data.attachments.length === 0) {
        showNotification('Please add at least one picture or invoice', 'error');
        return false;
    }

    return true;
}

// Handle job pictures preview
function handleJobPicturesPreview(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('picturesPreview');

    preview.innerHTML = '';

    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const pictureItem = document.createElement('div');
                pictureItem.className = 'picture-item';
                pictureItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-picture" onclick="removePicture(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                preview.appendChild(pictureItem);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Handle invoice preview
function handleInvoicePreview(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('invoicePreview');

    if (file) {
        preview.innerHTML = `
            <div class="invoice-item">
                <div class="file-icon">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <div class="file-info">
                    <h6>${file.name}</h6>
                    <small>${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                </div>
                <button type="button" class="remove-invoice" onclick="removeInvoice()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    }
}

// Convert file to base64
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            // Remove data:image/jpeg;base64, prefix
            const base64 = reader.result.split(',')[1];
            resolve(base64);
        };
        reader.onerror = error => reject(error);
    });
}

// Clear all previews
function clearCompleteJobPreviews() {
    const picturesPreview = document.getElementById('picturesPreview');
    const invoicePreview = document.getElementById('invoicePreview');

    if (picturesPreview) picturesPreview.innerHTML = '';
    if (invoicePreview) invoicePreview.innerHTML = '';
}

// Remove picture function
window.removePicture = function(index) {
    const preview = document.getElementById('picturesPreview');
    const pictureItems = preview.querySelectorAll('.picture-item');
    if (pictureItems[index]) {
        pictureItems[index].remove();
    }
};

// Remove invoice function
window.removeInvoice = function() {
    const preview = document.getElementById('invoicePreview');
    preview.innerHTML = '';
    document.getElementById('invoiceFile').value = '';
};

// Close other dropdowns when one is opened
window.closeOtherDropdowns = function(currentSection) {
    const sections = ['picturesSection', 'w9Section', 'invoiceSection'];

    sections.forEach(section => {
        if (section !== currentSection) {
            const element = document.getElementById(section);
            if (element && element.classList.contains('show')) {
                const collapse = new bootstrap.Collapse(element, {
                    toggle: false
                });
                collapse.hide();
            }
        }
    });
};

// Request Payment functionality
function initializeRequestPaymentForm() {
    const submitPaymentRequestBtn = document.getElementById('submitPaymentRequest');
    if (submitPaymentRequestBtn) {
        submitPaymentRequestBtn.addEventListener('click', handleRequestPayment);
    }
}

// Initialize Partial Payment Form
function initializePartialPaymentForm() {
    const submitPartialPaymentBtn = document.getElementById('submitPartialPayment');
    if (submitPartialPaymentBtn) {
        submitPartialPaymentBtn.addEventListener('click', handlePartialPayment);
    }

    // Add modal event listener to load payment info
    const partialPaymentModal = document.getElementById('partialPaymentModal');
    if (partialPaymentModal) {
        partialPaymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (button) {
                const vendorId = button.getAttribute('data-vendor-id');
                const vendorName = button.getAttribute('data-vendor-name');
                if (vendorId) {
                    loadPartialPaymentInfo(vendorId, vendorName);
                }
            }
        });
    }

    // Add amount input listener to update remaining balance
    const amountInput = document.getElementById('partialPaymentAmount');
    if (amountInput) {
        amountInput.addEventListener('input', updateRemainingBalance);
    }
}

async function handleRequestPayment() {
    try {
        const form = document.getElementById('requestPaymentForm');
        if (!form) {
            showNotification('Payment form not found', 'error');
            return;
        }

        const formData = new FormData(form);
        const paymentData = validateRequestPaymentForm(formData);

        if (!paymentData) {
            return; // Validation failed
        }

        // Show loading state
        const submitBtn = document.getElementById('submitPaymentRequest');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
        submitBtn.disabled = true;

        const response = await fetch('assets/api/request_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(paymentData)
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('requestPaymentModal'));
            if (modal) {
                modal.hide();
            }

            // Reset form
            form.reset();
            hideAllPaymentSections();

            // Reload vendors and timeline to update status
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
            loadPaymentMetrics(window.currentJobId);
        } else {
            showNotification(data.message, 'error');
        }

    } catch (error) {
        console.error('Request Payment Error:', error);
        showNotification('Failed to submit payment request', 'error');
    } finally {
        // Reset button state
        const submitBtn = document.getElementById('submitPaymentRequest');
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Request Payment';
        submitBtn.disabled = false;
    }
}

// Partial Payment functionality
async function loadPartialPaymentInfo(vendorId, vendorName) {
    try {
        // Get vendor details from job vendors API
        const response = await fetch(`assets/api/get_job_vendors.php?job_id=${window.currentJobId}`);
        const data = await response.json();

        if (data.success && data.data) {
            // Find vendor info
            const vendor = data.data.find(v => v.id == vendorId);
            if (vendor) {
                // Set hidden fields
                document.getElementById('partialPaymentVendorId').value = vendorId;
                document.getElementById('partialPaymentJobId').value = window.currentJobId;

                // Get estimated amount from final request approval
                const estimatedAmount = parseFloat(vendor.estimated_amount) || 0;

                // Calculate already paid amount from partial payments
                let paidAmount = 0;
                if (vendor.payment_info && vendor.payment_info.total_paid) {
                    paidAmount = parseFloat(vendor.payment_info.total_paid);
                }

                const remainingBalance = estimatedAmount - paidAmount;

                document.getElementById('estimatedAmountDisplay').textContent = `$${estimatedAmount.toFixed(2)}`;
                document.getElementById('remainingBalanceDisplay').textContent = `$${remainingBalance.toFixed(2)}`;

                // Update payment info content
                const paymentInfoContent = document.getElementById('paymentInfoContent');
                paymentInfoContent.innerHTML = `
                    <div class="mt-2">
                        <strong>Vendor:</strong> ${vendorName}<br>
                        <strong>Estimated Amount:</strong> $${estimatedAmount.toFixed(2)}<br>
                        ${paidAmount > 0 ? `<strong>Already Paid:</strong> $${paidAmount.toFixed(2)}<br>` : ''}
                        <strong>Remaining Balance:</strong> $${remainingBalance.toFixed(2)}
                    </div>
                `;

                // Set max amount for input
                const amountInput = document.getElementById('partialPaymentAmount');
                amountInput.max = remainingBalance;
                amountInput.placeholder = `Max: $${remainingBalance.toFixed(2)}`;
            } else {
                showNotification('Vendor not found', 'error');
            }
        } else {
            showNotification('Failed to load vendor data', 'error');
        }
    } catch (error) {
        console.error('Error loading partial payment info:', error);
        showNotification('Failed to load payment information', 'error');
    }
}

function updateRemainingBalance() {
    const amountInput = document.getElementById('partialPaymentAmount');
    const estimatedAmount = parseFloat(document.getElementById('estimatedAmountDisplay').textContent.replace('$', ''));
    const requestedAmount = parseFloat(amountInput.value) || 0;
    const remainingBalance = estimatedAmount - requestedAmount;

    document.getElementById('remainingBalanceDisplay').textContent = `$${remainingBalance.toFixed(2)}`;

    // Change color based on remaining balance
    const remainingDisplay = document.getElementById('remainingBalanceDisplay');
    if (remainingBalance < 0) {
        remainingDisplay.className = 'fw-bold text-danger';
    } else if (remainingBalance === 0) {
        remainingDisplay.className = 'fw-bold text-warning';
    } else {
        remainingDisplay.className = 'fw-bold text-success';
    }
}

async function handlePartialPayment() {
    try {
        const amount = parseFloat(document.getElementById('partialPaymentAmount').value);
        const vendorId = document.getElementById('partialPaymentVendorId').value;
        const jobId = document.getElementById('partialPaymentJobId').value;

        if (!amount || amount <= 0) {
            showNotification('Please enter a valid amount', 'error');
            return;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitPartialPayment');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
        submitBtn.disabled = true;

        const response = await fetch('assets/api/request_partial_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                vendor_id: vendorId,
                job_id: jobId,
                requested_amount: amount
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('partialPaymentModal'));
            if (modal) {
                modal.hide();
            }

            // Reset form
            document.getElementById('partialPaymentForm').reset();

            // Reload vendors and timeline to update status
            loadJobVendors(window.currentJobId);
            loadJobTimeline(window.currentJobId);
            loadPaymentMetrics(window.currentJobId);
        } else {
            showNotification(data.message, 'error');
        }

    } catch (error) {
        console.error('Partial Payment Error:', error);
        showNotification('Failed to submit partial payment request', 'error');
    } finally {
        // Reset button state
        const submitBtn = document.getElementById('submitPartialPayment');
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Request';
        submitBtn.disabled = false;
    }
}

function validateRequestPaymentForm(formData) {
    const paymentPlatform = formData.get('paymentPlatform');

    if (!paymentPlatform) {
        showNotification('Please select a payment platform', 'error');
        return null;
    }

    const paymentData = {
        vendor_id: window.currentVendorId,
        payment_platform: paymentPlatform
    };

    if (paymentPlatform === 'payment_link') {
        const paymentLinkUrl = formData.get('paymentLinkUrl');
        if (!paymentLinkUrl) {
            showNotification('Payment link URL is required', 'error');
            return null;
        }
        paymentData.payment_link_url = paymentLinkUrl;

    } else if (paymentPlatform === 'zelle') {
        const zelleEmailPhone = formData.get('zelleEmailPhone');
        const zelleType = formData.get('zelleType');

        if (!zelleEmailPhone) {
            showNotification('Zelle email/phone is required', 'error');
            return null;
        }
        if (!zelleType) {
            showNotification('Zelle type is required', 'error');
            return null;
        }

        paymentData.zelle_email_phone = zelleEmailPhone;
        paymentData.zelle_type = zelleType;

        if (zelleType === 'business') {
            const businessName = formData.get('businessName');
            if (!businessName) {
                showNotification('Business name is required', 'error');
                return null;
            }
            paymentData.business_name = businessName;

        } else if (zelleType === 'personal') {
            const firstName = formData.get('firstName');
            const lastName = formData.get('lastName');

            if (!firstName) {
                showNotification('First name is required', 'error');
                return null;
            }
            if (!lastName) {
                showNotification('Last name is required', 'error');
                return null;
            }

            paymentData.first_name = firstName;
            paymentData.last_name = lastName;
        }
    }

    return paymentData;
}

// Payment platform toggle functions
function togglePaymentFields() {
    const paymentPlatform = document.querySelector('input[name="paymentPlatform"]:checked');
    if (!paymentPlatform) return;

    hideAllPaymentSections();

    if (paymentPlatform.value === 'payment_link') {
        document.getElementById('paymentLinkSection').style.display = 'block';
    } else if (paymentPlatform.value === 'zelle') {
        document.getElementById('zelleSection').style.display = 'block';
    }
}

function toggleZelleFields() {
    const zelleType = document.getElementById('zelleType').value;

    // Hide all zelle fields
    document.getElementById('businessNameField').style.display = 'none';
    document.getElementById('personalNameFields').style.display = 'none';

    if (zelleType === 'business') {
        document.getElementById('businessNameField').style.display = 'block';
    } else if (zelleType === 'personal') {
        document.getElementById('personalNameFields').style.display = 'block';
    }
}

function hideAllPaymentSections() {
    document.getElementById('paymentLinkSection').style.display = 'none';
    document.getElementById('zelleSection').style.display = 'none';
}

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    let iconClass = 'info-circle';
    if (type === 'success') iconClass = 'check-circle';
    if (type === 'error') iconClass = 'exclamation-circle';

    notification.innerHTML = `
        <i class="bi bi-${iconClass}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Update individual vendor card unread count
async function updateVendorCardUnreadBadge(vendorId) {
    const jobId = getJobIdFromUrl();
    if (!jobId || !vendorId) return;

    try {
        const response = await fetch(`assets/api/get_vendor_unread_messages.php?job_id=${jobId}`);
        const result = await response.json();

        if (result.success && result.data) {
            const unreadCount = result.data[vendorId] || 0;
            const vendorCard = document.querySelector(`[data-vendor-id="${vendorId}"]`);

            if (vendorCard) {
                const unreadBadge = document.getElementById(`vendorUnreadBadge${vendorId}`) || vendorCard.querySelector('.unread-badge');

                if (unreadCount > 0) {
                    if (unreadBadge) {
                        unreadBadge.textContent = unreadCount;
                        unreadBadge.style.display = 'flex';
                    } else {
                        // Create new badge if it doesn't exist
                        const userAvatar = vendorCard.querySelector('.user-avatar');
                        if (userAvatar) {
                            const badge = document.createElement('div');
                            badge.className = 'unread-badge';
                            badge.id = `vendorUnreadBadge${vendorId}`;
                            badge.textContent = unreadCount;
                            userAvatar.appendChild(badge);
                        }
                    }
                } else {
                    if (unreadBadge) {
                        unreadBadge.style.display = 'none';
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error updating vendor card unread badge:', error);
    }
}

// Update header unread message count
async function updateHeaderUnreadCount() {
    try {
        // Call the global function from header.php
        if (typeof updateUnreadMessageCount === 'function') {
            await updateUnreadMessageCount();
        }
    } catch (error) {
        console.error('Error updating header unread count:', error);
    }
}

// Update both header and vendor card unread counts when messages are marked as read
async function markMessagesAsReadAndUpdateCounts(vendorId) {
    try {
        // Update vendor card unread count
        await updateVendorCardUnreadBadge(vendorId);

        // Update header unread count
        await updateHeaderUnreadCount();

    } catch (error) {
        console.error('Error updating unread counts after marking messages as read:', error);
    }
}

// Initialize Show Vendors Modal
function initializeShowVendorsModal() {
    const showVendorsModal = document.getElementById('showVendorsModal');
    if (showVendorsModal) {
        showVendorsModal.addEventListener('show.bs.modal', function() {
            loadAvailableVendorsForModal();
        });
    }

    // Initialize vendor selection functionality
    initializeVendorSelection();
}

// Load available vendors for the modal (vendors not assigned to current job)
async function loadAvailableVendorsForModal() {
    try {

        showVendorsLoading();

        const response = await fetch(`assets/api/get_available_vendors.php?job_id=${window.currentJobId}`);
        const data = await response.json();

        if (data.success) {
            displayAvailableVendorsInModal(data.vendors);
        } else {
            showVendorsError(data.message || 'Failed to load available vendors');
        }
    } catch (error) {
        console.error('Error loading available vendors:', error);
        showVendorsError('Failed to load available vendors');
    }
}

// Load vendors for the modal (existing function - keeping for compatibility)
async function loadVendorsForModal() {
    try {

        // Show loading state
        showVendorsLoading();

        const response = await fetch(`assets/api/get_job_vendors.php?job_id=${window.currentJobId}`);
        const result = await response.json();

        if (result.success) {
            displayVendorsInModal(result.data);
        } else {
            console.error('API Error:', result.message);
            showVendorsError(result.message || 'Failed to load vendors');
        }
    } catch (error) {
        console.error('Error loading vendors for modal:', error);
        showVendorsError('Network error. Please try again.');
    }
}

// Display available vendors in modal with selection checkboxes
function displayAvailableVendorsInModal(vendors) {
    console.log('Display available vendors in modal called with:', vendors);

    const loading = document.getElementById('vendorsLoading');
    const grid = document.getElementById('availableVendorsGrid');
    const emptyState = document.getElementById('vendorsEmptyState');
    const selectionControls = document.getElementById('vendorSelectionControls');

    console.log('DOM elements found:', {
        loading,
        grid,
        emptyState,
        selectionControls
    });

    if (loading) loading.style.display = 'none';

    if (vendors && vendors.length > 0) {
        if (selectionControls) selectionControls.style.display = 'block';
        if (emptyState) emptyState.style.display = 'none';

        if (grid) {
            grid.innerHTML = '';
            vendors.forEach(vendor => {
                const vendorCard = createAvailableVendorCard(vendor);
                grid.appendChild(vendorCard);
            });
            grid.style.display = 'block';
        }
    } else {
        if (selectionControls) selectionControls.style.display = 'none';
        if (grid) grid.style.display = 'none';
        if (emptyState) emptyState.style.display = 'block';
    }
}

// Create vendor card with checkbox for selection
function createAvailableVendorCard(vendor) {
    const card = document.createElement('div');
    card.className = 'vendor-card available-vendor-card';
    card.setAttribute('data-vendor-id', vendor.id);

    const quoteAmount = vendor.quote_type === 'paid_quote' && vendor.quote_amount ?
        `$${parseFloat(vendor.quote_amount).toFixed(2)}` :
        'Free Quote';

    card.innerHTML = `
        <div class="vendor-card-header">
            <div class="d-flex align-items-center">
                <input type="checkbox" class="form-check-input vendor-checkbox me-2" 
                       id="vendor_${vendor.id}" value="${vendor.id}">
                <label for="vendor_${vendor.id}" class="vendor-name fw-bold">${escapeHtml(vendor.vendor_name)}</label>
            </div>
        </div>
        <div class="vendor-card-body">
            <div class="vendor-info">
                <div class="info-item">
                    <i class="bi bi-telephone"></i>
                    <span>${vendor.phone || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <i class="bi bi-currency-dollar"></i>
                    <span>${quoteAmount}</span>
                </div>
                ${vendor.location ? `
                <div class="info-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>${escapeHtml(vendor.location)}</span>
                </div>
                ` : ''}
            </div>
        </div>
    `;

    return card;
}

// Show loading state for vendors modal
function showVendorsLoading() {
    const loading = document.getElementById('vendorsLoading');
    const grid = document.getElementById('availableVendorsGrid');
    const emptyState = document.getElementById('vendorsEmptyState');
    const selectionControls = document.getElementById('vendorSelectionControls');

    if (loading) loading.style.display = 'block';
    if (grid) grid.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';
    if (selectionControls) selectionControls.style.display = 'none';
}

// Show error state for vendors modal
function showVendorsError(message) {
    const loading = document.getElementById('vendorsLoading');
    const grid = document.getElementById('modalVendorsGrid');
    const emptyState = document.getElementById('vendorsEmptyState');

    if (loading) loading.style.display = 'none';
    if (grid) grid.style.display = 'none';
    if (emptyState) {
        emptyState.style.display = 'block';
        emptyState.innerHTML = `
            <div class="empty-state-content">
                <div class="empty-state-icon">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
                <h3>Error Loading Vendors</h3>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="loadVendorsForModal()">
                    <i class="bi bi-arrow-clockwise"></i> Try Again
                </button>
            </div>
        `;
    }
}

// Display vendors in modal
function displayVendorsInModal(vendors) {
    console.log('Display vendors in modal called with:', vendors);

    const loading = document.getElementById('vendorsLoading');
    const grid = document.getElementById('modalVendorsGrid');
    const emptyState = document.getElementById('vendorsEmptyState');

    console.log('DOM elements found:', {
        loading,
        grid,
        emptyState
    });

    if (loading) loading.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';

    if (!vendors || vendors.length === 0) {
        console.log('No vendors found, showing empty state');
        if (emptyState) emptyState.style.display = 'block';
        return;
    }

    console.log('Displaying', vendors.length, 'vendors');
    if (grid) {
        grid.style.display = 'block';
        const vendorCards = vendors.map(vendor => createVendorModalCard(vendor));
        console.log('Generated vendor cards:', vendorCards);
        grid.innerHTML = vendorCards.join('');
    }
}

// Create vendor card for modal
function createVendorModalCard(vendor) {
    const statusClass = getStatusClass(vendor.status);
    const statusText = getStatusText(vendor.status);

    return `
        <div class="vendor-card" data-vendor-id="${vendor.id}">
            <div class="vendor-header">
                <div class="vendor-title-section">
                    <h5 class="vendor-name">${escapeHtml(vendor.vendor_name)}</h5>
                </div>
                <div class="vendor-actions">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            
            <div class="vendor-details">
                <p><i class="bi bi-telephone"></i> ${escapeHtml(vendor.phone)}</p>
                ${vendor.location ? `<p><i class="bi bi-geo-alt"></i> ${escapeHtml(vendor.location)}</p>` : ''}
                <p><i class="bi bi-briefcase"></i> ${escapeHtml(vendor.vendor_platform || 'Not specified')}</p>
                <p><i class="bi bi-currency-dollar"></i> ${vendor.quote_display}</p>
                ${vendor.appointment_date_time ? `<p><i class="bi bi-calendar"></i> ${formatDateTime(vendor.appointment_date_time)}</p>` : ''}
            </div>
            
            <div class="vendor-footer">
                <small>
                    <i class="bi bi-clock"></i> Added ${vendor.created_ago}
                    ${vendor.updated_at !== vendor.created_at ? `<br><i class="bi bi-arrow-clockwise"></i> Updated ${getTimeAgo(vendor.updated_at)}` : ''}
                </small>
            </div>
        </div>
    `;
}

// Helper function to get status class
function getStatusClass(status) {
    const statusClasses = {
        'added': 'status-added',
        'visit_requested': 'status-visit_requested',
        'job_completed': 'status-job_completed',
        'requested_vendor_payment': 'status-payment_requested'
    };
    return statusClasses[status] || 'status-added';
}

// Helper function to get status text
function getStatusText(status) {
    const statusTexts = {
        'added': 'Added',
        'visit_requested': 'Visit Requested',
        'job_completed': 'Completed',
        'requested_vendor_payment': 'Payment Requested'
    };
    return statusTexts[status] || 'Added';
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function to format date time
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Comments functionality
let currentJobId = null;

// Load comments
async function loadComments(jobId) {
    currentJobId = jobId;
    const commentsList = document.getElementById('commentsList');
    const commentsCount = document.getElementById('commentsCount');
    const noCommentsPlaceholder = document.getElementById('noCommentsPlaceholder');

    if (!commentsList) return; // Exit if comments section doesn't exist

    try {
        const response = await fetch(`assets/api/get_job_comments.php?job_id=${jobId}`);
        const data = await response.json();

        if (data.success) {
            if (data.comments.length === 0) {
                commentsList.innerHTML = '';
                if (noCommentsPlaceholder) noCommentsPlaceholder.style.display = 'block';
                if (commentsCount) commentsCount.textContent = '0';
            } else {
                if (noCommentsPlaceholder) noCommentsPlaceholder.style.display = 'none';
                commentsList.innerHTML = data.comments.map(comment => createCommentHTML(comment)).join('');
                if (commentsCount) commentsCount.textContent = data.comments.length;
            }
        } else {
            throw new Error(data.message || 'Failed to load comments');
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        commentsList.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Failed to load comments</p>
            </div>
        `;
    }
}

// Create comment HTML
function createCommentHTML(comment) {
    const avatarText = comment.user_name.charAt(0).toUpperCase();
    const roleClass = comment.user_role === 'admin' ? 'admin' : 'user';

    return `
        <div class="comment-item">
            <div class="comment-avatar user-role-${comment.user_role}">
                ${avatarText}
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <span class="comment-author">${comment.user_name}</span>
                    <span class="comment-role ${roleClass}">${comment.user_role.toUpperCase()}</span>
                    <span class="comment-time">${comment.time_ago}</span>
                </div>
                <div class="comment-text">${escapeHtml(comment.comment)}</div>
            </div>
        </div>
    `;
}

// Initialize Comments functionality
function initializeComments() {
    const commentTextarea = document.getElementById('commentTextarea');
    const addCommentBtn = document.getElementById('addCommentBtn');
    const commentCharCount = document.getElementById('commentCharCount');

    if (!commentTextarea || !addCommentBtn) return;

    // Character count update
    commentTextarea.addEventListener('input', function() {
        const length = this.value.length;
        commentCharCount.textContent = length;

        // Update button state
        addCommentBtn.disabled = length === 0 || length > 1000;
    });

    // Add comment button click
    addCommentBtn.addEventListener('click', function() {
        addComment();
    });

    // Enter key to submit (Ctrl+Enter)
    commentTextarea.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            addComment();
        }
    });
}

// Add comment
async function addComment() {
    const commentTextarea = document.getElementById('commentTextarea');
    const addCommentBtn = document.getElementById('addCommentBtn');

    if (!commentTextarea || !addCommentBtn) return;

    const comment = commentTextarea.value.trim();
    if (!comment || !currentJobId) return;

    // Disable button during submission
    addCommentBtn.disabled = true;
    addCommentBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';

    try {
        const response = await fetch('assets/api/add_job_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                job_id: currentJobId,
                comment: comment
            })
        });

        const data = await response.json();

        if (data.success) {
            // Clear textarea
            commentTextarea.value = '';
            document.getElementById('commentCharCount').textContent = '0';

            // Reload comments
            await loadComments(currentJobId);

            // Show success notification
            showNotification('Comment added successfully', 'success');
        } else {
            throw new Error(data.message || 'Failed to add comment');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showNotification('Failed to add comment. Please try again.', 'error');
    } finally {
        // Re-enable button
        addCommentBtn.disabled = false;
        addCommentBtn.innerHTML = '<i class="bi bi-send"></i> Add Comment';
    }
}

// Show Notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';

    notification.innerHTML = `
        <i class="bi bi-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Edit Vendor Functionality
async function editVendor(vendorId) {
    try {
        // Fetch vendor data
        const response = await fetch(`assets/api/get_vendor_details.php?vendor_id=${vendorId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch vendor details');
        }

        const vendor = data.vendor;

        // Populate edit form
        document.getElementById('editVendorId').value = vendor.id;
        document.getElementById('editVendorName').value = vendor.vendor_name;
        document.getElementById('editVendorPhone').value = vendor.phone;
        document.getElementById('editQuoteType').value = vendor.quote_type;
        document.getElementById('editVendorPlatform').value = vendor.vendor_platform || '';
        document.getElementById('editVendorLocation').value = vendor.location || '';

        // Handle quote amount
        if (vendor.quote_type === 'paid_quote' && vendor.quote_amount > 0) {
            document.getElementById('editQuoteAmount').value = vendor.quote_amount;
            toggleEditQuoteAmount();
        }

        // Format appointment datetime for input
        const appointmentDate = new Date(vendor.appointment_date_time);
        const formattedDate = appointmentDate.toISOString().slice(0, 16);
        document.getElementById('editAppointmentDateTime').value = formattedDate;

        // Show modal
        const editModal = new bootstrap.Modal(document.getElementById('editVendorModal'));
        editModal.show();

    } catch (error) {
        console.error('Error loading vendor details:', error);
        showNotification('Failed to load vendor details', 'error');
    }
}

// Toggle edit quote amount section
function toggleEditQuoteAmount() {
    const quoteType = document.getElementById('editQuoteType').value;
    const amountSection = document.getElementById('editQuoteAmountSection');

    if (quoteType === 'paid_quote') {
        amountSection.style.display = 'block';
        document.getElementById('editQuoteAmount').required = true;
    } else {
        amountSection.style.display = 'none';
        document.getElementById('editQuoteAmount').required = false;
        document.getElementById('editQuoteAmount').value = '';
    }
}

// Initialize edit vendor form
function initializeEditVendorForm() {
    const submitBtn = document.getElementById('submitEditVendor');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitEditVendor);
    }
}

// Submit edit vendor
async function submitEditVendor() {
    const form = document.getElementById('editVendorForm');
    const submitBtn = document.getElementById('submitEditVendor');

    if (!form || !submitBtn) return;

    const formData = new FormData(form);
    const data = {
        vendor_id: formData.get('vendorId'),
        vendor_name: formData.get('vendorName'),
        phone: formData.get('vendorPhone'),
        quote_type: formData.get('quoteType'),
        quote_amount: parseFloat(formData.get('quoteAmount')) || 0,
        vendor_platform: formData.get('vendorPlatform'),
        location: formData.get('vendorLocation'),
        appointment_date_time: formData.get('appointmentDateTime')
    };

    // Validate required fields
    if (!data.vendor_name || !data.phone || !data.quote_type || !data.appointment_date_time) {
        showNotification('Please fill all required fields', 'error');
        return;
    }

    // Disable button during submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';

    try {
        const response = await fetch('assets/api/edit_vendor.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Vendor updated successfully', 'success');

            // Close modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editVendorModal'));
            editModal.hide();

            // Reload vendors
            await loadJobVendors(window.currentJobId);
            loadPaymentMetrics(window.currentJobId);

        } else {
            throw new Error(result.message || 'Failed to update vendor');
        }

    } catch (error) {
        console.error('Error updating vendor:', error);
        showNotification(error.message || 'Failed to update vendor', 'error');
    } finally {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Update Vendor';
    }
}

// Initialize vendor selection functionality
function initializeVendorSelection() {
    // Select All checkbox
    const selectAllCheckbox = document.getElementById('selectAllVendors');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const vendorCheckboxes = document.querySelectorAll('.vendor-checkbox');
            vendorCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateVendorSelectionUI();
        });
    }

    // Clear selection button
    const clearButton = document.getElementById('clearVendorSelection');
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            clearVendorSelection();
        });
    }

    // Add selected vendors button
    const addSelectedButton = document.getElementById('addSelectedVendors');
    if (addSelectedButton) {
        addSelectedButton.addEventListener('click', function() {
            addSelectedVendorsToJob();
        });
    }

    // Individual vendor checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('vendor-checkbox')) {
            updateVendorSelectionUI();
        }
    });
}

// Update vendor selection UI
function updateVendorSelectionUI() {
    const vendorCheckboxes = document.querySelectorAll('.vendor-checkbox');
    const selectedCheckboxes = document.querySelectorAll('.vendor-checkbox:checked');
    const selectAllCheckbox = document.getElementById('selectAllVendors');
    const selectedCount = selectedCheckboxes.length;

    // Update select all checkbox state
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = selectedCount === vendorCheckboxes.length && vendorCheckboxes.length > 0;
        selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < vendorCheckboxes.length;
    }

    // Update selected count
    const selectedCountBadge = document.getElementById('selectedVendorsCount');
    const selectedCountSpan = document.getElementById('selectedCount');
    const addSelectedButton = document.getElementById('addSelectedVendors');

    if (selectedCountBadge) {
        selectedCountBadge.textContent = `${selectedCount} selected`;
    }

    if (selectedCountSpan) {
        selectedCountSpan.textContent = selectedCount;
    }

    if (addSelectedButton) {
        addSelectedButton.style.display = selectedCount > 0 ? 'inline-block' : 'none';
    }
}

// Clear vendor selection
function clearVendorSelection() {
    const vendorCheckboxes = document.querySelectorAll('.vendor-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllVendors');

    vendorCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    updateVendorSelectionUI();
}

// Add selected vendors to job
async function addSelectedVendorsToJob() {
    const selectedCheckboxes = document.querySelectorAll('.vendor-checkbox:checked');
    const selectedVendorIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);

    if (selectedVendorIds.length === 0) {
        showNotification('Please select at least one vendor to add.', 'warning');
        return;
    }

    try {
        const response = await fetch('assets/api/bulk_add_vendors.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                job_id: window.currentJobId,
                vendor_ids: selectedVendorIds
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');

            // Clear selection
            clearVendorSelection();

            // Reload available vendors
            await loadAvailableVendorsForModal();

            // Reload job vendors in main page
            await loadJobVendors();

            // Close modal after a short delay
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('showVendorsModal'));
                if (modal) {
                    modal.hide();
                }
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to add vendors', 'error');
        }
    } catch (error) {
        console.error('Error adding vendors:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

// View Image in Modal
function viewImage(imagePath, imageName) {
    // Fix image path for user panel
    let correctedPath = imagePath;

    // Remove extra path prefixes for user panel
    if (correctedPath.startsWith('../../../uploads/')) {
        correctedPath = correctedPath.replace('../../../uploads/', 'uploads/');
    } else if (correctedPath.startsWith('../../uploads/')) {
        correctedPath = correctedPath.replace('../../uploads/', 'uploads/');
    } else if (correctedPath.startsWith('../uploads/')) {
        correctedPath = correctedPath.replace('../uploads/', 'uploads/');
    } else if (correctedPath.startsWith('uploads/')) {
        // Already correct
    }

    console.log('Original image path:', imagePath);
    console.log('Corrected image path:', correctedPath);

    // Create modal for image viewing
    const modalHtml = `
        <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${imageName}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${correctedPath}" alt="${imageName}" class="img-fluid" 
                             onerror="console.error('Failed to load image:', '${correctedPath}'); this.style.display='none'; this.parentNode.innerHTML='<div class=&quot;alert alert-warning&quot;><i class=&quot;bi bi-exclamation-triangle&quot;></i> Image not found: ${imageName}</div>';">
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('imageViewModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
    modal.show();

    // Remove modal when hidden
    document.getElementById('imageViewModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Download File
function downloadFile(filePath, fileName) {
    const link = document.createElement('a');
    link.href = filePath;
    link.download = fileName;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Load Payment Metrics
async function loadPaymentMetrics(jobId) {
    try {
        const response = await fetch(`assets/api/get_job_vendors.php?job_id=${jobId}`);
        const data = await response.json();

        if (data.success && data.data) {
            let totalEstimated = 0;
            let totalPaid = 0;
            let totalRemaining = 0;

            // Calculate totals from all vendors
            data.data.forEach(vendor => {
                if (vendor.estimated_amount > 0) {
                    totalEstimated += parseFloat(vendor.estimated_amount);

                    // Check if vendor has payment info (try both paths for compatibility)
                    let vendorPaid = 0;
                    if (vendor.total_paid && vendor.total_paid > 0) {
                        vendorPaid = parseFloat(vendor.total_paid);
                    } else if (vendor.action_button && vendor.action_button.payment_info && vendor.action_button.payment_info.total_paid) {
                        vendorPaid = parseFloat(vendor.action_button.payment_info.total_paid);
                    }
                    totalPaid += vendorPaid;
                }
            });

            totalRemaining = totalEstimated - totalPaid;

            // Update metric values (always show, even if 0)
            document.getElementById('estimatedAmount').textContent = `$${totalEstimated.toFixed(2)}`;
            document.getElementById('totalPaid').textContent = `$${totalPaid.toFixed(2)}`;
            document.getElementById('remainingBalance').textContent = `$${totalRemaining.toFixed(2)}`;

            // Update colors based on values
            const remainingElement = document.getElementById('remainingBalance');
            if (totalRemaining <= 0) {
                remainingElement.className = 'metric-status text-success';
            } else if (totalRemaining < totalEstimated * 0.5) {
                remainingElement.className = 'metric-status text-warning';
            } else {
                remainingElement.className = 'metric-status text-danger';
            }
        }
    } catch (error) {
        console.error('Error loading payment metrics:', error);
        // Show 0 values on error
        document.getElementById('estimatedAmount').textContent = '$0.00';
        document.getElementById('totalPaid').textContent = '$0.00';
        document.getElementById('remainingBalance').textContent = '$0.00';
    }
}