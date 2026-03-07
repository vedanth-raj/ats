<header class="header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="header-title">Management Auto Attendance System</h2>
    </div>
    <div class="header-right">
        <div class="user-menu">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</header>
