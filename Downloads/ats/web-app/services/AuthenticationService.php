<?php
/**
 * AuthenticationService - Manages user authentication and sessions
 * Handles login, logout, session validation, and role-based access control
 */

require_once __DIR__ . '/../config/json-database.php';

class AuthenticationService {
    private $db;
    private $sessionTimeout = 28800; // 8 hours in seconds
    
    public function __construct(JsonDB $db) {
        $this->db = $db;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Authenticate user and create session
     * @param string $username Username
     * @param string $password Password
     * @return array Result with success status, message, and user data
     */
    public function login($username, $password) {
        // Find user by username
        $users = $this->db->find('users', ['username' => $username]);
        
        if (empty($users)) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        $user = $users[0];
        
        // Verify password
        if (!$this->verifyPassword($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Create session
        $_SESSION['user_id'] = $user['auto_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'employee';
        $_SESSION['last_activity'] = time();
        $_SESSION['fingerprint'] = $this->generateFingerprint();
        
        // Update last login time
        $this->db->update('users', $user['auto_id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['auto_id'],
                'username' => $user['username'],
                'role' => $user['role'] ?? 'employee',
                'email' => $user['email'] ?? ''
            ]
        ];
    }
    
    /**
     * Logout user and destroy session
     */
    public function logout() {
        // Clear session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if user is authenticated
     * @return bool True if authenticated
     */
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive > $this->sessionTimeout) {
                $this->logout();
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        // Validate session fingerprint (prevent hijacking)
        if (isset($_SESSION['fingerprint'])) {
            $currentFingerprint = $this->generateFingerprint();
            if ($_SESSION['fingerprint'] !== $currentFingerprint) {
                error_log('Possible session hijacking attempt detected');
                $this->logout();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get current authenticated user
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $user = $this->db->findById('users', $_SESSION['user_id']);
        
        if ($user) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Check if current user has a specific role
     * @param string $role Role to check
     * @return bool True if user has the role
     */
    public function hasRole($role) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if current user is admin
     * @return bool True if user is admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Require authentication - redirect to login if not authenticated
     * @param string $redirectUrl URL to redirect to after login
     */
    public function requireAuth($redirectUrl = '/login.php') {
        if (!$this->isAuthenticated()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Require admin role - redirect if not admin
     * @param string $redirectUrl URL to redirect to if not admin
     */
    public function requireAdmin($redirectUrl = '/index.php') {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Hash password using bcrypt
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verify password against hash
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public function verifyPassword($password, $hash) {
        // Check if hash is bcrypt format
        if (substr($hash, 0, 4) === '$2y$' || substr($hash, 0, 4) === '$2a$') {
            return password_verify($password, $hash);
        }
        
        // Fallback for plain text passwords (for backward compatibility)
        // This should be removed in production
        return $password === $hash;
    }
    
    /**
     * Generate session fingerprint for hijacking prevention
     * @return string Fingerprint hash
     */
    private function generateFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        return md5($userAgent . $ipAddress);
    }
    
    /**
     * Change user password
     * @param int $userId User ID
     * @param string $oldPassword Current password
     * @param string $newPassword New password
     * @return array Result with success status and message
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->db->findById('users', $userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Verify old password
        if (!$this->verifyPassword($oldPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'New password must be at least 6 characters'
            ];
        }
        
        // Update password
        try {
            $this->db->update('users', $userId, [
                'password' => $this->hashPassword($newPassword)
            ]);
            
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to change password'
            ];
        }
    }
    
    /**
     * Get session timeout in seconds
     * @return int Timeout in seconds
     */
    public function getSessionTimeout() {
        return $this->sessionTimeout;
    }
    
    /**
     * Set session timeout
     * @param int $seconds Timeout in seconds
     */
    public function setSessionTimeout($seconds) {
        $this->sessionTimeout = $seconds;
    }
}
?>
