<?php 
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || !isset($_SESSION['user_role'])) {
    header('Location: ../../index.php');
    exit();
}

// Check if user role is 'admin'
if ($_SESSION['user_role'] !== 'admin') {
    if ($_SESSION['user_role'] === 'user') {
        header('Location: ../../user/dashboard.php');
    } else {
        header('Location: ../../index.php');
    }
    exit();
}

// Set user data for use in the page
$currentUser = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role']
];

$pageTitle = 'HandyRepairCenter Invoice';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - HandyRepairCenter</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        </div>
        
        <div class="topbar-right">
            <div class="topbar-notification-icon" id="notificationIcon">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger" id="notificationBadge">0</span>
            </div>
            <div class="topbar-message-icon" id="messageIcon">
                <i class="bi bi-chat-dots"></i>
                <span class="badge bg-danger" id="messageBadge">0</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle user-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></div>
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        <span class="user-role">Administrator</span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../../profile.php">
                        <i class="bi bi-person"></i> My Profile
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item logout-item" href="../../assets/api/logout.php" id="logoutBtn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="admin-panel-title">
                <i class="bi bi-shield-check"></i>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="../../dashboard.php" class="nav-link">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../users.php" class="nav-link">
                        <i class="bi bi-person-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../jobs.php" class="nav-link">
                        <i class="bi bi-clipboard-data"></i>
                        <span>All Jobs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../vendors.php" class="nav-link">
                        <i class="bi bi-building-gear"></i>
                        <span>Manage Vendors</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../requests.php" class="nav-link">
                        <i class="bi bi-bell-fill"></i>
                        <span>Requests</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="../../create-invoice.php" class="nav-link">
                        <i class="bi bi-receipt"></i>
                        <span>Create Invoice</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Back Button -->
        <div class="invoice-nav">
            <a href="../../create-invoice.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Invoice Selection
            </a>
        </div>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-section">
                <img src="assets/images/Getfixready-LOGO_1.png" alt="HandyRepairCenter Logo" class="logo">

            </div>
            <!-- <div class="invoice-details">
                <div class="invoice-number">Invoice No. 1-46602</div>
                <div class="invoice-date">Date: 09/29/2025</div>
            </div> -->
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="billing-left">
                <div class="billing-label">Invoice To:</div>
                <div class="billing-address">
                    <div>AT&T</div>
                    <div>1205 N Cedar St</div>
                    <div>Borger, TX 79007</div>
                </div>
            </div>
            <div class="invoice-details">
                <div class="invoice-number">Invoice No. 1-46602</div>
                <div class="invoice-date">Date: 09/29/2025</div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-container">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="item-col">Item</th>
                        <th class="qty-col">Quantity</th>
                        <th class="price-col">Unit Price</th>
                        <th class="total-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Material (Benjamin Moore Paint)</td>
                        <td>-</td>
                        <td>$290.00</td>
                        <td>$290.00</td>
                    </tr>
                    <tr>
                        <td>Material (Benjamin Moore Paint)</td>
                        <td>-</td>
                        <td>$290.00</td>
                        <td>$290.00</td>
                    </tr>
                    <tr>
                        <td>Material (Benjamin Moore Paint)</td>
                        <td>-</td>
                        <td>$290.00</td>
                        <td>$290.00</td>
                    </tr>
                    <tr>
                        <td>Material (Benjamin Moore Paint)</td>
                        <td>-</td>
                        <td>$210.00</td>
                        <td>$210.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="subtotal">
                <span>Subtotal: $980.00</span>
            </div>
            <div class="total">
                <span>Total: $980.00</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">Thank you</div>
            <div class="footer-right">PHONE NO: (469) 943-2530</div>
        </div>
        </div>
    </div>

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
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/notification-service.js"></script>
</body>

</html>