# Complete Setup Guide - Unified Attendance System

## 🚀 Quick Start

### 1. Basic Setup (Required)
```bash
# Install Node.js dependencies
npm install

# Start the server
npm start
```

The application will be available at: **http://localhost:3000**

### 2. Face Recognition Setup (Optional)

#### Option A: Automatic Setup
```bash
python setup_face_recognition.py
```

#### Option B: Manual Setup
```bash
# Install Python dependencies
pip install face_recognition opencv-python numpy Pillow dlib

# Or use requirements file
pip install -r python/requirements.txt
```

## 📋 System Requirements

### Node.js Application
- Node.js 14+ 
- npm or yarn
- 2GB RAM minimum

### Face Recognition (Optional)
- Python 3.7+
- CMake (for dlib compilation)
- Visual Studio Build Tools (Windows)
- 4GB RAM recommended

## 🔧 Configuration

### Environment Variables (.env)
```env
PORT=3000
NODE_ENV=development
FACE_RECOGNITION_ENABLED=true
FACE_RECOGNITION_CONFIDENCE_THRESHOLD=0.6
MAX_FILE_SIZE=10485760
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100
LOG_LEVEL=info
```

## 🎯 Testing the System

### 1. Access Main Dashboard
- Open: http://localhost:3000
- Check system status indicator

### 2. Test Employee Registration
- Click "Register Employee"
- Fill in employee details
- Upload a clear photo (optional)
- Submit form

### 3. Test Attendance Marking
- Click "Mark Attendance" 
- Use manual entry with Employee ID
- Try check-in and check-out

### 4. Test Admin Dashboard
- Access: http://localhost:3000/admin/
- View system statistics
- Check employee and attendance data

### 5. Test API Endpoints
- Access: http://localhost:3000/test.html
- Test all API endpoints
- Verify responses

## 🐛 Troubleshooting

### Server Won't Start
```bash
# Check if port 3000 is in use
netstat -ano | findstr :3000

# Kill existing processes
taskkill /F /PID <process_id>

# Restart server
npm start
```

### Face Recognition Issues
```bash
# Test Python installation
python -c "import face_recognition; print('OK')"

# Reinstall dependencies
pip uninstall face_recognition opencv-python
pip install face_recognition opencv-python

# Check CMake installation (Windows)
cmake --version
```

### Database/File Issues
```bash
# Check data directories exist
ls -la data/
ls -la data/employees/

# Reset data (if needed)
rm -rf data/
mkdir -p data/employees
echo "[]" > data/employees/employees.json
echo "[]" > data/attendance.json
```

## 📁 Project Structure

```
unified-attendance-interface/
├── server.js              # Main server file
├── package.json           # Node.js dependencies
├── .env                   # Environment configuration
├── public/                # Frontend files
│   ├── index.html         # Main dashboard
│   ├── employee-registration.html
│   ├── attendance.html
│   └── admin/index.html   # Admin dashboard
├── services/              # Backend services
│   ├── face-recognition.js
│   ├── employee-service.js
│   ├── attendance-service.js
│   └── system-monitor.js
├── routes/                # API routes
│   └── admin.js
├── python/                # Face recognition engine
│   ├── face_recognition_engine.py
│   └── requirements.txt
├── data/                  # Data storage
│   ├── employees/
│   └── attendance.json
└── logs/                  # System logs
```

## 🔐 Security Notes

- Admin routes use simple token authentication (demo only)
- In production, implement proper JWT authentication
- Configure HTTPS and secure headers
- Set up proper database instead of JSON files
- Enable rate limiting and input validation

## 📞 Support

If you encounter issues:

1. Check the console logs in the browser
2. Check server logs in the terminal
3. Verify all dependencies are installed
4. Test with the `/test.html` page
5. Check the troubleshooting section above

## 🎉 Success Indicators

✅ Server starts without errors
✅ Main dashboard loads at http://localhost:3000
✅ Employee registration works
✅ Attendance marking works
✅ Admin dashboard shows data
✅ API test page shows green responses
✅ Face recognition status shows "initialized" (if Python setup)