# Face Recognition Attendance System - Complete Guide

## 🎯 Overview

This system uses **face recognition technology** to automatically mark attendance for employees/students. It works in 3 main phases:

1. **Dataset Creation** - Capture face images
2. **Model Training** - Train AI model to recognize faces
3. **Attendance Marking** - Automatic attendance via face recognition

---

## 📋 How It Works

### Phase 1: Dataset Creation (One-time setup per employee)

**Purpose**: Capture multiple photos of each employee's face to train the AI model.

**Process**:
1. Admin adds employee details in the system
2. Run the dataset creator script for that employee
3. Employee stands in front of webcam
4. System captures 15-20 photos of their face from different angles
5. Photos are saved in `Datasets/[Employee_ID]/` folder

**Command**:
```bash
python dataset-creator.py -e [EMPLOYEE_ID] -n 15
```

**Example**:
```bash
python dataset-creator.py -e 0001 -n 15
```

**What happens**:
- Webcam opens
- Press **Spacebar** to capture each photo
- System says "Ok, Next" after each successful capture
- After 15 photos, system says "Thank you, image capturing is done"
- Press **Escape** to cancel anytime

---

### Phase 2: Model Training (One-time per employee)

**Purpose**: Train the AI to recognize the employee's face from the captured images.

**Process**:
1. System reads all photos from the employee's dataset folder
2. Extracts facial features (encodings) from each photo
3. Creates a trained model file (.pickle)
4. Generates a QR code for the employee

**Command**:
```bash
python training.py -e [EMPLOYEE_ID]
```

**Example**:
```bash
python training.py -e 0001
```

**Output**:
- Trained model: `Trained_Models/0001/0001_(Model).pickle`
- QR Code: `Trained_Models/0001/0001_(QRCode).jpg`

---

### Phase 3: Attendance Marking (Daily use)

**Purpose**: Employees mark attendance by showing QR code and face verification.

**Process**:

#### Step 1: QR Code Scan
1. Employee shows their QR code to the webcam
2. System reads the QR code and identifies the employee
3. System greets: "Good Morning [Name]"

#### Step 2: Face Verification (Liveness Detection)
1. System asks: "Please blink your eyes to confirm your identity"
2. Employee blinks twice
3. System captures face image and compares with trained model
4. If match found: Attendance marked ✅
5. If no match: "Unauthorized face, access denied" ❌

#### Step 3: Attendance Recorded
- **Check-In**: Records entry time
- **Check-Out**: Records exit time

**Command**:
```bash
python run.py
```

**Keyboard Controls**:
- **Spacebar**: Restart the process
- **Enter**: Mark attendance as pending (for manual review)
- **Escape**: Exit the system

---

## 🔐 Security Features

### 1. **Liveness Detection (Blink Detection)**
- Prevents photo/video spoofing
- Requires real person to blink eyes
- Uses eye aspect ratio (EAR) algorithm

### 2. **QR Code Authentication**
- Each employee has unique QR code
- QR code must be scanned first
- Prevents unauthorized access

### 3. **Face Matching**
- Compares live face with trained model
- Uses face_recognition library (dlib + HOG)
- Tolerance: 0.5 (adjustable)

### 4. **Dual Verification**
- QR Code (What you have)
- Face Recognition (Who you are)

---

## 📊 Database Structure

### Attendance Table
```sql
- auto_id: Unique ID
- employee_id: Employee identifier
- in_time: Check-in time
- out_time: Check-out time
- _date: Date (dd-mm-yyyy)
- face_recognition_entering: True/False
- face_recognition_exiting: True/False
- face_recognition_entering_img_path: Image path (if pending)
- face_recognition_exiting_img_path: Image path (if pending)
```

---

## 🎓 For Students/Employees

### How to Give Attendance:

1. **Get Your QR Code**
   - Admin will provide your unique QR code
   - Print it or save on your phone

2. **Approach the Attendance System**
   - Stand in front of the webcam
   - Hold your QR code visible to camera

3. **Wait for Greeting**
   - System will say: "Good Morning [Your Name]"

4. **Blink Your Eyes**
   - System will ask you to blink
   - Blink your eyes twice naturally
   - Look directly at the camera

5. **Attendance Marked**
   - System will say: "Your attendance has been marked"
   - You're done! ✅

