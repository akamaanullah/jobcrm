<?php $pageTitle = 'Dashboard'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>My Notifications</h2>
                <p>Stay updated with your job requests and vendor communications</p>
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
                    <h3 id="totalNotificationsCount">0</h3>
                    <span class="metric-status text-primary">MY NOTIFICATIONS</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardUnread">
                <div class="metric-icon unread">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <div class="metric-content">
                    <h3 id="unreadNotificationsCount">0</h3>
                    <span class="metric-status text-warning">UNREAD</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardPending">
                <div class="metric-icon pending">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="pendingNotificationsCount">0</h3>
                    <span class="metric-status text-info">PENDING RESPONSE</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardResolved">
                <div class="metric-icon resolved">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-content">
                    <h3 id="resolvedNotificationsCount">0</h3>
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
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-btn" data-filter="accepted">
                    <i class="bi bi-check-circle"></i>
                    <span>Request Accepted</span>
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-btn" data-filter="visit">
                    <i class="bi bi-eye"></i>
                    <span>Visit Approved</span>
                    <span class="filter-count">0</span>
                </button>
                <button class="filter-btn" data-filter="payment">
                    <i class="bi bi-credit-card"></i>
                    <span>Payment Ready</span>
                    <span class="filter-count">0</span>
                </button>
            </div>
        </div>

        <!-- Request Cards Section -->
        <div class="requests-container" id="notificationsContainer">
            <!-- Dynamic notification cards will be loaded here -->
            <div class="text-center py-5" id="notificationsLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading notifications...</p>
            </div>
        </div>

</div>

</main>
</div>


<!-- Payment Request Details Modal -->
<div class="modal fade" id="paymentRequestModal" tabindex="-1" aria-labelledby="paymentRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card"></i> Payment Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentRequestModalBody">
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
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Job Completion Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="jobCompletedModalBody">
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

<!-- Partial Payment Modal -->
<div class="modal fade" id="partialPaymentModal" tabindex="-1" aria-labelledby="partialPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cash-stack"></i> Partial Payment Details
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="partialPaymentModalBody">
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

