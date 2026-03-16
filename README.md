# Unified Attendance Interface

A modern web-based attendance system with face recognition capabilities.

## Features

- Face recognition-based attendance
- Employee management
- Real-time attendance tracking
- Admin dashboard
- System monitoring and health checks
- Secure API with rate limiting
- WebSocket support for real-time updates

## Installation

### 1. Install Node.js Dependencies
```bash
npm install
```

### 2. Set up Environment Variables
Copy `.env.example` to `.env` and configure as needed:
```bash
cp .env.example .env
```

### 3. Set up Face Recognition (Optional but Recommended)
For full face recognition functionality, install Python dependencies:

**Option A: Automatic Setup**
```bash
python setup_face_recognition.py
```

**Option B: Manual Setup**
```bash
# Install Python dependencies
pip install face_recognition opencv-python numpy Pillow

# Or install from requirements file
pip install -r python/requirements.txt
```

**Note:** Face recognition requires:
- Python 3.7+
- CMake (for dlib compilation)
- Visual Studio Build Tools (on Windows)

### 4. Start the Server
```bash
npm start
```

The application will be available at `http://localhost:3000`

## Quick Start

1. **Access the Application**: Open `http://localhost:3000` in your browser
2. **Register Employees**: Click "Register Employee" to add new employees
3. **Mark Attendance**: Use "Mark Attendance" for check-in/check-out
4. **Admin Dashboard**: Access admin features at `/admin/`
5. **Test APIs**: Use `/test.html` to test API endpoints

## Face Recognition Setup

The system works in two modes:

### Production Mode (Recommended)
- Requires Python face_recognition library
- Provides real face recognition capabilities
- More secure and accurate

### Fallback Mode
- Works without Python dependencies
- Manual employee ID entry only
- Suitable for testing or when face recognition isn't needed

To enable face recognition:
1. Run `python setup_face_recognition.py`
2. Restart the Node.js server
3. Register employees with photos
4. Use face recognition for attendance

## Development

Run in development mode with auto-reload:
```bash
npm run dev
```

## Testing

Run tests:
```bash
npm test
```

## API Endpoints

### Employees
- `GET /api/employees` - Get all employees
- `POST /api/employees` - Create new employee
- `PUT /api/employees/:id` - Update employee
- `DELETE /api/employees/:id` - Delete employee

### Face Recognition
- `POST /api/face/register` - Register employee face
- `POST /api/face/verify` - Verify employee face
- `GET /api/face/status` - Get face recognition system status

### Attendance
- `GET /api/attendance` - Get attendance records
- `POST /api/attendance/checkin` - Check in employee
- `POST /api/attendance/checkout` - Check out employee
- `GET /api/attendance/report` - Generate attendance report

### System
- `GET /api/system/status` - Get system status
- `GET /api/system/health` - Health check

## License

MIT