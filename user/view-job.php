<?php
$pageTitle = 'View Job';

// Get job ID from URL parameter
$job_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$job_id) {
    header('Location: my-jobs.php');
    exit();
}

include 'header.php';
include 'sidebar.php';
?>


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
                <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#showVendorsModal">
                    <i class="bi bi-people"></i> Show Vendors
                </button>
                <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                    <i class="bi bi-person-plus"></i> Add Vendor
                </button>
                <a href="my-jobs.php" class="btn btn-secondary">
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
                    <span class="metric-status text-success" id="slaDeadline">Loading...</span>
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

        <!-- Job Description Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-file-text"></i> Job Description</h3>
            </div>
            <div class="card-content">
                <div class="job-details">
                    <h4>Job Details:</h4>
                    <p id="jobDetail">Loading job details...</p>
                </div>
                <div class="additional-notes" id="additionalNotesSection" style="display: none;">
                    <h4>Additional Notes:</h4>
                    <p id="additionalNotes">No additional notes provided.</p>
                </div>
            </div>
        </div>

        <!-- Attached Files Card -->
        <div class="job-detail-card job-detail-card-wide mb-4" id="attachedFilesCard" style="display: none;">
            <div class="card-header">
                <h3><i class="bi bi-paperclip"></i> Attached Files</h3>
                <span class="badge bg-danger" id="attachmentCount">0 files</span>
            </div>
            <div class="card-content">
                <div class="attached-files-grid" id="attachedFilesGrid">
                    <!-- Dynamic files will be loaded here -->
                </div>
                <div class="no-attachments" id="noAttachments" style="display: none;">
                    <div class="text-center py-4">
                        <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Attachments</h5>
                        <p class="text-muted">No files have been attached to this job yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Vendors Section Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-people"></i> Assigned Vendors</h3>
                <button class="btn btn-primary" title="Chat with All Vendors" data-bs-toggle="modal"
                    data-bs-target="#userChatModal" id="chatWithVendorsBtn" style="display: none;">
                    <i class="bi bi-chat-dots text-white"></i> Chat with Vendors
                </button>
            </div>
            <div class="card-content">
                <div class="vendors-grid" id="vendorsGrid">
                    <!-- Dynamic vendors will be loaded here -->
                </div>
                <div class="no-vendors" id="noVendors" style="display: none;">
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Vendors Assigned</h5>
                        <p class="text-muted">No vendors have been assigned to this job yet.</p>
                    </div>
                </div>
                <div class="loading-vendors" id="loadingVendors">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading vendors...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section Card -->
        <div class="job-detail-card job-detail-card-wide mb-4">
            <div class="card-header">
                <h3><i class="bi bi-chat-dots"></i> Comments & Discussion</h3>
                <span class="badge bg-banger" id="commentsCount">0</span>
            </div>
            <div class="card-content">
                <!-- Add Comment Form -->
                <div class="add-comment-section mb-4">
                    <div class="comment-form">
                        <div class="comment-input-wrapper">
                            <textarea class="form-control comment-textarea" id="commentTextarea"
                                placeholder="Add a comment about this job..." rows="3" maxlength="1000"></textarea>
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
                <div class="timeline" id="jobTimeline">
                    <!-- Dynamic timeline items will be loaded here -->
                    <div class="text-center py-4" id="timelineLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading timeline...</p>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>

</main>
</div>


<!-- Show Vendors Modal -->
<div class="modal fade" id="showVendorsModal" tabindex="-1" aria-labelledby="showVendorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showVendorsModalLabel">
                    <i class="bi bi-people"></i> Available Vendors - Select to Add
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Vendor Selection Controls -->
                <div class="vendor-selection-controls mb-3" style="display: none;" id="vendorSelectionControls">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAllVendors" class="form-check-input me-2">
                                <label for="selectAllVendors" class="form-check-label fw-bold">Select All</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                <span id="selectedVendorsCount" class="badge bg-danger me-2">0 selected</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="clearVendorSelection">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="vendorsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading vendors...</p>
                </div>

                <!-- Available Vendors Grid -->
                <div id="availableVendorsGrid" class="vendors-grid" style="display: none;">
                    <!-- Available vendors will be loaded here -->
                </div>

                <!-- Empty State -->
                <div id="vendorsEmptyState" class="text-center py-5" style="display: none;">
                    <div class="empty-state-content">
                        <div class="empty-state-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>No Vendors Available</h3>
                        <p>No vendors are available to add to this job. You can create new vendors using the "Add
                            Vendor" button.</p>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addVendorModal"
                            data-bs-dismiss="modal">
                            <i class="bi bi-person-plus"></i> Add New Vendor
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
                <button type="button" class="btn btn-success" id="addSelectedVendors" style="display: none;">
                    <i class="bi bi-plus-circle"></i> Add Selected Vendors (<span id="selectedCount">0</span>)
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal"
                    data-bs-dismiss="modal">
                    <i class="bi bi-person-plus"></i> Add New Vendor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Vendor Modal -->
<div class="modal fade" id="editVendorModal" tabindex="-1" aria-labelledby="editVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVendorModalLabel">
                    <i class="bi bi-pencil"></i> Edit Vendor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editVendorForm">
                    <input type="hidden" id="editVendorId" name="vendorId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVendorName" class="form-label">
                                    <i class="bi bi-person"></i> Vendor Name *
                                </label>
                                <input type="text" class="form-control" id="editVendorName" name="vendorName"
                                    placeholder="Enter vendor name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVendorPhone" class="form-label">
                                    <i class="bi bi-phone"></i> Phone Number *
                                </label>
                                <input type="tel" class="form-control" id="editVendorPhone" name="vendorPhone"
                                    placeholder="Enter phone number" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editQuoteType" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Quote Type *
                                </label>
                                <select class="form-select" id="editQuoteType" name="quoteType" required
                                    onchange="toggleEditQuoteAmount()">
                                    <option value="">Select Quote Type</option>
                                    <option value="free_quote">Free Quote</option>
                                    <option value="paid_quote">Paid Quote</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="editQuoteAmountSection" style="display: none;">
                                <label for="editQuoteAmount" class="form-label">
                                    <i class="bi bi-cash"></i> Quote Amount *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="editQuoteAmount" name="quoteAmount"
                                        placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVendorPlatform" class="form-label">
                                    <i class="bi bi-globe"></i> Vendor Platform
                                </label>
                                <select class="form-select" id="editVendorPlatform" name="vendorPlatform">
                                    <option value="">Select Platform (Optional)</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="fiverr">Fiverr</option>
                                    <option value="upwork">Upwork</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="website">Website</option>
                                    <option value="referral">Referral</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVendorLocation" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Location
                                </label>
                                <input type="text" class="form-control" id="editVendorLocation" name="vendorLocation"
                                    placeholder="Enter vendor location (Optional)">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editAppointmentDateTime" class="form-label">
                                    <i class="bi bi-calendar-event"></i> Appointment Date & Time *
                                </label>
                                <input type="datetime-local" class="form-control" id="editAppointmentDateTime"
                                    name="appointmentDateTime" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editVendorNotes" class="form-label">
                            <i class="bi bi-file-text"></i> Additional Notes
                        </label>
                        <textarea class="form-control" id="editVendorNotes" name="vendorNotes" rows="3"
                            placeholder="Enter any additional notes about this vendor..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitEditVendor">
                    <i class="bi bi-check-circle"></i> Update Vendor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVendorModalLabel">
                    <i class="bi bi-person-plus"></i> Add New Vendor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addVendorForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendorName" class="form-label">
                                    <i class="bi bi-person"></i> Vendor Name *
                                </label>
                                <input type="text" class="form-control" id="vendorName" name="vendorName"
                                    placeholder="Enter vendor name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendorPhone" class="form-label">
                                    <i class="bi bi-phone"></i> Phone Number *
                                </label>
                                <input type="tel" class="form-control" id="vendorPhone" name="vendorPhone"
                                    placeholder="Enter phone number" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quoteType" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Quote Type *
                                </label>
                                <select class="form-select" id="quoteType" name="quoteType" required
                                    onchange="toggleQuoteAmount()">
                                    <option value="">Select Quote Type</option>
                                    <option value="free_quote">Free Quote</option>
                                    <option value="paid_quote">Paid Quote</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="quoteAmountSection" style="display: none;">
                                <label for="quoteAmount" class="form-label">
                                    <i class="bi bi-cash"></i> Quote Amount *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="quoteAmount" name="quoteAmount"
                                        placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendorPlatform" class="form-label">
                                    <i class="bi bi-globe"></i> Vendor Platform
                                </label>
                                <select class="form-select" id="vendorPlatform" name="vendorPlatform">
                                    <option value="">Select Platform (Optional)</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="fiverr">Fiverr</option>
                                    <option value="upwork">Upwork</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="website">Website</option>
                                    <option value="referral">Referral</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendorLocation" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Location
                                </label>
                                <input type="text" class="form-control" id="vendorLocation" name="vendorLocation"
                                    placeholder="Enter vendor location (Optional)">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appointmentDateTime" class="form-label">
                                    <i class="bi bi-calendar-event"></i> Appointment Date & Time *
                                </label>
                                <input type="datetime-local" class="form-control" id="appointmentDateTime"
                                    name="appointmentDateTime" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="vendorNotes" class="form-label">
                            <i class="bi bi-file-text"></i> Additional Notes
                        </label>
                        <textarea class="form-control" id="vendorNotes" name="vendorNotes" rows="3"
                            placeholder="Enter any additional notes about this vendor..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitAddVendor">
                    <i class="bi bi-check-circle"></i> Add Vendor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Final Visit Approval Modal -->
