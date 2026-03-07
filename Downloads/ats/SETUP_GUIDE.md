# Management Auto Attendance System - Setup Guide

## Quick Start

This guide will help you set up and run the Management Auto Attendance System on your local machine.

## Prerequisites

✅ Windows OS
✅ MySQL Server 9.1 (Already installed)
✅ Visual Studio 2022 (Already installed)
✅ .NET Framework 4.8

## Step 1: Database Setup

### Option A: Automatic Setup (Recommended)
Run the setup script:
```bash
.\setup-database.bat
```

### Option B: Manual Setup
1. Open MySQL Command Line or MySQL Workbench
2. Run the following commands:
```sql
CREATE DATABASE IF NOT EXISTS management_auto_attendance_system;
USE management_auto_attendance_system;
SOURCE 1-database/management_auto_attendance_system.sql;
```

## Step 2: Configure Database Connection

The application is pre-configured with these default settings:
- **Server**: localhost
- **Database**: management_auto_attendance_system
- **Username**: root
- **Password**: (empty)

If your MySQL has a different password, edit the file:
`0-management-auto-attendance-system/Management_Auto_Attendance_System/App.config`

Change this line:
```xml
<setting name="server_password" serializeAs="String">
    <value></value>  <!-- Add your MySQL password here -->
</setting>
```

## Step 3: Build and Run

### Option A: Quick Run (Recommended)
Double-click: `RUN_PROJECT.bat`

### Option B: Manual Run
1. Open `0-management-auto-attendance-system/Management_Auto_Attendance_System.sln` in Visual Studio
2. Press F5 to run

## Default Login Credentials

- **Username**: admin
- **Password**: kuna123

## Features

- Employee Management
- Face Recognition for Attendance
- Attendance Reports
- Dataset Creation for Face Recognition
- Training Module

## Troubleshooting

### Database Connection Error
- Ensure MySQL Server is running
- Check username/password in App.config
- Verify database exists: `SHOW DATABASES;`

### Build Errors
- Ensure .NET Framework 4.8 is installed
- Restore NuGet packages in Visual Studio

### Face Recognition Issues
- Ensure webcam is connected and accessible
- Grant camera permissions to the application

## Project Structure

```
├── 0-management-auto-attendance-system/
│   └── Management_Auto_Attendance_System/
│       ├── bin/Debug/                    # Compiled application
│       ├── App.config                    # Configuration file
│       └── *.cs                          # Source code files
├── 1-database/
│   └── management_auto_attendance_system.sql  # Database schema
├── RUN_PROJECT.bat                       # Quick run script
├── setup-database.bat                    # Database setup script
└── SETUP_GUIDE.md                        # This file
```

## Support

For issues or questions, check the configuration files or rebuild the project in Visual Studio.
