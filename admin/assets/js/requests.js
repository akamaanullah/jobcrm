// Admin Notifications Management
class AdminNotifications {
    constructor() {
        this.currentFilter = 'all';
        this.notifications = [];
        this.stats = {};
        this.init();
    }

    init() {
        this.checkURLParameters();
        this.loadNotifications();
        this.bindEvents();
    }

    checkURLParameters() {
        // Check for URL parameters to set initial filter
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('filter');
        const requestId = urlParams.get('request_id');

        if (filter) {
            this.currentFilter = filter;
            // Set the active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.filter === filter) {
                    btn.classList.add('active');
                }
            });
        }

        if (requestId) {
            // Store request ID for highlighting specific request
            this.highlightRequestId = requestId;
        }
    }

    bindEvents() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleFilterChange(e.target.closest('.filter-btn').dataset.filter);
            });
        });

        // Mark all as read button
        const markAllReadBtn = document.querySelector('.btn-back');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
    }

    async loadNotifications(filter = null) {
        // Use currentFilter if no filter specified
        if (!filter) {
            filter = this.currentFilter;
        }
        try {
            this.showLoading();

            const response = await fetch(`assets/api/get_notifications.php?filter=${filter}`);
            const data = await response.json();

            if (data.success) {
                this.notifications = data.data;
                this.stats = data.stats;
                this.renderNotifications();
                this.updateStats();
                this.updateFilterCounts();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Load Notifications Error:', error);
            this.showError('Failed to load notifications');
        }
    }

    renderNotifications() {
        const container = document.getElementById('notificationsContainer');

        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No notifications found</h4>
                    <p class="text-muted">There are no notifications matching your current filter.</p>
                </div>
            `;
            return;
        }

        const notificationsHTML = this.notifications.map(notification =>
            this.createNotificationCard(notification)
        ).join('');

        container.innerHTML = notificationsHTML;
        this.bindNotificationEvents();
    }

    createNotificationCard(notification) {
        const iconClass = this.getNotificationIcon(notification.type);
        const statusBadge = this.getStatusBadge(notification);
        const actionButtons = this.createActionButtons(notification);

        return `
            <div class="request-card" data-notification-id="${notification.id}">
                <div class="request-icon ${notification.is_read ? '' : 'unread'}">
                    <i class="${iconClass}"></i>
                </div>
                <div class="request-content">
                    <div class="request-header">
                        <h4>${this.getNotificationTitle(notification.type)}</h4>
                        <div class="request-meta">
                            <span class="request-time">${notification.time_ago}</span>
                            ${statusBadge}
                        </div>
                    </div>
                    <p class="request-description">${notification.message}</p>
                    <div class="request-actions">
                        ${actionButtons}
                    </div>
                </div>
            </div>
        `;
    }

    getNotificationIcon(type) {
        const icons = {
            'visit_request': 'bi bi-eye',
            'final_visit_request': 'bi bi-check-circle',
            'request_vendor_payment': 'bi bi-credit-card',
            'partial_payment_requested': 'bi bi-cash-stack',
            'vendor_added': 'bi bi-person-plus',
            'job_completed': 'bi bi-check-circle',
            'invoice_reminder': 'bi bi-receipt'
        };
        return icons[type] || 'bi bi-bell';
    }

    getNotificationTitle(type) {
        const titles = {
            'visit_request': 'Visit Request',
            'final_visit_request': 'Final Visit Request',
            'request_vendor_payment': 'Payment Request',
            'partial_payment_requested': 'Partial Payment Request',
            'vendor_added': 'Vendor Added',
            'job_completed': 'Job Completed',
            'invoice_reminder': 'Invoice Reminder'
        };
        return titles[type] || 'Notification';
    }

    getStatusBadge(notification) {
        // Special badge for invoice_reminder
        if (notification.type === 'invoice_reminder') {
            // Check if invoice has been generated (action_required = 0)
            if (notification.action_required == 0) {
                return '<span class="status-badge status-completed">RESOLVED</span>';
            } else {
                return '<span class="status-badge status-invoice">ACTION NEEDED</span>';
            }
        }

        if (notification.action_required == 1 && notification.is_read == 0) {
            return '<span class="status-badge status-pending">PENDING</span>';
        } else if (notification.action_required == 0 && notification.is_read == 0) {
            return '<span class="status-badge status-unread">UNREAD</span>';
        } else if (notification.action_required == 0) {
            return '<span class="status-badge status-completed">RESOLVED</span>';
        } else {
            return '<span class="status-badge status-read">READ</span>';
        }
    }

    createActionButtons(notification) {
        let buttons = [];

        // Special handling for invoice_reminder - show Generate Invoice button
        if (notification.type === 'invoice_reminder') {
            // Only show Generate Invoice button if not yet resolved
            if (notification.action_required == 1) {
                buttons.push(`
                    <a href="invoices-generator.php?job_id=${notification.job_id}">
                        <button class="btn-action btn-primary">
                            <i class="bi bi-receipt"></i> Generate Invoice
                        </button>
                    </a>
                `);
            } else {
                // Invoice already generated - show view invoice button
                const invoiceParam = notification.invoice_number ? `invoice=${notification.invoice_number}` : `job_id=${notification.job_id}`;
                buttons.push(`
                    <a href="view-invoice.php?${invoiceParam}">
                        <button class="btn-action btn-success">
                            <i class="bi bi-eye"></i> View Invoice
                        </button>
                    </a>
                `);
            }
        }
        // Action required notifications get accept/reject buttons (except invoice_reminder and partial_payment_requested)
        else if (notification.action_required == 1 && notification.type !== 'partial_payment_requested') {
            buttons.push(`
                <button class="btn-action btn-accept" data-notification-id="${notification.id}" data-action="accept">
                    <i class="bi bi-check-circle"></i> Accept
                </button>
            `);
            buttons.push(`
                <button class="btn-action btn-reject" data-notification-id="${notification.id}" data-action="reject">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            `);
        }

        // View form buttons for specific notification types
        switch (notification.type) {
            case 'final_visit_request':
                buttons.push(`
                    <button class="btn-action btn-view-form" data-notification-id="${notification.id}" data-modal="finalVisitRequestModal">
                        <i class="bi bi-eye"></i> View Form
                    </button>
                `);
                break;
            case 'job_completed':
                buttons.push(`
                    <button class="btn-action btn-view-form" data-notification-id="${notification.id}" data-modal="jobCompletedModal">
                        <i class="bi bi-eye"></i> View Form
                    </button>
                `);
                break;
            case 'request_vendor_payment':
                buttons.push(`
                    <button class="btn-action btn-view-form" data-notification-id="${notification.id}" data-modal="paymentRequestModal">
                        <i class="bi bi-eye"></i> View Form
                    </button>
                `);
                break;
            case 'partial_payment_requested':
                buttons.push(`
                    <button class="btn-action btn-view-form" data-notification-id="${notification.id}" data-modal="partialPaymentRequestModal">
                        <i class="bi bi-eye"></i> View Form
                    </button>
                `);
                break;
        }

        // Job and vendor chat buttons
        if (notification.job_id) {
            buttons.push(`
                <a href="view-job.php?id=${notification.job_id}">
                    <button class="btn-action btn-job">
                        <i class="bi bi-eye"></i> Job
                    </button>
                </a>
            `);
        }

        // Mark as read button for unread notifications
        if (notification.is_read == 0) {
            buttons.push(`
                <button class=" btn-action btn-mark-read" data-notification-id="${notification.id}">
                    <i class="bi bi-check"></i> Mark as Read
                </button>
            `);
        }

        return buttons.join('');
    }

    bindNotificationEvents() {
        // Accept/Reject buttons
        document.querySelectorAll('.btn-accept, .btn-reject').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const notificationId = e.target.closest('button').dataset.notificationId;
                const action = e.target.closest('button').dataset.action;
                this.handleNotificationAction(notificationId, action);
            });
        });

        // View form buttons
        document.querySelectorAll('.btn-view-form').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const notificationId = e.target.closest('button').dataset.notificationId;
                const modal = e.target.closest('button').dataset.modal;
                this.loadFormData(notificationId, modal);
            });
        });

        // Mark as read buttons
        document.querySelectorAll('.btn-mark-read').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const notificationId = e.target.closest('button').dataset.notificationId;
                this.markNotificationAsRead(notificationId);
            });
        });
    }

    async handleNotificationAction(notificationId, action) {
        try {
            const response = await fetch('assets/api/notification_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId,
                    action: action
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.loadNotifications(this.currentFilter); // Reload notifications
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Notification Action Error:', error);
            this.showError('Failed to process action');
        }
    }

    async loadFormData(notificationId, modalType) {
        try {
            const response = await fetch(`assets/api/get_form_data.php?notification_id=${notificationId}&type=${modalType}`);
            const data = await response.json();

            if (data.success) {
                this.populateModal(modalType, data.data);
                // Open the modal
                const modal = new bootstrap.Modal(document.getElementById(modalType));
                modal.show();

                // Fix modal height for partial payment modal
                if (modalType === 'partialPaymentRequestModal') {
                    const modalElement = document.getElementById(modalType);
                    const modalDialog = modalElement.querySelector('.modal-dialog');
                    const modalBody = modalElement.querySelector('.modal-body');

                    // Set proper heights
                    modalDialog.style.maxHeight = '90vh';
                    modalBody.style.maxHeight = 'calc(90vh - 120px)';
                    modalBody.style.overflowY = 'auto';

                    // Initialize partial payment modal functionality
                    this.initializePartialPaymentModal(notificationId, data.data);
                }
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Load Form Data Error:', error);
            this.showError('Failed to load form data');
        }
    }

    populateModal(modalType, formData) {
        const modalBody = document.getElementById(`${modalType}Body`);

        switch (modalType) {
            case 'paymentRequestModal':
                modalBody.innerHTML = this.createPaymentFormHTML(formData);
                break;
            case 'finalVisitRequestModal':
                modalBody.innerHTML = this.createFinalVisitFormHTML(formData);
                break;
            case 'jobCompletedModal':
                modalBody.innerHTML = this.createJobCompletedFormHTML(formData);
                break;
            case 'partialPaymentRequestModal':
                modalBody.innerHTML = this.createPartialPaymentFormHTML(formData);
                break;
        }
    }

    createPaymentFormHTML(data) {
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

        // Use processed data from API (same logic as view-job.js)
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
                                        <i class="bi bi-building"></i> Business/Type
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
                            <i class="bi bi-person-circle text-success"></i> Vendor Information
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
                `<a href="tel:${data.vendor_phone}" class="text-decoration-none ">
                                                <i class="bi bi-telephone me-1"></i>
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
                            <i class="bi bi-info-circle text-info"></i> Request Details
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
                                ${data.created_at ? this.getTimeAgo(data.created_at) : 'N/A'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    createFinalVisitFormHTML(data) {
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
                            <i class="bi bi-calendar-event"></i> Request Details
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
                            <i class="bi bi-info-circle"></i> Additional Information
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

    createJobCompletedFormHTML(data) {
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
                    <div class="detail-card-body" >
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

    handleFilterChange(filter) {
        // Update active filter button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-filter="${filter}"]`).classList.add('active');

        this.currentFilter = filter;
        this.loadNotifications(filter);
    }

    updateStats() {
        document.getElementById('totalNotifications').textContent = this.stats.total || 0;
        document.getElementById('unreadNotifications').textContent = this.stats.unread || 0;
        document.getElementById('pendingNotifications').textContent = this.stats.pending || 0;
        document.getElementById('resolvedNotifications').textContent = this.stats.resolved || 0;
    }

    updateFilterCounts() {
        document.getElementById('countAll').textContent = this.stats.total || 0;
        document.getElementById('countVisit').textContent = this.stats.visit_requests || 0;
        document.getElementById('countApproval').textContent = this.stats.final_approvals || 0;
        document.getElementById('countPayment').textContent = this.stats.payment_requests || 0;
        document.getElementById('countVendor').textContent = this.stats.vendor_added || 0;
        document.getElementById('countCompleted').textContent = this.stats.job_completed || 0;
        document.getElementById('countInvoice').textContent = this.stats.invoice_reminders || 0;
    }

    // Helper function for time ago
    getTimeAgo(dateString) {
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

    async markAllAsRead() {
        try {
            const response = await fetch('assets/api/mark_all_read.php', {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.loadNotifications(this.currentFilter);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Mark All Read Error:', error);
            this.showError('Failed to mark all as read');
        }
    }

    async markNotificationAsRead(notificationId) {
        try {
            const response = await fetch('assets/api/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Notification marked as read');
                this.loadNotifications(this.currentFilter);
            } else {
                this.showError(data.message || 'Failed to mark notification as read');
            }
        } catch (error) {
            console.error('Mark Notification Read Error:', error);
            this.showError('Failed to mark notification as read');
        }
    }

    showLoading() {
        const container = document.getElementById('notificationsContainer');
        container.innerHTML = `
            <div class="text-center py-5" id="loadingNotifications">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading notifications...</p>
            </div>
        `;
    }

    createPartialPaymentFormHTML(data) {
        // Format date and time properly
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

        // Calculate payment percentage
        const paymentPercentage = data.estimated_amount > 0 ?
            ((parseFloat(data.requested_amount || 0) / parseFloat(data.estimated_amount)) * 100).toFixed(1) :
            '0.0';

        // Check if payment is already processed
        const isProcessed = data.status && data.status !== 'pending';
        const hasScreenshot = data.screenshot_path && data.screenshot_path.trim() !== '';

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
                                <h5 class="mb-1">Partial Payment Request</h5>
                                <p class="text-muted mb-0">Review payment details and approve or reject</p>
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
                                    <span class="info-label">Job ID:</span>
                                    <span class="info-value">#${data.job_id || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Job Address:</span>
                                    <span class="info-value">${data.job_address || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Information -->
                    <div class="col-md-6 mb-4">
                        <div class="info-card h-100">
                            <div class="info-card-header">
                                <i class="bi bi-person-badge text-success"></i>
                                <h6 class="mb-0">Vendor Information</h6>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Vendor Name:</span>
                                    <span class="info-value">${data.vendor_name || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Vendor ID:</span>
                                    <span class="info-value">#${data.vendor_id || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value">${data.vendor_phone || 'N/A'}</span>
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
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="payment-metric">
                                            <div class="metric-label">Requested Amount</div>
                                            <div class="metric-value text-primary">$${parseFloat(data.requested_amount || 0).toFixed(2)}</div>
                                            <div class="metric-percentage">${paymentPercentage}% of total</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="payment-metric">
                                            <div class="metric-label">Estimated Amount</div>
                                            <div class="metric-value text-info">$${parseFloat(data.estimated_amount || 0).toFixed(2)}</div>
                                            <div class="metric-percentage">Total job value</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="payment-metric">
                                            <div class="metric-label">Request Date</div>
                                            <div class="metric-value text-secondary">${formatDateTime(data.created_at)}</div>
                                            <div class="metric-percentage">When requested</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
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
                </div>

                <!-- Request Information -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="info-card h-100">
                            <div class="info-card-header">
                                <i class="bi bi-person text-info"></i>
                                <h6 class="mb-0">Request Information</h6>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Requested By:</span>
                                    <span class="info-value">${data.user_name || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Request Date:</span>
                                    <span class="info-value">${formatDateTime(data.created_at)}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status:</span>
                                    <span class="badge bg-warning text-dark">${data.status || 'Pending'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="col-md-6 mb-4">
                        <div class="info-card h-100">
                            <div class="info-card-header">
                                <i class="bi bi-calculator text-secondary"></i>
                                <h6 class="mb-0">Payment Summary</h6>
                            </div>
                            <div class="info-card-body">
                                <div class="summary-item">
                                    <span class="summary-label">Current Request:</span>
                                    <span class="summary-value">$${parseFloat(data.requested_amount || 0).toFixed(2)}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Total Paid After:</span>
                                    <span class="summary-value">$${(parseFloat(data.total_paid || 0) + parseFloat(data.requested_amount || 0)).toFixed(2)}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Remaining After:</span>
                                    <span class="summary-value">$${parseFloat(data.remaining_balance || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Screenshot Section -->
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
                                    <img src="../${data.screenshot_path}" alt="Payment Screenshot" class="screenshot-image" onclick="window.open('${data.screenshot_path}', '_blank')">
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
                                <li>The vendor can still <strong>complete the job</strong> after receiving this payment</li>
                                <li>Final payment will be processed upon <strong>job completion</strong></li>
                                <li>Please review all amounts carefully before approval</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* Modal Scroll Fix */
                #partialPaymentRequestModal .modal-dialog {
                    max-height: 90vh !important;
                }
                #partialPaymentRequestModal .modal-content {
                    max-height: 90vh !important;
                }
                #partialPaymentRequestModal .modal-body {
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
                .payment-metric {
                    text-align: center;
                    padding: 16px 8px;
                    background: #fff;
                    border-radius: 8px;
                    border: 1px solid #e9ecef;
                    margin-bottom: 8px;
                }
                .metric-label {
                    font-size: 0.8rem;
                    color: #6c757d;
                    font-weight: 500;
                    margin-bottom: 4px;
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
                }
                .summary-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 6px 0;
                }
                .summary-label {
                    font-weight: 500;
                    color: #6c757d;
                    font-size: 0.9rem;
                }
                .summary-value {
                    font-weight: 600;
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
                    border-color: #007bff;
                    transform: scale(1.02);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                }
                .screenshot-actions {
                    display: flex;
                    justify-content: center;
                    gap: 8px;
                }
                
                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .payment-metric {
                        margin-bottom: 12px;
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
            </style>
        `;
    }

    initializePartialPaymentModal(notificationId, formData) {
        // Store current notification ID
        this.currentPartialPaymentNotificationId = notificationId;

        // Check if payment is already processed
        const isProcessed = formData.status && formData.status !== 'pending';

        // Update modal footer based on status
        this.updateModalFooter(isProcessed);
    }

    updateModalFooter(isProcessed) {
        const modalFooter = document.querySelector('#partialPaymentRequestModal .modal-footer');
        if (!modalFooter) return;

        if (isProcessed) {
            // For processed requests, show only close button
            modalFooter.innerHTML = `
                <div class="d-flex justify-content-end w-100">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            `;
        } else {
            // For pending requests, show upload and action buttons
            modalFooter.innerHTML = `
                <div class="d-flex justify-content-between w-100">
                <div class="d-grid w-100">
                    <div class="d-flex justify-content-center w-100">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="button" class="btn btn-success me-2" id="approvePartialPayment">
                            <i class="bi bi-check-circle"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" id="rejectPartialPayment">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                    </div>
                    <div class="upload-section d-grid mt-4">
                        <label for="partialPaymentScreenshot" class="btn btn-back btn-sm">
                            <i class="bi bi-camera"></i> Upload Screenshot
                        </label>
                        <input type="file" id="partialPaymentScreenshot" accept="image/*" style="display: none;">
                        <span id="screenshotFileName" class="ms-2 text-muted small"></span>
                    </div>
                </div>
                </div>
            `;

            // Re-bind event handlers for the new buttons
            this.bindModalButtonEvents();
        }
    }

    bindModalButtonEvents() {
        // File upload handler
        const fileInput = document.getElementById('partialPaymentScreenshot');
        const fileNameSpan = document.getElementById('screenshotFileName');

        if (fileInput && fileNameSpan) {
            // Remove existing event listeners
            fileInput.removeEventListener('change', this.handleFileUpload);

            // Add new event listener
            this.handleFileUpload = (e) => {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        this.showError('Please select an image file');
                        fileInput.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        this.showError('File size must be less than 5MB');
                        fileInput.value = '';
                        return;
                    }

                    fileNameSpan.textContent = file.name;
                    fileNameSpan.className = 'ms-2 text-success small';
                } else {
                    fileNameSpan.textContent = '';
                }
            };

            fileInput.addEventListener('change', this.handleFileUpload);
        }

        // Approve button handler
        const approveBtn = document.getElementById('approvePartialPayment');
        if (approveBtn) {
            // Remove existing event listeners
            approveBtn.removeEventListener('click', this.handleApproveClick);

            // Add new event listener
            this.handleApproveClick = () => {
                this.handlePartialPaymentAction('accept');
            };

            approveBtn.addEventListener('click', this.handleApproveClick);
        }

        // Reject button handler
        const rejectBtn = document.getElementById('rejectPartialPayment');
        if (rejectBtn) {
            // Remove existing event listeners
            rejectBtn.removeEventListener('click', this.handleRejectClick);

            // Add new event listener
            this.handleRejectClick = () => {
                this.handlePartialPaymentAction('reject');
            };

            rejectBtn.addEventListener('click', this.handleRejectClick);
        }
    }

    async handlePartialPaymentAction(action) {
        try {
            const fileInput = document.getElementById('partialPaymentScreenshot');
            const file = fileInput.files[0];

            // For approve action, require screenshot
            if (action === 'accept' && !file) {
                this.showError('Please upload a screenshot before approving the partial payment');
                return;
            }

            // Show loading state
            const approveBtn = document.getElementById('approvePartialPayment');
            const rejectBtn = document.getElementById('rejectPartialPayment');
            const originalApproveText = approveBtn.innerHTML;
            const originalRejectText = rejectBtn.innerHTML;

            if (action === 'accept') {
                approveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                approveBtn.disabled = true;
            } else {
                rejectBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                rejectBtn.disabled = true;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('notification_id', this.currentPartialPaymentNotificationId);
            formData.append('action', action);

            if (file) {
                formData.append('screenshot', file);
            }

            const response = await fetch('assets/api/partial_payment_action.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('partialPaymentRequestModal'));
                if (modal) {
                    modal.hide();
                }

                // Reset form
                fileInput.value = '';
                document.getElementById('screenshotFileName').textContent = '';

                // Reload notifications
                this.loadNotifications(this.currentFilter);
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            console.error('Partial Payment Action Error:', error);
            this.showError('Failed to process partial payment action');
        } finally {
            // Reset button states
            const approveBtn = document.getElementById('approvePartialPayment');
            const rejectBtn = document.getElementById('rejectPartialPayment');
            approveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Approve';
            approveBtn.disabled = false;
            rejectBtn.innerHTML = '<i class="bi bi-x-circle"></i> Reject';
            rejectBtn.disabled = false;
        }
    }

    showError(message) {
        const container = document.getElementById('notificationsContainer');
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                <h4 class="mt-3 text-danger">Error</h4>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary" onclick="adminNotifications.loadNotifications('${this.currentFilter}')">
                    <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
            </div>
        `;
    }

    showSuccess(message) {
        // Create a temporary success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="bi bi-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminNotifications = new AdminNotifications();
});