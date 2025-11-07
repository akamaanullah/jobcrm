// User Notifications Management
document.addEventListener('DOMContentLoaded', function () {
    // Initialize DOM elements
    const searchInput = document.getElementById('notificationSearchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const notificationsContainer = document.getElementById('notificationsContainer');
    const loadingElement = document.getElementById('notificationsLoading');
    const markAllReadBtn = document.querySelector('.btn-back');

    // Load initial data
    loadNotifications();

    // Add event listeners
    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadNotifications, 300));
    }

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllNotificationsRead);
    }

    // Filter button event listeners
    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            // Load notifications with new filter
            loadNotifications();
        });
    });

    // Add modal event listeners
    setupModalEventListeners();
});

// Setup modal event listeners
function setupModalEventListeners() {
    // Payment Request Modal
    const paymentRequestModal = document.getElementById('paymentRequestModal');
    if (paymentRequestModal) {
        paymentRequestModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const notificationId = button.getAttribute('data-notification-id');
            if (notificationId) {
                loadFormData('payment', notificationId);
            }
        });
    }

    // Final Visit Request Modal
    const finalVisitRequestModal = document.getElementById('finalVisitRequestModal');
    if (finalVisitRequestModal) {
        finalVisitRequestModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const notificationId = button.getAttribute('data-notification-id');
            if (notificationId) {
                loadFormData('finalVisit', notificationId);
            }
        });
    }

    // Job Completed Modal
    const jobCompletedModal = document.getElementById('jobCompletedModal');
    if (jobCompletedModal) {
        jobCompletedModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const notificationId = button.getAttribute('data-notification-id');
            if (notificationId) {
                loadFormData('jobCompleted', notificationId);
            }
        });
    }

    // Partial Payment Modal
    const partialPaymentModal = document.getElementById('partialPaymentModal');
    if (partialPaymentModal) {
        partialPaymentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const notificationId = button.getAttribute('data-notification-id');
            if (notificationId) {
                loadFormData('partialPayment', notificationId);
            }
        });
    }
}

// Load form data for modals
async function loadFormData(type, notificationId) {
    try {
        const response = await fetch(`assets/api/get_form_data.php?type=${type}&notification_id=${notificationId}`);
        const result = await response.json();

        if (result.success) {
            let modalBodyId, formHTML;

            switch (type) {
                case 'payment':
                    modalBodyId = 'paymentRequestModalBody';
                    formHTML = createPaymentRequestFormHTML(result.data);
                    break;
                case 'finalVisit':
                    modalBodyId = 'finalVisitRequestModalBody';
                    formHTML = createFinalVisitRequestFormHTML(result.data);
                    break;
                case 'jobCompleted':
                    modalBodyId = 'jobCompletedModalBody';
                    formHTML = createJobCompletedFormHTML(result.data);
                    break;
                case 'partialPayment':
                    modalBodyId = 'partialPaymentModalBody';
                    formHTML = createPartialPaymentFormHTML(result.data);
                    break;
            }

            if (modalBodyId && formHTML) {
                const modalBody = document.getElementById(modalBodyId);
                if (modalBody) {
                    modalBody.innerHTML = formHTML;
                }
            }
        } else {
            console.error('Load Form Data Error:', result.message);
        }
    } catch (error) {
        console.error('Load Form Data Error:', error);
    }
}

