// Get job ID from URL
function getJobIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
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

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

// Update Status Actions
function updateStatusActions(pendingNotification) {
    const statusActions = document.getElementById('statusActions');
    const actionInfo = document.getElementById('actionInfo');
    const viewFormBtn = document.getElementById('viewFormBtn');
    const statusIcon = document.getElementById('statusIcon');

    if (!statusActions) return;

    if (pendingNotification) {
        // Show action buttons if action required OR if notification has forms
        const hasActionRequired = pendingNotification.action_required == 1;
        const hasForm = ['request_vendor_payment', 'final_visit_request', 'job_completed'].includes(pendingNotification.type);

        if (hasActionRequired || hasForm) {
            statusActions.style.display = 'block';

            // Show/hide View Form button based on notification type
            if (viewFormBtn) {
                viewFormBtn.style.display = hasForm ? 'inline-block' : 'none';
            }

            // Show/hide Accept/Reject buttons only if action required
            const acceptBtn = document.getElementById('acceptBtn');
            const rejectBtn = document.getElementById('rejectBtn');
            if (acceptBtn) acceptBtn.style.display = hasActionRequired ? 'inline-block' : 'none';
            if (rejectBtn) rejectBtn.style.display = hasActionRequired ? 'inline-block' : 'none';
        }

        // Update status icon based on notification type
        if (statusIcon) {
            let iconClass = 'bi-info-circle ';
            switch (pendingNotification.type) {
                case 'request_vendor_payment':
                    iconClass = 'bi-currency-dollar';
                    break;
                case 'final_visit_request':
                    iconClass = 'bi-check-circle';
                    break;
                case 'visit_request':
                    iconClass = 'bi-person-check';
                    break;
                case 'job_completed':
                    iconClass = 'bi-check2-all';
                    break;
                case 'partial_payment_requested':
                    iconClass = 'bi-cash-stack';
                    break;
                case 'partial_payment_accepted':
                    iconClass = 'bi-check-circle';
                    break;
                case 'partial_payment_rejected':
                    iconClass = 'bi-x-circle';
                    break;
                default:
                    iconClass = 'bi-info-circle';
            }
            statusIcon.className = iconClass;
        }

        // Update action info based on notification type
        let infoText = '';
        switch (pendingNotification.type) {
            case 'request_vendor_payment':
                infoText = `Payment request from ${pendingNotification.user_name || 'User'}`;
                break;
            case 'final_visit_request':
                infoText = `Final visit request from ${pendingNotification.user_name || 'User'}`;
                break;
            case 'visit_request':
                infoText = `Visit request from ${pendingNotification.vendor_name || 'Vendor'}`;
                break;
            case 'job_completed':
                if (hasActionRequired) {
                    infoText = `Job completion from ${pendingNotification.vendor_name || 'Vendor'}`;
                } else {
                    infoText = `Job completed by ${pendingNotification.vendor_name || 'Vendor'} - View completion details`;
                }
                break;
            case 'partial_payment_requested':
                infoText = `Partial payment request from ${pendingNotification.user_name || 'User'}`;
                break;
            case 'partial_payment_accepted':
                infoText = `Partial payment accepted for ${pendingNotification.vendor_name || 'Vendor'}`;
                break;
            case 'partial_payment_rejected':
                infoText = `Partial payment rejected for ${pendingNotification.vendor_name || 'Vendor'}`;
                break;
            default:
                infoText = hasActionRequired ? 'Action required for this job' : 'View details';
        }

        if (actionInfo) {
            actionInfo.textContent = infoText;
        }

        // Store notification ID for action handling
        window.currentPendingNotification = pendingNotification;

    } else {
        // Hide action buttons and reset icon
        statusActions.style.display = 'none';
        if (statusIcon) {
            statusIcon.className = 'bi-info-circle';
        }
        if (actionInfo) {
            actionInfo.textContent = 'No pending actions';
        }
        window.currentPendingNotification = null;
    }
}

