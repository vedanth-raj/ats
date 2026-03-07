# Management Auto Attendance System

A Windows Forms application for managing employee attendance using face recognition technology.

## Features

- 👤 **Employee Management** - Add, edit, and manage employee records
- 📸 **Face Recognition** - Automated attendance tracking using facial recognition
- 📊 **Attendance Reports** - Generate and export attendance reports
- 🎯 **Dataset Creation** - Create training datasets for face recognition
- 🧠 **Training Module** - Train face recognition models for employees
- 🔐 **User Authentication** - Secure login system

## Technology Stack

- **Framework**: .NET Framework 4.8
- **Language**: C# (Windows Forms)
- **Database**: MySQL
- **Face Recognition**: Custom implementation
- **IDE**: Visual Studio 2022

## Prerequisites

- Windows OS
- MySQL Server (9.1 or compatible)
- Visual Studio 2022 (or MSBuild tools)
- .NET Framework 4.8
- Webcam (for face recognition features)

## Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/vedanth-raj/ats.git
cd ats
```

### 2. Setup Database
Run the automated setup script:
```bash
setup-database.bat
```

Or manually import the database:
```bash
mysql -u root -p
CREATE DATABASE management_auto_attendance_system;
USE management_auto_attendance_system;
SOURCE 1-database/management_auto_attendance_system.sql;
```

### 3. Configure Database Connection (if needed)
If your MySQL password is not empty, run:
```bash
configure-database.bat
```

Or manually edit `0-management-auto-attendance-system/Management_Auto_Attendance_System/App.config`

### 4. Run the Application
```bash
RUN_PROJECT.bat
```

## Default Login Credentials

- **Username**: `admin`
- **Password**: `kuna123`

## Project Structure

```
ats/
├── 0-management-auto-attendance-system/
│   └── Management_Auto_Attendance_System/
│       ├── bin/                          # Compiled binaries
│       ├── obj/                          # Build artifacts
│       ├── Properties/                   # Assembly info
│       ├── Resources/                    # Images and resources
│       ├── App.config                    # Configuration file
│       ├── *.cs                          # Source code files
│       ├── *.Designer.cs                 # Form designers
│       └── *.resx                        # Resource files
├── 1-database/
│   └── management_auto_attendance_system.sql  # Database schema
├── RUN_PROJECT.bat                       # Quick run script
├── setup-database.bat                    # Database setup script
├── configure-database.bat                # Configuration helper
├── SETUP_GUIDE.md                        # Detailed setup guide
└── README.md                             # This file
```

## Database Schema

The application uses the following tables:

- **users** - User authentication
- **employees** - Employee information
- **attendance** - Attendance records with face recognition data
- **datasets** - Face recognition training datasets
- **training** - Training history and model information

## Configuration

Database settings are stored in `App.config`:

```xml
<setting name="server" serializeAs="String">
    <value>localhost</value>
</setting>
<setting name="dbname" serializeAs="String">
    <value>management_auto_attendance_system</value>
</setting>
<setting name="sever_username" serializeAs="String">
    <value>root</value>
</setting>
<setting name="server_password" serializeAs="String">
    <value></value>
</setting>
```

## Building from Source

### Using Visual Studio
1. Open `0-management-auto-attendance-system/Management_Auto_Attendance_System.sln`
2. Press `F5` to build and run

### Using MSBuild (Command Line)
```bash
"C:\Program Files\Microsoft Visual Studio\2022\Community\MSBuild\Current\Bin\MSBuild.exe" ^
  "0-management-auto-attendance-system\Management_Auto_Attendance_System.sln" ^
  /p:Configuration=Debug /t:Build
```

## Troubleshooting

### Database Connection Error
- Ensure MySQL Server is running
- Verify username/password in `App.config`
- Check if database exists: `SHOW DATABASES;`

### Build Errors
- Ensure .NET Framework 4.8 SDK is installed
- Restore NuGet packages in Visual Studio
- Check that all COM references are available

### Face Recognition Issues
- Ensure webcam is connected and accessible
- Grant camera permissions to the application
- Check that training datasets are created for employees

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is available for educational and commercial use.

## Support

For issues, questions, or contributions, please open an issue on GitHub.

## Acknowledgments

- Face recognition implementation
- MySQL database integration
- Windows Forms UI framework
