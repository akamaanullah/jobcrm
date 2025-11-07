
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || !isset($_SESSION['user_role'])) {
    // Redirect to login page
    header('Location: ../index.php');
    exit();
}

// Check if user role is 'admin'
if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to appropriate dashboard based on role
    if ($_SESSION['user_role'] === 'user') {
        header('Location: ../user/dashboard.php');
    } elseif ($_SESSION['user_role'] === 'manager') {
        header('Location: ../manager/dashboard.php');
    } else {
        header('Location: ../index.php');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job System Portal - Admin Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/grouped_vendors.css">
    <link rel="stylesheet" href="assets/css/password_form.css">
</head>
<body>

 <!-- Top Bar -->
 <header class="topbar">
            <div class="topbar-left">
                <!-- <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button> -->
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
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person"></i> My Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout-item" href="../assets/api/logout.php" id="logoutBtn">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>
   