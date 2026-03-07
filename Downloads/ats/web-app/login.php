<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config/database.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT auto_id, username FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['auto_id'];
            $_SESSION['username'] = $user['username'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
        
        $stmt->close();
        closeDBConnection($conn);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Management Auto Attendance System</h1>
                <p>Please login to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                <p class="text-muted">Default credentials: admin / kuna123</p>
            </div>
        </div>
    </div>
</body>
</html>
