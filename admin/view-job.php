<?php $pageTitle = 'View Job'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2 id="jobTitle">Loading...</h2>
                <p id="jobSubtitle">Loading job details...</p>
            </div>
            <div class="welcome-actions">
                <a href="jobs.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Jobs
                </a>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardSLA">
                <div class="metric-icon sla">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="metric-content">
                    <p class="metric-label">SLA Deadline</p>
                    <span class="metric-status" id="slaDeadline">Loading...</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardEstimated">
                <div class="metric-icon estimated">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="metric-content">
                    <p class="metric-label">Estimated Amount</p>
                    <span class="metric-status text-primary" id="estimatedAmount">$0.00</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardPaid">
                <div class="metric-icon paid">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-content">
                    <p class="metric-label">Total Paid</p>
                    <span class="metric-status text-success" id="totalPaid">$0.00</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardRemaining">
                <div class="metric-icon remaining">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="metric-content">
                    <p class="metric-label">Remaining Balance</p>
                    <span class="metric-status text-warning" id="remainingBalance">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Workflow Status Card (Moved below metrics) -->
        <div class="job-detail-card mb-4" id="workflowStatusCard">
            <div class="card-header">
                <h3><i class="bi bi-briefcase"></i> Workflow Status</h3>
            </div>
            <div class="card-content">
                <!-- Current Status Display -->
                <div class="workflow-status-display mb-3">
                    <div class="d-flex align-items-center">
                        <div class="status-icon me-3">
                            <i class="bi bi-info-circle" id="statusIcon"></i>
                        </div>
                        <div class="status-content flex-grow-1">
                            <h5 class="mb-1" id="workflowStatus">Loading...</h5>
                            <p class="text-muted mb-0" id="actionInfo">Loading workflow information...</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Hidden by default, shown when action required) -->
                <div class="status-actions" id="statusActions" style="display: none;">
                    <div class="action-buttons d-flex gap-2 flex-wrap">
                        <button class="btn btn-success" id="acceptBtn" onclick="handleNotificationAction('accept')">
                            <i class="bi bi-check-circle"></i> Accept
                        </button>
                        <button class="btn btn-danger" id="rejectBtn" onclick="handleNotificationAction('reject')">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                        <button class="btn btn-info" id="viewFormBtn" onclick="handleViewForm()" style="display: none;">
                            <i class="bi bi-eye"></i> View Form
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Description Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-file-text"></i> Job Description</h3>
            </div>
            <div class="card-content">
                <div class="job-details">
                    <div class="detail-section">
                        <h4><i class="bi bi-shop"></i> Store Information</h4>
                        <p><strong>Store Name:</strong> <span id="storeName">Loading...</span></p>
                        <p><strong>Address:</strong> <span id="storeAddress">Loading...</span></p>
                        <p><strong>Job Type:</strong> <span id="jobType">Loading...</span></p>
                    </div>

                    <div class="detail-section">
                        <h4><i class="bi bi-list-ul"></i> Job Details</h4>
                        <p id="jobDetails">Loading...</p>
                    </div>

                    <div class="detail-section" id="additionalNotesSection" style="display: none;">
                        <h4><i class="bi bi-sticky"></i> Additional Notes</h4>
                        <p id="additionalNotes">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attached Files Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-paperclip"></i> Attached Files</h3>
            </div>
            <div class="card-content">
                <div id="attachedFilesContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading attached files...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Vendors Section Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-people"></i> Assigned Vendors</h3>
                <button class="btn btn-primary" id="chatWithAllVendorsBtn" title="Chat with User"
                    data-bs-toggle="modal" data-bs-target="#adminChatModal" style="display: none;">
                    <i class="bi bi-chat-dots text-white"></i> Chat with User
                </button>
            </div>
            <div class="card-content">
                <div id="vendorsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading assigned vendors...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-chat-dots"></i> Comments & Discussion</h3>
                <span class="badge bg-danger" id="commentsCount">0</span>
            </div>
            <div class="card-content">
                <!-- Add Comment Form -->
                <div class="add-comment-section mb-4">
                    <div class="comment-form">
                        <div class="comment-input-wrapper">
                            <textarea 
                                class="form-control comment-textarea" 
                                id="commentTextarea" 
                                placeholder="Add a comment about this job..." 
                                rows="3"
                                maxlength="1000"></textarea>
                            <div class="comment-actions">
                                <small class="text-muted">
                                    <span id="commentCharCount">0</span>/1000 characters
                                </small>
                                <button class="btn btn-back btn-sm" id="addCommentBtn">
                                    <i class="bi bi-send"></i> Add Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments List -->
                <div class="comments-section">
                    <div id="commentsList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading comments...</p>
                        </div>
                    </div>
                    
                    <!-- No comments placeholder -->
                    <div class="no-comments-placeholder" id="noCommentsPlaceholder" style="display: none;">
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No comments yet</h5>
                            <p class="text-muted">Be the first to add a comment about this job</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="job-detail-card job-detail-card-wide">
            <div class="card-header">
                <h3><i class="bi bi-clock-history"></i> Job Timeline</h3>
            </div>
            <div class="card-content">
                <div id="timelineContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading job timeline...</p>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>