// Create form HTML functions
function createPaymentRequestFormHTML(data) {
    return `
        <div class="form-details-container">
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-credit-card text-primary"></i> Payment Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-currency-dollar"></i> Payment Amount
                                </label>
                                <div class="detail-value amount">$${data.amount || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-credit-card"></i> Payment Platform
                                </label>
                                <div class="detail-value">${data.payment_platform || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createFinalVisitRequestFormHTML(data) {
    return `
        <div class="form-details-container">
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-calendar-check text-primary"></i> Final Visit Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-calendar"></i> Visit Date & Time
                                </label>
                                <div class="detail-value">${data.visit_date_time || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-currency-dollar"></i> Estimated Amount
                                </label>
                                <div class="detail-value amount">$${data.estimated_amount || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createJobCompletedFormHTML(data) {
    return `
        <div class="form-details-container">
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <h6><i class="bi bi-check-circle text-success"></i> Job Completion Information</h6>
                </div>
                <div class="detail-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">
                                    <i class="bi bi-calendar"></i> Completion Date
                                </label>
                                <div class="detail-value">${data.created_at || 'N/A'}</div>
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
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createPartialPaymentFormHTML(data) {
    const formatDateTime = (dateString) => {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const isProcessed = data.status && data.status !== 'pending';
    const hasScreenshot = data.screenshot_path && data.screenshot_path.trim() !== '';
    const requestedAmount = parseFloat(data.requested_amount || 0);
    const estimatedAmount = parseFloat(data.estimated_amount || 0);
    const paymentPercentage = estimatedAmount > 0 ? ((requestedAmount / estimatedAmount) * 100).toFixed(1) : 0;

    return `
        <div class="form-details">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-3">
                        <div class="payment-icon me-3">
                            <i class="bi bi-cash-stack fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Partial Payment ${data.status === 'approved' ? 'Approved' : data.status === 'rejected' ? 'Rejected' : 'Request'}</h5>
                            <p class="text-muted mb-0">${data.status === 'approved' ? 'Your payment request has been approved and processed' : data.status === 'rejected' ? 'Your payment request was not approved' : 'Your payment request is under review'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Information Grid -->
            <div class="row">
                <!-- Job Information -->
                <div class="col-md-6 mb-4">
                    <div class="info-card h-100">
                        <div class="info-card-header">
                            <i class="bi bi-briefcase text-primary"></i>
                            <h6 class="mb-0">Job Information</h6>
                        </div>
                        <div class="info-card-body">
                            <div class="info-item">
                                <span class="info-label">Job Title:</span>
                                <span class="info-value">${data.job_title || 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Job Address:</span>
                                <span class="info-value">${data.job_address || 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Vendor Name:</span>
                                <span class="info-value">${data.vendor_name || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="col-md-6 mb-4">
                    <div class="info-card h-100">
                        <div class="info-card-header">
                            <i class="bi bi-currency-dollar text-success"></i>
                            <h6 class="mb-0">Payment Information</h6>
                        </div>
                        <div class="info-card-body">
                            <div class="info-item">
                                <span class="info-label">Requested Amount:</span>
                                <span class="info-value">$${requestedAmount.toFixed(2)}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estimated Amount:</span>
                                <span class="info-value">$${estimatedAmount.toFixed(2)}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span class="badge ${isProcessed ? (data.status === 'approved' ? 'bg-success' : 'bg-danger') : 'bg-warning'}">${data.status ? data.status.toUpperCase() : 'PENDING'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="bi bi-calculator text-warning"></i>
                            <h6 class="mb-0">Payment Details</h6>
                        </div>
                        <div class="info-card-body">
                            <div class="payment-details-grid">
                                <div class="payment-metric">
                                    <div class="metric-label">Requested Amount</div>
                                    <div class="metric-value text-primary">$${requestedAmount.toFixed(2)}</div>
                                    <div class="metric-percentage">${paymentPercentage}% of total</div>
                                </div>
                                <div class="payment-metric">
                                    <div class="metric-label">Estimated Amount</div>
                                    <div class="metric-value text-info">$${estimatedAmount.toFixed(2)}</div>
                                    <div class="metric-percentage">Total job value</div>
                                </div>
                                <div class="payment-metric">
                                    <div class="metric-label">Request Date</div>
                                    <div class="metric-value text-secondary">${formatDateTime(data.created_at)}</div>
                                    <div class="metric-percentage">When requested</div>
                                </div>
                                <div class="payment-metric">
                                    <div class="metric-label">Status</div>
                                    <div class="metric-value">
                                        <span class="badge ${isProcessed ? (data.status === 'approved' ? 'bg-success' : 'bg-danger') : 'bg-warning'} fs-6">
                                            ${data.status ? data.status.toUpperCase() : 'PENDING'}
                                        </span>
                                    </div>
                                    <div class="metric-percentage">Current status</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Screenshot Section (if exists) -->
            ${hasScreenshot ? `
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="bi bi-image text-primary"></i>
                            <h6 class="mb-0">Payment Screenshot</h6>
                        </div>
                        <div class="info-card-body">
                            <div class="screenshot-container">
                                <img src="../${data.screenshot_path}" alt="Payment Screenshot" class="screenshot-image" onclick="window.open('../${data.screenshot_path}', '_blank')">
                                <div class="screenshot-actions mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.open('../${data.screenshot_path}', '_blank')">
                                        <i class="bi bi-eye"></i> View Full Size
                                    </button>
                                    <a href="../${data.screenshot_path}" download class="btn btn-outline-success btn-sm ms-2">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Important Notes -->
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill text-info me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Important Information</h6>
                        <ul class="mb-0">
                            <li>This is a <strong>partial payment request</strong> for ongoing work</li>
                            <li>${data.status === 'approved' ? 'Your payment has been <strong>approved</strong> and will be processed shortly' : data.status === 'rejected' ? 'Your payment request was <strong>rejected</strong>. You can request again or complete the job' : 'Your payment request is <strong>under review</strong> by the admin'}</li>
                            <li>Final payment will be processed upon <strong>job completion</strong></li>
                            <li>You can <strong>chat with the vendor</strong> for any questions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Modal Scroll Fix */
            #partialPaymentModal .modal-dialog {
                max-height: 90vh !important;
            }
            #partialPaymentModal .modal-content {
                max-height: 90vh !important;
            }
            #partialPaymentModal .modal-body {
                max-height: calc(90vh - 120px) !important;
                overflow-y: auto !important;
                padding: 20px !important;
            }
            
            /* Form Styling */
            .info-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 16px;
            }
            .info-card-header {
                background: #fff;
                padding: 12px 16px;
                border-bottom: 1px solid #e9ecef;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .info-card-body {
                padding: 16px;
            }
            .info-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f1f3f4;
            }
            .info-item:last-child {
                border-bottom: none;
            }
            .info-label {
                font-weight: 600;
                color: #6c757d;
                font-size: 0.9rem;
            }
            .info-value {
                font-weight: 500;
                color: #212529;
            }
            
            /* Screenshot Styling */
            .screenshot-container {
                text-align: center;
            }
            .screenshot-image {
                max-width: 100%;
                max-height: 300px;
                border: 2px solid #e9ecef;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .screenshot-image:hover {
                transform: scale(1.02);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .screenshot-actions {
                display: flex;
                justify-content: center;
                gap: 8px;
            }
            
            /* Payment Details Grid */
            .payment-details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
                margin-top: 16px;
            }
            
            .payment-metric {
                text-align: center;
                padding: 20px 16px;
                background: #fff;
                border-radius: 12px;
                border: 1px solid #e9ecef;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .payment-metric:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            }
            
            .metric-label {
                font-size: 0.85rem;
                color: #6c757d;
                font-weight: 600;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .metric-value {
                font-size: 1rem;
                font-weight: 700;
                margin-bottom: 6px;
                line-height: 1.2;
            }
            
            .metric-percentage {
                font-size: 0.75rem;
                color: #6c757d;
                font-weight: 500;
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .payment-details-grid {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 12px;
                }
                .payment-metric {
                    padding: 16px 12px;
                }
                .metric-value {
                    font-size: 1.3rem;
                }
                .screenshot-image {
                    max-height: 200px;
                }
                .screenshot-actions {
                    flex-direction: column;
                    align-items: center;
                }
                .screenshot-actions .btn {
                    width: 100%;
                    max-width: 200px;
                }
            }
            
            @media (max-width: 576px) {
                .payment-details-grid {
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                }
                .payment-metric {
                    padding: 14px 10px;
                }
                .metric-value {
                    font-size: 1.2rem;
                }
                .metric-label {
                    font-size: 0.8rem;
                }
            }
        </style>
    `;
}

// Load notifications from API
async function loadNotifications() {
    try {
        const searchInput = document.getElementById('notificationSearchInput');
        const activeFilter = document.querySelector('.filter-btn.active');
        const sortBy = document.getElementById('notificationSortBy');

        const params = new URLSearchParams();

        if (searchInput && searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }

        if (activeFilter) {
            const filter = activeFilter.getAttribute('data-filter');
            if (filter !== 'all') {
                params.append('filter', filter);
            }
        }

        if (sortBy && sortBy.value) {
            params.append('sort_by', sortBy.value);
        }

        const response = await fetch(`assets/api/get_notifications.php?${params.toString()}`);
        const result = await response.json();

        if (result.success) {
            updateMetrics(result.stats);
            displayNotifications(result.data);
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Load Notifications Error:', error);
        showError('Failed to load notifications');
    }
}

// Update metrics cards
function updateMetrics(stats) {
    const totalCount = document.getElementById('totalNotificationsCount');
    const unreadCount = document.getElementById('unreadNotificationsCount');
    const pendingCount = document.getElementById('pendingNotificationsCount');
    const resolvedCount = document.getElementById('resolvedNotificationsCount');

    if (totalCount) {
        totalCount.textContent = stats.total_notifications || 0;
    }

    if (unreadCount) {
        unreadCount.textContent = stats.unread_notifications || 0;
    }

    if (pendingCount) {
        pendingCount.textContent = stats.pending_notifications || 0;
    }

    if (resolvedCount) {
        resolvedCount.textContent = stats.resolved_notifications || 0;
    }

    // Update filter button counts
    updateFilterCounts(stats);
}

// Update filter button counts
function updateFilterCounts(stats) {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        const filter = button.getAttribute('data-filter');
        const countElement = button.querySelector('.filter-count');

        if (countElement) {
            switch (filter) {
                case 'all':
                    countElement.textContent = stats.total_notifications || 0;
                    break;
                case 'accepted':
                    countElement.textContent = stats.visit_requests || 0;
                    break;
                case 'visit':
                    countElement.textContent = stats.visit_requests || 0;
                    break;
                case 'payment':
                    countElement.textContent = stats.payment_ready || 0;
                    break;
                case 'approval':
                    countElement.textContent = stats.final_approvals || 0;
                    break;
                case 'completed':
                    countElement.textContent = stats.job_completed || 0;
                    break;
                case 'rejected':
                    countElement.textContent = stats.rejected_requests || 0;
                    break;
            }
        }
    });
}

// Display notifications in container
function displayNotifications(notifications) {
    const notificationsContainer = document.getElementById('notificationsContainer');
    const loadingElement = document.getElementById('notificationsLoading');

    if (loadingElement) {
        loadingElement.remove();
    }

    if (!notifications || notifications.length === 0) {
        notificationsContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">No Notifications Found</h4>
                <p class="text-muted">No notifications match your current filter criteria.</p>
            </div>
        `;
        return;
    }

    const notificationsHTML = notifications.map(notification => createNotificationCard(notification)).join('');
    notificationsContainer.innerHTML = notificationsHTML;
}

// Create notification card HTML
function createNotificationCard(notification) {
    const iconClass = getNotificationIcon(notification.type);
    const statusClass = getNotificationStatusClass(notification.type, notification.is_read, notification.action_required);
    const statusText = getNotificationStatusText(notification.type, notification.action_required);
    const isUnread = !notification.is_read ? 'unread' : '';

    return `
        <div class="request-card ${isUnread}" data-notification-id="${notification.id}">
            <div class="request-icon ${statusClass}">
                <i class="${iconClass}"></i>
            </div>
            <div class="request-content">
                <div class="request-header">
                    <h4>${getNotificationTitle(notification.type)}</h4>
                    <div class="request-meta">
                        <span class="request-time">${notification.created_ago}</span>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                </div>
                <p class="request-description">${notification.message}</p>
                <div class="request-actions">
                    ${getNotificationActions(notification)}
                </div>
            </div>
        </div>
    `;
}

// Get notification icon based on type
function getNotificationIcon(type) {
    const iconMap = {
        'visit_request': 'bi bi-eye-fill',
        'request_visit_accepted': 'bi bi-check-circle-fill',
        'visit_request_rejected': 'bi bi-x-circle-fill',
        'final_visit_request': 'bi bi-calendar-check-fill',
        'final_visit_request_accepted': 'bi bi-check-circle-fill',
        'final_visit_request_rejected': 'bi bi-x-circle-fill',
        'job_completed': 'bi bi-check-circle-fill',
        'request_vendor_payment': 'bi bi-credit-card-fill',
        'vendor_payment_accepted': 'bi bi-check-circle-fill',
        'vendor_payment_rejected': 'bi bi-x-circle-fill',
        'partial_payment_requested': 'bi bi-cash-stack',
        'partial_payment_accepted': 'bi bi-check-circle-fill',
        'partial_payment_rejected': 'bi bi-x-circle-fill',
        'vendor_added': 'bi bi-person-plus-fill'
    };
    return iconMap[type] || 'bi bi-bell-fill';
}

// Get notification status class
function getNotificationStatusClass(type, isRead, actionRequired) {
    if (isRead) {
        return 'request-icon-resolved';
    }

    const statusMap = {
        'visit_request': 'request-icon-pending',
        'request_visit_accepted': 'request-icon-accepted',
        'visit_request_rejected': 'request-icon-rejected',
        'final_visit_request': 'request-icon-pending',
        'final_visit_request_accepted': 'request-icon-accepted',
        'final_visit_request_rejected': 'request-icon-rejected',
        'job_completed': 'request-icon-accepted',
        'request_vendor_payment': actionRequired ? 'request-icon-payment' : 'request-icon-accepted',
        'vendor_payment_accepted': 'request-icon-accepted',
        'vendor_payment_rejected': 'request-icon-rejected',
        'partial_payment_requested': 'request-icon-pending',
        'partial_payment_accepted': 'request-icon-accepted',
        'partial_payment_rejected': 'request-icon-rejected',
        'vendor_added': 'request-icon-accepted'
    };
    return statusMap[type] || 'request-icon-pending';
}

// Get notification status text
function getNotificationStatusText(type, actionRequired) {
    const statusMap = {
        'visit_request': 'PENDING',
        'request_visit_accepted': 'ACCEPTED',
        'visit_request_rejected': 'REJECTED',
        'final_visit_request': 'PENDING',
        'final_visit_request_accepted': 'APPROVED',
        'final_visit_request_rejected': 'REJECTED',
        'job_completed': 'COMPLETED',
        'request_vendor_payment': actionRequired ? 'PENDING' : 'SUBMITTED',
        'vendor_payment_accepted': 'ACCEPTED',
        'vendor_payment_rejected': 'REJECTED',
        'partial_payment_requested': 'PENDING',
        'partial_payment_accepted': 'ACCEPTED',
        'partial_payment_rejected': 'REJECTED',
        'vendor_added': 'ADDED'
    };
    return statusMap[type] || 'PENDING';
}

// Get notification title
function getNotificationTitle(type) {
    const titleMap = {
        'visit_request': 'Visit Request',
        'request_visit_accepted': 'Request Accepted',
        'visit_request_rejected': 'Visit Request',
        'final_visit_request': 'Final Visit Request',
        'final_visit_request_accepted': 'Visit Approved',
        'final_visit_request_rejected': 'Final Visit Request',
        'job_completed': 'Job Completed',
        'request_vendor_payment': 'Payment Request',
        'vendor_payment_accepted': 'Payment Ready',
        'vendor_payment_rejected': 'Payment Request',
        'partial_payment_requested': 'Partial Payment Request',
        'partial_payment_accepted': 'Partial Payment Approved',
        'partial_payment_rejected': 'Partial Payment Request',
        'vendor_added': 'Vendor Added'
    };
    return titleMap[type] || 'Notification';
}

// Get notification actions based on type
function getNotificationActions(notification) {
    const actions = [];


    // Always show View Job button if job_id exists
    if (notification.job_id) {
        actions.push(`
            <a href="view-job.php?id=${notification.job_id}">
                <button class="btn-action btn-job">
                    <i class="bi bi-eye"></i> View Job
                </button>
            </a>
        `);
    }

    // Add specific actions based on notification type
    switch (notification.type) {
        case 'request_vendor_payment':
            actions.push(`
                <button class="btn-action btn-view-form" data-bs-toggle="modal" data-bs-target="#paymentRequestModal" data-notification-id="${notification.id}">
                    <i class="bi bi-eye"></i> View Details
                </button>
            `);
            break;

        case 'final_visit_request_accepted':
            actions.push(`
                <button class="btn-action btn-view-form" data-bs-toggle="modal" data-bs-target="#finalVisitRequestModal" data-notification-id="${notification.id}">
                    <i class="bi bi-eye"></i> View Details
                </button>
            `);
            if (notification.vendor_id && notification.vendor_name) {
                const vendorAvatar = getInitials(notification.vendor_name);
                actions.push(`
                    <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#userChatModal" 
                            data-vendor="${notification.vendor_name}" data-vendor-avatar="${vendorAvatar}" data-vendor-id="${notification.vendor_id}" data-job-id="${notification.job_number || 'N/A'}">
                        <i class="bi bi-chat"></i> Chat with Vendor
                    </button>
                `);
            }
            break;

        case 'job_completed':
            actions.push(`
                <button class="btn-action btn-view-form" data-bs-toggle="modal" data-bs-target="#jobCompletedModal" data-notification-id="${notification.id}">
                    <i class="bi bi-eye"></i> View Details
                </button>
            `);
            break;

        case 'request_visit_accepted':
        case 'vendor_payment_accepted':
            if (notification.vendor_id && notification.vendor_name) {
                const vendorAvatar = getInitials(notification.vendor_name);
                actions.push(`
                    <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#userChatModal" 
                            data-vendor="${notification.vendor_name}" data-vendor-avatar="${vendorAvatar}" data-vendor-id="${notification.vendor_id}" data-job-id="${notification.job_number || 'N/A'}">
                        <i class="bi bi-chat"></i> Chat with Vendor
                    </button>
                `);
            }
            break;

        case 'partial_payment_accepted':
            actions.push(`
                <button class="btn-action btn-view-form" data-bs-toggle="modal" data-bs-target="#partialPaymentModal" data-notification-id="${notification.id}">
                    <i class="bi bi-eye"></i> View Details
                </button>
            `);
            if (notification.vendor_id && notification.vendor_name) {
                const vendorAvatar = getInitials(notification.vendor_name);
                actions.push(`
                    <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#userChatModal" 
                            data-vendor="${notification.vendor_name}" data-vendor-avatar="${vendorAvatar}" data-vendor-id="${notification.vendor_id}" data-job-id="${notification.job_number || 'N/A'}">
                        <i class="bi bi-chat"></i> Chat with Vendor
                    </button>
                `);
            }
            break;

        case 'partial_payment_requested':
        case 'partial_payment_rejected':
            actions.push(`
                <button class="btn-action btn-view-form" data-bs-toggle="modal" data-bs-target="#partialPaymentModal" data-notification-id="${notification.id}">
                    <i class="bi bi-eye"></i> View Details
                </button>
            `);
            break;
    }

    return actions.join('');
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
            const notificationCards = document.querySelectorAll('.request-card');
            notificationCards.forEach(card => {
                card.classList.remove('unread');
            });

            // Update header and sidebar badges
            if (typeof updateUnreadNotificationCount === 'function') {
                await updateUnreadNotificationCount();
            }

            // Reload notifications to update metrics
            loadNotifications();

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

// Get initials from name
function getInitials(name) {
    if (!name) return 'U';
    return name.split(' ').map(word => word.charAt(0).toUpperCase()).join('').substring(0, 2);
}

// Show error message
function showError(message) {
    const notificationsContainer = document.getElementById('notificationsContainer');
    const loadingElement = document.getElementById('notificationsLoading');

    if (loadingElement) {
        loadingElement.remove();
    }

    notificationsContainer.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h4 class="text-danger mt-3">Error</h4>
            <p class="text-muted">${message}</p>
            <button class="btn btn-primary btn-sm" onclick="loadNotifications()">
                <i class="bi bi-arrow-clockwise"></i> Retry
            </button>
        </div>
    `;
}

// Show notification message
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Add to body
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
