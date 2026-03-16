# Face Recognition Setup Guide

## Current Status: ⚠️ CMake Required

The face recognition setup requires CMake to compile the `dlib` library. Here are your options:

## Option 1: Install CMake (Recommended)

### Step 1: Download and Install CMake
1. Go to https://cmake.org/download/
2. Download "Windows x64 Installer" (cmake-3.x.x-windows-x86_64.msi)
3. Run the installer
4. **IMPORTANT**: Check "Add CMake to system PATH" during installation

### Step 2: Restart Command Prompt
Close and reopen your command prompt/PowerShell to refresh the PATH.

### Step 3: Verify CMake Installation
```bash
cmake --version
```

### Step 4: Install Face Recognition
```bash
cd unified-attendance-interface
python -m pip install dlib
python -m pip install face_recognition opencv-python numpy Pillow
```

### Step 5: Test Installation
```bash
python -c "import face_recognition; print('Face recognition ready!')"
```

### Step 6: Restart Server
```bash
npm start
```

## Option 2: Use Pre-compiled dlib (Alternative)

If CMake installation fails, try installing a pre-compiled version:

```bash
python -m pip install dlib-binary
python -m pip install face_recognition opencv-python numpy Pillow
```

## Option 3: Continue Without Face Recognition

The system works perfectly without face recognition using manual employee ID entry:

✅ **Currently Working:**
- Employee registration and management
- Manual attendance (check-in/check-out by Employee ID)
- Admin dashboard and reporting
- All core functionality

❌ **Not Available Without Face Recognition:**
- Automatic face detection for attendance
- Photo-based employee verification

## Verification Steps

After installing CMake and face recognition libraries:

1. **Test Python Import:**
   ```bash
   python -c "import face_recognition; print('SUCCESS: Face recognition ready!')"
   ```

2. **Check Server Status:**
   - Restart the Node.js server
   - Check http://localhost:3000/api/face/status
   - Should show `"initialized": true`

3. **Test Face Registration:**
   - Go to employee registration
   - Upload a clear photo
   - System should process the face

## Troubleshooting

### CMake Issues
- Make sure to add CMake to PATH during installation
- Restart command prompt after installation
- Try running `cmake --version` to verify

### dlib Compilation Issues
- Ensure you have Visual Studio Build Tools installed
- Try the pre-compiled dlib-binary package
- Consider using Python 3.9-3.11 (better compatibility)

### Face Recognition Issues
- Use clear, well-lit photos
- Ensure only one face per image
- Photos should be at least 200x200 pixels

## Current System Status

Your attendance system is **fully functional** for core operations. Face recognition is an **optional enhancement** that can be added when ready.

**Next Steps:**
1. Install CMake from cmake.org
2. Run the face recognition setup
3. Or continue using the system with manual ID entry