</main>
</div>

<!-- Admin Chat Modal -->
<div class="modal fade" id="adminChatModal" tabindex="-1" aria-labelledby="adminChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content user-chat-modal-content">
            <div class="modal-header user-chat-header">
                <div class="user-chat-user-info">
                    <div class="user-chat-avatar">A</div>
                    <div>
                        <h5 class="user-chat-username" id="modalChatUsername">Discussion with User about Vendors</h5>
                        <p class="user-chat-status" id="modalChatStatus">Loading job details... <span
                                class="badge bg-secondary">Loading</span></p>
                    </div>
                </div>
                <div class="user-chat-area-actions">
                    <button class="user-chat-action-btn" title="Attachments" data-bs-toggle="modal"
                        data-bs-target="#adminAttachmentsModal">
                        <i class="bi bi-paperclip"></i>
                        <span class="badge" id="adminModalAttachmentCount">0</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body user-chat-body">
                <div class="user-chat-container">
                    <!-- Admin Vendors Sidebar -->
                    <div class="user-vendors-sidebar">
                        <div class="user-vendors-header">
                            <h6>Vendors</h6>
                            <button class="user-btn-collapse" title="Collapse">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                        <div class="user-vendors-list" id="adminChatVendorsList">
                            <!-- Dynamic vendors will be loaded here -->
                            <div class="text-center py-3" id="adminChatVendorsLoading">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading vendors...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Chat Area -->
                    <div class="user-chat-area">
                        <div class="user-chat-area-header">
                            <div class="user-chat-area-user">
                                <div class="user-chat-area-avatar" id="adminChatAreaAvatar">T</div>
                                <div>
                                    <div class="user-chat-area-name" id="adminChatAreaName">Chat about testing</div>
                                    <div class="user-chat-area-job" id="adminChatAreaJob">Loading job details... <span
                                            class="badge bg-secondary">Loading</span></div>
                                </div>
                            </div>
                            <div class="user-chat-area-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="openVendorAttachmentsModal()"
                                    title="View Vendor Attachments">
                                    <i class="bi bi-paperclip"></i>
                                    <span class="badge bg-danger ms-1" id="adminChatAttachmentCount">0</span>
                                </button>
                            </div>
                        </div>

                        <!-- Admin Messages Area -->
                        <div class="user-messages-area" id="adminMessagesArea">
                            <!-- Dynamic messages will be loaded here -->
                            <div class="text-center py-4" id="adminMessagesLoading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading messages...</p>
                            </div>

                            <!-- No messages placeholder -->
                            <div class="user-no-messages" id="adminNoMessagesPlaceholder" style="display: none;">
                                <div class="user-no-messages-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h6>No messages yet</h6>
                                <p>Start a conversation about this vendor</p>
                            </div>
                        </div>

                        <!-- Attachment Preview Area -->
                        <div class="attachment-preview-area" id="adminAttachmentPreviewArea" style="display: none;">
                            <div class="attachment-preview-header">
                                <span class="attachment-preview-title">Attached Files</span>
                                <button class="btn btn-sm btn-outline-secondary" id="adminClearAllAttachments"
                                    title="Clear All">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="attachment-preview-list" id="adminAttachmentPreviewList">
                                <!-- Preview items will be added here -->
                            </div>
                        </div>

                        <!-- Admin Message Input Area -->
                        <div class="user-message-input-area">
                            <div class="user-message-input-wrapper">
                                <button class="user-message-attach-btn" title="Attach File" id="adminChatAttachBtn">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <input type="file" id="adminChatFileInput" style="display: none;" multiple
                                    accept="image/*,.pdf,.doc,.docx,.txt">
                                <input type="text" class="user-message-input" placeholder="Type your message...">
                                <button class="user-message-send-btn" title="Send Message">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin All Attachments Modal (All Vendors) -->
