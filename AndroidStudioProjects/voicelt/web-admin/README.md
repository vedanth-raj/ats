# Campus Voting System - Web Admin Panel

This is the PHP + MySQL admin panel for the Campus Voting System.

## Setup Instructions

1. **Install XAMPP or similar PHP server** with MySQL support.

2. **Create MySQL Database:**
   - Open phpMyAdmin (usually at http://localhost/phpmyadmin)
   - Create a new database named `campus_voting`
   - Run the following SQL to create tables:

```sql
CREATE TABLE nominees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sap_id VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    manifesto TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. **Configure Firebase:**
   - Create a Firebase project at https://console.firebase.google.com/
   - Enable Realtime Database
   - Enable Authentication with Phone provider
   - Copy your project ID and update `config.php`:
     ```php
     $firebaseProjectId = "your-actual-project-id";
     ```

4. **Update Admin Credentials:**
   - In `config.php`, change the admin username and password:
     ```php
     $adminUsername = "your_admin_username";
     $adminPassword = "your_secure_password";
     ```

5. **Run the Application:**
   - Place the `web-admin` folder in your web server's root directory (e.g., `htdocs` for XAMPP)
   - Access the admin panel at `http://localhost/web-admin/login.php`

## Features

- **Admin Login:** Secure login with username/password
- **Add Nominee:** Form to add nominees, stores in MySQL and pushes to Firebase
- **View Nominees:** Display all nominees from MySQL
- **View Results:** Fetch and display live voting results from Firebase

## Security Notes

- In production, use hashed passwords for admin credentials
- Implement proper input validation and sanitization
- Use HTTPS for secure communication
- Regularly update dependencies

## Troubleshooting

### MySQL Connection Error
If you get "Access denied for user 'root'@'localhost'", try these solutions:

1. **Check MySQL is running:** Start MySQL service in XAMPP control panel
2. **Default XAMPP setup:** Usually no password for root user
3. **If password is set:** Update `$password` in `config.php`
4. **Alternative credentials:** Create a new MySQL user with proper permissions

### Database Not Found
- Create the `campus_voting` database in phpMyAdmin
- Ensure the database name matches exactly in `config.php`

### Firebase Connection Issues
- Verify your Firebase project ID is correct
- Check that Realtime Database is enabled
- Ensure security rules allow access (see firebase_setup.md)