### Tips for Best Results:
- ✅ Good lighting
- ✅ Look directly at camera
- ✅ Remove glasses (if possible)
- ✅ Keep face clearly visible
- ✅ Don't cover face with mask/scarf
- ✅ Stand at arm's length from camera

---

## 🛠️ Technical Requirements

### Hardware:
- Webcam (720p or higher recommended)
- Computer with Windows OS
- Good lighting in the room

### Software:
- Python 3.7+
- OpenCV
- face_recognition
- dlib
- imutils
- pyzbar (for QR code)
- pyttsx3 (text-to-speech)
- MySQL database

### Installation:
```bash
pip install opencv-python
pip install face-recognition
pip install dlib
pip install imutils
pip install pyzbar
pip install pyttsx3
pip install mysql-connector-python
pip install playsound
```

Or use the provided environment file:
```bash
conda env create -f environment.yml
conda activate attendance-system
```

---

## 🎯 Workflow Diagram

```
┌─────────────────────────────────────────────────────────┐
│                    ADMIN SETUP                          │
│  1. Add Employee → 2. Create Dataset → 3. Train Model  │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                 DAILY ATTENDANCE                        │
│                                                         │
│  Employee Shows QR Code                                 │
│         ↓                                               │
│  System Identifies Employee                             │
│         ↓                                               │
│  System Asks to Blink                                   │
│         ↓                                               │
│  Employee Blinks Twice                                  │
│         ↓                                               │
│  System Captures Face                                   │
│         ↓                                               │
│  Face Matching with Trained Model                       │
│         ↓                                               │
│  ✅ Match Found → Attendance Marked                     │
│  ❌ No Match → Access Denied                            │
└─────────────────────────────────────────────────────────┘
```

---

## 🔧 Configuration

### Face Recognition Settings
File: `System_Settings/Face_rec.txt`
- `True`: Allow manual attendance (press Enter)
- `False`: Strict face recognition only

### Database Configuration
File: `run.py` (lines 24-30)
```python
mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    passwd="",  # Your MySQL password
    database="management_auto_attendance_system"
)
```

---

## 📱 Voice Feedback

The system provides voice feedback for better user experience:

- **Greetings**: "Good Morning/Afternoon/Evening [Name]"
- **Instructions**: "Please blink your eyes to confirm your identity"
- **Success**: "Your attendance has been marked"
- **Errors**: "Unauthorized face, access denied"
- **Encouragement**: "You look smart today", "Have a nice day"

---

## 🚨 Troubleshooting

### Problem: Face not detected
**Solution**: 
- Ensure good lighting
- Move closer to camera
- Remove obstructions (glasses, mask)

### Problem: QR code not scanning
**Solution**:
- Hold QR code steady
- Ensure QR code is clear and not damaged
- Adjust distance from camera

### Problem: Blink not detected
**Solution**:
- Blink naturally (not too fast)
- Look directly at camera
- Ensure eyes are clearly visible

### Problem: Face not matching
**Solution**:
- Retrain the model with more photos
- Ensure dataset photos are clear
- Check if lighting conditions match

---

## 📈 Admin Features

### View Attendance Reports
- Daily attendance summary
- Employee-wise reports
- Export to Excel
- Filter by date range

### Manage Employees
- Add new employees
- Edit employee details
- Delete employees
- View dataset status
- View training status

### Pending Approvals
- Review manually marked attendance
- Approve/reject based on captured image
- Useful when face recognition fails

---

## 🎓 Use Cases

### 1. **Schools/Colleges**
- Student attendance in classrooms
- Lab attendance
- Library entry/exit
- Exam hall attendance

### 2. **Offices**
- Employee check-in/check-out
- Meeting room attendance
- Shift management
- Overtime tracking

### 3. **Events**
- Conference attendance
- Workshop participation
- Seminar tracking

---

## 🔒 Privacy & Security

- Face data is stored locally
- Encrypted model files
- No cloud storage
- GDPR compliant (with proper consent)
- Data can be deleted anytime

---

## 📞 Support

For issues or questions:
1. Check this guide
2. Review the setup documentation
3. Check system logs
4. Contact system administrator

---

**System Version**: 1.0.0  
**Last Updated**: March 2026  
**Technology**: Python + OpenCV + face_recognition + MySQL
