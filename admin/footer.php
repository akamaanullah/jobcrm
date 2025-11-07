
    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content chat-modal-content">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-avatar">A</div>
                        <div>
                            <h6 class="chat-username">Chat with abc</h6>
                            <p class="chat-status">Job #JOB-3174 • new</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Chat Body -->
                <div class="chat-body">
                    <div class="chat-container">
                        <!-- Vendors Sidebar -->
                        <div class="vendors-sidebar">
                            <div class="vendors-header">
                                <h6>Vendors</h6>
                                <button class="btn-collapse">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </div>
                            <div class="vendors-list">
                                <div class="vendor-item active">
                                    <div class="vendor-avatar">A</div>
                                    <div class="vendor-info">
                                        <div class="vendor-name">abc</div>
                                        <div class="vendor-job">Job #JOB-3174 • new</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Area -->
                        <div class="chat-area">
                            <div class="chat-area-header">
                                <div class="chat-area-user">
                                    <div class="chat-area-avatar">A</div>
                                    <div>
                                        <div class="chat-area-name">Chat with abc</div>
                                        <div class="chat-area-job">Job #JOB-3174 • new</div>
                                    </div>
                                </div>
                                <div class="chat-area-actions">
                                    <button class="chat-action-btn" title="Attachments">
                                        <i class="bi bi-paperclip"></i>
                                        <span class="badge">0</span>
                                    </button>
                                    <button class="chat-action-btn" title="More Options">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Messages Area -->
                            <div class="messages-area">
                                <div class="no-messages">
                                    <div class="no-messages-icon">
                                        <i class="bi bi-chat-dots"></i>
                                    </div>
                                    <h6>No Messages Yet</h6>
                                    <p>Start the conversation about this vendor</p>
                                </div>
                            </div>

                            <!-- Message Input Area -->
                            <div class="message-input-area">
                                <div class="message-input-wrapper">
                                    <input type="text" class="message-input" placeholder="Type your message...">
                                    <button class="message-attach-btn" title="Attach File">
                                        <i class="bi bi-paperclip"></i>
                                    </button>
                                    <button class="message-send-btn" title="Send Message">
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    <script src="assets/js/notification-service.js"></script>
</body>
</html>