<div class="modal fade" id="finalVisitApprovalModal" tabindex="-1" aria-labelledby="finalVisitApprovalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalVisitApprovalModalLabel">
                    <i class="bi bi-calendar-check"></i> Request Final Visit Approval
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="finalVisitApprovalForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="finalVisitEstimatedAmount" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Estimated Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="finalVisitEstimatedAmount"
                                        name="estimatedAmount" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="finalVisitDateTime" class="form-label">
                                    <i class="bi bi-calendar-event"></i> Visit Date & Time
                                </label>
                                <input type="datetime-local" class="form-control" id="finalVisitDateTime"
                                    name="visitDateTime" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="finalVisitPaymentMode" class="form-label">
                            <i class="bi bi-credit-card"></i> Payment Mode
                        </label>
                        <select class="form-select" id="finalVisitPaymentMode" name="paymentMode" required>
                            <option value="">Select Payment Mode</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="paypal">PayPal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="finalVisitAdditionalNotes" class="form-label">
                            <i class="bi bi-file-text"></i> Additional Notes
                        </label>
                        <textarea class="form-control" id="finalVisitAdditionalNotes" name="additionalNotes" rows="4"
                            placeholder="Enter any additional notes or special requirements..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitFinalVisitApproval">
                    <i class="bi bi-check-circle"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Job Modal -->
<div class="modal fade" id="completeJobModal" tabindex="-1" aria-labelledby="completeJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeJobModalLabel">
                    <i class="bi bi-check-circle-fill"></i> Complete Job
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="completeJobForm">
                    <!-- Add Pictures Section -->
                    <div class="mb-4">
                        <div class="dropdown-section">
                            <button class="btn btn-outline-primary w-100 dropdown-toggle" type="button"
                                data-bs-toggle="collapse" data-bs-target="#picturesSection" aria-expanded="false"
                                onclick="closeOtherDropdowns('picturesSection')">
                                <i class="bi bi-camera-fill"></i> Add Pictures
                            </button>
                            <div class="collapse" id="picturesSection">
                                <div class="dropdown-content">
                                    <div class="mb-3">
                                        <label for="jobPictures" class="form-label">
                                            <i class="bi bi-images"></i> Upload Job Pictures
                                        </label>
                                        <input type="file" class="form-control" id="jobPictures" name="jobPictures"
                                            multiple accept="image/*">
                                        <div class="form-text">Select multiple images to upload (JPG, PNG, GIF)</div>
                                    </div>
                                    <div id="picturesPreview" class="pictures-preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add W9 Section -->
                    <div class="mb-4">
                        <div class="dropdown-section">
                            <button class="btn btn-outline-success w-100 dropdown-toggle" type="button"
                                data-bs-toggle="collapse" data-bs-target="#w9Section" aria-expanded="true"
                                onclick="closeOtherDropdowns('w9Section')">
                                <i class="bi bi-file-earmark-text-fill"></i> Add W9 Information
                            </button>
                            <div class="collapse show" id="w9Section">
                                <div class="dropdown-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vendorBusinessName" class="form-label">
                                                    <i class="bi bi-building"></i> Vendor/Business Name
                                                </label>
                                                <input type="text" class="form-control" id="vendorBusinessName"
                                                    name="vendorBusinessName" placeholder="Enter business name"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vendorAddress" class="form-label">
                                                    <i class="bi bi-geo-alt"></i> Address
                                                </label>
                                                <input type="text" class="form-control" id="vendorAddress"
                                                    name="vendorAddress" placeholder="Enter full address" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vendorEINSSN" class="form-label">
                                                    <i class="bi bi-card-text"></i> EIN/SSN
                                                </label>
                                                <input type="text" class="form-control" id="vendorEINSSN"
                                                    name="vendorEINSSN" placeholder="Enter EIN or SSN" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="entityType" class="form-label">
                                                    <i class="bi bi-diagram-3"></i> Entity Type
                                                </label>
                                                <select class="form-select" id="entityType" name="entityType" required>
                                                    <option value="">Select Entity Type</option>
                                                    <option value="individual">Individual</option>
                                                    <option value="partnership">Partnership</option>
                                                    <option value="corporation">Corporation</option>
                                                    <option value="s_corporation">S Corporation</option>
                                                    <option value="llc">LLC</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Invoice Section -->
                    <div class="mb-4">
                        <div class="dropdown-section">
                            <button class="btn btn-outline-warning w-100 dropdown-toggle" type="button"
                                data-bs-toggle="collapse" data-bs-target="#invoiceSection" aria-expanded="false"
                                onclick="closeOtherDropdowns('invoiceSection')">
                                <i class="bi bi-receipt"></i> Add Invoice
                            </button>
                            <div class="collapse" id="invoiceSection">
                                <div class="dropdown-content">
                                    <div class="mb-3">
                                        <label for="invoiceFile" class="form-label">
                                            <i class="bi bi-file-earmark-pdf"></i> Upload Invoice
                                        </label>
                                        <input type="file" class="form-control" id="invoiceFile" name="invoiceFile"
                                            accept=".pdf,.doc,.docx">
                                        <div class="form-text">Upload invoice document (PDF, DOC, DOCX)</div>
                                    </div>
                                    <div id="invoicePreview" class="invoice-preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitCompleteJob">
                    <i class="bi bi-check-circle"></i> Complete Job
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Payment Modal -->
<div class="modal fade" id="requestPaymentModal" tabindex="-1" aria-labelledby="requestPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestPaymentModalLabel">
                    <i class="bi bi-credit-card-fill"></i> Request Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="requestPaymentForm">
                    <!-- Payment Platform Selection -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-wallet2"></i> Payment Platform
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check payment-platform-option">
                                    <input class="form-check-input" type="radio" name="paymentPlatform" id="paymentLink"
                                        value="payment_link" onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="paymentLink">
                                        <i class="bi bi-link-45deg"></i> Payment Link/Invoice
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check payment-platform-option">
                                    <input class="form-check-input" type="radio" name="paymentPlatform" id="zelle"
                                        value="zelle" onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="zelle">
                                        <i class="bi bi-phone"></i> Zelle
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Link/Invoice Section -->
                    <div id="paymentLinkSection" class="payment-section" style="display: none;">
                        <div class="dropdown-section">
                            <div class="dropdown-content">
                                <div class="mb-3">
                                    <label for="paymentLinkUrl" class="form-label">
                                        <i class="bi bi-link-45deg"></i> Payment Link/Invoice URL
                                    </label>
                                    <input type="url" class="form-control" id="paymentLinkUrl" name="paymentLinkUrl"
                                        placeholder="https://example.com/payment-link">
                                    <div class="form-text">Enter the payment link or invoice URL</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zelle Section -->
                    <div id="zelleSection" class="payment-section" style="display: none;">
                        <div class="dropdown-section">
                            <div class="dropdown-content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="zelleEmailPhone" class="form-label">
                                                <i class="bi bi-envelope"></i> Zelle Email/Phone Number
                                            </label>
                                            <input type="text" class="form-control" id="zelleEmailPhone"
                                                name="zelleEmailPhone" placeholder="email@example.com or +1234567890">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="zelleType" class="form-label">
                                                <i class="bi bi-building"></i> Type
                                            </label>
                                            <select class="form-select" id="zelleType" name="zelleType"
                                                onchange="toggleZelleFields()">
                                                <option value="">Select Type</option>
                                                <option value="business">Business</option>
                                                <option value="personal">Personal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Business Name Field -->
                                <div id="businessNameField" class="mb-3" style="display: none;">
                                    <label for="businessName" class="form-label">
                                        <i class="bi bi-building"></i> Business Name
                                    </label>
                                    <input type="text" class="form-control" id="businessName" name="businessName"
                                        placeholder="Enter business name">
                                </div>

                                <!-- Personal Name Fields -->
                                <div id="personalNameFields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="firstName" class="form-label">
                                                    <i class="bi bi-person"></i> First Name
                                                </label>
                                                <input type="text" class="form-control" id="firstName" name="firstName"
                                                    placeholder="Enter first name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="lastName" class="form-label">
                                                    <i class="bi bi-person"></i> Last Name
                                                </label>
                                                <input type="text" class="form-control" id="lastName" name="lastName"
                                                    placeholder="Enter last name">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitPaymentRequest">
                    <i class="bi bi-check-circle"></i> Request Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Partial Payment Modal -->
