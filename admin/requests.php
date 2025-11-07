<?php $pageTitle = 'Notifications'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>Notifications</h2>
                <p>Manage all system notifications and requests</p>
            </div>
            <div class="welcome-actions">
                <button class="btn btn-back" title="Mark All As Read">
                    <i class="bi bi-check-all"></i> Mark All As Read
                </button>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotal">
                <div class="metric-icon notifications">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalNotifications">0</h3>
                    <span class="metric-status text-primary">TOTAL NOTIFICATIONS</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardUnread">
                <div class="metric-icon unread">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <div class="metric-content">
                    <h3 id="unreadNotifications">0</h3>
                    <span class="metric-status text-warning">UNREAD</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardPending">
                <div class="metric-icon pending">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="pendingNotifications">0</h3>
                    <span class="metric-status text-info">PENDING</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardResolved">
                <div class="metric-icon resolved">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-content">
                    <h3 id="resolvedNotifications">0</h3>
                    <span class="metric-status text-success">RESOLVED</span>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="bi bi-bell"></i>
                    <span>All Notifications</span>
                    <span class="filter-count" id="countAll">0</span>
                </button>
                <button class="filter-btn" data-filter="visit">
                    <i class="bi bi-eye"></i>
                    <span>Visit Requests</span>
                    <span class="filter-count" id="countVisit">0</span>
                </button>
                <button class="filter-btn" data-filter="approval">
                    <i class="bi bi-check-circle"></i>
                    <span>Final Approvals</span>
                    <span class="filter-count" id="countApproval">0</span>
                </button>
                <button class="filter-btn" data-filter="payment">
                    <i class="bi bi-credit-card"></i>
                    <span>Payment Requests</span>
                    <span class="filter-count" id="countPayment">0</span>
                </button>
                <button class="filter-btn" data-filter="vendor">
                    <i class="bi bi-person-plus"></i>
                    <span>Vendor Added</span>
                    <span class="filter-count" id="countVendor">0</span>
                </button>
                <button class="filter-btn" data-filter="completed">
                    <i class="bi bi-check-circle"></i>
                    <span>Job Completed</span>
                    <span class="filter-count" id="countCompleted">0</span>
                </button>
                <button class="filter-btn" data-filter="invoice">
                    <i class="bi bi-receipt"></i>
                    <span>Invoice Reminders</span>
                    <span class="filter-count" id="countInvoice">0</span>
                </button>
            </div>
        </div>

        <!-- Request Cards Section -->
        <div class="requests-container" id="notificationsContainer">
            <!-- Dynamic notifications will be loaded here -->
            <div class="text-center py-5" id="loadingNotifications">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading notifications...</p>
            </div>
        </div>

</div>

</main>
</div>


<!-- Payment Request Details Modal -->
<div class="modal fade" id="paymentRequestModal" tabindex="-1" aria-labelledby="paymentRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentRequestModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Final Visit Request Modal -->
<div class="modal fade" id="finalVisitRequestModal" tabindex="-1" aria-labelledby="finalVisitRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check "></i> Final Visit Request Details
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="finalVisitRequestModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Job Completed Modal -->
<div class="modal fade" id="jobCompletedModal" tabindex="-1" aria-labelledby="jobCompletedModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Completion Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="jobCompletedModalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Partial Payment Request Modal -->
<div class="modal fade" id="partialPaymentRequestModal" tabindex="-1" aria-labelledby="partialPaymentRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cash-stack"></i> Partial Payment Request Details
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="partialPaymentRequestModalBody" style="overflow-y: auto; max-height: calc(90vh - 120px);">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <!-- Dynamic content will be loaded here based on payment status -->
            </div>
        </div>
    </div>
</div>




<script src="assets/js/requests.js"></script>
<?php include 'footer.php'; ?>