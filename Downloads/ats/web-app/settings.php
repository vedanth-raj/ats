<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p>System configuration</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Database Configuration</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Server:</strong> localhost
                        </div>
                        <div class="info-item">
                            <strong>Database:</strong> management_auto_attendance_system
                        </div>
                        <div class="info-item">
                            <strong>Username:</strong> root
                        </div>
                        <div class="info-item">
                            <strong>Status:</strong> <span class="status-badge status-complete">Connected</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2>System Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Version:</strong> 1.0.0
                        </div>
                        <div class="info-item">
                            <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                        </div>
                        <div class="info-item">
                            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>

<style>
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.info-item strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}
</style>