<div class="modal fade" id="partialPaymentModal" tabindex="-1" aria-labelledby="partialPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partialPaymentModalLabel">
                    <i class="bi bi-cash-stack"></i> Request Partial Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="partialPaymentForm">
                    <!-- Payment Info Display -->
                    <div class="alert alert-info" id="paymentInfoAlert">
                        <i class="bi bi-info-circle"></i>
                        <strong>Payment Information:</strong>
                        <div id="paymentInfoContent">
                            Loading payment details...
                        </div>
                    </div>

                    <!-- Amount Input -->
                    <div class="mb-4">
                        <label for="partialPaymentAmount" class="form-label">
                            <i class="bi bi-currency-dollar"></i> Partial Payment Amount *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="partialPaymentAmount"
                                name="partialPaymentAmount" placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-lightbulb"></i> Enter the amount you want to request as partial payment
                        </div>
                    </div>

                    <!-- Estimated Amount Info -->
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-calculator"></i> Payment Summary
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Estimated Amount:</small>
                                        <div class="fw-bold" id="estimatedAmountDisplay">$0.00</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Remaining Balance:</small>
                                        <div class="fw-bold text-success" id="remainingBalanceDisplay">$0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" id="partialPaymentVendorId" name="vendorId">
                    <input type="hidden" id="partialPaymentJobId" name="jobId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitPartialPayment">
                    <i class="bi bi-check-circle"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Chat Modal -->
<div class="modal fade" id="userChatModal" tabindex="-1" aria-labelledby="userChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content user-chat-modal-content">
            <div class="modal-header user-chat-header">
                <div class="user-chat-user-info">
                    <div class="user-chat-avatar">A</div>
                    <div>
                        <h5 class="user-chat-username">Discussion with Admin about Vendors</h5>
                        <p class="user-chat-status">Job #JOB-001 <span class="badge bg-success">Active</span></p>
                    </div>
                </div>
                <div class="user-chat-area-actions">
                    <button class="user-chat-action-btn" title="Attachments" data-bs-toggle="modal"
                        data-bs-target="#attachmentsModal">
                        <i class="bi bi-paperclip"></i>
                        <span class="badge" id="modalAttachmentCount">0</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body user-chat-body">
                <div class="user-chat-container">
                    <!-- User Vendors Sidebar -->
                    <div class="user-vendors-sidebar">
                        <div class="user-vendors-header">
                            <h6>Vendors</h6>
                            <button class="user-btn-collapse" title="Collapse">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                        <div class="user-vendors-list" id="chatVendorsList">
                            <!-- Dynamic vendors will be loaded here -->
                            <div class="text-center py-3" id="chatVendorsLoading">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading vendors...</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Chat Area -->
                    <div class="user-chat-area">
                        <div class="user-chat-area-header">
                            <div class="user-chat-area-user">
                                <div class="user-chat-area-avatar" id="chatAreaAvatar">T</div>
                                <div>
                                    <div class="user-chat-area-name" id="chatAreaName">Chat about testing</div>
                                    <div class="user-chat-area-job">Job #JOB-001 <span
                                            class="badge bg-success">Active</span></div>
                                </div>
                            </div>
                            <div class="user-chat-area-actions">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#attachmentsModal" title="View Attachments">
                                    <i class="bi bi-paperclip"></i>
                                    <span class="badge bg-danger ms-1" id="chatAttachmentCount">0</span>
                                </button>
                            </div>
                        </div>

                        <!-- User Messages Area -->
                        <div class="user-messages-area" id="messagesArea">
                            <!-- Dynamic messages will be loaded here -->
                            <div class="text-center py-4" id="messagesLoading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading messages...</p>
                            </div>

                            <!-- No messages placeholder -->
                            <div class="user-no-messages" id="noMessagesPlaceholder" style="display: none;">
                                <div class="user-no-messages-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h6>No messages yet</h6>
                                <p>Start a conversation about this vendor</p>
                            </div>
                        </div>

                        <!-- Attachment Preview Area -->
                        <div class="attachment-preview-area" id="attachmentPreviewArea" style="display: none;">
                            <div class="attachment-preview-header">
                                <span class="attachment-preview-title">Attached Files</span>
                                <button class="btn btn-sm btn-outline-secondary" id="clearAllAttachments"
                                    title="Clear All">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="attachment-preview-list" id="attachmentPreviewList">
                                <!-- Preview items will be added here -->
                            </div>
                        </div>

                        <!-- User Message Input Area -->
                        <div class="user-message-input-area">
                            <div class="user-message-input-wrapper">
                                <button class="user-message-attach-btn" title="Attach File" id="chatAttachBtn">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <input type="file" id="chatFileInput" style="display: none;" multiple
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

<!-- Attachments Modal -->
<div class="modal fade" id="attachmentsModal" tabindex="-1" aria-labelledby="attachmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentsModalLabel">
                    <i class="bi bi-paperclip"></i> Attachments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="attachments-container">
                    <div class="attachments-header">
                        <div class="attachment-vendor-info">
                            <div class="attachment-vendor-avatar" id="attachmentVendorAvatar">T</div>
                            <div>
                                <h6 id="attachmentVendorName">testing</h6>
                                <small class="text-muted">Job #JOB-001</small>
                            </div>
                        </div>
                        <div class="attachment-stats">
                            <span class="badge bg-danger" id="attachmentCount">3 files</span>
                        </div>
                    </div>

                    <div class="attachments-list" id="attachmentsList">
                        <!-- Dynamic attachments will be loaded here -->
                        <div class="text-center py-4" id="attachmentsLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading attachments...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="attachments-empty" id="attachmentsEmpty" style="display: none;">
                        <div class="empty-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>
                        <h6>No Attachments</h6>
                        <p class="text-muted">No files have been shared in this conversation yet.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Upload New File
                </button> -->
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="assets/js/view-job.js?t=<?php echo time(); ?>"></script>