<div class="modal fade" id="adminAttachmentsModal" tabindex="-1" aria-labelledby="adminAttachmentsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminAttachmentsModalLabel">
                    <i class="bi bi-paperclip"></i> All Job Attachments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="attachments-container">
                    <div class="attachments-header">
                        <div class="attachment-vendor-info">
                            <div class="attachment-vendor-avatar" id="adminAttachmentVendorAvatar">J</div>
                            <div>
                                <h6 id="adminAttachmentJobName">Job Attachments</h6>
                                <small class="text-muted" id="adminAttachmentJobId">Loading job details...</small>
                            </div>
                        </div>
                        <div class="attachment-stats">
                            <span class="badge bg-danger" id="adminAttachmentCount">0 files</span>
                        </div>
                    </div>

                    <div class="attachments-list" id="adminAttachmentsList">
                        <!-- Dynamic attachments will be loaded here -->
                        <div class="text-center py-4" id="adminAttachmentsLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading all attachments...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="attachments-empty" id="adminAttachmentsEmpty" style="display: none;">
                        <div class="empty-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>
                        <h6>No Attachments</h6>
                        <p class="text-muted">No files have been shared in this job yet.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Vendor Specific Attachments Modal -->
<div class="modal fade" id="vendorAttachmentsModal" tabindex="-1" aria-labelledby="vendorAttachmentsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vendorAttachmentsModalLabel">
                    <i class="bi bi-paperclip"></i> Vendor Attachments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="attachments-container">
                    <div class="attachments-header">
                        <div class="attachment-vendor-info">
                            <div class="attachment-vendor-avatar" id="vendorAttachmentAvatar">V</div>
                            <div>
                                <h6 id="vendorAttachmentName">Vendor Name</h6>
                                <small class="text-muted" id="vendorAttachmentJobId">Loading job details...</small>
                            </div>
                        </div>
                        <div class="attachment-stats">
                            <span class="badge bg-danger" id="vendorAttachmentCount">0 files</span>
                        </div>
                    </div>

                    <div class="attachments-list" id="vendorAttachmentsList">
                        <!-- Dynamic vendor attachments will be loaded here -->
                        <div class="text-center py-4" id="vendorAttachmentsLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading vendor attachments...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="attachments-empty" id="vendorAttachmentsEmpty" style="display: none;">
                        <div class="empty-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>
                        <h6>No Attachments</h6>
                        <p class="text-muted">No files have been shared with this vendor yet.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check"></i> Final Visit Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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

<?php include 'footer.php'; ?>

<!-- View Job JavaScript -->
<script src="assets/js/view-job.js"></script>