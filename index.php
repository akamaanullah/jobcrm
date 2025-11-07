<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    // Redirect to appropriate dashboard based on role
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['user_role'] === 'manager') {
        header('Location: manager/dashboard.php');
    } elseif ($_SESSION['user_role'] === 'user') {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job System Portal - Login</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Login Container -->
    <div class="login-container">
        <!-- Background Elements -->
        <div class="bg-elements">
            <div class="bg-circle bg-circle-1"></div>
            <div class="bg-circle bg-circle-2"></div>
            <div class="bg-circle bg-circle-3"></div>
        </div>
        
        <!-- Login Card -->
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h1 class="logo-text">Job System Portal</h1>
                </div>
                <p class="login-subtitle">Welcome back! Please sign in to your account</p>
            </div>
            
            <!-- Login Form -->
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="bi bi-person"></i>
                        Username
                    </label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        <div class="input-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <div class="input-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                
                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="bi bi-arrow-clockwise"></i>
                        Signing In...
                    </span>
                </button>
            </form>
            
            <!-- Footer -->
            <div class="login-footer">
                <p class="footer-text">
                    <i class="bi bi-shield-lock"></i>
                    Secure login with enterprise-grade security
                </p>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <div class="alert-container" id="alertContainer"></div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/login.js"></script>
</body>
</html>