// Handle View Form
async function handleViewForm() {
    if (!window.currentPendingNotification) {
        showNotification('No pending notification found', 'error');
        return;
    }

    const notification = window.currentPendingNotification;
    let modalType = '';

    // Determine modal type based on notification type
    switch (notification.type) {
        case 'request_vendor_payment':
            modalType = 'paymentRequestModal';
            break;
        case 'final_visit_request':
            modalType = 'finalVisitRequestModal';
            break;
        case 'job_completed':
            modalType = 'jobCompletedModal';
            break;
        default:
            showNotification('No form available for this notification type', 'error');
            return;
    }

    try {
        // Load form data using GET parameters
        const response = await fetch(`assets/api/get_form_data.php?notification_id=${notification.id}&type=${modalType}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('API Response:', data); // Debug log

        if (data.success && data.data) {
            // Populate modal
            populateModal(modalType, data.data);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById(modalType));
            modal.show();
        } else {
            throw new Error(data.message || 'Failed to load form data');
        }
    } catch (error) {
        console.error('Error loading form data:', error);
        showNotification(error.message || 'Failed to load form data', 'error');
    }
}

// Populate Modal Content
function populateModal(modalType, formData) {
    const modalBody = document.getElementById(`${modalType}Body`);

    switch (modalType) {
        case 'paymentRequestModal':
            modalBody.innerHTML = createPaymentFormHTML(formData);
            break;
        case 'finalVisitRequestModal':
            modalBody.innerHTML = createFinalVisitFormHTML(formData);
            break;
        case 'jobCompletedModal':
            modalBody.innerHTML = createJobCompletedFormHTML(formData);
            break;
    }
}

// Create Payment Form HTML
function createPaymentFormHTML(data) {
    // Determine payment details based on platform
    let paymentDetails = '';
    let paymentContact = '';
    let paymentIcon = '';

    if (data.payment_platform === 'payment_link_invoice') {
        paymentDetails = data.payment_link_invoice_url || 'N/A';
        paymentContact = 'Payment Link';
        paymentIcon = 'bi-link-45deg';
    } else if (data.payment_platform === 'zelle') {
        paymentDetails = data.zelle_email_phone || 'N/A';
        paymentContact = 'Zelle Email/Phone';
        paymentIcon = 'bi-phone';
    } else {
        paymentIcon = 'bi-credit-card';
    }

    // Use processed data from API
    let contactPerson = data.contact_person || 'N/A';
    let businessName = data.business_name || 'N/A';

    return `
        <div class="form-details-container">
            <!-- Payment Request Information -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-credit-card text-primary"></i> Payment Request Information
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-credit-card"></i> Payment Platform
                                </label>
                                <div class="detail-value">
                                    <span class="badge bg-primary">${data.payment_platform || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="${paymentIcon}"></i> ${paymentContact}
                                </label>
                                <div class="detail-value">
                                    ${paymentDetails && paymentDetails !== 'N/A' && paymentDetails.trim() !== '' && paymentDetails.trim() !== 'null' ?
            (data.payment_platform === 'payment_link_invoice' ?
                `<div class="payment-link-container">
                                            <a href="${paymentDetails}" target="_blank" class="payment-link-btn">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                                View Payment Link
                                            </a>
                                            <small class="text-muted d-block mt-1 payment-link-url">${paymentDetails}</small>
                                        </div>` :
                `<div class="payment-info-container">
                                            <span class="payment-info-text">${paymentDetails}</span>
                                        </div>`) :
            'N/A'
        }
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-shop"></i> Business/Type
                                </label>
                                <div class="detail-value">${businessName}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-person"></i> Contact Person
                                </label>
                                <div class="detail-value">${contactPerson}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person-circle"></i> Vendor Information
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-person"></i> Vendor Name
                                </label>
                                <div class="detail-value">${data.vendor_name || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-telephone"></i> Vendor Phone
                                </label>
                                <div class="detail-value">
                                    ${data.vendor_phone ?
            `<a href="tel:${data.vendor_phone}" class="text-decoration-none text-dark">
                                            ${data.vendor_phone}
                                        </a>` :
            'N/A'
        }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Request Details
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <label class="detail-label">
                            <i class="bi bi-calendar"></i> Request Date
                        </label>
                        <div class="detail-value">
                            ${data.created_at ? new Date(data.created_at).toLocaleString() : 'N/A'}
                        </div>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">
                            <i class="bi bi-clock"></i> Time Since Request
                        </label>
                        <div class="detail-value">
                            ${data.created_at ? getTimeAgo(data.created_at) : 'N/A'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper function for time ago
function getTimeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < 60) {
        return `${diffInSeconds} seconds ago`;
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days !== 1 ? 's' : ''} ago`;
    }
}

// Create Final Visit Form HTML
function createFinalVisitFormHTML(data) {
    return `
        <div class="form-details-container">
            <!-- Vendor Information Card -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-person-circle"></i> Vendor Information
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-person"></i> Vendor Name
                                </label>
                                <div class="detail-value">${data.vendor_name || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-telephone"></i> Phone Number
                                </label>
                                <div class="detail-value">${data.vendor_phone || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details Card -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-event "></i> Request Details
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-currency-dollar"></i> Estimated Amount
                                </label>
                                <div class="detail-value amount">$${data.estimated_amount || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-credit-card"></i> Payment Mode
                                </label>
                                <div class="detail-value">${data.payment_mode || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-calendar-check"></i> Visit Date & Time
                                </label>
                                <div class="detail-value">${data.visit_date_time || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-flag"></i> Status
                                </label>
                                <div class="detail-value">
                                    <span class="badge ${data.status === 'accepted' ? 'bg-success' : data.status === 'rejected' ? 'bg-danger' : 'bg-warning'} fs-6">
                                        <i class="bi bi-${data.status === 'accepted' ? 'check-circle' : data.status === 'rejected' ? 'x-circle' : 'clock'}"></i>
                                        ${data.status ? data.status.toUpperCase() : 'PENDING'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-info"></i> Additional Information
                    </h6>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <label class="detail-label">
                            <i class="bi bi-file-text"></i> Additional Notes
                        </label>
                        <div class="detail-value notes">${data.additional_notes || 'No notes provided'}</div>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">
                            <i class="bi bi-clock-history"></i> Request Date
                        </label>
                        <div class="detail-value">${data.created_at || 'N/A'}</div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Create Job Completed Form HTML
function createJobCompletedFormHTML(data) {
    // Format created date
    const createdDate = data.created_at ? new Date(data.created_at).toLocaleString() : 'N/A';

    // Separate attachments by type
    const pictures = data.attachments ? data.attachments.filter(att => att.attachment_type === 'pictures') : [];
    const invoices = data.attachments ? data.attachments.filter(att => att.attachment_type === 'invoices') : [];

    // Generate pictures HTML
    let picturesHTML = '';
    if (pictures.length > 0) {
        picturesHTML = `
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-images text-primary"></i> Job Pictures</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row g-3">
                        ${pictures.map(picture => `
                            <div class="col-md-4 col-lg-3">
                                <div class="attachment-item p-2 border rounded shadow-sm" style="cursor: pointer;" onclick="window.open('../${picture.attachment_path}', '_blank')">
                                    <img src="../${picture.attachment_path}" alt="Job Picture" class="attachment-thumbnail"
                                        style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; transition: transform 0.3s ease;"
                                        onmouseover="this.style.transform='scale(1.05)'" 
                                        onmouseout="this.style.transform='scale(1)'"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="attachment-icon d-none align-items-center justify-content-center" 
                                        style="width: 100%; height: 120px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                                        <i class="bi bi-image text-primary" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    // Generate invoices HTML
    let invoicesHTML = '';
    if (invoices.length > 0) {
        invoicesHTML = `
            <div class="detail-card">
                <div class="detail-card-header">
                    <h6><i class="bi bi-receipt text-success"></i> Invoice Documents</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row g-3">
                        ${invoices.map(invoice => `
                            <div class="col-md-4 col-lg-3">
                                <div class="attachment-item p-3 border rounded shadow-sm text-center" style="cursor: pointer; transition: all 0.3s ease;" 
                                    onclick="window.open('../${invoice.attachment_path}', '_blank')"
                                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">
                                    <div class="attachment-icon d-flex align-items-center justify-content-center mx-auto" 
                                        style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    return `
        <div class="form-details-container">
            <!-- Job Completion Information -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-check-circle"></i> Job Completion Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-calendar"></i> Completion Date
                                </label>
                                <div class="detail-value">${createdDate}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-person"></i> Vendor Name
                                </label>
                                <div class="detail-value">${data.vendor_name || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-file-text"></i> Completion Notes
                                </label>
                                <div class="detail-value notes">${data.completion_notes || 'No completion notes provided'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- W9 Information -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-file-earmark-text"></i> W9 Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-building"></i> Business Name
                                </label>
                                <div class="detail-value">${data.w9_vendor_business_name || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-card-text"></i> EIN/SSN
                                </label>
                                <div class="detail-value">${data.w9_ein_ssn || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-geo-alt"></i> Address
                                </label>
                                <div class="detail-value">${data.w9_address || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-diagram-3"></i> Entity Type
                                </label>
                                <div class="detail-value">${data.w9_entity_type || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-telephone"></i> Vendor Phone
                                </label>
                                <div class="detail-value">${data.vendor_phone || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Pictures -->
            ${picturesHTML}
            
            <!-- Invoice Documents -->
            ${invoicesHTML}
        </div>
    `;
}

// Handle Notification Action
async function handleNotificationAction(action) {
    if (!window.currentPendingNotification) {
        showNotification('No pending notification found', 'error');
        return;
    }

    const notification = window.currentPendingNotification;
    const actionBtn = document.getElementById(`${action}Btn`);
    const otherBtn = document.getElementById(`${action === 'accept' ? 'reject' : 'accept'}Btn`);
    const originalText = actionBtn.innerHTML;

    // Show loading state on clicked button
    actionBtn.disabled = true;
    actionBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

    // Disable other button
    if (otherBtn) {
        otherBtn.disabled = true;
    }

    try {
        const response = await fetch('assets/api/notification_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notification.id,
                action: action
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');

            // Update button to show success state
            actionBtn.innerHTML = `<i class="bi bi-check-circle"></i> ${action === 'accept' ? 'Accepted' : 'Rejected'}`;
            actionBtn.className = `btn btn-sm ${action === 'accept' ? 'btn-success' : 'btn-danger'}`;

            // Hide the entire status actions section after a delay
            setTimeout(() => {
                const statusActions = document.getElementById('statusActions');
                if (statusActions) {
                    statusActions.style.display = 'none';
                }

                // Update workflow status in the card
                const workflowStatus = document.getElementById('workflowStatus');
                if (workflowStatus) {
                    if (action === 'accept') {
                        workflowStatus.textContent = 'Payment Accepted';
                        workflowStatus.className = 'metric-status text-success';
                    } else {
                        workflowStatus.textContent = 'Payment Rejected';
                        workflowStatus.className = 'metric-status text-danger';
                    }
                }
            }, 1500);

        } else {
            throw new Error(data.message || 'Failed to process action');
        }
    } catch (error) {
        console.error('Error handling notification action:', error);
        showNotification(error.message || 'Failed to process action', 'error');

        // Reset button on error
        actionBtn.disabled = false;
        actionBtn.innerHTML = originalText;
        if (otherBtn) {
            otherBtn.disabled = false;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Get job ID from URL
    const jobId = getJobIdFromUrl();

    if (!jobId) {
        showError('Job ID not found in URL');
        return;
    }

    // Load job details
    loadJobDetails(jobId);
});

// Load Job Details
async function loadJobDetails(jobId) {
    try {
        const response = await fetch(`assets/api/get_job_view_details.php?job_id=${jobId}`);
        const data = await response.json();

        if (data.success) {
            const jobData = data.data;
            displayJobDetails(jobData);
        } else {
            showError(data.message || 'Failed to load job details');
        }
    } catch (error) {
        console.error('Error loading job details:', error);
        showError('An error occurred while loading job details');
    }
}

// Display Job Details
function displayJobDetails(jobData) {
    const {
        job,
        pictures,
        vendors,
        timeline,
        workflow_status,
        pending_notification
    } = jobData;

    // Update welcome banner
    updateWelcomeBanner(job);

    // Update metrics cards
    updateMetricsCards(job, workflow_status, pending_notification);

    // Update job description
    updateJobDescription(job);

    // Update attached files
    updateAttachedFiles(pictures);

    // Update assigned vendors
    updateAssignedVendors(vendors);

    // Update timeline
    updateTimeline(timeline);

    // Load comments
    loadComments(job.id);
}

// Update Welcome Banner
function updateWelcomeBanner(job) {
    const jobTitle = document.getElementById('jobTitle');
    const jobSubtitle = document.getElementById('jobSubtitle');

    if (jobTitle) {
        jobTitle.textContent = `${job.store_name} - JOB-${job.id}`;
    }

    if (jobSubtitle) {
        const statusBadge = getStatusBadge(job.status);
        const timeAgo = getTimeAgo(job.created_at);
        jobSubtitle.innerHTML = `<span class="badge ${statusBadge.class}">${statusBadge.text}</span> • Created ${timeAgo}`;
    }
}

// Update Metrics Cards
function updateMetricsCards(job, workflowStatus, pendingNotification) {
    // SLA Deadline
    const slaDeadline = document.getElementById('slaDeadline');
    if (slaDeadline) {
        if (job.job_sla) {
            const slaDate = new Date(job.job_sla);
            const now = new Date();
            const isOverdue = slaDate < now;

            slaDeadline.textContent = formatDateTime(job.job_sla);
            slaDeadline.className = `metric-status ${isOverdue ? 'text-danger' : 'text-success'}`;
        } else {
            slaDeadline.textContent = 'No SLA set';
            slaDeadline.className = 'metric-status text-muted';
        }
    }

    // Workflow Status (now in separate card)
    const workflowStatusEl = document.getElementById('workflowStatus');
    if (workflowStatusEl) {
        workflowStatusEl.textContent = workflowStatus;
        workflowStatusEl.className = 'metric-status';
    }

    // Handle pending notification actions
    updateStatusActions(pendingNotification);

    // Load payment metrics
    loadPaymentMetrics(job.id);
}

// Update Job Description
function updateJobDescription(job) {
    // Store Information
    const storeName = document.getElementById('storeName');
    const storeAddress = document.getElementById('storeAddress');
    const jobType = document.getElementById('jobType');

    if (storeName) storeName.textContent = job.store_name;
    if (storeAddress) storeAddress.textContent = job.address;
    if (jobType) jobType.textContent = job.job_type;

    // Job Details
    const jobDetails = document.getElementById('jobDetails');
    if (jobDetails) {
        jobDetails.textContent = job.job_detail;
    }

    // Additional Notes
    const additionalNotesSection = document.getElementById('additionalNotesSection');
    const additionalNotes = document.getElementById('additionalNotes');

    if (job.additional_notes && job.additional_notes.trim()) {
        if (additionalNotesSection) additionalNotesSection.style.display = 'block';
        if (additionalNotes) additionalNotes.textContent = job.additional_notes;
    } else {
        if (additionalNotesSection) additionalNotesSection.style.display = 'none';
    }

    // Update the HTML structure for better styling
    updateJobDescriptionHTML(job);
}

// Update Job Description HTML Structure
function updateJobDescriptionHTML(job) {
    const jobDetailsContainer = document.querySelector('.job-details');
    if (!jobDetailsContainer) return;

    let html = `
        <div class="detail-section">
            <h4><i class="bi bi-shop"></i> Store Information</h4>
            <div class="store-info-grid">
                <div class="store-info-item">
                    <strong>Store Name</strong>
                    <span>${job.store_name}</span>
                </div>
                <div class="store-info-item">
                    <strong>Address</strong>
                    <span>${job.address}</span>
                </div>
                <div class="store-info-item">
                    <strong>Job Type</strong>
                    <span>${job.job_type}</span>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4><i class="bi bi-list-ul"></i> Job Details</h4>
            <div class="job-details-content">
                <p>${job.job_detail}</p>
            </div>
        </div>
    `;

    // Add Additional Notes if exists
    if (job.additional_notes && job.additional_notes.trim()) {
        html += `
            <div class="detail-section">
                <h4><i class="bi bi-sticky"></i> Additional Notes</h4>
                <div class="additional-notes-content">
                    <p>${job.additional_notes}</p>
                </div>
            </div>
        `;
    }

    jobDetailsContainer.innerHTML = html;
}

// Update Attached Files
function updateAttachedFiles(pictures) {
    const attachedFilesContent = document.getElementById('attachedFilesContent');

    if (!attachedFilesContent) return;

    if (!pictures || pictures.length === 0) {
        attachedFilesContent.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No files attached to this job</p>
            </div>
        `;
        return;
    }

    let html = '<div class="attached-files-grid">';

    pictures.forEach(picture => {
        // Fix picture path
        let picturePath = picture.picture_path;
        if (picturePath.startsWith('../../../')) {
            picturePath = picturePath.replace('../../../', '');
        }
        if (!picturePath.startsWith('../') && !picturePath.startsWith('http')) {
            picturePath = '../' + picturePath;
        }

        html += `
            <div class="file-item">
                <div class="file-preview">
                    <img src="${picturePath}" 
                         alt="${picture.picture_name}" 
                         class="file-image"
                         onclick="viewImage('${picturePath}', '${picture.picture_name}')">
                    <div class="file-actions-overlay">
                        <button class="btn-file-action" onclick="downloadFile('${picturePath}', '${picture.picture_name}')" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    attachedFilesContent.innerHTML = html;
}

// Update Assigned Vendors
async function updateAssignedVendors(vendors) {
    const vendorsContent = document.getElementById('vendorsContent');
    const chatWithAllVendorsBtn = document.getElementById('chatWithAllVendorsBtn');

    if (!vendorsContent) return;

    if (!vendors || vendors.length === 0) {
        vendorsContent.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No vendors assigned to this job</p>
            </div>
        `;
        if (chatWithAllVendorsBtn) chatWithAllVendorsBtn.style.display = 'none';
        return;
    }

    // Show chat button if vendors exist
    if (chatWithAllVendorsBtn) chatWithAllVendorsBtn.style.display = 'inline-block';

    // Get unread counts for all vendors
    const jobId = getJobIdFromUrl();
    let unreadCounts = {};

    if (jobId) {
        try {
            const response = await fetch(`assets/api/get_vendor_unread_counts.php?job_id=${jobId}`);
            const result = await response.json();
            if (result.success && result.data) {
                unreadCounts = result.data;
            }
        } catch (error) {
            console.error('Error fetching unread counts for vendor cards:', error);
        }
    }

    let html = '<div class="users-grid">';

    vendors.forEach(vendor => {
        const initials = getInitials(vendor.first_name, vendor.last_name);
        const statusBadge = getVendorStatusBadge(vendor.status);
        const statusClass = getVendorStatusClass(vendor.status);
        const displayName = vendor.first_name || vendor.vendor_name || 'Unknown Vendor';
        const displayEmail = vendor.email || 'No email provided';
        const displayPhone = vendor.phone_number || vendor.phone || 'No phone provided';

        // Get unread count for this vendor
        const unreadCount = unreadCounts[vendor.id] ? unreadCounts[vendor.id].unread_count : 0;
        const showUnreadBadge = unreadCount > 0;

        html += `
            <div class="user-card" data-vendor-id="${vendor.id}">
                <div class="user-avatar">
                    <span>${initials}</span>
                    ${showUnreadBadge ? `<div class="unread-badge">${unreadCount}</div>` : ''}
                </div>
                <div class="user-info">
                    <h4>${displayName}</h4>
                    ${displayEmail && displayEmail !== 'No email provided' ? `<p class="user-email">${displayEmail}</p>` : ''}
                    <p class="user-phone"><i class="bi bi-telephone"></i> ${displayPhone}</p>
                    <div class="user-badges">
                        <!-- Badges removed as requested -->
                    </div>
                    <div class="user-details">
                        <p><i class="bi bi-calendar"></i> Assigned: ${getTimeAgo(vendor.created_at)}</p>
                        <p><i class="bi bi-briefcase"></i> Status: ${statusBadge}</p>
                        ${vendor.quote_type ? `<p><i class="bi bi-currency-dollar"></i> Quote: ${vendor.quote_type.replace('_', ' ').toUpperCase()}</p>` : ''}
                        ${vendor.quote_amount ? `<p><i class="bi bi-cash"></i> Amount: $${vendor.quote_amount}</p>` : ''}
                    </div>
                </div>
                <div class="user-actions">
                    <!-- View icon removed as requested -->
                </div>
            </div>
        `;
    });

    html += '</div>';
    vendorsContent.innerHTML = html;
}

// Update Timeline
function updateTimeline(timeline) {
    const timelineContent = document.getElementById('timelineContent');

    if (!timelineContent) return;

    if (!timeline || timeline.length === 0) {
        timelineContent.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No timeline events found</p>
            </div>
        `;
        return;
    }

    let html = '<div class="timeline">';

    timeline.forEach((event, index) => {
        const isLast = index === timeline.length - 1;
        const statusClass = event.status === 'completed' ? 'completed' :
            event.status === 'active' ? 'active' : '';

        // Use icon from database or fallback to event_type mapping
        let iconClass = event.icon || 'bi-circle';

        // Fallback icon mapping if icon not provided from database
        if (!event.icon) {
            switch (event.event_type) {
                case 'job_created':
                    iconClass = 'bi-briefcase-fill';
                    break;
                case 'user_assigned':
                    iconClass = 'bi-person-check-fill';
                    break;
                case 'vendor_assigned':
                case 'vendor_added':
                    iconClass = 'bi-person-plus-fill';
                    break;
                case 'visit_requested':
                    iconClass = 'bi-eye-fill';
                    break;
                case 'visit_accepted':
                case 'visit_rejected':
                    iconClass = 'bi-check-circle-fill';
                    break;
                case 'final_visit_requested':
                    iconClass = 'bi-calendar-check-fill';
                    break;
                case 'final_visit_accepted':
                case 'final_visit_rejected':
                    iconClass = 'bi-check-circle-fill';
                    break;
                case 'job_completed':
                    iconClass = 'bi-check-circle-fill';
                    break;
                case 'payment_requested':
                    iconClass = 'bi-credit-card-fill';
                    break;
                case 'payment_accepted':
                case 'payment_rejected':
                    iconClass = 'bi-check-circle-fill';
                    break;
            }
        }

        html += `
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
                    <span class="timeline-time">${getTimeAgo(event.event_time)}</span>
                </div>
            </div>
        `;
    });

    html += '</div>';
    timelineContent.innerHTML = html;
}

// Helper Functions
function getStatusBadge(status) {
    const badges = {
        'added': {
            class: 'bg-info',
            text: 'ADDED'
        },
        'in_progress': {
            class: 'bg-warning',
            text: 'IN PROGRESS'
        },
        'completed': {
            class: 'bg-success',
            text: 'COMPLETED'
        }
    };
    return badges[status] || {
        class: 'bg-secondary',
        text: status ? status.toUpperCase() : 'ADDED'
    };
}

function getVendorStatusBadge(status) {
    const badges = {
        'visit_requested': 'Visit Requested',
        'final_visit_requested': 'Final Visit Requested',
        'in_progress': 'In Progress',
        'job_completed': 'Job Completed',
        'vendor_payment_accepted': 'Payment Accepted',
        'requested_vendor_payment': 'Payment Requested'
    };
    return badges[status] || 'Assigned';
}

function getVendorStatusClass(status) {
    const classes = {
        'visit_requested': 'badge-warning',
        'final_visit_requested': 'badge-info',
        'in_progress': 'badge-primary',
        'job_completed': 'badge-success',
        'vendor_payment_accepted': 'badge-success',
        'requested_vendor_payment': 'badge-warning'
    };
    return classes[status] || 'badge-secondary';
}

function getInitials(firstName, lastName) {
    // Handle vendor names (single name)
    if (firstName && !lastName) {
        const name = firstName.trim();
        if (name.length >= 2) {
            return name.substring(0, 2).toUpperCase();
        } else {
            return name.charAt(0).toUpperCase();
        }
    }

    // Handle regular first/last names
    const first = firstName ? firstName.charAt(0).toUpperCase() : '';
    const last = lastName ? lastName.charAt(0).toUpperCase() : '';
    return first + last;
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getTimeAgo(datetime) {
    const time = Date.now() - new Date(datetime).getTime();

    if (time < 60000) {
        return 'just now';
    } else if (time < 3600000) {
        const minutes = Math.floor(time / 60000);
        return minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
    } else if (time < 86400000) {
        const hours = Math.floor(time / 3600000);
        return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
    } else if (time < 2592000000) {
        const days = Math.floor(time / 86400000);
        return days + ' day' + (days > 1 ? 's' : '') + ' ago';
    } else if (time < 31536000000) {
        const months = Math.floor(time / 2592000000);
        return months + ' month' + (months > 1 ? 's' : '') + ' ago';
    } else {
        const years = Math.floor(time / 31536000000);
        return years + ' year' + (years > 1 ? 's' : '') + ' ago';
    }
}

// Action Functions
function viewImage(imagePath, imageName) {
    // Fix image path for admin panel
    let correctedPath = imagePath;

    // Remove extra path prefixes for admin panel
    if (correctedPath.startsWith('../../../uploads/')) {
        correctedPath = correctedPath.replace('../../../uploads/', '../uploads/');
    } else if (correctedPath.startsWith('../../uploads/')) {
        correctedPath = correctedPath.replace('../../uploads/', '../uploads/');
    } else if (correctedPath.startsWith('../uploads/')) {
        // Already correct
    } else if (correctedPath.startsWith('uploads/')) {
        correctedPath = '../' + correctedPath;
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

function downloadFile(filePath, fileName) {
    const link = document.createElement('a');
    link.href = filePath;
    link.download = fileName;
    link.click();
}

function viewVendorDetails(vendorId) {
    // Redirect to vendor details page
    window.location.href = `vendor-details.php?id=${vendorId}`;
}

function showError(message) {
    const mainContent = document.getElementById('mainContent');
    if (mainContent) {
        mainContent.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${message}
            </div>
        `;
    }
}

// Admin Chat Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Global variables for admin chat functionality
    let adminCurrentVendorId = null;
    let adminCurrentVendorName = null;
    let adminCurrentVendorAvatar = null;
    let adminCurrentJobId = null;
    let adminCurrentJobData = null; // Store job data for dynamic header updates
    let adminSelectedFiles = [];
    let adminIsScrolling = false;
    let adminLastMessageId = null; // Track last message ID for smart polling
    let adminMessagePollingInterval = null; // Message polling interval

    // Initialize admin chat modal when it opens
    const adminChatModal = document.getElementById('adminChatModal');
    if (adminChatModal) {
        adminChatModal.addEventListener('shown.bs.modal', function() {
            console.log('Admin chat modal opened');
            adminCurrentJobId = getJobIdFromUrl();
            if (adminCurrentJobId) {
                loadJobDataForModal(); // Load job data first
                loadAdminChatVendors();
                updateAdminModalAttachmentCount(); // Update total attachment count

                // Start periodic unread count refresh
                startUnreadCountRefresh();

                // Start smart message polling when vendor is selected
                startMessagePolling();

                setTimeout(() => {
                    scrollAdminChatToBottom();
                }, 300);
            }
        });

        // Stop refresh when modal is hidden
        adminChatModal.addEventListener('hidden.bs.modal', function() {
            stopUnreadCountRefresh();
            stopMessagePolling();
        });
    }

    // Unread count refresh interval
    let unreadCountInterval = null;

    // Start periodic unread count refresh
    function startUnreadCountRefresh() {
        // Clear existing interval
        if (unreadCountInterval) {
            clearInterval(unreadCountInterval);
        }

        // Refresh every 10 seconds
        unreadCountInterval = setInterval(() => {
            refreshUnreadCounts();
        }, 10000);
    }

    // Stop unread count refresh
    function stopUnreadCountRefresh() {
        if (unreadCountInterval) {
            clearInterval(unreadCountInterval);
            unreadCountInterval = null;
        }
    }

    // Start smart message polling
    function startMessagePolling() {
        // Clear existing interval
        if (adminMessagePollingInterval) {
            clearInterval(adminMessagePollingInterval);
        }

        // Poll for new messages every 5 seconds
        adminMessagePollingInterval = setInterval(() => {
            pollForNewMessages();
        }, 5000);
    }

    // Stop message polling
    function stopMessagePolling() {
        if (adminMessagePollingInterval) {
            clearInterval(adminMessagePollingInterval);
            adminMessagePollingInterval = null;
        }
    }

    // Poll for new messages (only append new ones)
    async function pollForNewMessages() {
        if (!adminCurrentVendorId || !adminCurrentJobId) return;

        try {
            const response = await fetch(`assets/api/get_messages.php?vendor_id=${adminCurrentVendorId}&job_id=${adminCurrentJobId}&mark_as_read=false&last_message_id=${adminLastMessageId || 0}`);
            const result = await response.json();

            if (result.success && result.messages && result.messages.length > 0) {
                // Only append new messages
                appendNewMessages(result.messages);

                // Update last message ID
                const latestMessage = result.messages[result.messages.length - 1];
                if (latestMessage && latestMessage.id > adminLastMessageId) {
                    adminLastMessageId = latestMessage.id;
                }

                // Scroll to bottom if new messages
                setTimeout(() => {
                    scrollAdminChatToBottom();
                }, 100);
            }
        } catch (error) {
            console.error('Error polling for new messages:', error);
        }
    }

    // Append new messages to chat area
    function appendNewMessages(newMessages) {
        const messagesArea = document.getElementById('adminMessagesArea');
        const noMessagesPlaceholder = document.getElementById('adminNoMessagesPlaceholder');

        if (!messagesArea) return;

        // Hide no messages placeholder
        if (noMessagesPlaceholder) {
            noMessagesPlaceholder.style.display = 'none';
        }

        // Show new message indicator BEFORE new messages
        if (newMessages.length > 0) {
            showNewMessageIndicator(newMessages.length);

            // Wait a moment for indicator to show, then append messages
            setTimeout(() => {
                newMessages.forEach(message => {
                    const messageElement = createAdminMessageElement(message);
                    messagesArea.appendChild(messageElement);
                });
            }, 100);
        } else {
            // If no new messages, just append them directly
            newMessages.forEach(message => {
                const messageElement = createAdminMessageElement(message);
                messagesArea.appendChild(messageElement);
            });
        }
    }

    // Create single message element (extracted from displayAdminMessages)
    function createAdminMessageElement(message) {
        const isOwnMessage = message.sender_role === 'admin';
        const messageClass = isOwnMessage ? 'message-item sent' : 'message-item received';
        const avatar = isOwnMessage ? 'A' : adminCurrentVendorAvatar;
        const senderName = isOwnMessage ? 'You' : message.sender_name;

        const messageElement = document.createElement('div');
        messageElement.className = messageClass;

        // Check if message has attachments
        let attachmentHtml = '';
        if (message.attachments && message.attachments.length > 0) {
            attachmentHtml = message.attachments.map(attachment => {
                const isImage = /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(attachment.file_path);
                if (isImage) {
                    return `
                        <div class="message-attachment">
z                            <div class="attachment-item">
                                <div class="attachment-icon">
                                    <img src="../uploads/messages/${attachment.file_path}" 
                                         alt="${attachment.file_name}" 
                                         class="attachment-image"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                </div>
                                <div class="attachment-info">
                                    <div class="attachment-name">${attachment.file_name}</div>
                                    <div class="attachment-meta">Image</div>
                                </div>
                                <div class="attachment-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadAdminAttachment('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')" title="Download">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="viewImage('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    return `
                        <div class="message-attachment">
                            <a href="../uploads/messages/${attachment.file_path}" 
                               download="${attachment.file_name}"
                               class="attachment-file">
                                <i class="bi bi-paperclip"></i>
                                ${attachment.file_name}
                            </a>
                        </div>
                    `;
                }
            }).join('');
        }

        messageElement.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-sender">${senderName}</span>
                    <span class="message-time">${formatTime(message.created_at)}</span>
                </div>
                ${message.message ? `<div class="message-text">${message.message}</div>` : ''}
                ${attachmentHtml}
            </div>
        `;

        return messageElement;
    }

    // Show new message indicator
    function showNewMessageIndicator(count) {
        const messagesArea = document.getElementById('adminMessagesArea');
        if (!messagesArea) return;

        // Remove existing indicator
        const existingIndicator = messagesArea.querySelector('.new-message-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        // Create new indicator
        const indicator = document.createElement('div');
        indicator.className = 'new-message-indicator';
        indicator.innerHTML = `
            <div class="new-message-badge">
                <i class="bi bi-arrow-down"></i>
                ${count} new message${count > 1 ? 's' : ''}
            </div>
        `;

        // Insert at the bottom of messages area (after existing messages)
        messagesArea.appendChild(indicator);

        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (indicator && indicator.parentNode) {
                indicator.remove();
            }
        }, 3000);
    }

    // Refresh unread counts
    async function refreshUnreadCounts() {
        if (!adminCurrentJobId) return;

        try {
            const response = await fetch(`assets/api/get_vendor_unread_counts.php?job_id=${adminCurrentJobId}`);
            const result = await response.json();

            if (result.success) {
                updateVendorUnreadCounts(result.data);
            }
        } catch (error) {
            console.error('Error refreshing unread counts:', error);
        }
    }

    // Update vendor unread counts in UI
    function updateVendorUnreadCounts(unreadCounts) {
        Object.keys(unreadCounts).forEach(vendorId => {
            const unreadData = unreadCounts[vendorId];
            const unreadElement = document.getElementById(`adminVendorUnread${vendorId}`);

            if (unreadElement) {
                const badge = unreadElement.querySelector('.badge');
                const unreadCount = unreadData.unread_count;

                if (unreadCount > 0) {
                    unreadElement.style.display = 'block';
                    if (badge) {
                        badge.textContent = unreadCount;
                    }
                } else {
                    unreadElement.style.display = 'none';
                    if (badge) {
                        badge.textContent = '0';
                    }
                }
            }
        });
    }

    // Initialize admin attachments modal when it opens (All Vendors)
    const adminAttachmentsModal = document.getElementById('adminAttachmentsModal');
    if (adminAttachmentsModal) {
        adminAttachmentsModal.addEventListener('shown.bs.modal', function() {
            console.log('Admin all attachments modal opened');
            loadAdminAttachments();
        });
    }

    // Initialize vendor attachments modal when it opens (Specific Vendor)
    const vendorAttachmentsModal = document.getElementById('vendorAttachmentsModal');
    if (vendorAttachmentsModal) {
        vendorAttachmentsModal.addEventListener('shown.bs.modal', function() {
            console.log('Vendor attachments modal opened');
            loadVendorAttachments();
        });
    }


    // Load job data for modal headers
    async function loadJobDataForModal() {
        try {
            const response = await fetch(`assets/api/get_job_view_details.php?job_id=${adminCurrentJobId}`);
            const result = await response.json();

            if (result.success && result.data && result.data.job) {
                adminCurrentJobData = result.data.job;
                updateChatModalHeaders();
            }
        } catch (error) {
            console.error('Error loading job data for modal:', error);
        }
    }

    // Update chat modal headers with job data
    function updateChatModalHeaders() {
        if (!adminCurrentJobData) return;

        // Update main modal header
        const modalUsername = document.getElementById('modalChatUsername');
        const modalStatus = document.getElementById('modalChatStatus');

        if (modalUsername) {
            modalUsername.textContent = `Discussion about ${adminCurrentJobData.store_name}`;
        }

        if (modalStatus) {
            const statusBadge = getStatusBadge(adminCurrentJobData.status);
            modalStatus.innerHTML = `Job #JOB-${adminCurrentJobData.id} <span class="badge ${statusBadge.class}">${statusBadge.text}</span>`;
        }

        // Update chat area job info
        const chatAreaJob = document.getElementById('adminChatAreaJob');
        if (chatAreaJob) {
            const statusBadge = getStatusBadge(adminCurrentJobData.status);
            chatAreaJob.innerHTML = `Job #JOB-${adminCurrentJobData.id} <span class="badge ${statusBadge.class}">${statusBadge.text}</span>`;
        }

        // Update attachments modal job info
        const attachmentJobId = document.getElementById('adminAttachmentJobId');
        if (attachmentJobId) {
            attachmentJobId.textContent = `Job #JOB-${adminCurrentJobData.id}`;
        }

        // Update vendor attachments modal job info
        const vendorAttachmentJobId = document.getElementById('vendorAttachmentJobId');
        if (vendorAttachmentJobId) {
            vendorAttachmentJobId.textContent = `Job #JOB-${adminCurrentJobData.id}`;
        }
    }

    // Load vendors for admin chat modal
    async function loadAdminChatVendors() {
        try {
            // Load both job details and unread counts
            const [jobResponse, unreadResponse] = await Promise.all([
                fetch(`assets/api/get_job_view_details.php?job_id=${adminCurrentJobId}`),
                fetch(`assets/api/get_vendor_unread_counts.php?job_id=${adminCurrentJobId}`)
            ]);

            const jobResult = await jobResponse.json();
            const unreadResult = await unreadResponse.json();

            if (jobResult.success && jobResult.data.vendors.length > 0) {
                const unreadCounts = unreadResult.success ? unreadResult.data : {};
                displayAdminChatVendors(jobResult.data.vendors, unreadCounts);
                // Select first vendor by default
                if (jobResult.data.vendors.length > 0) {
                    selectAdminVendor(jobResult.data.vendors[0].id, jobResult.data.vendors[0].vendor_name, getInitials(jobResult.data.vendors[0].vendor_name, ''));
                }
            } else {
                showAdminNoVendors();
            }
        } catch (error) {
            console.error('Error loading admin chat vendors:', error);
            showAdminNoVendors();
        }
    }

    // Display admin chat vendors
    function displayAdminChatVendors(vendors, unreadCounts = {}) {
        const vendorsList = document.getElementById('adminChatVendorsList');
        if (!vendorsList) return;

        let html = '';
        vendors.forEach(vendor => {
            const initials = getInitials(vendor.vendor_name, '');
            const isActive = adminCurrentVendorId === vendor.id ? 'active' : '';
            const unreadCount = unreadCounts[vendor.id] ? unreadCounts[vendor.id].unread_count : 0;
            const showUnread = unreadCount > 0;

            html += `
                <div class="user-vendor-item ${isActive}" onclick="selectAdminVendor(${vendor.id}, '${vendor.vendor_name}', '${initials}')">
                    <div class="user-vendor-avatar">${initials}</div>
                    <div class="user-vendor-info">
                        <div class="user-vendor-name">${vendor.vendor_name}</div>
                        <div class="user-vendor-status">${getVendorStatusBadge(vendor.status)}</div>
                    </div>
                    <div class="user-vendor-unread" id="adminVendorUnread${vendor.id}" style="display: ${showUnread ? 'block' : 'none'};">
                        <span class="badge bg-danger">${unreadCount}</span>
                    </div>
                </div>
            `;
        });

        vendorsList.innerHTML = html;
    }

    // Clear vendor unread count
    function clearVendorUnreadCount(vendorId) {
        const unreadElement = document.getElementById(`adminVendorUnread${vendorId}`);
        if (unreadElement) {
            unreadElement.style.display = 'none';
            const badge = unreadElement.querySelector('.badge');
            if (badge) {
                badge.textContent = '0';
            }
        }
    }

    // Update vendor card unread badge (in the main vendor cards section)
    async function updateVendorCardUnreadBadge(vendorId) {
        try {
            // Get current unread count for this specific vendor
            const response = await fetch(`assets/api/get_vendor_unread_counts.php?job_id=${adminCurrentJobId}`);
            const result = await response.json();

            if (result.success && result.data) {
                const unreadCount = result.data[vendorId] ? result.data[vendorId].unread_count : 0;

                // Find the specific vendor card using data-vendor-id
                const vendorCard = document.querySelector(`.user-card[data-vendor-id="${vendorId}"]`);

                if (vendorCard) {
                    const unreadBadge = vendorCard.querySelector('.unread-badge');

                    if (unreadBadge) {
                        if (unreadCount === 0) {
                            // Hide the unread badge for this vendor
                            unreadBadge.style.display = 'none';
                            unreadBadge.textContent = '0';
                        } else {
                            // Update the count
                            unreadBadge.textContent = unreadCount;
                            unreadBadge.style.display = 'inline-block';
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error updating vendor card unread badge:', error);
        }
    }

    // Update header unread count
    async function updateHeaderUnreadCount() {
        try {
            // Call the existing header update function from script.js
            if (typeof updateMessageBadges === 'function') {
                await updateMessageBadges();
            } else {
                // Fallback: direct API call
                const response = await fetch('assets/api/get_total_unread_messages.php');
                const result = await response.json();

                if (result.success && result.data) {
                    const messageBadge = document.getElementById('messageBadge');
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
            }
        } catch (error) {
            console.error('Error updating header unread count:', error);
        }
    }

    // Show no vendors message
    function showAdminNoVendors() {
        const vendorsList = document.getElementById('adminChatVendorsList');
        if (vendorsList) {
            vendorsList.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No vendors assigned</p>
                </div>
            `;
        }
    }

    // Select admin vendor
    async function selectAdminVendor(vendorId, vendorName, avatar) {
        adminCurrentVendorId = vendorId;
        adminCurrentVendorName = vendorName;
        adminCurrentVendorAvatar = avatar;

        // Update UI
        updateAdminChatHeader();
        updateAdminVendorSelection();

        // Clear unread count for this vendor
        clearVendorUnreadCount(vendorId);

        // Load messages
        await loadAdminMessages();
    }

    // Update admin chat header
    function updateAdminChatHeader() {
        const chatAreaAvatar = document.getElementById('adminChatAreaAvatar');
        const chatAreaName = document.getElementById('adminChatAreaName');

        if (chatAreaAvatar) chatAreaAvatar.textContent = adminCurrentVendorAvatar;
        if (chatAreaName) chatAreaName.textContent = `Chat about ${adminCurrentVendorName}`;

        // Update attachment count
        updateAdminChatAttachmentCount();
    }

    // Update admin chat attachment count
    async function updateAdminChatAttachmentCount() {
        if (!adminCurrentVendorId || !adminCurrentJobId) return;

        try {
            const response = await fetch(`assets/api/get_messages.php?vendor_id=${adminCurrentVendorId}&job_id=${adminCurrentJobId}&mark_as_read=false`);
            const result = await response.json();

            if (result.success && result.data) {
                let attachmentCount = 0;
                result.data.forEach(message => {
                    if (message.attachments && Array.isArray(message.attachments)) {
                        attachmentCount += message.attachments.length;
                    }
                });

                const attachmentCountElement = document.getElementById('adminChatAttachmentCount');
                if (attachmentCountElement) {
                    attachmentCountElement.textContent = attachmentCount;
                }
            }
        } catch (error) {
            console.error('Error updating attachment count:', error);
        }
    }

    // Update admin modal total attachment count
    async function updateAdminModalAttachmentCount() {
        if (!adminCurrentJobId) return;

        try {
            // Get all vendors for this job
            const vendorsResponse = await fetch(`assets/api/get_job_view_details.php?job_id=${adminCurrentJobId}`);
            const vendorsResult = await vendorsResponse.json();

            if (vendorsResult.success && vendorsResult.data && vendorsResult.data.vendors) {
                let totalAttachmentCount = 0;

                // Get attachment count for each vendor
                for (const vendor of vendorsResult.data.vendors) {
                    try {
                        const messagesResponse = await fetch(`assets/api/get_messages.php?vendor_id=${vendor.id}&job_id=${adminCurrentJobId}&mark_as_read=false`);
                        const messagesResult = await messagesResponse.json();

                        if (messagesResult.success && messagesResult.data) {
                            messagesResult.data.forEach(message => {
                                if (message.attachments && Array.isArray(message.attachments)) {
                                    totalAttachmentCount += message.attachments.length;
                                }
                            });
                        }
                    } catch (error) {
                        console.error(`Error getting attachments for vendor ${vendor.id}:`, error);
                    }
                }

                const modalAttachmentCountElement = document.getElementById('adminModalAttachmentCount');
                if (modalAttachmentCountElement) {
                    modalAttachmentCountElement.textContent = totalAttachmentCount;
                }
            }
        } catch (error) {
            console.error('Error updating modal attachment count:', error);
        }
    }

    // Load admin attachments for the modal
    async function loadAdminAttachments() {
        if (!adminCurrentJobId) return;

        const attachmentsList = document.getElementById('adminAttachmentsList');
        const attachmentsLoading = document.getElementById('adminAttachmentsLoading');
        const attachmentsEmpty = document.getElementById('adminAttachmentsEmpty');
        const attachmentVendorAvatar = document.getElementById('adminAttachmentVendorAvatar');
        const attachmentVendorName = document.getElementById('adminAttachmentVendorName');
        const attachmentCount = document.getElementById('adminAttachmentCount');

        if (attachmentsLoading) attachmentsLoading.style.display = 'block';
        if (attachmentsEmpty) attachmentsEmpty.style.display = 'none';

        try {
            // Get all vendors for this job
            const vendorsResponse = await fetch(`assets/api/get_job_view_details.php?job_id=${adminCurrentJobId}`);
            const vendorsResult = await vendorsResponse.json();

            if (vendorsResult.success && vendorsResult.data && vendorsResult.data.vendors) {
                let allAttachments = [];
                let totalAttachmentCount = 0;

                // Get attachments for each vendor
                for (const vendor of vendorsResult.data.vendors) {
                    try {
                        const messagesResponse = await fetch(`assets/api/get_messages.php?vendor_id=${vendor.id}&job_id=${adminCurrentJobId}&mark_as_read=false`);
                        const messagesResult = await messagesResponse.json();

                        if (messagesResult.success && messagesResult.data) {
                            messagesResult.data.forEach(message => {
                                if (message.attachments && Array.isArray(message.attachments)) {
                                    message.attachments.forEach(attachment => {
                                        attachment.vendor_name = vendor.vendor_name;
                                        attachment.vendor_id = vendor.id;
                                        allAttachments.push(attachment);
                                        totalAttachmentCount++;
                                    });
                                }
                            });
                        }
                    } catch (error) {
                        console.error(`Error getting attachments for vendor ${vendor.id}:`, error);
                    }
                }

                if (attachmentsLoading) attachmentsLoading.style.display = 'none';

                if (allAttachments.length > 0) {
                    // Update header info
                    if (attachmentVendorAvatar) attachmentVendorAvatar.textContent = 'A';
                    if (attachmentVendorName) attachmentVendorName.textContent = 'All Vendors';
                    if (attachmentCount) attachmentCount.textContent = `${totalAttachmentCount} file${totalAttachmentCount > 1 ? 's' : ''}`;

                    // Display attachments
                    displayAdminAttachments(allAttachments);
                } else {
                    // Show empty state
                    if (attachmentVendorAvatar) attachmentVendorAvatar.textContent = 'A';
                    if (attachmentVendorName) attachmentVendorName.textContent = 'All Vendors';
                    if (attachmentCount) attachmentCount.textContent = '0 files';
                    if (attachmentsEmpty) attachmentsEmpty.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error loading admin attachments:', error);
            if (attachmentsLoading) attachmentsLoading.style.display = 'none';
            if (attachmentsEmpty) attachmentsEmpty.style.display = 'block';
        }
    }

    // Display admin attachments
    function displayAdminAttachments(attachments) {
        const attachmentsList = document.getElementById('adminAttachmentsList');
        if (!attachmentsList) return;

        let html = '';
        attachments.forEach(attachment => {
            html += createAdminAttachmentItem(attachment);
        });

        attachmentsList.innerHTML = html;
    }

    // Create admin attachment item
    function createAdminAttachmentItem(attachment) {
        const fileExtension = attachment.file_name.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif'].includes(fileExtension);

        // Fix attachment file path for admin panel
        let correctedPath = attachment.file_path;
        if (correctedPath.startsWith('../../../uploads/')) {
            correctedPath = correctedPath.replace('../../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../../uploads/')) {
            correctedPath = correctedPath.replace('../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../uploads/')) {
            // Already correct
        } else if (correctedPath.startsWith('uploads/')) {
            correctedPath = '../' + correctedPath;
        }

        let previewHtml = '';
        if (isImage) {
            previewHtml = `
                <div class="attachment-preview">
                    <img src="${correctedPath}" alt="${attachment.file_name}" class="attachment-image" 
                         onerror="console.error('Failed to load attachment image:', '${correctedPath}'); this.style.display='none'; this.parentNode.innerHTML='<div class=&quot;alert alert-warning alert-sm&quot;><i class=&quot;bi bi-exclamation-triangle&quot;></i> Image not found</div>';">
                </div>
            `;
        } else {
            const iconClass = getFileIcon(attachment.file_type);
            previewHtml = `
                <div class="attachment-icon">
                    <i class="${iconClass}"></i>
                </div>
            `;
        }

        return `
            <div class="attachment-item">
                ${previewHtml}
                <div class="attachment-info">
                    <div class="attachment-name">${attachment.file_name}</div>
                    <div class="attachment-meta">
                        ${formatFileSize(attachment.file_size)} • ${formatDateTime(attachment.created_at)}
                        ${attachment.vendor_name ? ` • ${attachment.vendor_name}` : ''}
                    </div>
                </div>
                <div class="attachment-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadAdminAttachment('${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="viewAdminAttachment('${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Update admin vendor selection
    function updateAdminVendorSelection() {
        const vendorItems = document.querySelectorAll('#adminChatVendorsList .user-vendor-item');
        vendorItems.forEach(item => {
            item.classList.remove('active');
        });

        const activeItem = document.querySelector(`#adminChatVendorsList .user-vendor-item[onclick*="${adminCurrentVendorId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }
    }

    // Load admin messages
    async function loadAdminMessages() {
        if (!adminCurrentVendorId || !adminCurrentJobId) return;

        const messagesArea = document.getElementById('adminMessagesArea');
        const messagesLoading = document.getElementById('adminMessagesLoading');
        const noMessagesPlaceholder = document.getElementById('adminNoMessagesPlaceholder');

        if (messagesLoading) messagesLoading.style.display = 'block';
        if (noMessagesPlaceholder) noMessagesPlaceholder.style.display = 'none';

        try {
            const response = await fetch(`assets/api/get_messages.php?vendor_id=${adminCurrentVendorId}&job_id=${adminCurrentJobId}&mark_as_read=true`);
            const result = await response.json();

            if (messagesLoading) messagesLoading.style.display = 'none';

            if (result.success && result.data.length > 0) {
                displayAdminMessages(result.data);

                // Set last message ID for polling
                const latestMessage = result.data[result.data.length - 1];
                if (latestMessage && latestMessage.id) {
                    adminLastMessageId = latestMessage.id;
                }

                setTimeout(() => {
                    scrollAdminChatToBottom();
                }, 100);
            } else {
                if (noMessagesPlaceholder) noMessagesPlaceholder.style.display = 'block';
            }

            // Update vendor card unread badge and header count after marking messages as read
            updateVendorCardUnreadBadge(adminCurrentVendorId);
            updateHeaderUnreadCount();

        } catch (error) {
            console.error('Error loading admin messages:', error);
            if (messagesLoading) messagesLoading.style.display = 'none';
            if (noMessagesPlaceholder) noMessagesPlaceholder.style.display = 'block';
        }
    }

    // Display admin messages
    function displayAdminMessages(messages) {
        const messagesArea = document.getElementById('adminMessagesArea');
        if (!messagesArea) {
            console.error('adminMessagesArea not found');
            return;
        }

        let html = '';
        let currentDate = '';

        messages.forEach((message, index) => {
            const messageDate = new Date(message.created_at).toDateString();
            if (messageDate !== currentDate) {
                currentDate = messageDate;
                html += `
                    <div class="message-date-separator">
                        <div class="date-line"></div>
                        <div class="date-text">${formatDate(message.created_at)}</div>
                        <div class="date-line"></div>
                    </div>
                `;
            }

            const isOwnMessage = message.sender_role === 'admin';
            const messageClass = isOwnMessage ? 'message-item sent' : 'message-item received';
            const avatar = isOwnMessage ? 'A' : adminCurrentVendorAvatar;
            const senderName = isOwnMessage ? 'You' : message.sender_name;

            html += `
                <div class="${messageClass}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">${senderName}</span>
                            <span class="message-time">${getTimeAgo(message.created_at)}</span>
                        </div>
                        ${message.message ? `<div class="message-text">${message.message}</div>` : ''}
                        ${message.attachments && Array.isArray(message.attachments) && message.attachments.length > 0 ?
                    `<div class="message-attachment">
                                ${message.attachments.map(attachment => createAdminAttachmentElement(attachment)).join('')}
                            </div>` : ''}
                    </div>
                </div>
            `;
        });

        messagesArea.innerHTML = html;
    }

    // Create admin attachment element
    function createAdminAttachmentElement(attachment) {
        return `
            <div class="attachment-item">
                <div class="attachment-icon">
                    <i class="${getFileIcon(attachment.file_type)}"></i>
                </div>
                <div class="attachment-info">
                    <div class="attachment-name">${attachment.file_name}</div>
                    <div class="attachment-meta">${formatFileSize(attachment.file_size)} • ${formatDateTime(attachment.created_at)}</div>
                </div>
                <div class="attachment-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadAdminAttachment('${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="viewAdminAttachment('${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Scroll admin chat to bottom
    function scrollAdminChatToBottom() {
        if (adminIsScrolling) return;

        adminIsScrolling = true;
        const messagesArea = document.getElementById('adminMessagesArea');
        if (messagesArea) {
            requestAnimationFrame(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
                adminIsScrolling = false;
            });
        }
    }

    // Format date for admin messages
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Download admin attachment
    function downloadAdminAttachment(filePath, fileName) {
        // Fix file path for admin panel
        let correctedPath = filePath;

        // Remove extra path prefixes for admin panel
        if (correctedPath.startsWith('../../../uploads/')) {
            correctedPath = correctedPath.replace('../../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../../uploads/')) {
            correctedPath = correctedPath.replace('../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../uploads/')) {
            // Already correct
        } else if (correctedPath.startsWith('uploads/')) {
            correctedPath = '../' + correctedPath;
        }

        console.log('Download - Original path:', filePath);
        console.log('Download - Corrected path:', correctedPath);

        const link = document.createElement('a');
        link.href = correctedPath;
        link.download = fileName;
        link.click();
    }

    // View admin attachment
    function viewAdminAttachment(filePath, fileName) {
        // Fix file path for admin panel
        let correctedPath = filePath;

        // Remove extra path prefixes for admin panel
        if (correctedPath.startsWith('../../../uploads/')) {
            correctedPath = correctedPath.replace('../../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../../uploads/')) {
            correctedPath = correctedPath.replace('../../uploads/', '../uploads/');
        } else if (correctedPath.startsWith('../uploads/')) {
            // Already correct
        } else if (correctedPath.startsWith('uploads/')) {
            correctedPath = '../' + correctedPath;
        }

        console.log('Original path:', filePath);
        console.log('Corrected path:', correctedPath);

        // Create modal for viewing attachment
        const modalHtml = `
            <div class="modal fade" id="adminAttachmentViewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${fileName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${correctedPath}" alt="${fileName}" class="img-fluid" 
                                 onerror="console.error('Failed to load image:', '${correctedPath}'); this.style.display='none'; this.parentNode.innerHTML='<div class=&quot;alert alert-warning&quot;><i class=&quot;bi bi-exclamation-triangle&quot;></i> Image not found: ${fileName}</div>';">
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('adminAttachmentViewModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('adminAttachmentViewModal'));
        modal.show();

        // Remove modal when hidden
        document.getElementById('adminAttachmentViewModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Setup message sending functionality
    setupAdminMessageSending();

    // Setup message sending listeners
    function setupAdminMessageSending() {
        const messageInput = document.querySelector('#adminChatModal .user-message-input');
        const sendButton = document.querySelector('#adminChatModal .user-message-send-btn');
        const attachButton = document.querySelector('#adminChatModal .user-message-attach-btn');
        const fileInput = document.querySelector('#adminChatModal .user-message-input[type="file"]') || document.getElementById('adminChatFileInput');

        if (sendButton) {
            sendButton.addEventListener('click', sendAdminMessage);
        }

        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendAdminMessage();
                }
            });
        }

        if (attachButton && fileInput) {
            attachButton.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                showAdminFilePreviews(this.files);
            });
        }
    }

    // Send admin message
    async function sendAdminMessage() {
        if (!adminCurrentVendorId || !adminCurrentJobId) {
            showNotification('Please select a vendor first', 'error');
            return;
        }

        const messageInput = document.querySelector('#adminChatModal .user-message-input');
        const message = messageInput ? messageInput.value.trim() : '';
        const fileInput = document.getElementById('adminChatFileInput');
        const hasFiles = fileInput && fileInput.files.length > 0;

        if (!message && !hasFiles) {
            showNotification('Please enter a message or attach a file', 'error');
            return;
        }

        // Show loading state
        const sendButton = document.querySelector('#adminChatModal .user-message-send-btn');
        const originalContent = sendButton ? sendButton.innerHTML : '';
        if (sendButton) {
            sendButton.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            sendButton.disabled = true;
        }

        try {
            const formData = new FormData();
            formData.append('vendor_id', adminCurrentVendorId);
            formData.append('job_id', adminCurrentJobId);
            formData.append('message', message);

            if (hasFiles) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('attachments[]', fileInput.files[i]);
                }
            }

            const response = await fetch('assets/api/send_message.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Clear input and files
                if (messageInput) messageInput.value = '';
                if (fileInput) fileInput.value = '';

                // Clear attachment previews
                clearAdminAttachmentPreviews();

                // Reload messages and update last message ID
                await loadAdminMessages();

                // Update last message ID after sending
                if (result.message_id) {
                    adminLastMessageId = result.message_id;
                }

                // Update attachment count
                await updateAdminChatAttachmentCount();

                // Update total modal attachment count
                await updateAdminModalAttachmentCount();

                showNotification('Message sent successfully', 'success');
            } else {
                showNotification(result.message || 'Failed to send message', 'error');
            }

        } catch (error) {
            console.error('Error sending message:', error);
            showNotification('An error occurred while sending message', 'error');
        } finally {
            // Reset button
            if (sendButton) {
                sendButton.innerHTML = originalContent;
                sendButton.disabled = false;
            }
        }
    }

    // Show admin file previews
    function showAdminFilePreviews(files) {
        const previewArea = document.getElementById('adminAttachmentPreviewArea');
        const previewList = document.getElementById('adminAttachmentPreviewList');

        if (!previewArea || !previewList) return;

        if (files.length === 0) {
            previewArea.style.display = 'none';
            return;
        }

        previewArea.style.display = 'block';

        let html = '';
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            html += createAdminFilePreviewItem(file, i);
        }

        previewList.innerHTML = html;
    }

    // Create admin file preview item
    function createAdminFilePreviewItem(file, index) {
        const fileIcon = getFileIconForPreview(file.type);
        const fileSize = formatFileSize(file.size);

        return `
            <div class="attachment-preview-item" data-index="${index}">
                <div class="attachment-preview-icon">
                    <i class="${fileIcon}"></i>
                </div>
                <div class="attachment-preview-info">
                    <div class="attachment-preview-name">${file.name}</div>
                    <div class="attachment-preview-size">${fileSize}</div>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="removeAdminAttachmentPreview(${index})">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
    }

    // Remove admin attachment preview
    function removeAdminAttachmentPreview(index) {
        const fileInput = document.getElementById('adminChatFileInput');
        if (!fileInput) return;

        // Create new FileList without the removed file
        const dt = new DataTransfer();
        for (let i = 0; i < fileInput.files.length; i++) {
            if (i !== index) {
                dt.items.add(fileInput.files[i]);
            }
        }
        fileInput.files = dt.files;

        // Update preview
        showAdminFilePreviews(fileInput.files);
    }

    // Clear all admin attachment previews
    function clearAdminAttachmentPreviews() {
        const previewArea = document.getElementById('adminAttachmentPreviewArea');
        const fileInput = document.getElementById('adminChatFileInput');

        if (previewArea) previewArea.style.display = 'none';
        if (fileInput) fileInput.value = '';
    }

    // Get file icon for preview
    function getFileIconForPreview(fileType) {
        if (fileType.startsWith('image/')) {
            return 'bi bi-image';
        } else if (fileType.includes('pdf')) {
            return 'bi bi-file-pdf';
        } else if (fileType.includes('word') || fileType.includes('document')) {
            return 'bi bi-file-word';
        } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
            return 'bi bi-file-excel';
        } else {
            return 'bi bi-file';
        }
    }

    // Get file icon for attachments
    function getFileIcon(fileType) {
        if (fileType.startsWith('image/')) {
            return 'bi bi-image';
        } else if (fileType.includes('pdf')) {
            return 'bi bi-file-pdf';
        } else if (fileType.includes('word') || fileType.includes('document')) {
            return 'bi bi-file-word';
        } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
            return 'bi bi-file-excel';
        } else {
            return 'bi bi-file';
        }
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Show notification
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
                notification.remove();
            }
        }, 5000);
    }

    // Load vendor specific attachments
    async function loadVendorAttachments() {
        if (!adminCurrentVendorId || !adminCurrentJobId) return;

        const attachmentsList = document.getElementById('vendorAttachmentsList');
        const attachmentsLoading = document.getElementById('vendorAttachmentsLoading');
        const attachmentsEmpty = document.getElementById('vendorAttachmentsEmpty');

        if (attachmentsLoading) attachmentsLoading.style.display = 'block';
        if (attachmentsEmpty) attachmentsEmpty.style.display = 'none';

        try {
            const response = await fetch(`assets/api/get_messages.php?vendor_id=${adminCurrentVendorId}&job_id=${adminCurrentJobId}&mark_as_read=false`);
            const result = await response.json();

            if (attachmentsLoading) attachmentsLoading.style.display = 'none';

            if (result.success && result.data) {
                let allAttachments = [];
                result.data.forEach(message => {
                    if (message.attachments && Array.isArray(message.attachments)) {
                        allAttachments = allAttachments.concat(message.attachments);
                    }
                });

                if (allAttachments.length > 0) {
                    displayVendorAttachments(allAttachments);
                } else {
                    if (attachmentsEmpty) attachmentsEmpty.style.display = 'block';
                }
            } else {
                if (attachmentsEmpty) attachmentsEmpty.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading vendor attachments:', error);
            if (attachmentsLoading) attachmentsLoading.style.display = 'none';
            if (attachmentsEmpty) attachmentsEmpty.style.display = 'block';
        }
    }

    // Display vendor attachments
    function displayVendorAttachments(attachments) {
        const attachmentsList = document.getElementById('vendorAttachmentsList');
        if (!attachmentsList) return;

        let html = '';
        attachments.forEach(attachment => {
            const fileIcon = getFileIcon(attachment.file_name);
            const fileSize = formatFileSize(attachment.file_size);

            // Fix attachment file path for admin panel
            let correctedPath = attachment.file_path;
            if (correctedPath.startsWith('../../../uploads/')) {
                correctedPath = correctedPath.replace('../../../uploads/', '../uploads/');
            } else if (correctedPath.startsWith('../../uploads/')) {
                correctedPath = correctedPath.replace('../../uploads/', '../uploads/');
            } else if (correctedPath.startsWith('../uploads/')) {
                // Already correct
            } else if (correctedPath.startsWith('uploads/')) {
                correctedPath = '../' + correctedPath;
            }

            html += `
                <div class="attachment-item d-flex align-items-center p-3 border rounded shadow-sm mb-3">
                    <div class="attachment-icon me-3">
                        <i class="${fileIcon} fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="attachment-name fw-bold text-truncate" title="${attachment.file_name}">${attachment.file_name}</div>
                        <small class="text-muted d-flex align-items-center">
                            <i class="bi bi-file-earmark me-1"></i> ${fileSize} • ${attachment.created_at}
                        </small>
                    </div>
                    <div class="attachment-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadAdminAttachment('${correctedPath}', '${attachment.file_name}')">
                            <i class="bi bi-download"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="viewAdminAttachment('${correctedPath}', '${attachment.file_name}')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        attachmentsList.innerHTML = html;
    }

    // Open vendor attachments modal
    function openVendorAttachmentsModal() {
        if (!adminCurrentVendorId) {
            showNotification('Please select a vendor first', 'error');
            return;
        }

        // Update vendor info in modal
        const vendorAvatar = document.getElementById('vendorAttachmentAvatar');
        const vendorName = document.getElementById('vendorAttachmentName');

        if (vendorAvatar) vendorAvatar.textContent = adminCurrentVendorAvatar;
        if (vendorName) vendorName.textContent = adminCurrentVendorName;

        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('vendorAttachmentsModal'));
        modal.show();
    }

    // Make functions globally available
    window.selectAdminVendor = selectAdminVendor;
    window.downloadAdminAttachment = downloadAdminAttachment;
    window.viewAdminAttachment = viewAdminAttachment;
    window.removeAdminAttachmentPreview = removeAdminAttachmentPreview;
    window.openVendorAttachmentsModal = openVendorAttachmentsModal;

    // Add comment function
    async function addComment() {
        const textarea = document.getElementById('commentTextarea');
        const comment = textarea.value.trim();

        if (!comment) {
            showNotification('Please enter a comment', 'error');
            return;
        }

        if (comment.length > 1000) {
            showNotification('Comment is too long (max 1000 characters)', 'error');
            return;
        }

        const addBtn = document.getElementById('addCommentBtn');
        const originalText = addBtn.innerHTML;

        // Show loading state
        addBtn.disabled = true;
        addBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';

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
                textarea.value = '';
                updateCharCount();

                // Reload comments
                await loadComments(currentJobId);

                showNotification('Comment added successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to add comment');
            }
        } catch (error) {
            console.error('Error adding comment:', error);
            showNotification(error.message || 'Failed to add comment', 'error');
        } finally {
            // Reset button
            addBtn.disabled = false;
            addBtn.innerHTML = originalText;
        }
    }

    // Update character count
    function updateCharCount() {
        const textarea = document.getElementById('commentTextarea');
        const charCount = document.getElementById('commentCharCount');
        const addBtn = document.getElementById('addCommentBtn');

        if (charCount) {
            charCount.textContent = textarea.value.length;
        }

        if (addBtn) {
            addBtn.disabled = textarea.value.trim().length === 0 || textarea.value.length > 1000;
        }
    }

    // Make functions globally available
    window.addComment = addComment;
    window.updateCharCount = updateCharCount;
    window.handleNotificationAction = handleNotificationAction;
    window.handleViewForm = handleViewForm;

    // Event listeners for comments
    const textarea = document.getElementById('commentTextarea');
    const addBtn = document.getElementById('addCommentBtn');

    if (textarea) {
        textarea.addEventListener('input', updateCharCount);
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                if (!addBtn.disabled) {
                    addComment();
                }
            }
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', addComment);
    }
});

// Load Payment Metrics
async function loadPaymentMetrics(jobId) {
    try {
        // Fetch job vendors to calculate payment metrics
        const response = await fetch(`assets/api/get_job_view_details.php?job_id=${jobId}`);
        const data = await response.json();

        if (data.success && data.data.vendors) {
            let totalEstimated = 0;
            let totalPaid = 0;
            let totalRemaining = 0;

            // Calculate totals from all vendors
            data.data.vendors.forEach(vendor => {
                // Check if vendor has final request approval
                if (vendor.estimated_amount && vendor.estimated_amount > 0) {
                    totalEstimated += parseFloat(vendor.estimated_amount);

                    // Check if vendor has paid amount
                    if (vendor.total_paid && vendor.total_paid > 0) {
                        totalPaid += parseFloat(vendor.total_paid);
                    }
                }
            });

            totalRemaining = totalEstimated - totalPaid;

            // Update metric values (always show, even if 0)
            const estimatedAmountEl = document.getElementById('estimatedAmount');
            const totalPaidEl = document.getElementById('totalPaid');
            const remainingBalanceEl = document.getElementById('remainingBalance');

            if (estimatedAmountEl) {
                estimatedAmountEl.textContent = `$${totalEstimated.toFixed(2)}`;
            }

            if (totalPaidEl) {
                totalPaidEl.textContent = `$${totalPaid.toFixed(2)}`;
            }

            if (remainingBalanceEl) {
                remainingBalanceEl.textContent = `$${totalRemaining.toFixed(2)}`;

                // Update colors based on values
                if (totalRemaining <= 0) {
                    remainingBalanceEl.className = 'metric-status text-success';
                } else if (totalRemaining < totalEstimated * 0.5) {
                    remainingBalanceEl.className = 'metric-status text-warning';
                } else {
                    remainingBalanceEl.className = 'metric-status text-danger';
                }
            }
        }
    } catch (error) {
        console.error('Error loading payment metrics:', error);
        // Show 0 values on error
        const estimatedAmountEl = document.getElementById('estimatedAmount');
        const totalPaidEl = document.getElementById('totalPaid');
        const remainingBalanceEl = document.getElementById('remainingBalance');

        if (estimatedAmountEl) estimatedAmountEl.textContent = '$0.00';
        if (totalPaidEl) totalPaidEl.textContent = '$0.00';
        if (remainingBalanceEl) remainingBalanceEl.textContent = '$0.00';
    }
}