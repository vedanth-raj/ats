# Management Auto Attendance System - Web Application

## ✅ Installation Complete!

Your web-based attendance system is now running on your local server.

## Access the Application

**URL**: http://localhost/attendance-system

**Default Login Credentials**:
- Username: `admin`
- Password: `kuna123`

## Quick Start

### Option 1: Use the Launcher
Double-click: `OPEN_WEB_APP.bat`

### Option 2: Manual Access
1. Make sure XAMPP Apache and MySQL are running
2. Open your browser
3. Go to: http://localhost/attendance-system

## Features Available

### 📊 Dashboard
- View total employees
- Today's attendance count
- Dataset and training statistics
- Recent attendance records

### 👥 Employee Management
- View all employees
- Add new employees
- Edit employee details
- Delete employees
- Track dataset and model status

### 📋 Attendance Records
- View all attendance records
- Filter by date
- Filter by employee ID
- See face recognition verification status
- Track in/out times

### 📈 Reports
- Daily reports
- Weekly reports
- Monthly reports
- Export to Excel (coming soon)

### 💾 Datasets
- View face recognition datasets
- Track number of images per employee
- See creation dates

### 🧠 Training
- View model training history
- Track trained images count
- Monitor training dates

### ⚙️ Settings
- Database configuration
- System information
- PHP version details

## File Locations

- **Web Application**: `C:\xampp\htdocs\attendance-system\`
- **Source Files**: `web-app\` (in your project folder)
- **Database**: MySQL (management_auto_attendance_system)

## Configuration

Database settings are in: `C:\xampp\htdocs\attendance-system\config\database.php`

Current configuration:
```php
DB_HOST: localhost
DB_USER: root
DB_PASS: my_sql
DB_NAME: management_auto_attendance_system
```

## Troubleshooting

### Can't Access the Website?
1. Check if Apache is running in XAMPP Control Panel
2. Check if MySQL is running in XAMPP Control Panel
3. Try: http://127.0.0.1/attendance-system

### Database Connection Error?
1. Verify MySQL is running
2. Check database credentials in `config/database.php`
3. Ensure database exists: `management_auto_attendance_system`

### Login Not Working?
- Default credentials: admin / kuna123
- Check the `users` table in the database

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Server**: Apache (XAMPP)
- **Icons**: Font Awesome 6.0

## Browser Compatibility

- ✅ Chrome (Recommended)
- ✅ Firefox
- ✅ Edge
- ✅ Safari
- ✅ Opera

## Mobile Responsive

The interface is fully responsive and works on:
- 📱 Mobile phones
- 📱 Tablets
- 💻 Laptops
- 🖥️ Desktops

## Next Steps

1. **Customize**: Edit the files in `C:\xampp\htdocs\attendance-system\`
2. **Add Features**: Extend functionality as needed
3. **Secure**: Change default password in production
4. **Backup**: Regularly backup your database

## Support

For issues or questions:
1. Check the main README.md
2. Review SETUP_GUIDE.md
3. Check XAMPP error logs: `C:\xampp\apache\logs\error.log`

## Updates

To update the web application:
1. Make changes in the `web-app\` folder
2. Run `SETUP_WEB_APP.bat` again to copy files

---

**Enjoy your web-based attendance management system!** 🎉
