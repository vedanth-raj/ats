<?php
session_start();
require_once 'config/json-database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index-fast.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $db = new JsonDB();
        $users = $db->query('users');
        
        foreach ($users as $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['auto_id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index-fast.php');
                exit();
            }
        }
        
        $error = 'Invalid username or password';
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🚀 Management Auto Attendance System</h1>
                <p>Please login to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Login
                </button>
            </form>
            
            <div class="login-footer">
                <p class="text-muted">Default credentials: admin / kuna123</p>
                <p style="margin-top: 0.5rem; color: #27ae60;">⚡ Fast Mode - JSON Database (No MySQL)</p>
            </div>
        </div>
    </div>
</body>
</html>