<!-- User Chat Modal -->
<div class="modal fade" id="userChatModal" tabindex="-1" aria-labelledby="userChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content user-chat-modal-content">
            <div class="modal-header user-chat-header">
                <div class="user-chat-user-info">
                    <div class="user-chat-avatar" id="chatAreaAvatar">T</div>
                    <div>
                        <h5 class="user-chat-username" id="chatAreaName">Chat about testing</h5>
                        <p class="user-chat-status" id="chatAreaJob">Job #JOB-001 <span
                                class="badge bg-success">Active</span></p>
                    </div>
                </div>
                <div class="user-chat-area-actions">
                    <button class="user-chat-action-btn" title="Attachments" data-bs-toggle="modal"
                        data-bs-target="#attachmentsModal">
                        <i class="bi bi-paperclip"></i>
                        <span class="badge">3</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body user-chat-body">
                <div class="user-chat-container">
                    <!-- User Chat Area -->
                    <div class="user-chat-area">
                        <!-- User Messages Area -->
                        <div class="user-messages-area" id="messagesArea">
                            <!-- Messages will be loaded dynamically here -->
                            <div class="user-no-messages" id="noMessagesPlaceholder">
                                <div class="user-no-messages-icon">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h6>No messages yet</h6>
                                <p>Start a conversation with the vendor</p>
                            </div>
                        </div>

                        <!-- File Preview Area -->
                        <div class="attachment-preview-area" id="chatAttachmentPreview" style="display: none;">
                            <div class="attachment-preview-header">
                                <span class="attachment-preview-title">Attached Files</span>
                                <button class="btn btn-sm btn-outline-danger" onclick="clearAllAttachments()">
                                    <i class="bi bi-x"></i> Clear All
                                </button>
                            </div>
                            <div class="attachment-preview-list" id="chatAttachmentList">
                                <!-- Attachment previews will be added here -->
                            </div>
                        </div>

                        <!-- User Message Input Area -->
                        <div class="user-message-input-area">
                            <div class="user-message-input-wrapper">
                                <button class="user-message-attach-btn" title="Attach File" id="chatAttachBtn">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <input type="file" id="chatFileInput" style="display: none;" multiple
                                    accept="image/*,.pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx">
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
    <div class="modal-dialog modal-lg">
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
                                <h6 id="attachmentVendorName">Vendor Name</h6>
                                <small class="text-muted" id="attachmentJobInfo">Job #N/A</small>
                            </div>
                        </div>
                        <div class="attachment-stats">
                            <span class="badge bg-primary" id="attachmentCount">0 files</span>
                        </div>
                    </div>

                    <div class="attachments-list" id="attachmentsList">
                        <!-- Attachments will be loaded dynamically here -->
                        <div class="attachments-empty" id="attachmentsEmpty">
                            <div class="attachments-empty-icon">
                                <i class="bi bi-paperclip"></i>
                            </div>
                            <h6>No attachments yet</h6>
                            <p>Files shared in this conversation will appear here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Chat Modal Vendor Selection for Notifications
    document.addEventListener('DOMContentLoaded', function () {
        const chatModal = document.getElementById('userChatModal');
        const chatAreaAvatar = document.getElementById('chatAreaAvatar');
        const chatAreaName = document.getElementById('chatAreaName');
        const chatAreaJob = document.getElementById('chatAreaJob');

        // Function to handle chat button clicks (called dynamically)
        function handleChatButtonClick(button) {
            const vendorName = button.getAttribute('data-vendor');
            const vendorAvatar = button.getAttribute('data-vendor-avatar');
            const jobId = button.getAttribute('data-job-id');
            const vendorId = button.getAttribute('data-vendor-id');

            console.log('Opening chat with vendor:', {
                name: vendorName,
                avatar: vendorAvatar,
                jobId: jobId,
                vendorId: vendorId
            });

            // Set global variables for current chat context
            currentVendorId = vendorId;
            currentJobId = jobId;
            currentVendorName = vendorName;
            currentVendorAvatar = vendorAvatar;

            // Update chat modal header
            if (chatAreaAvatar) chatAreaAvatar.textContent = vendorAvatar || 'V';
            if (chatAreaName) chatAreaName.textContent = `Chat with ${vendorName || 'Vendor'}`;
            if (chatAreaJob) chatAreaJob.innerHTML = `Job #${jobId || 'N/A'} <span class="badge bg-success">Active</span>`;

            // Update attachments modal header
            updateAttachmentsModalHeader(vendorName, vendorAvatar, jobId);

            // Update attachment button count
            updateAttachmentButtonCount();

            // Update messages area with vendor-specific content
            updateMessagesForVendor(vendorName, vendorAvatar, vendorId, jobId);

            // Setup message sending event listeners
            setupMessageSendingListeners();
        }

        // Handle existing chat button clicks
        const chatButtons = document.querySelectorAll('.btn-vendor[data-bs-target="#userChatModal"]');
        chatButtons.forEach(button => {
            button.addEventListener('click', function () {
                handleChatButtonClick(this);
            });
        });

        // Handle dynamically added chat buttons (for notifications loaded via AJAX)
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-vendor[data-bs-target="#userChatModal"]')) {
                const button = e.target.closest('.btn-vendor[data-bs-target="#userChatModal"]');
                handleChatButtonClick(button);
            }
        });

        // Function to update messages based on selected vendor
        function updateMessagesForVendor(vendorName, vendorAvatar, vendorId, jobId) {
            const messagesArea = document.getElementById('messagesArea');
            if (!messagesArea) return;

            console.log('Updating messages for vendor:', {
                name: vendorName,
                avatar: vendorAvatar,
                vendorId: vendorId,
                jobId: jobId
            });

            // Show loading state
            messagesArea.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading messages...</span>
                    </div>
                    <p class="text-muted mt-2">Loading messages...</p>
                </div>
            `;

            // Load messages from API
            loadMessages(vendorId, jobId, vendorName, vendorAvatar);
        }

        // Function to load messages from API
        async function loadMessages(vendorId, jobId, vendorName, vendorAvatar) {
            try {
                const response = await fetch(`assets/api/get_messages.php?vendor_id=${vendorId}&job_id=${jobId}`);
                const result = await response.json();

                if (result.success) {
                    displayMessages(result.data.messages, vendorName, vendorAvatar);
                } else {
                    showNoMessages(vendorName);
                    console.error('Failed to load messages:', result.message);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                showNoMessages(vendorName);
            }
        }

        // Function to display messages
        function displayMessages(messages, vendorName, vendorAvatar) {
            const messagesArea = document.getElementById('messagesArea');
            if (!messagesArea) return;

            if (!messages || messages.length === 0) {
                showNoMessages(vendorName);
                return;
            }

            // Clear loading state
            messagesArea.innerHTML = '';

            // Reverse messages to show oldest first (since API returns newest first)
            const reversedMessages = messages.reverse();

            // Group messages by date
            const groupedMessages = groupMessagesByDate(reversedMessages);

            // Display grouped messages
            Object.keys(groupedMessages).forEach(date => {
                // Add date separator
                const dateSeparator = document.createElement('div');
                dateSeparator.className = 'message-date-separator';
                dateSeparator.innerHTML = `
                    <div class="date-line">
                        <span class="date-text">${date}</span>
                    </div>
                `;
                messagesArea.appendChild(dateSeparator);

                // Add messages for this date
                groupedMessages[date].forEach(message => {
                    const messageElement = createMessageElement(message, vendorAvatar);
                    messagesArea.appendChild(messageElement);
                });
            });

            // Auto scroll to bottom after messages are rendered
            setTimeout(() => {
                scrollChatToBottom();
            }, 200);
        }

        // Function to group messages by date
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

        // Function to create message element
        function createMessageElement(message, vendorAvatar) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${message.is_sent ? 'sent' : 'received'}`;

            const avatar = message.is_sent ? 'U' : (vendorAvatar || message.sender_avatar);
            const senderName = message.is_sent ? 'You' : message.sender_name;

            messageDiv.innerHTML = `
                        <div class="message-avatar">
                    <span>${avatar}</span>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                        <span class="message-sender">${senderName}</span>
                        <span class="message-time">${message.formatted_time_only}</span>
                            </div>
                    ${message.message ? `<div class="message-text">${message.message}</div>` : ''}
                    ${message.attachment ? createAttachmentElement(message.attachments) : ''}
                        </div>
                    `;

            return messageDiv;
        }

        // Function to create attachment element
        function createAttachmentElement(attachments) {
            if (!attachments || attachments.length === 0) return '';

            let attachmentHTML = '<div class="message-attachment">';

            attachments.forEach(attachment => {
                const fileIcon = getFileIcon(attachment.file_type);
                attachmentHTML += `
                    <div class="attachment-item">
                        <div class="attachment-icon">
                            <i class="${fileIcon}"></i>
                        </div>
                        <div class="attachment-info">
                            <div class="attachment-name">${attachment.file_name}</div>
                            <div class="attachment-meta">
                                <span class="attachment-size">${formatFileSize(attachment.file_size)}</span>
                                <span class="attachment-date">${attachment.created_at}</span>
                            </div>
                        </div>
                        <div class="attachment-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="downloadAttachment('${attachment.file_path}', '${attachment.file_name}')">
                                <i class="bi bi-download"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            attachmentHTML += '</div>';
            return attachmentHTML;
        }

        // Function to get file icon based on file type
        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return 'bi bi-image';
            if (fileType.includes('pdf')) return 'bi bi-file-pdf';
            if (fileType.includes('word') || fileType.includes('document')) return 'bi bi-file-word';
            if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'bi bi-file-excel';
            if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'bi bi-file-ppt';
            return 'bi bi-file';
        }

        // Function to format file size
        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Function to show no messages state
        function showNoMessages(vendorName) {
            const messagesArea = document.getElementById('messagesArea');
            if (!messagesArea) return;

            messagesArea.innerHTML = `
                <div class="user-no-messages" id="noMessagesPlaceholder">
                    <div class="user-no-messages-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h6>No messages yet</h6>
                    <p>Start a conversation with ${vendorName || 'the vendor'}</p>
                        </div>
                    `;
        }

        // Function to download attachment
        function downloadAttachment(filePath, fileName) {
            const link = document.createElement('a');
            link.href = filePath;
            link.download = fileName;
            link.click();
        }

        // Global variables for current chat context
        let currentVendorId = null;
        let currentJobId = null;
        let currentVendorName = null;
        let currentVendorAvatar = null;

        // Function to send message
        async function sendMessage() {
            const messageInput = document.querySelector('.user-message-input');
            const message = messageInput.value.trim();

            if (!currentVendorId || !currentJobId) {
                return;
            }

            // Check if we have either message or attachment
            const fileInput = document.getElementById('chatFileInput');
            const hasMessage = message && message.trim().length > 0;
            const hasAttachment = fileInput && fileInput.files.length > 0;

            if (!hasMessage && !hasAttachment) {
                showNotification('Please enter a message or attach a file', 'warning');
                return;
            }

            // Disable input and show loading
            messageInput.disabled = true;
            const sendBtn = document.querySelector('.user-message-send-btn');
            const originalBtnContent = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            sendBtn.disabled = true;

            try {
                // Prepare form data
                const formData = new FormData();
                formData.append('vendor_id', currentVendorId);
                formData.append('job_id', currentJobId);
                formData.append('message', message || ''); // Allow empty message
                // receiver_id is determined automatically by API based on sender role

                // Add file if selected
                const fileInput = document.getElementById('chatFileInput');
                if (fileInput.files.length > 0) {
                    formData.append('attachment', fileInput.files[0]);
                }

                const response = await fetch('assets/api/send_message.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Clear input
                    messageInput.value = '';
                    fileInput.value = '';

                    // Clear file attachments preview
                    clearAllAttachments();

                    // Add message to chat
                    addMessageToChat(result.data);

                    // Update attachment count if message had attachment
                    if (result.data.attachment) {
                        updateAttachmentButtonCount();
                    }

                    // Show success notification
                    showNotification('Message sent successfully', 'success');
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error sending message:', error);
                showNotification('Failed to send message: ' + error.message, 'error');
            } finally {
                // Re-enable input
                messageInput.disabled = false;
                sendBtn.innerHTML = originalBtnContent;
                sendBtn.disabled = false;
                messageInput.focus();
            }
        }

        // Function to add message to chat
        function addMessageToChat(messageData) {
            const messagesArea = document.getElementById('messagesArea');
            const noMessagesPlaceholder = document.getElementById('noMessagesPlaceholder');

            // Remove no messages placeholder if it exists
            if (noMessagesPlaceholder) {
                noMessagesPlaceholder.remove();
            }

            // Create message element
            const messageElement = createMessageElement(messageData, currentVendorAvatar);

            // Add to messages area
            messagesArea.appendChild(messageElement);

            // Auto scroll to bottom immediately
            scrollChatToBottom();
        }

        // Flag to prevent multiple scrolls
        let isScrolling = false;

        // Function to scroll chat to bottom
        function scrollChatToBottom() {
            if (isScrolling) return; // Prevent multiple scrolls

            const messagesArea = document.getElementById('messagesArea');
            if (!messagesArea) return;

            isScrolling = true;

            // Use requestAnimationFrame for smooth, single scroll
            requestAnimationFrame(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
                isScrolling = false;
            });
        }

        // Function to update attachments modal header
        function updateAttachmentsModalHeader(vendorName, vendorAvatar, jobId) {
            const attachmentVendorAvatar = document.getElementById('attachmentVendorAvatar');
            const attachmentVendorName = document.getElementById('attachmentVendorName');
            const attachmentJobInfo = document.getElementById('attachmentJobInfo');

            if (attachmentVendorAvatar) attachmentVendorAvatar.textContent = vendorAvatar || 'V';
            if (attachmentVendorName) attachmentVendorName.textContent = vendorName || 'Vendor';
            if (attachmentJobInfo) attachmentJobInfo.textContent = `Job #${jobId || 'N/A'}`;
        }

        // Function to update attachment button count
        async function updateAttachmentButtonCount() {
            if (!currentVendorId || !currentJobId) return;

            try {
                const response = await fetch(`assets/api/get_messages.php?vendor_id=${currentVendorId}&job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success) {
                    // Count total attachments
                    let totalAttachments = 0;
                    result.data.messages.forEach(message => {
                        if (message.attachment && message.attachments && message.attachments.length > 0) {
                            totalAttachments += message.attachments.length;
                        }
                    });

                    // Update button badge
                    const attachmentButton = document.querySelector('.user-chat-action-btn[data-bs-target="#attachmentsModal"] .badge');
                    if (attachmentButton) {
                        attachmentButton.textContent = totalAttachments;
                        attachmentButton.style.display = totalAttachments > 0 ? 'inline' : 'none';
                    }
                }
            } catch (error) {
                console.error('Error updating attachment count:', error);
            }
        }

        // Function to load attachments for the current conversation
        async function loadAttachments() {
            if (!currentVendorId || !currentJobId) return;

            try {
                const response = await fetch(`assets/api/get_messages.php?vendor_id=${currentVendorId}&job_id=${currentJobId}`);
                const result = await response.json();

                if (result.success) {
                    displayAttachments(result.data.messages);
                } else {
                    console.error('Failed to load attachments:', result.message);
                    showEmptyAttachments();
                }
            } catch (error) {
                console.error('Error loading attachments:', error);
                showEmptyAttachments();
            }
        }

        // Function to display attachments in the modal
        function displayAttachments(messages) {
            const attachmentsList = document.getElementById('attachmentsList');
            const attachmentCount = document.getElementById('attachmentCount');
            const attachmentsEmpty = document.getElementById('attachmentsEmpty');

            if (!attachmentsList) return;

            // Filter messages that have attachments
            const messagesWithAttachments = messages.filter(message => message.attachment && message.attachments && message.attachments.length > 0);

            if (messagesWithAttachments.length === 0) {
                showEmptyAttachments();
                return;
            }

            // Clear existing content
            attachmentsList.innerHTML = '';

            // Count total attachments
            let totalAttachments = 0;

            // Display attachments
            messagesWithAttachments.forEach(message => {
                message.attachments.forEach(attachment => {
                    const attachmentItem = createAttachmentModalItem(attachment, message);
                    attachmentsList.appendChild(attachmentItem);
                    totalAttachments++;
                });
            });

            // Update count
            if (attachmentCount) {
                attachmentCount.textContent = `${totalAttachments} file${totalAttachments !== 1 ? 's' : ''}`;
            }
        }

        // Function to create attachment item for modal
        function createAttachmentModalItem(attachment, message) {
            const item = document.createElement('div');
            item.className = 'attachment-item';

            const fileIcon = getFileIconForModal(attachment.file_type);
            const fileSize = formatFileSize(attachment.file_size);
            const messageDate = new Date(message.created_at).toLocaleDateString();

            item.innerHTML = `
                <div class="attachment-icon">
                    ${fileIcon}
                </div>
                <div class="attachment-info">
                    <div class="attachment-name">${attachment.file_name}</div>
                    <div class="attachment-meta">
                        <span class="attachment-size">${fileSize}</span>
                        <span class="attachment-date">${messageDate}</span>
                    </div>
                </div>
                <div class="attachment-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadAttachment('${attachment.file_path}', '${attachment.file_name}')">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            `;

            return item;
        }

        // Function to get file icon for modal
        function getFileIconForModal(fileType) {
            if (fileType.startsWith('image/')) {
                return '<i class="bi bi-file-earmark-image text-primary"></i>';
            } else if (fileType === 'application/pdf') {
                return '<i class="bi bi-file-earmark-pdf text-danger"></i>';
            } else if (fileType.includes('word') || fileType.includes('document')) {
                return '<i class="bi bi-file-earmark-word text-info"></i>';
            } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
                return '<i class="bi bi-file-earmark-excel text-success"></i>';
            } else if (fileType.includes('powerpoint') || fileType.includes('presentation')) {
                return '<i class="bi bi-file-earmark-ppt text-warning"></i>';
            } else if (fileType.includes('text')) {
                return '<i class="bi bi-file-earmark-text text-secondary"></i>';
            } else {
                return '<i class="bi bi-file-earmark text-muted"></i>';
            }
        }

        // Function to show empty attachments state
        function showEmptyAttachments() {
            const attachmentsList = document.getElementById('attachmentsList');
            const attachmentCount = document.getElementById('attachmentCount');

            if (attachmentsList) {
                attachmentsList.innerHTML = `
                    <div class="attachments-empty" id="attachmentsEmpty">
                        <div class="attachments-empty-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>
                        <h6>No attachments yet</h6>
                        <p>Files shared in this conversation will appear here</p>
                    </div>
                `;
            }

            if (attachmentCount) {
                attachmentCount.textContent = '0 files';
            }
        }


        // Function to show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Function to show file previews
        function showFilePreviews(files) {
            const previewArea = document.getElementById('chatAttachmentPreview');
            const previewList = document.getElementById('chatAttachmentList');

            if (!previewArea || !previewList) return;

            // Clear existing previews
            previewList.innerHTML = '';

            // Add each file preview
            Array.from(files).forEach((file, index) => {
                const previewItem = createFilePreviewItem(file, index);
                previewList.appendChild(previewItem);
            });

            // Show preview area
            previewArea.style.display = 'block';
        }

        // Function to create file preview item
        function createFilePreviewItem(file, index) {
            const item = document.createElement('div');
            item.className = 'attachment-preview-item';
            item.dataset.index = index;

            const fileIcon = getFileIconForPreview(file.type);
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

        // Function to get file icon for preview
        function getFileIconForPreview(fileType) {
            if (fileType.startsWith('image/')) {
                return '<i class="bi bi-file-earmark-image text-primary"></i>';
            } else if (fileType === 'application/pdf') {
                return '<i class="bi bi-file-earmark-pdf text-danger"></i>';
            } else if (fileType.includes('word') || fileType.includes('document')) {
                return '<i class="bi bi-file-earmark-word text-info"></i>';
            } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
                return '<i class="bi bi-file-earmark-excel text-success"></i>';
            } else if (fileType.includes('powerpoint') || fileType.includes('presentation')) {
                return '<i class="bi bi-file-earmark-ppt text-warning"></i>';
            } else if (fileType.includes('text')) {
                return '<i class="bi bi-file-earmark-text text-secondary"></i>';
            } else {
                return '<i class="bi bi-file-earmark text-muted"></i>';
            }
        }

        // Function to remove individual attachment preview
        window.removeAttachmentPreview = function (index) {
            const fileInput = document.getElementById('chatFileInput');
            const previewList = document.getElementById('chatAttachmentList');

            if (!fileInput || !previewList) return;

            // Remove the preview item
            const itemToRemove = previewList.querySelector(`[data-index="${index}"]`);
            if (itemToRemove) {
                itemToRemove.remove();
            }

            // Update file input
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            fileInput.files = dt.files;

            // Hide preview area if no files left
            if (fileInput.files.length === 0) {
                const previewArea = document.getElementById('chatAttachmentPreview');
                if (previewArea) {
                    previewArea.style.display = 'none';
                }
            }
        }

        // Function to clear all attachments
        window.clearAllAttachments = function () {
            const fileInput = document.getElementById('chatFileInput');
            const previewArea = document.getElementById('chatAttachmentPreview');
            const previewList = document.getElementById('chatAttachmentList');

            if (fileInput) {
                fileInput.value = '';
            }

            if (previewList) {
                previewList.innerHTML = '';
            }

            if (previewArea) {
                previewArea.style.display = 'none';
            }

            showNotification('All attachments cleared', 'info');
        }

        // Function to setup message sending event listeners
        function setupMessageSendingListeners() {
            // Remove existing listeners to avoid duplicates
            const sendBtn = document.querySelector('.user-message-send-btn');
            const messageInput = document.querySelector('.user-message-input');
            const attachBtn = document.querySelector('.user-message-attach-btn');

            if (sendBtn) {
                // Remove existing click listener
                sendBtn.replaceWith(sendBtn.cloneNode(true));
                const newSendBtn = document.querySelector('.user-message-send-btn');
                newSendBtn.addEventListener('click', sendMessage);
            }

            if (messageInput) {
                // Remove existing keydown listener
                messageInput.replaceWith(messageInput.cloneNode(true));
                const newMessageInput = document.querySelector('.user-message-input');
                newMessageInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }

            if (attachBtn) {
                // Remove existing click listener
                attachBtn.replaceWith(attachBtn.cloneNode(true));
                const newAttachBtn = document.querySelector('.user-message-attach-btn');
                newAttachBtn.addEventListener('click', function () {
                    document.getElementById('chatFileInput').click();
                });
            }

            // File input change listener
            const fileInput = document.getElementById('chatFileInput');
            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    const files = this.files;
                    if (files.length > 0) {
                        showFilePreviews(files);
                        showNotification(`${files.length} file(s) selected`, 'success');
                    }
                });
            }
        }

        // Modal ready for real-time messaging
        if (chatModal) {
            chatModal.addEventListener('shown.bs.modal', function () {
                console.log('Chat modal opened and ready for messaging');

                // Scroll to bottom when modal opens
                setTimeout(() => {
                    scrollChatToBottom();
                }, 300);
            });
        }

        // Attachments modal event listener
        const attachmentsModal = document.getElementById('attachmentsModal');
        if (attachmentsModal) {
            attachmentsModal.addEventListener('shown.bs.modal', function () {
                console.log('Attachments modal opened');
                // Load attachments when modal opens
                loadAttachments();
            });
        }
    });
</script>

<script src="assets/js/notifications.js"></script>

<?php include 'footer.php'; ?>