<script>
    // Dynamic Chat Modal for View Job Page
    document.addEventListener('DOMContentLoaded', function () {
        // Global variables for chat functionality
        let currentVendorId = null;
        let currentVendorName = null;
        let currentVendorAvatar = null;
        let currentJobId = window.currentJobId;
        let selectedFiles = [];
        let isScrolling = false;
        let userLastMessageId = null; // Track last message ID for smart polling
        let userMessagePollingInterval = null; // Message polling interval
        let unreadCountInterval = null; // Unread count refresh interval

        // Smart Message Polling Functions
        function startMessagePolling() {
            console.log('🚀 Starting message polling...');
            if (userMessagePollingInterval) {
                clearInterval(userMessagePollingInterval);
            }
            userMessagePollingInterval = setInterval(() => {
                pollForNewMessages();
            }, 5000); // Poll every 5 seconds
        }

        function stopMessagePolling() {
            if (userMessagePollingInterval) {
                clearInterval(userMessagePollingInterval);
                userMessagePollingInterval = null;
            }
        }

        async function pollForNewMessages() {
            if (!currentVendorId || !currentJobId) return;
            try {
                const response = await fetch(`assets/api/get_messages.php?vendor_id=${currentVendorId}&job_id=${currentJobId}&mark_as_read=false&last_message_id=${userLastMessageId || 0}`);
                const result = await response.json();
                if (result.success && result.messages && result.messages.length > 0) {
                    // Only append new messages
                    appendNewMessages(result.messages);

                    // Update last message ID
                    const latestMessage = result.messages[result.messages.length - 1];
                    if (latestMessage && latestMessage.id > userLastMessageId) {
                        userLastMessageId = latestMessage.id;
                    }

                    // Scroll to bottom if new messages
                    setTimeout(() => {
                        scrollChatToBottom();
                    }, 100);
                }
            } catch (error) {
                console.error('Error polling for new messages:', error);
            }
        }

        // Start periodic unread count refresh
        function startUnreadCountRefresh() {
            // Clear existing interval
            if (unreadCountInterval) {
                clearInterval(unreadCountInterval);
            }

            // Refresh every 10 seconds
            unreadCountInterval = setInterval(() => {
                loadUnreadCounts();
            }, 10000);
        }

        // Stop unread count refresh
        function stopUnreadCountRefresh() {
            if (unreadCountInterval) {
                clearInterval(unreadCountInterval);
                unreadCountInterval = null;
            }
        }

        function appendNewMessages(newMessages) {
            const messagesArea = document.getElementById('messagesArea');
            const noMessagesPlaceholder = document.getElementById('noMessagesPlaceholder');
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
                        const messageElement = createUserMessageElement(message);
                        messagesArea.appendChild(messageElement);
                    });
                }, 100);
            } else {
                // If no new messages, just append them directly
                newMessages.forEach(message => {
                    const messageElement = createUserMessageElement(message);
                    messagesArea.appendChild(messageElement);
                });
            }
        }

        function createUserMessageElement(message) {
            const isOwnMessage = message.sender_role === 'user';
            const messageClass = isOwnMessage ? 'message-item sent' : 'message-item received';
            const avatar = isOwnMessage ? 'U' : currentVendorAvatar;
            const senderName = isOwnMessage ? 'You' : message.sender_name;

            const messageElement = document.createElement('div');
            messageElement.className = messageClass;

            let attachmentHtml = '';
            if (message.attachments && message.attachments.length > 0) {
                attachmentHtml = message.attachments.map(attachment => {
                    const isImage = /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(attachment.file_path);
                    if (isImage) {
                        return `
                            <div class="message-attachment">
                                <img src="../uploads/messages/${attachment.file_path}" 
                                     alt="${attachment.file_name}" 
                                     onclick="viewImage('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')"
                                     class="attachment-image">
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

        function showNewMessageIndicator(count) {
            const messagesArea = document.getElementById('messagesArea');
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

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        // Initialize chat modal when it opens
        const chatModal = document.getElementById('userChatModal');
        if (chatModal) {
            chatModal.addEventListener('shown.bs.modal', function () {
                console.log('Chat modal opened');
                loadChatVendors();
                startMessagePolling(); // Start smart polling
                startUnreadCountRefresh(); // Start periodic unread count refresh
                setTimeout(() => {
                    scrollChatToBottom();
                }, 300);
            });

            chatModal.addEventListener('hidden.bs.modal', function () {
                console.log('Chat modal closed');
                stopMessagePolling(); // Stop smart polling
                stopUnreadCountRefresh(); // Stop periodic unread count refresh
            });
        }

        // Load vendors for chat modal
        async function loadChatVendors() {
            try {
                console.log('🟢 loadChatVendors called for job:', currentJobId);
                const response = await fetch(`assets/api/get_job_vendors.php?job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    console.log('📋 Vendors received:', result.data);
                    displayChatVendors(result.data);
                    // Select first vendor by default
                    if (result.data.length > 0) {
                        console.log('🎯 Selecting first vendor:', result.data[0]);
                        selectVendor(result.data[0].id, result.data[0].vendor_name, result.data[0].avatar);
                    }
                    // Load total attachment count for modal header
                    loadTotalAttachmentCount();
                } else {
                    showNoVendorsMessage();
                }
            } catch (error) {
                console.error('Error loading chat vendors:', error);
                showNoVendorsMessage();
            }
        }

        // Display vendors in chat sidebar
        function displayChatVendors(vendors) {
            const vendorsList = document.getElementById('chatVendorsList');
            const loadingElement = document.getElementById('chatVendorsLoading');

            if (loadingElement) {
                loadingElement.remove();
            }

            if (vendors.length === 0) {
                showNoVendorsMessage();
                return;
            }

            const vendorsHTML = vendors.map(vendor => `
            <div class="user-vendor-item" data-vendor-id="${vendor.id}" data-vendor-name="${vendor.vendor_name}" data-vendor-avatar="${vendor.avatar}">
                <div class="user-vendor-avatar">${vendor.avatar}</div>
                <div class="user-vendor-info">
                    <div class="user-vendor-name">${vendor.vendor_name}</div>
                </div>
                <div class="user-vendor-unread">
                    <span class="unread-badge" id="unreadBadge_${vendor.id}" style="display: none;">0</span>
                </div>
            </div>
        `).join('');

            vendorsList.innerHTML = vendorsHTML;
            setupVendorSelection();
            loadUnreadCounts();
        }

        // Show no vendors message
        function showNoVendorsMessage() {
            const vendorsList = document.getElementById('chatVendorsList');
            const loadingElement = document.getElementById('chatVendorsLoading');

            if (loadingElement) {
                loadingElement.remove();
            }

            vendorsList.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                <h6 class="text-muted mt-2">No Vendors</h6>
                <p class="text-muted">No vendors assigned to this job yet.</p>
            </div>
        `;
        }

        // Setup vendor selection functionality
        function setupVendorSelection() {
            console.log('🔧 Setting up vendor selection...');
            const vendorItems = document.querySelectorAll('.user-vendor-item');
            console.log('📝 Found vendor items:', vendorItems.length);

            vendorItems.forEach((item, index) => {
                const vendorId = item.getAttribute('data-vendor-id');
                const vendorName = item.getAttribute('data-vendor-name');
                console.log(`📌 Setting up click handler for vendor ${index}: ${vendorName} (ID: ${vendorId})`);

                item.addEventListener('click', function () {
                    const vendorId = this.getAttribute('data-vendor-id');
                    const vendorName = this.getAttribute('data-vendor-name');
                    const vendorAvatar = this.getAttribute('data-vendor-avatar');

                    console.log('👆 Vendor clicked:', vendorName);
                    selectVendor(vendorId, vendorName, vendorAvatar);
                });
            });
        }

        // Select a vendor and load their messages
        function selectVendor(vendorId, vendorName, vendorAvatar) {
            console.log('🔵 selectVendor called:', { vendorId, vendorName, vendorAvatar });

            // Update active state
            document.querySelectorAll('.user-vendor-item').forEach(item => {
                item.classList.remove('active');
                // Clear inline styles
                item.style.backgroundColor = '';
                item.style.borderLeft = '';
                item.style.paddingLeft = '';
            });

            const selectedItem = document.querySelector(`.user-vendor-item[data-vendor-id="${vendorId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('active');
                // Force inline styles for testing
                selectedItem.style.backgroundColor = '#e0e0e0';
                selectedItem.style.borderLeft = '4px solid #2d2d2d';
                selectedItem.style.paddingLeft = '0.5rem';
                console.log('✅ Added active class to vendor:', vendorName);
                console.log('🔍 Element classes:', selectedItem.className);
                console.log('🔍 Element:', selectedItem);
            } else {
                console.log('❌ Selected item not found for vendor ID:', vendorId);
            }

            // Update global variables
            currentVendorId = vendorId;
            currentVendorName = vendorName;
            currentVendorAvatar = vendorAvatar;

            // Update chat area header
            const chatAreaAvatar = document.getElementById('chatAreaAvatar');
            const chatAreaName = document.getElementById('chatAreaName');

            if (chatAreaAvatar) chatAreaAvatar.textContent = vendorAvatar;
            if (chatAreaName) chatAreaName.textContent = `Chat about ${vendorName}`;

            // Update attachment modal header
            updateAttachmentsModalHeader(vendorName, vendorAvatar);

            // Clear unread count for this vendor
            clearVendorUnreadCount(vendorId);

            // Load messages for this vendor
            loadMessages(vendorId, currentJobId);

            // Load attachments for this vendor
            loadAttachments();

            // Scroll to bottom
            setTimeout(() => {
                scrollChatToBottom();
            }, 100);
        }

        // Load messages for selected vendor
        async function loadMessages(vendorId, jobId) {
            try {
                const messagesArea = document.getElementById('messagesArea');
                const loadingElement = document.getElementById('messagesLoading');
                const noMessagesElement = document.getElementById('noMessagesPlaceholder');

                // Show loading
                if (loadingElement) loadingElement.style.display = 'block';
                if (noMessagesElement) noMessagesElement.style.display = 'none';

                const response = await fetch(`assets/api/get_messages.php?vendor_id=${vendorId}&job_id=${jobId}`);
                const result = await response.json();


                if (result.success) {
                    if (result.data.messages && result.data.messages.length > 0) {
                        displayMessages(result.data.messages);
                        // Set lastMessageId for smart polling
                        const latestMessage = result.data.messages[result.data.messages.length - 1];
                        if (latestMessage && latestMessage.id) {
                            userLastMessageId = latestMessage.id;
                        }
                    } else {
                        showNoMessages();
                    }
                    // Update unread counts after loading messages (messages are marked as read)
                    loadUnreadCounts();
                } else {
                    console.error('Error loading messages:', result.message);
                    showNoMessages();
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                showNoMessages();
            }
        }

        // Display messages in chat area
        function displayMessages(messages) {
            const messagesArea = document.getElementById('messagesArea');
            const loadingElement = document.getElementById('messagesLoading');
            const noMessagesElement = document.getElementById('noMessagesPlaceholder');

            // Hide loading and no messages
            if (loadingElement) loadingElement.style.display = 'none';
            if (noMessagesElement) noMessagesElement.style.display = 'none';

            // Reverse messages to show oldest first
            messages.reverse();

            // Group messages by date
            const groupedMessages = groupMessagesByDate(messages);

            let messagesHTML = '';
            Object.keys(groupedMessages).forEach(date => {
                messagesHTML += `
                <div class="message-date-separator">
                    <div class="date-line">
                        <span class="date-text">${date}</span>
                    </div>
                </div>
            `;

                groupedMessages[date].forEach(message => {
                    messagesHTML += createMessageElement(message);
                });
            });

            messagesArea.innerHTML = messagesHTML;

            // Scroll to bottom
            setTimeout(() => {
                scrollChatToBottom();
            }, 100);
        }

        // Group messages by date
        function groupMessagesByDate(messages) {
            const grouped = {};
            messages.forEach(message => {
                const date = message.formatted_date;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(message);
            });
            return grouped;
        }

        // Create message element
        function createMessageElement(message) {
            const isSent = message.is_sent;
            const messageClass = isSent ? 'sent' : 'received';
            const senderName = isSent ? 'You' : message.sender_name;
            const senderAvatar = isSent ? 'U' : message.sender_avatar;

            let messageHTML = `
            <div class="message-item ${messageClass}">
                <div class="message-avatar">
                    <span>${senderAvatar}</span>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender">${senderName}</span>
                        <span class="message-time">${message.formatted_time_only}</span>
                    </div>
        `;

            // Add message text if exists
            if (message.message && message.message.trim()) {
                messageHTML += `<div class="message-text">${message.message}</div>`;
            }

            // Add attachments if exist
            if (message.attachments && message.attachments.length > 0) {
                messageHTML += '<div class="message-attachment">';
                message.attachments.forEach(attachment => {
                    messageHTML += createAttachmentElement(attachment);
                });
                messageHTML += '</div>';
            }

            messageHTML += `
                </div>
            </div>
        `;

            return messageHTML;
        }

        // Create attachment element
        function createAttachmentElement(attachment) {
            const fileIcon = getFileIcon(attachment.file_type);
            const fileSize = formatFileSize(attachment.file_size);

            return `
            <div class="attachment-item">
                <div class="attachment-icon">
                    ${fileIcon}
                </div>
                <div class="attachment-info">
                    <div class="attachment-name">${attachment.file_name}</div>
                    <div class="attachment-meta">${fileSize}</div>
                </div>
                <div class="attachment-actions">
                    <button class="btn btn-sm btn-outline-primary" title="Download" onclick="downloadAttachment('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" title="View" onclick="viewImage('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        `;
        }

        // Show no messages state
        function showNoMessages() {
            const messagesArea = document.getElementById('messagesArea');
            const loadingElement = document.getElementById('messagesLoading');
            const noMessagesElement = document.getElementById('noMessagesPlaceholder');

            if (loadingElement) loadingElement.style.display = 'none';
            if (noMessagesElement) noMessagesElement.style.display = 'block';
            messagesArea.innerHTML = '';
            messagesArea.appendChild(noMessagesElement);
        }

        // Load unread counts for all vendors
        async function loadUnreadCounts() {
            try {
                const response = await fetch(`assets/api/get_vendor_unread_messages.php?job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success) {
                    Object.keys(result.data).forEach(vendorId => {
                        const unreadCount = result.data[vendorId];

                        // Update chat sidebar badge
                        const chatBadge = document.getElementById(`unreadBadge_${vendorId}`);
                        if (chatBadge) {
                            if (unreadCount > 0) {
                                chatBadge.textContent = unreadCount;
                                chatBadge.style.display = 'flex';
                            } else {
                                chatBadge.style.display = 'none';
                            }
                        }

                        // Update vendor card badge
                        const vendorCardBadge = document.getElementById(`vendorUnreadBadge${vendorId}`);
                        if (vendorCardBadge) {
                            if (unreadCount > 0) {
                                vendorCardBadge.textContent = unreadCount;
                                vendorCardBadge.style.display = 'flex';
                            } else {
                                vendorCardBadge.style.display = 'none';
                            }
                        }
                    });

                    // Also update header unread count
                    if (typeof updateUnreadMessageCount === 'function') {
                        await updateUnreadMessageCount();
                    }
                }
            } catch (error) {
                console.error('Error loading unread counts:', error);
            }
        }

        // Clear unread count for specific vendor
        function clearVendorUnreadCount(vendorId) {
            // Clear chat sidebar badge
            const chatBadge = document.getElementById(`unreadBadge_${vendorId}`);
            if (chatBadge) {
                chatBadge.style.display = 'none';
                chatBadge.textContent = '0';
            }

            // Clear vendor card badge
            const vendorCardBadge = document.getElementById(`vendorUnreadBadge${vendorId}`);
            if (vendorCardBadge) {
                vendorCardBadge.style.display = 'none';
                vendorCardBadge.textContent = '0';
            }

            // Also update header unread count
            if (typeof updateUnreadMessageCount === 'function') {
                updateUnreadMessageCount();
            }
        }

        // Update attachments modal header
        function updateAttachmentsModalHeader(vendorName, vendorAvatar) {
            const attachmentVendorAvatar = document.getElementById('attachmentVendorAvatar');
            const attachmentVendorName = document.getElementById('attachmentVendorName');

            if (attachmentVendorAvatar) attachmentVendorAvatar.textContent = vendorAvatar;
            if (attachmentVendorName) attachmentVendorName.textContent = vendorName;
        }

        // Load attachments for current vendor
        async function loadAttachments() {
            if (!currentVendorId || !currentJobId) return;

            try {
                const response = await fetch(`assets/api/get_messages.php?vendor_id=${currentVendorId}&job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success) {
                    displayAttachments(result.data.messages);
                }
            } catch (error) {
                console.error('Error loading attachments:', error);
            }
        }

        // Display attachments in modal
        function displayAttachments(messages) {
            const attachmentsList = document.getElementById('attachmentsList');
            const loadingElement = document.getElementById('attachmentsLoading');
            const emptyElement = document.getElementById('attachmentsEmpty');
            const countElement = document.getElementById('attachmentCount');
            const chatAttachmentCount = document.getElementById('chatAttachmentCount');

            if (loadingElement) loadingElement.style.display = 'none';

            // Filter messages with attachments
            const messagesWithAttachments = messages.filter(message =>
                message.attachments && message.attachments.length > 0
            );

            let totalAttachments = 0;
            messagesWithAttachments.forEach(message => {
                totalAttachments += message.attachments.length;
            });

            // Update chat header attachment count
            if (chatAttachmentCount) {
                chatAttachmentCount.textContent = totalAttachments;
                chatAttachmentCount.style.display = totalAttachments > 0 ? 'inline' : 'none';
            }

            if (totalAttachments === 0) {
                if (emptyElement) emptyElement.style.display = 'block';
                if (countElement) countElement.textContent = '0 files';
                return;
            }

            if (emptyElement) emptyElement.style.display = 'none';

            let attachmentsHTML = '';

            messagesWithAttachments.forEach(message => {
                message.attachments.forEach(attachment => {
                    attachmentsHTML += createAttachmentModalItem(attachment, message);
                });
            });

            attachmentsList.innerHTML = attachmentsHTML;
            if (countElement) countElement.textContent = `${totalAttachments} file${totalAttachments > 1 ? 's' : ''}`;
        }

        // Create attachment modal item
        function createAttachmentModalItem(attachment, message) {
            const fileIcon = getFileIcon(attachment.file_type);
            const fileSize = formatFileSize(attachment.file_size);
            const timeAgo = message.formatted_time;

            return `
            <div class="attachment-item">
                <div class="attachment-icon">
                    ${fileIcon}
                </div>
                <div class="attachment-info">
                    <div class="attachment-name">${attachment.file_name}</div>
                    <div class="attachment-meta">
                        <span class="attachment-size">${fileSize}</span>
                        <span class="attachment-date">${timeAgo}</span>
                    </div>
                </div>
                <div class="attachment-actions">
                    <button class="btn btn-sm btn-outline-primary" title="Download" onclick="downloadAttachment('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" title="View" onclick="viewImage('../uploads/messages/${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        `;
        }

        // Setup message sending functionality
        setupMessageSendingListeners();

        // Setup message sending listeners
        function setupMessageSendingListeners() {
            const sendBtn = document.querySelector('.user-message-send-btn');
            const messageInput = document.querySelector('.user-message-input');
            const attachBtn = document.getElementById('chatAttachBtn');
            const fileInput = document.getElementById('chatFileInput');

            // Send button click
            if (sendBtn) {
                sendBtn.addEventListener('click', sendMessage);
            }

            // Enter key in message input
            if (messageInput) {
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }

            // Attach button click
            if (attachBtn && fileInput) {
                attachBtn.addEventListener('click', function () {
                    fileInput.click();
                });

                fileInput.addEventListener('change', function (e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        Array.from(files).forEach(file => {
                            if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                                selectedFiles.push(file);
                            }
                        });
                        showFilePreviews(selectedFiles);
                    }
                });
            }
        }

        // Send message function
        async function sendMessage() {
            if (!currentVendorId || !currentJobId) {
                showNotification('Please select a vendor first', 'error');
                return;
            }

            const messageInput = document.querySelector('.user-message-input');
            const message = messageInput ? messageInput.value.trim() : '';
            const hasMessage = message.length > 0;
            const hasAttachment = selectedFiles.length > 0;

            if (!hasMessage && !hasAttachment) {
                showNotification('Please enter a message or attach a file', 'error');
                return;
            }

            // Disable input and show loading
            if (messageInput) messageInput.disabled = true;
            const sendBtn = document.querySelector('.user-message-send-btn');
            if (sendBtn) {
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            }

            try {
                const formData = new FormData();
                formData.append('vendor_id', currentVendorId);
                formData.append('job_id', currentJobId);
                if (hasMessage) {
                    formData.append('message', message);
                }

                // Add attachments
                selectedFiles.forEach((file, index) => {
                    formData.append('attachment', file);
                });

                const response = await fetch('assets/api/send_message.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Clear input and attachments
                    if (messageInput) messageInput.value = '';
                    selectedFiles = [];
                    clearAllAttachments();

                    // Add message to chat
                    addMessageToChat(result.data);

                    // Set lastMessageId for smart polling
                    if (result.data && result.data.id) {
                        userLastMessageId = result.data.id;
                    }

                    // Update unread counts
                    loadUnreadCounts();

                    // Update total attachment count if message has attachments
                    if (result.data.attachments && result.data.attachments.length > 0) {
                        loadTotalAttachmentCount();
                    }

                    showNotification('Message sent successfully', 'success');
                } else {
                    showNotification(result.message || 'Failed to send message', 'error');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showNotification('Error sending message', 'error');
            } finally {
                // Re-enable input
                if (messageInput) messageInput.disabled = false;
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="bi bi-send"></i>';
                }
            }
        }

        // Add message to chat interface
        function addMessageToChat(messageData) {
            const messagesArea = document.getElementById('messagesArea');
            const noMessagesElement = document.getElementById('noMessagesPlaceholder');

            // Remove no messages placeholder if exists
            if (noMessagesElement && noMessagesElement.style.display !== 'none') {
                noMessagesElement.style.display = 'none';
            }

            // Create message element
            const messageElement = createMessageElement(messageData);

            // Add to messages area
            messagesArea.insertAdjacentHTML('beforeend', messageElement);

            // Update attachment count if message has attachments
            if (messageData.attachments && messageData.attachments.length > 0) {
                updateAttachmentCount();
            }

            // Scroll to bottom
            scrollChatToBottom();
        }

        // File attachment preview functions
        function showFilePreviews(files) {
            const previewArea = document.getElementById('attachmentPreviewArea');
            const previewList = document.getElementById('attachmentPreviewList');

            if (files.length === 0) {
                previewArea.style.display = 'none';
                return;
            }

            previewList.innerHTML = '';
            files.forEach((file, index) => {
                const previewItem = createFilePreviewItem(file, index);
                previewList.appendChild(previewItem);
            });

            previewArea.style.display = 'block';
        }

        function createFilePreviewItem(file, index) {
            const item = document.createElement('div');
            item.className = 'attachment-preview-item';
            item.dataset.index = index;

            const fileIcon = getFileIcon(file.type);
            const fileSize = formatFileSize(file.size);

            item.innerHTML = `
            <div class="attachment-preview-icon">
                ${fileIcon}
            </div>
            <div class="attachment-preview-info">
                <div class="attachment-preview-name">${file.name}</div>
                <div class="attachment-preview-meta">${fileSize}</div>
            </div>
            <button class="attachment-preview-remove" onclick="removeAttachmentPreview(${index})" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        `;

            return item;
        }

        // Clear all attachments
        function clearAllAttachments() {
            selectedFiles = [];
            const previewArea = document.getElementById('attachmentPreviewArea');
            const fileInput = document.getElementById('chatFileInput');

            if (previewArea) previewArea.style.display = 'none';
            if (fileInput) fileInput.value = '';
        }

        // Remove attachment preview
        window.removeAttachmentPreview = function (index) {
            selectedFiles.splice(index, 1);
            if (selectedFiles.length === 0) {
                clearAllAttachments();
            } else {
                showFilePreviews(selectedFiles);
            }
        };

        // Clear all attachments button
        const clearAllBtn = document.getElementById('clearAllAttachments');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearAllAttachments);
        }

        // Utility functions
        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) {
                return '<i class="bi bi-file-earmark-image text-primary"></i>';
            } else if (fileType === 'application/pdf') {
                return '<i class="bi bi-file-earmark-pdf text-danger"></i>';
            } else if (fileType.includes('word') || fileType.includes('document')) {
                return '<i class="bi bi-file-earmark-word text-info"></i>';
            } else if (fileType.includes('text')) {
                return '<i class="bi bi-file-earmark-text text-secondary"></i>';
            } else {
                return '<i class="bi bi-file-earmark text-muted"></i>';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Load total attachment count for all vendors in the job
        async function loadTotalAttachmentCount() {
            if (!currentJobId) return;

            try {
                const response = await fetch(`assets/api/get_job_vendors.php?job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    let totalAttachments = 0;

                    // Get attachment count for each vendor
                    for (const vendor of result.data) {
                        try {
                            const messagesResponse = await fetch(`assets/api/get_messages.php?vendor_id=${vendor.id}&job_id=${currentJobId}`);
                            const messagesResult = await messagesResponse.json();

                            if (messagesResult.success && messagesResult.data.messages) {
                                messagesResult.data.messages.forEach(message => {
                                    if (message.attachments && message.attachments.length > 0) {
                                        totalAttachments += message.attachments.length;
                                    }
                                });
                            }
                        } catch (error) {
                            console.error(`Error loading messages for vendor ${vendor.id}:`, error);
                        }
                    }

                    // Update modal header attachment count
                    const modalAttachmentCount = document.getElementById('modalAttachmentCount');
                    if (modalAttachmentCount) {
                        modalAttachmentCount.textContent = totalAttachments;
                        modalAttachmentCount.style.display = totalAttachments > 0 ? 'inline' : 'none';
                    }
                }
            } catch (error) {
                console.error('Error loading total attachment count:', error);
            }
        }

        // Update attachment count in chat header
        function updateAttachmentCount() {
            if (!currentVendorId || !currentJobId) return;

            // Reload attachments to update count
            loadAttachments();
            // Also update total count
            loadTotalAttachmentCount();
        }

        // Scroll to bottom function
        function scrollChatToBottom() {
            if (isScrolling) return;
            const messagesArea = document.getElementById('messagesArea');
            if (!messagesArea) return;

            isScrolling = true;
            requestAnimationFrame(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
                isScrolling = false;
            });
        }

        // Attachment modal event listener
        const attachmentsModal = document.getElementById('attachmentsModal');
        if (attachmentsModal) {
            attachmentsModal.addEventListener('shown.bs.modal', function () {
                console.log('Attachments modal opened');
                loadAttachments();
            });
        }

        // Sidebar toggle functionality
        const toggleBtn = document.querySelector('.user-btn-collapse');
        const sidebar = document.querySelector('.user-vendors-sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');

                const icon = this.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.className = 'bi bi-chevron-right';
                } else {
                    icon.className = 'bi bi-chevron-left';
                }
            });
        }

        // Global functions for attachment actions
        window.downloadAttachment = function (filePath, fileName) {
            // Fix file path for user panel
            let correctedPath = filePath;
            
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
            
            console.log('Download - Original path:', filePath);
            console.log('Download - Corrected path:', correctedPath);
            
            const link = document.createElement('a');
            link.href = correctedPath;
            link.download = fileName;
            link.click();
        };

        window.viewAttachment = function (filePath) {
            window.open(filePath, '_blank');
        };

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    });
</script>

<style>
    /* Vendors Grid Layout */
    .vendors-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    /* Responsive Design for Vendors Grid */
    @media (max-width: 768px) {
        .vendors-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    /* Chat Header Actions */
    .user-chat-area-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border-light);
    }

    .user-chat-area-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-chat-area-actions .btn {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .user-chat-area-actions .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 480px) {
        .vendors-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
    }

    /* Badge Styling */
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border-radius: var(--radius-md);
    }

    .badge-lg i {
        font-size: 0.9rem;
    }

    /* Button Styling for Actions */
    .user-actions .btn,
    .user-status-actions .btn {
        background: var(--accent-red);
    color: var(--text-white);
    border: 1px solid var(--accent-red);
    }

    .user-actions .btn:hover,
    .user-status-actions .btn:hover {
        background: var(--btn-red-hover);
    border-color: var(--btn-red-hover);
    color: var(--text-white);
    transform: translateY(-1px);
    }


    /* .comment-actions .btn-back {
    background: var(--accent-red);
    color: var(--text-white);
    border: 1px solid var(--accent-red);
}
.comment-actions .btn-back:hover {
    background: var(--btn-red-hover);
    border-color: var(--btn-red-hover);
    color: var(--text-white);
    transform: translateY(-1px);
} */



    .user-actions .btn:active,
    .user-status-actions .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .user-actions .btn i,
    .user-status-actions .btn i {
        margin-right: 0.5rem;
        font-size: 0.9rem;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
    }

    /* Specific Button Colors and Gradients */
    .user-status-actions .btn-primary {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        color: white;
    }

    .user-status-actions .btn-primary:hover {
        background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
    }

    .user-status-actions .btn-success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
    }

    .user-status-actions .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .user-status-actions .btn-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }

    .user-status-actions .btn-warning:hover {
        background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
    }

    /* Badge Color Enhancements */
    .badge-sm.bg-success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
        color: white;
    }

    .badge-sm.bg-primary {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%) !important;
        color: white;
    }

    .badge-sm.bg-info {
        background: linear-gradient(135deg, #06B6D4 0%, #0891B2 100%) !important;
        color: white;
    }

    /* Modal Form Styling */
    .modal-content {
        border-radius: var(--radius-lg);
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-body .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .modal-body .form-control,
    .modal-body .form-select {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(106, 142, 235, 0.1);
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .modal-footer .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
    }

    /* Modal Scrollable */
    .modal-dialog-scrollable .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
        background: var(--accent-blue);
        border-radius: 3px;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--primary-color);
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-primary:hover {
        background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(220, 38, 38, 0.3);
    }

    .modal-footer .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
    }

    /* Dropdown Sections Styling */
    .dropdown-section {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .dropdown-section .btn {
        border: none;
        border-radius: 0;
        padding: 1rem 1.5rem;
        font-weight: 600;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
    }

    .dropdown-section .btn:focus {
        box-shadow: none;
    }

    .dropdown-section .btn-outline-primary {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.1) 100%);
        color: var(--accent-blue);
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .dropdown-section .btn-outline-primary:hover {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(29, 78, 216, 0.2) 100%);
        border-color: var(--accent-blue);
    }

    .dropdown-section .btn-outline-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
        color: var(--success-color);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .dropdown-section .btn-outline-success:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.2) 100%);
        border-color: var(--success-color);
    }

    .dropdown-section .btn-outline-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .dropdown-section .btn-outline-warning:hover {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%);
        border-color: #F59E0B;
    }

    .dropdown-content {
        padding: 1.5rem;
        background: rgba(248, 250, 252, 0.5);
        border-top: 1px solid var(--border-color);
    }

    /* Pictures Preview */
    .pictures-preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .picture-item {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-light);
    }

    .picture-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .picture-item .remove-picture {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        cursor: pointer;
    }

    /* Invoice Preview */
    .invoice-preview {
        margin-top: 1rem;
    }

    .invoice-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-light);
    }

    .invoice-item .file-icon {
        font-size: 2rem;
        color: var(--accent-blue);
    }

    .invoice-item .file-info h6 {
        margin: 0;
        color: var(--text-dark);
    }

    .invoice-item .file-info small {
        color: var(--text-muted);
    }

    .invoice-item .remove-invoice {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: var(--radius-sm);
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .invoice-item .remove-invoice:hover {
        background: rgba(239, 68, 68, 0.2);
        border-color: #EF4444;
    }

    /* Payment Platform Options */
    .payment-platform-option {
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .payment-platform-option:hover {
        border-color: var(--accent-blue);
        background: rgba(59, 130, 246, 0.05);
    }

    .payment-platform-option .form-check-input:checked+.form-check-label {
        color: var(--accent-blue);
        font-weight: 600;
    }

    .payment-platform-option .form-check-input:checked~* {
        border-color: var(--accent-blue);
    }

    .payment-platform-option .form-check-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }

    .payment-platform-option .form-check-input {
        margin-right: 0.75rem;
        transform: scale(1.2);
    }

    /* Payment Sections */
    .payment-section {
        margin-top: 1rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
    }

    .user-actions .action-btn {
        align-self: flex-start;
    }

    /* User Status Actions Styling */
    .user-status-actions {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .user-status-actions .btn,
    .user-status-actions .badge {
        width: 100%;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .user-status-actions .badge {
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* User Name Section Styling */
    .user-name-section {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .user-name-section h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .badge-sm {
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: var(--radius-lg);
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .badge-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .badge-sm i {
        font-size: 0.9rem;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
    }

    /* Add Vendor Modal Styling */
    .modal-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-body .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .modal-body .form-control,
    .modal-body .form-select {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(106, 142, 235, 0.1);
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .modal-footer .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, var(--accent-red) 0%, #a01a20 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-primary:hover {
        background: linear-gradient(135deg, #a01a20 0%, #8b151a 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(185, 30, 38, 0.3);
    }

    .modal-footer .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(185, 30, 38, 0.2);
    }

    /* Welcome Actions Styling */
    .welcome-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .welcome-actions .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .welcome-actions .btn-primary {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        border: none;
        color: white;
    }

    .welcome-actions .btn-primary:hover {
        background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(59, 130, 246, 0.3);
    }

    .welcome-actions .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        color: white;
    }

    .welcome-actions .btn-secondary:hover {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(108, 117, 125, 0.3);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Request Final Visit Approval Form
        const submitFinalVisitBtn = document.getElementById('submitFinalVisitApproval');
        const finalVisitApprovalForm = document.getElementById('finalVisitApprovalForm');

        if (submitFinalVisitBtn && finalVisitApprovalForm) {
            submitFinalVisitBtn.addEventListener('click', function () {
                // Validate form
                const estimatedAmount = document.getElementById('finalVisitEstimatedAmount').value;
                const visitDateTime = document.getElementById('finalVisitDateTime').value;
                const paymentMode = document.getElementById('finalVisitPaymentMode').value;
                const additionalNotes = document.getElementById('finalVisitAdditionalNotes').value;

                if (!estimatedAmount || !visitDateTime || !paymentMode) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }

                // Show loading state
                submitFinalVisitBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Submitting...';
                submitFinalVisitBtn.disabled = true;

                // Simulate form submission
                setTimeout(() => {
                    // Reset form
                    finalVisitApprovalForm.reset();

                    // Reset button
                    submitFinalVisitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Request';
                    submitFinalVisitBtn.disabled = false;

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('finalVisitApprovalModal'));
                    modal.hide();

                    // Show success message
                    showNotification('Final visit approval request submitted successfully!', 'success');

                    // Here you would typically send data to server
                    console.log('Final Visit Request:', {
                        estimatedAmount,
                        visitDateTime,
                        paymentMode,
                        additionalNotes
                    });
                }, 2000);
            });
        }


        // Request Payment Form
        const submitPaymentRequestBtn = document.getElementById('submitPaymentRequest');
        const requestPaymentForm = document.getElementById('requestPaymentForm');

        if (submitPaymentRequestBtn && requestPaymentForm) {
            submitPaymentRequestBtn.addEventListener('click', function () {
                const paymentPlatform = document.querySelector('input[name="paymentPlatform"]:checked');

                if (!paymentPlatform) {
                    showNotification('Please select a payment platform', 'error');
                    return;
                }

                let isValid = true;
                let errorMessage = '';

                if (paymentPlatform.value === 'payment_link') {
                    const paymentLinkUrl = document.getElementById('paymentLinkUrl').value;
                    if (!paymentLinkUrl) {
                        isValid = false;
                        errorMessage = 'Please enter payment link/invoice URL';
                    }
                } else if (paymentPlatform.value === 'zelle') {
                    const zelleEmailPhone = document.getElementById('zelleEmailPhone').value;
                    const zelleType = document.getElementById('zelleType').value;

                    if (!zelleEmailPhone || !zelleType) {
                        isValid = false;
                        errorMessage = 'Please fill in Zelle email/phone and type';
                    } else if (zelleType === 'business') {
                        const businessName = document.getElementById('businessName').value;
                        if (!businessName) {
                            isValid = false;
                            errorMessage = 'Please enter business name';
                        }
                    } else if (zelleType === 'personal') {
                        const firstName = document.getElementById('firstName').value;
                        const lastName = document.getElementById('lastName').value;
                        if (!firstName || !lastName) {
                            isValid = false;
                            errorMessage = 'Please enter first and last name';
                        }
                    }
                }

                if (!isValid) {
                    showNotification(errorMessage, 'error');
                    return;
                }

                // Show loading state
                submitPaymentRequestBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Processing...';
                submitPaymentRequestBtn.disabled = true;

                // Simulate form submission
                setTimeout(() => {
                    // Reset form
                    requestPaymentForm.reset();
                    hideAllPaymentSections();

                    // Reset button
                    submitPaymentRequestBtn.innerHTML = '<i class="bi bi-check-circle"></i> Request Payment';
                    submitPaymentRequestBtn.disabled = false;

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('requestPaymentModal'));
                    modal.hide();

                    // Show success message
                    showNotification('Payment request submitted successfully!', 'success');

                    // Here you would typically send data to server
                    console.log('Payment Request Data:', {
                        platform: paymentPlatform.value,
                        data: getPaymentData(paymentPlatform.value)
                    });
                }, 2000);
            });
        }

        // Toggle payment fields based on platform selection
        window.togglePaymentFields = function () {
            const paymentPlatform = document.querySelector('input[name="paymentPlatform"]:checked');

            // Hide all sections first
            hideAllPaymentSections();

            if (paymentPlatform) {
                if (paymentPlatform.value === 'payment_link') {
                    document.getElementById('paymentLinkSection').style.display = 'block';
                } else if (paymentPlatform.value === 'zelle') {
                    document.getElementById('zelleSection').style.display = 'block';
                }
            }
        };

        // Toggle Zelle fields based on type selection
        window.toggleZelleFields = function () {
            const zelleType = document.getElementById('zelleType').value;
            const businessNameField = document.getElementById('businessNameField');
            const personalNameFields = document.getElementById('personalNameFields');

            // Hide all fields first
            businessNameField.style.display = 'none';
            personalNameFields.style.display = 'none';

            if (zelleType === 'business') {
                businessNameField.style.display = 'block';
            } else if (zelleType === 'personal') {
                personalNameFields.style.display = 'block';
            }
        };

        // Hide all payment sections
        function hideAllPaymentSections() {
            document.getElementById('paymentLinkSection').style.display = 'none';
            document.getElementById('zelleSection').style.display = 'none';
            document.getElementById('businessNameField').style.display = 'none';
            document.getElementById('personalNameFields').style.display = 'none';
        }

        // Get payment data based on platform
        function getPaymentData(platform) {
            if (platform === 'payment_link') {
                return {
                    url: document.getElementById('paymentLinkUrl').value
                };
            } else if (platform === 'zelle') {
                const data = {
                    emailPhone: document.getElementById('zelleEmailPhone').value,
                    type: document.getElementById('zelleType').value
                };

                if (data.type === 'business') {
                    data.businessName = document.getElementById('businessName').value;
                } else if (data.type === 'personal') {
                    data.firstName = document.getElementById('firstName').value;
                    data.lastName = document.getElementById('lastName').value;
                }

                return data;
            }
            return {};
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
    });
</script>

<style>
    /* Notification Styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: var(--radius-md);
        padding: 1rem 1.5rem;
        box-shadow: var(--shadow-medium);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 1000;
        transform: translateX(400px);
        transition: all 0.3s ease;
        border-left: 4px solid var(--accent-blue);
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification-success {
        border-left-color: var(--success-color);
    }

    .notification-success i {
        color: var(--success-color);
    }

    .notification-error {
        border-left-color: #EF4444;
    }

    .notification-error i {
        color: #EF4444;
    }
</style>