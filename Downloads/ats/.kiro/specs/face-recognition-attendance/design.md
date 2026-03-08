# Design Document: Face Recognition Attendance System

## Overview

The Face Recognition Attendance System is a browser-based application that automates employee attendance tracking using facial recognition technology. The system runs entirely in modern web browsers (Chrome/Edge 90+) using a PHP backend with Apache server and a JavaScript frontend leveraging TensorFlow.js or face-api.js for client-side face detection and recognition.

### Key Design Goals

1. **Performance**: Page loads under 2 seconds, real-time face detection at 10+ FPS
2. **Reliability**: JSON file-based storage to eliminate MySQL timeout issues
3. **Security**: Multi-factor verification (face recognition + optional QR code)
4. **Usability**: Simple interface for both employees and administrators
5. **Accuracy**: Face recognition with 0.75+ confidence threshold

### Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Face Recognition**: TensorFlow.js with face-api.js library
- **Backend**: PHP 7.4+ running on Apache (XAMPP)
- **Database**: JSON file storage (no MySQL)
- **Media**: Browser MediaDevices API for webcam access
- **Platform**: Windows with XAMPP server

### System Architecture Pattern

The system follows a **three-phase workflow**:

1. **Dataset Creation Phase**: Capture 50 face images per employee
2. **Model Training Phase**: Generate face embeddings and train recognition models
3. **Attendance Phase**: Real-time face detection, recognition, and automatic attendance marking

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Browser (Client)                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   HTML/CSS   │  │  JavaScript  │  │  TensorFlow  │     │
│  │   UI Layer   │  │  Controller  │  │  face-api.js │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│         │                  │                  │             │
│         └──────────────────┴──────────────────┘             │
│                            │                                │
│                    MediaDevices API                         │
│                       (Webcam)                              │
└─────────────────────────────┬───────────────────────────────┘
                              │ HTTP/AJAX
                              │
┌─────────────────────────────┴───────────────────────────────┐
│                   Apache Server (XAMPP)                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  PHP Pages   │  │  JSON DB     │  │  File System │     │
│  │  (Backend)   │  │  Handler     │  │  (datasets/  │     │
│  │              │  │              │  │   models/)   │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

### Component Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  UI Components (HTML/CSS)                            │  │
│  │  - Login Page                                        │  │
│  │  - Dashboard                                         │  │
│  │  - Employee Management                               │  │
│  │  - Dataset Creator Interface                         │  │
│  │  - Model Training Interface                          │  │
│  │  - Attendance Marking Interface                      │  │
│  │  - Reports Interface                                 │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────┴───────────────────────────────┐
│                   Application Layer                         │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  JavaScript Controllers                              │  │
│  │  - WebcamController: Manages camera access          │  │
│  │  - FaceDetectionController: Detects faces in frames │  │
│  │  - FaceRecognitionController: Identifies employees  │  │
│  │  - DatasetController: Captures training images      │  │
│  │  - TrainingController: Trains face models           │  │
│  │  - AttendanceController: Marks attendance           │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────┴───────────────────────────────┐
│                    Business Logic Layer                     │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  PHP Backend Services                                │  │
│  │  - EmployeeService: CRUD operations                 │  │
│  │  - AttendanceService: Attendance logic              │  │
│  │  - AuthenticationService: Session management        │  │
│  │  - ReportService: Generate reports                  │  │
│  │  - ValidationService: Input validation              │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────┴───────────────────────────────┐
│                     Data Access Layer                       │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  JsonDB Class                                        │  │
│  │  - query(table): Read records                       │  │
│  │  - insert(table, data): Create record               │  │
│  │  - update(table, id, data): Update record           │  │
│  │  - delete(table, id): Delete record                 │  │
│  │  - count(table, where): Count records               │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────┴───────────────────────────────┐
│                      Storage Layer                          │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  File System                                         │  │
│  │  - data/*.json: Database files                      │  │
│  │  - datasets/{employee_id}/*.jpg: Training images    │  │
│  │  - models/{employee_id}/model.json: Trained models  │  │
│  │  - data/*.backup.json: Backup files                 │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Face Recognition Pipeline

```
┌─────────────────────────────────────────────────────────────┐
│                  Attendance Marking Flow                    │
└─────────────────────────────────────────────────────────────┘

1. Camera Activation
   ↓
   navigator.mediaDevices.getUserMedia({video: true})
   ↓
2. Video Stream Display
   ↓
   <video> element with autoplay
   ↓
3. Face Detection Loop (10+ FPS)
   ↓
   face-api.js detectSingleFace()
   ↓
4. Face Detected?
   ├─ No → Display "Position your face" message
   └─ Yes → Continue
       ↓
5. Extract Face Descriptors
   ↓
   face-api.js computeFaceDescriptor()
   ↓
6. Load All Employee Models
   ↓
   Fetch models/*.json from server
   ↓
7. Compare Descriptors
   ↓
   euclideanDistance(detected, stored) < threshold
   ↓
8. Match Found (confidence > 0.75)?
   ├─ No → Display "Face not recognized"
   └─ Yes → Continue
       ↓
9. Check Duplicate Attendance
   ↓
   Query attendance.json for today's date + employee_id
   ↓
10. Already Marked?
    ├─ Yes → Display "Already marked today"
    └─ No → Continue
        ↓
11. Create Attendance Record
    ↓
    POST to process-attendance.php
    ↓
12. Save to JSON Database
    ↓
    attendance.json updated
    ↓
13. Display Success Message
    ↓
    "✅ Attendance marked for [Name] at [Time]"
```

### Dataset Creation and Training Flow

```
┌─────────────────────────────────────────────────────────────┐
│              Dataset Creation Flow                          │
└─────────────────────────────────────────────────────────────┘

1. Admin Selects Employee
   ↓
2. Activate Webcam
   ↓
3. Start Capture Loop
   ↓
4. For i = 1 to 50:
   ├─ Detect face in frame
   ├─ If face detected:
   │  ├─ Crop face region (160x160)
   │  ├─ Save as datasets/{employee_id}/image_{i}.jpg
   │  └─ Update progress: {i}/50
   └─ Wait 200ms
   ↓
5. Update employees.json
   ├─ is_dataset_available = 'True'
   └─ dataset_image_count = 50
   ↓
6. Display "Dataset Complete"

┌─────────────────────────────────────────────────────────────┐
│                 Model Training Flow                         │
└─────────────────────────────────────────────────────────────┘

1. Admin Selects Employee(s)
   ↓
2. Load Dataset Images
   ↓
   Read all datasets/{employee_id}/*.jpg
   ↓
3. For Each Image:
   ├─ Load image into canvas
   ├─ Detect face
   ├─ Extract 128-dimensional descriptor
   └─ Store in descriptors array
   ↓
4. Compute Average Descriptor
   ↓
   avgDescriptor = mean(descriptors)
   ↓
5. Create Model Object
   ↓
   {
     employee_id: "0001",
     descriptor: [128 floats],
     training_date: "2024-01-15",
     image_count: 50
   }
   ↓
6. Save Model
   ↓
   models/{employee_id}/model.json
   ↓
7. Update employees.json
   ├─ is_model_available = 'True'
   └─ model_training_date = current_date
   ↓
8. Display "Training Complete"
```

## Components and Interfaces

### Frontend Components

#### 1. WebcamController

**Purpose**: Manages webcam access and video stream lifecycle

**Interface**:
```javascript
class WebcamController {
  constructor(videoElement)
  
  async startCamera(constraints = {video: true})
  // Returns: Promise<MediaStream>
  // Throws: NotAllowedError, NotFoundError
  
  stopCamera()
  // Stops all tracks and releases camera
  
  captureFrame()
  // Returns: ImageData from current video frame
  
  isActive()
  // Returns: boolean
  
  getDevices()
  // Returns: Promise<MediaDeviceInfo[]>
  // Lists available cameras
  
  switchCamera(deviceId)
  // Switches to specified camera device
}
```

**Key Behaviors**:
- Requests camera permissions on first use
- Handles permission denial gracefully
- Releases camera when page unloads
- Supports multiple camera selection

#### 2. FaceDetectionController

**Purpose**: Detects faces in video frames using face-api.js

**Interface**:
```javascript
class FaceDetectionController {
  constructor(modelPath = '/models/face-api')
  
  async loadModels()
  // Loads face-api.js models (SSD MobileNet)
  // Returns: Promise<void>
  
  async detectFace(imageData)
  // Returns: Promise<Detection | null>
  // Detection: {box: {x, y, width, height}, score: float}
  
  async detectFaceWithLandmarks(imageData)
  // Returns: Promise<DetectionWithLandmarks | null>
  
  startDetectionLoop(videoElement, callback, fps = 10)
  // Continuously detects faces at specified FPS
  // callback(detection): called for each frame
  
  stopDetectionLoop()
  
  drawDetection(canvas, detection)
  // Draws bounding box on canvas
}
```

**Key Behaviors**:
- Preloads models on page load for performance
- Runs detection at 10+ FPS
- Returns null if no face detected
- Provides bounding box coordinates

#### 3. FaceRecognitionController

**Purpose**: Identifies employees by comparing face descriptors

**Interface**:
```javascript
class FaceRecognitionController {
  constructor(threshold = 0.75)
  
  async loadModels()
  // Loads face-api.js recognition models
  
  async loadEmployeeModels()
  // Fetches all models/*.json from server
  // Returns: Promise<Map<employeeId, descriptor>>
  
  async extractDescriptor(imageData)
  // Returns: Promise<Float32Array> (128 dimensions)
  
  recognizeFace(descriptor)
  // Returns: {employeeId: string, confidence: float} | null
  // Compares with all loaded employee models
  
  computeDistance(desc1, desc2)
  // Returns: float (Euclidean distance)
  
  setThreshold(threshold)
  // Updates recognition confidence threshold
}
```

**Key Behaviors**:
- Loads all employee models into memory on initialization
- Uses Euclidean distance for descriptor comparison
- Returns match only if confidence > threshold
- Handles missing or corrupted models gracefully

#### 4. DatasetController

**Purpose**: Captures training images for employees

**Interface**:
```javascript
class DatasetController {
  constructor(employeeId, targetCount = 50)
  
  async startCapture(videoElement, progressCallback)
  // Captures images at 200ms intervals
  // progressCallback(current, total): called after each capture
  
  stopCapture()
  
  async captureImage(videoElement)
  // Returns: Promise<Blob> (JPEG image)
  
  async uploadImage(imageBlob, imageNumber)
  // Uploads to server: datasets/{employeeId}/image_{n}.jpg
  // Returns: Promise<boolean>
  
  async finalizeDataset()
  // Updates employee record in database
  // Returns: Promise<void>
}
```

**Key Behaviors**:
- Detects face before capturing each image
- Skips frames without detected faces
- Resizes images to 160x160 pixels
- Shows progress indicator

#### 5. TrainingController

**Purpose**: Trains face recognition models from datasets

**Interface**:
```javascript
class TrainingController {
  constructor()
  
  async trainEmployee(employeeId, progressCallback)
  // Trains model for single employee
  // progressCallback(percent): called during training
  // Returns: Promise<{success: boolean, message: string}>
  
  async trainMultiple(employeeIds, progressCallback)
  // Trains models for multiple employees sequentially
  
  async loadDatasetImages(employeeId)
  // Returns: Promise<HTMLImageElement[]>
  
  async extractDescriptors(images)
  // Returns: Promise<Float32Array[]>
  
  computeAverageDescriptor(descriptors)
  // Returns: Float32Array
  
  async saveModel(employeeId, descriptor)
  // Saves to models/{employeeId}/model.json
  // Returns: Promise<boolean>
}
```

**Key Behaviors**:
- Validates minimum image count (30 images)
- Computes average descriptor from all training images
- Saves model in JSON format for browser loading
- Updates employee record with training metadata

#### 6. AttendanceController

**Purpose**: Manages attendance marking logic

**Interface**:
```javascript
class AttendanceController {
  constructor()
  
  async markAttendance(employeeId)
  // Returns: Promise<{success: boolean, message: string}>
  
  async checkDuplicate(employeeId, date)
  // Returns: Promise<boolean>
  
  async getTodayAttendance()
  // Returns: Promise<AttendanceRecord[]>
  
  formatTime(date)
  // Returns: string "hh:mm:ss AM/PM"
  
  formatDate(date)
  // Returns: string "dd-mm-yyyy"
}
```

**Key Behaviors**:
- Checks for duplicate attendance before marking
- Records timestamp in specified format
- Validates employee has trained model
- Provides user-friendly error messages

### Backend Components (PHP)

#### 1. JsonDB Class

**Purpose**: Provides database operations on JSON files

**Interface**:
```php
class JsonDB {
  public function __construct($dataDir = 'data/')
  
  public function query($table)
  // Returns: array of records
  
  public function insert($table, $data)
  // Returns: int (auto_id of inserted record)
  
  public function update($table, $id, $data)
  // Returns: bool (success)
  
  public function delete($table, $id)
  // Returns: bool (success)
  
  public function count($table, $where = [])
  // Returns: int
  
  private function save($table, $data)
  // Writes data to JSON file with backup
  
  private function backup($table)
  // Creates .backup.json file
  
  private function restore($table)
  // Restores from backup if main file corrupted
}
```

**Key Behaviors**:
- Creates backup before each write
- Uses file locking to prevent concurrent writes
- Validates JSON structure before saving
- Auto-restores from backup on corruption

#### 2. EmployeeService

**Purpose**: Handles employee management operations

**Interface**:
```php
class EmployeeService {
  private $db;
  
  public function __construct(JsonDB $db)
  
  public function getAllEmployees()
  // Returns: array
  
  public function getEmployee($employeeId)
  // Returns: array | null
  
  public function createEmployee($data)
  // Returns: array {success: bool, message: string, id: int}
  
  public function updateEmployee($id, $data)
  // Returns: array {success: bool, message: string}
  
  public function deleteEmployee($id)
  // Returns: array {success: bool, message: string}
  // Also deletes dataset and model files
  
  public function validateEmployeeData($data)
  // Returns: array {valid: bool, errors: array}
  
  public function isEmployeeIdUnique($employeeId)
  // Returns: bool
}
```

**Key Behaviors**:
- Validates all input data
- Ensures employee_id uniqueness
- Cascades delete to datasets and models
- Returns structured response arrays

#### 3. AttendanceService

**Purpose**: Manages attendance records and business rules

**Interface**:
```php
class AttendanceService {
  private $db;
  
  public function __construct(JsonDB $db)
  
  public function markAttendance($employeeId)
  // Returns: array {success: bool, message: string}
  
  public function checkDuplicateToday($employeeId)
  // Returns: bool
  
  public function getTodayAttendance()
  // Returns: array
  
  public function getAttendanceByDate($date)
  // Returns: array
  
  public function getAttendanceByDateRange($startDate, $endDate)
  // Returns: array
  
  public function getEmployeeAttendance($employeeId, $startDate, $endDate)
  // Returns: array
  
  public function calculateStatistics($attendance)
  // Returns: array {total: int, present: int, absent: int, percentage: float}
}
```

**Key Behaviors**:
- Enforces one attendance per employee per day
- Records timestamps in specified format
- Validates employee exists and has trained model
- Calculates attendance statistics

#### 4. AuthenticationService

**Purpose**: Manages user authentication and sessions

**Interface**:
```php
class AuthenticationService {
  private $db;
  
  public function __construct(JsonDB $db)
  
  public function login($username, $password)
  // Returns: array {success: bool, message: string, user: array}
  
  public function logout()
  // Destroys session and clears cookies
  
  public function isAuthenticated()
  // Returns: bool
  
  public function getCurrentUser()
  // Returns: array | null
  
  public function hasRole($role)
  // Returns: bool
  
  public function hashPassword($password)
  // Returns: string (bcrypt hash)
  
  public function verifyPassword($password, $hash)
  // Returns: bool
}
```

**Key Behaviors**:
- Uses bcrypt for password hashing
- Creates secure HTTP-only session cookies
- Validates session on each request
- Expires sessions after 8 hours inactivity

#### 5. ReportService

**Purpose**: Generates attendance reports

**Interface**:
```php
class ReportService {
  private $db;
  
  public function __construct(JsonDB $db)
  
  public function generateDailyReport($date = null)
  // Returns: array
  
  public function generateWeeklyReport()
  // Returns: array (last 7 days)
  
  public function generateMonthlyReport($month = null, $year = null)
  // Returns: array
  
  public function exportToCSV($data, $filename)
  // Returns: string (CSV content)
  
  public function filterByEmployee($data, $employeeId)
  // Returns: array
  
  public function filterByDepartment($data, $department)
  // Returns: array
}
```

**Key Behaviors**:
- Generates reports for different time periods
- Calculates attendance statistics
- Exports to CSV format
- Supports filtering by employee/department

## Data Models

### Database Schema (JSON Files)

#### employees.json
```json
[
  {
    "auto_id": 1,
    "employee_id": "0001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@company.com",
    "phone": "1234567890",
    "department": "Engineering",
    "dob": "1990-01-15",
    "gender": "Male",
    "job_title": "Software Developer",
    "nic": "123456789V",
    "address": "123 Main St",
    "marital_status": "Single",
    "photo_path": "photos/0001.jpg",
    "is_dataset_available": "True",
    "is_model_available": "True",
    "dataset_image_count": 50,
    "dataset_creation_date": "2024-01-10",
    "model_training_date": "2024-01-11",
    "created_at": "2024-01-10 09:00:00",
    "updated_at": "2024-01-11 10:30:00"
  }
]
```

#### attendance.json
```json
[
  {
    "auto_id": 1,
    "employee_id": "0001",
    "in_time": "09:15:30 AM",
    "out_time": "05:45:20 PM",
    "_date": "15-01-2024",
    "face_recognition_entering": "True",
    "face_recognition_exiting": "True",
    "face_recognition_entering_img_path": "",
    "face_recognition_exiting_img_path": "",
    "total_hours": "8.5",
    "status": "Present",
    "created_at": "2024-01-15 09:15:30"
  }
]
```

#### users.json
```json
[
  {
    "auto_id": 1,
    "username": "admin",
    "password": "$2y$10$...", 
    "role": "admin",
    "email": "admin@company.com",
    "created_at": "2024-01-01 00:00:00",
    "last_login": "2024-01-15 08:00:00"
  }
]
```

#### datasets.json
```json
[
  {
    "auto_id": 1,
    "employee_id": "0001",
    "image_count": 50,
    "creation_date": "2024-01-10",
    "created_by": "admin",
    "status": "Complete",
    "path": "datasets/0001/"
  }
]
```

#### training.json
```json
[
  {
    "auto_id": 1,
    "employee_id": "0001",
    "training_date": "2024-01-11",
    "image_count": 50,
    "model_path": "models/0001/model.json",
    "accuracy_score": 0.95,
    "trained_by": "admin",
    "status": "Active"
  }
]
```

#### settings.json
```json
{
  "working_hours_start": "09:00:00",
  "working_hours_end": "17:00:00",
  "late_arrival_threshold_minutes": 15,
  "face_recognition_confidence_threshold": 0.75,
  "dataset_image_count": 50,
  "dataset_capture_interval_ms": 200,
  "session_timeout_hours": 8,
  "face_detection_fps": 10,
  "image_dimensions": {
    "width": 160,
    "height": 160
  }
}
```

### Face Model Format

#### models/{employee_id}/model.json
```json
{
  "employee_id": "0001",
  "descriptor": [
    0.123, -0.456, 0.789, ...
  ],
  "descriptor_length": 128,
  "training_date": "2024-01-11",
  "image_count": 50,
  "version": "1.0"
}
```

### File System Structure

```
web-app/
├── index.php
├── login.php
├── dashboard.php
├── config/
│   └── json-database.php
├── services/
│   ├── EmployeeService.php
│   ├── AttendanceService.php
│   ├── AuthenticationService.php
│   └── ReportService.php
├── pages/
│   ├── employees.php
│   ├── dataset-creator.php
│   ├── model-training.php
│   ├── webcam-attendance.php
│   └── reports.php
├── api/
│   ├── process-attendance.php
│   ├── employee-api.php
│   ├── dataset-api.php
│   └── training-api.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── webcam-controller.js
│   │   ├── face-detection-controller.js
│   │   ├── face-recognition-controller.js
│   │   ├── dataset-controller.js
│   │   ├── training-controller.js
│   │   └── attendance-controller.js
│   └── lib/
│       └── face-api.min.js
├── data/
│   ├── employees.json
│   ├── attendance.json
│   ├── users.json
│   ├── datasets.json
│   ├── training.json
│   ├── settings.json
│   └── *.backup.json
├── datasets/
│   └── {employee_id}/
│       ├── image_1.jpg
│       ├── image_2.jpg
│       └── ...
├── models/
│   ├── face-api/
│   │   ├── ssd_mobilenetv1_model-weights_manifest.json
│   │   ├── face_landmark_68_model-weights_manifest.json
│   │   └── face_recognition_model-weights_manifest.json
│   └── {employee_id}/
│       └── model.json
└── photos/
    └── {employee_id}.jpg
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Before defining the correctness properties, let me analyze each acceptance criterion for testability.

### Acceptance Criteria Testing Prework


### Property Reflection

After analyzing all acceptance criteria, I've identified the following redundancies and consolidation opportunities:

**Performance Properties Consolidation:**
- Properties 2.1 (page load < 2s), 13.1 (attendance < 1s), 13.2 (employee list < 1.5s), 13.3 (webcam latency < 100ms) can be consolidated into a single comprehensive performance property
- Properties 10.5 (read < 100ms) and 10.6 (write < 200ms) are specific database performance metrics that should remain separate

**Face Detection/Recognition Consolidation:**
- Properties 3.3 (detection FPS), 3.4 (bounding box), 4.1 (feature extraction), and 4.2 (comparison) describe a pipeline that can be tested as a single end-to-end property
- Property 4.3 (confidence threshold) and 4.6 (recognition time) should remain separate as they test different aspects

**Attendance Record Properties Consolidation:**
- Properties 5.2 (required fields), 5.3 (time format), and 5.4 (date format) can be combined into a single property about attendance record structure
- Properties 5.5 (duplicate check) and 17.1/17.2 (one per day) are testing the same constraint and should be consolidated

**Dataset/Training Properties Consolidation:**
- Properties 7.3 (50 images), 7.6 (JPEG 160x160), and 7.7 (file structure) describe dataset creation that can be tested together
- Properties 8.2 (load images), 8.4 (average descriptor), and 8.5 (JSON format) describe training pipeline that can be consolidated

**Backup Properties Consolidation:**
- Properties 15.1 (create backup), 15.2 (naming pattern), and 15.3 (maintain recent) describe backup behavior that can be tested as one property
- Properties 15.5 (validate) and 15.7 (atomic writes) are related to write integrity and can be combined

**Session Management Consolidation:**
- Properties 11.3 (create session), 11.5 (store variables), and 11.6 (verify session) describe session lifecycle that can be tested together
- Property 11.10 (bcrypt hashing) should remain separate as it's about password security

**Validation Properties Consolidation:**
- Properties 6.2 (unique employee_id) and 6.3 (valid email) are both input validation and can be combined into a general validation property
- Properties 19.8 (SQL injection) and 19.9 (XSS prevention) are both input sanitization and can be combined

After reflection, I've reduced the testable properties from 150+ to approximately 40 core properties that provide comprehensive coverage without redundancy.

### Correctness Properties

### Property 1: Page Load Performance

*For any* page in the system, when a user navigates to that page, the page load time (including time to first contentful paint and time to interactive) should be less than 2 seconds under normal network conditions.

**Validates: Requirements 2.1**

### Property 2: Face Detection Pipeline

*For any* video frame from the webcam, when face detection is running at 10+ FPS, if a face is detected then a bounding box should be drawn and facial features should be extracted for recognition.

**Validates: Requirements 3.3, 3.4, 4.1**

### Property 3: Face Recognition Confidence Threshold

*For any* detected face descriptor, when compared against all employee models, a match should only be identified if the Euclidean distance corresponds to a confidence score above 0.75.

**Validates: Requirements 4.3**

### Property 4: Face Recognition Performance

*For any* face detection event, the complete recognition process (feature extraction, comparison, and identification) should complete within 1 second.

**Validates: Requirements 4.6**

### Property 5: Attendance Record Structure

*For any* attendance record created by the system, it must contain employee_id, date in format "dd-mm-yyyy", time in format "hh:mm:ss AM/PM", and recognition status fields.

**Validates: Requirements 5.2, 5.3, 5.4**

### Property 6: Duplicate Attendance Prevention

*For any* employee and any given date, only one attendance record should exist in the database, and subsequent attempts to mark attendance for the same employee on the same date should be rejected.

**Validates: Requirements 5.5, 5.6, 17.1, 17.2**

### Property 7: Attendance Persistence Performance

*For any* attendance record, when saved to the JSON database, the write operation should complete within 500 milliseconds.

**Validates: Requirements 5.8**

### Property 8: Employee ID Uniqueness

*For any* new employee being added to the system, the employee_id must be unique across all existing employees, and duplicate employee_id values should be rejected.

**Validates: Requirements 6.2**

### Property 9: Input Validation

*For any* user input (email, employee_id, dates, etc.), the system should validate the format and reject invalid inputs with specific error messages before processing.

**Validates: Requirements 6.3, 12.10, 19.8, 19.9**

### Property 10: Cascade Delete

*For any* employee deletion, all associated data (training dataset images, face model files, and database records) should also be deleted from the file system and database.

**Validates: Requirements 6.8**

### Property 11: Immediate Persistence

*For any* data modification operation (create, update, delete), changes should be persisted to the JSON database immediately without requiring explicit save action.

**Validates: Requirements 6.9**

### Property 12: Dataset Image Capture

*For any* dataset creation session, exactly 50 face images should be captured at 200ms intervals, saved as JPEG files with dimensions 160x160 pixels, and stored in the directory structure datasets/{employee_id}/image_{number}.jpg.

**Validates: Requirements 7.3, 7.4, 7.6, 7.7**

### Property 13: Face Detection Before Capture

*For any* image capture attempt during dataset creation, a face must be detected in the frame before the image is saved, otherwise the capture should be skipped.

**Validates: Requirements 7.5**

### Property 14: Dataset Metadata Recording

*For any* completed dataset, the system should update the JSON database with metadata including employee_id, image_count, and creation_date.

**Validates: Requirements 7.10**

### Property 15: Model Training Pipeline

*For any* employee with a complete dataset, when training is initiated, all images should be loaded, descriptors extracted, an average descriptor computed, and the model saved as JSON in models/{employee_id}/model.json.

**Validates: Requirements 8.2, 8.4, 8.5, 8.6**

### Property 16: Training Metadata Recording

*For any* completed training session, the system should update the JSON database with training metadata including employee_id, training_date, and model_path.

**Validates: Requirements 8.9**

### Property 17: Report Date Filtering

*For any* report type (daily, weekly, monthly), the system should filter attendance records to include only those within the specified date range.

**Validates: Requirements 9.2, 9.3, 9.4**

### Property 18: Attendance Statistics Calculation

*For any* set of attendance records, the system should correctly calculate total present, total absent, and attendance percentage statistics.

**Validates: Requirements 9.6**

### Property 19: Report Filtering

*For any* attendance report, when filtered by employee_id or department, only records matching the filter criteria should be included in the results.

**Validates: Requirements 9.9**

### Property 20: Report Data Loading Capacity

*For any* report query, the system should load and display up to 1000 attendance records from the JSON database without pagination.

**Validates: Requirements 9.10**

### Property 21: JSON Database Read Performance

*For any* JSON file up to 1MB in size, read operations should complete within 100 milliseconds.

**Validates: Requirements 10.5**

### Property 22: JSON Database Write Performance

*For any* JSON file up to 1MB in size, write operations should complete within 200 milliseconds.

**Validates: Requirements 10.6**

### Property 23: JSON Validation Before Write

*For any* write operation to the JSON database, the data structure should be validated as valid JSON before writing to prevent file corruption.

**Validates: Requirements 10.7, 15.5**

### Property 24: Backup Creation Before Write

*For any* write operation to a JSON file, a backup copy with naming pattern {filename}.backup.json should be created before the write, and only the most recent backup should be maintained.

**Validates: Requirements 15.1, 15.2, 15.3**

### Property 25: Atomic File Operations

*For any* write operation to the JSON database, the operation should be atomic (all-or-nothing) to prevent partial writes that could corrupt the data.

**Validates: Requirements 15.7**

### Property 26: Manual Backup Archive Creation

*For any* manual backup request, the system should create a timestamped ZIP archive containing all JSON files and image directories.

**Validates: Requirements 15.10**

### Property 27: Session Creation on Login

*For any* successful login with valid credentials, the system should create a session with unique session_id, store user_id and username in session variables, and set an HTTP-only cookie.

**Validates: Requirements 11.2, 11.3, 11.5**

### Property 28: Session Validation on Protected Pages

*For any* request to a protected page, the system should verify that a valid session exists, and redirect to login page if no valid session is found.

**Validates: Requirements 11.6, 11.7**

### Property 29: Session Cleanup on Logout

*For any* logout action, the system should destroy the session, clear all session variables, and remove session cookies.

**Validates: Requirements 11.8**

### Property 30: Session Timeout

*For any* session, if there is no activity for 8 hours, the session should expire and the user should be required to log in again.

**Validates: Requirements 11.9**

### Property 31: Password Hashing

*For any* password stored in the database, it should be hashed using the bcrypt algorithm, and plain text passwords should never be stored.

**Validates: Requirements 11.10**

### Property 32: Error Message Display

*For any* error condition, the system should display a user-friendly error message describing the issue rather than technical error details.

**Validates: Requirements 12.1**

### Property 33: Error Logging

*For any* error that occurs, detailed error information should be logged to the browser console (for JavaScript errors) or server error log (for PHP errors) for debugging purposes.

**Validates: Requirements 12.7, 12.8**

### Property 34: Webcam Track Cleanup

*For any* page navigation away from a webcam page, all active webcam tracks should be stopped to release the camera device.

**Validates: Requirements 14.6**

### Property 35: Webcam Resolution Support

*For any* webcam with resolution between 640x480 and 1920x1080, the system should successfully access and display the video stream.

**Validates: Requirements 14.8**

### Property 36: MediaDevices API Detection

*For any* browser, before attempting webcam access, the system should detect if navigator.mediaDevices.getUserMedia is supported.

**Validates: Requirements 14.1**

### Property 37: Face Model Storage Format

*For any* trained employee model, it should be stored as a JSON file containing the employee_id, descriptor array (128 or 512 dimensions), training_date, and image_count.

**Validates: Requirements 16.1, 16.2, 16.9**

### Property 38: Model Loading on Attendance Page

*For any* attendance page load, all available employee face models should be loaded into browser memory before face recognition begins.

**Validates: Requirements 16.3**

### Property 39: Model Status Tracking

*For any* employee whose training dataset is updated, the existing face model should be marked as outdated in the database.

**Validates: Requirements 16.5**

### Property 40: Attendance Without Model Prevention

*For any* employee without a trained face model, attempts to mark attendance through face recognition should be prevented with an appropriate error message.

**Validates: Requirements 16.8**

### Property 41: In-Time Recording

*For any* first attendance mark of the day, the system should record the current time as in_time in the attendance record.

**Validates: Requirements 17.3**

### Property 42: Out-Time Update

*For any* existing attendance record with in_time, the system should allow updating the same record with out_time as a separate action.

**Validates: Requirements 17.4**

### Property 43: Time Validation

*For any* attendance record with both in_time and out_time, the out_time must be chronologically later than the in_time.

**Validates: Requirements 17.5**

### Property 44: Hours Calculation

*For any* attendance record with both in_time and out_time, the system should calculate total hours worked as the time difference between out_time and in_time.

**Validates: Requirements 17.6**

### Property 45: Incomplete Attendance Status

*For any* attendance record without out_time by end of day, the status should be marked as "incomplete".

**Validates: Requirements 17.7**

### Property 46: Date Validation

*For any* attendance record creation or edit, the date must be the current date (no backdating or future-dating allowed).

**Validates: Requirements 17.8**

### Property 47: Audit Trail for Admin Edits

*For any* attendance record edited by an admin user, the system should log the change with admin_id, timestamp, and reason in an audit trail.

**Validates: Requirements 17.10**

### Property 48: Role-Based Access Control

*For any* user, access to features should be granted based on their role: Admin_User can access all features, while Employee_User can only access attendance marking.

**Validates: Requirements 19.2, 19.3, 19.4**

### Property 49: Dual Authorization Validation

*For any* sensitive operation, user role should be validated on both frontend (UI) and backend (API endpoint) to prevent unauthorized access.

**Validates: Requirements 19.6, 19.7**

### Property 50: Settings Validation

*For any* settings update, values should be validated to ensure they are within acceptable ranges (e.g., confidence threshold 0.0-1.0, dataset count 30-100).

**Validates: Requirements 20.4, 20.5, 20.8**

### Property 51: Dynamic Settings Application

*For any* settings change, the new values should be applied immediately without requiring server restart or page reload.

**Validates: Requirements 20.9**

### Property 52: Settings Persistence

*For any* settings modification, the changes should be persisted to settings.json in the JSON database.

**Validates: Requirements 20.7**


## Error Handling

### Error Categories and Handling Strategies

#### 1. Webcam Access Errors

**Error Types:**
- `NotAllowedError`: User denied camera permissions
- `NotFoundError`: No camera device available
- `NotReadableError`: Camera in use by another application
- `OverconstrainedError`: Requested constraints cannot be satisfied

**Handling Strategy:**
```javascript
try {
  stream = await navigator.mediaDevices.getUserMedia({video: true});
} catch (error) {
  switch(error.name) {
    case 'NotAllowedError':
      displayError('Camera access denied. Please enable camera permissions in browser settings.');
      break;
    case 'NotFoundError':
      displayError('No camera found. Please connect a webcam and try again.');
      break;
    case 'NotReadableError':
      displayError('Camera is in use by another application. Please close other apps and try again.');
      break;
    default:
      displayError('Camera error: ' + error.message);
      logError('Webcam', error);
  }
}
```

**User Guidance:**
- Display step-by-step instructions to enable camera permissions
- Provide browser-specific guidance (Chrome vs Edge)
- Show visual indicators when camera is active

#### 2. Face Detection Errors

**Error Types:**
- No face detected in frame
- Multiple faces detected
- Face too small or too far
- Poor lighting conditions

**Handling Strategy:**
```javascript
const detection = await faceDetector.detectFace(imageData);

if (!detection) {
  consecutiveFailures++;
  if (consecutiveFailures >= 30) { // 3 seconds at 10 FPS
    displayGuidance('Please position your face in the camera view');
  }
} else {
  consecutiveFailures = 0;
  if (detection.box.width < 100 || detection.box.height < 100) {
    displayGuidance('Please move closer to the camera');
  }
}
```

**User Guidance:**
- "Position your face in the center of the frame"
- "Move closer to the camera"
- "Ensure good lighting"
- Visual feedback with face outline overlay

#### 3. Face Recognition Errors

**Error Types:**
- No matching employee found
- Confidence below threshold
- Model not loaded
- Corrupted model file

**Handling Strategy:**
```javascript
const result = await faceRecognizer.recognizeFace(descriptor);

if (!result) {
  displayError('Face not recognized. Please ensure you are registered in the system.');
  logEvent('Recognition failed', {descriptor: descriptor.slice(0, 5)});
} else if (result.confidence < 0.75) {
  displayError(`Recognition confidence too low (${result.confidence.toFixed(2)}). Please try again.`);
} else {
  // Proceed with attendance marking
}
```

**Fallback Options:**
- Allow manual employee ID entry
- Provide "Try Again" button
- Suggest retraining model if repeated failures

#### 4. Database Errors

**Error Types:**
- File not found
- JSON parse error (corrupted file)
- Write permission denied
- Disk space full

**Handling Strategy (PHP):**
```php
try {
  $data = $this->query('attendance');
} catch (Exception $e) {
  if (file_exists($this->dataDir . 'attendance.backup.json')) {
    // Restore from backup
    $this->restore('attendance');
    error_log('Restored attendance.json from backup: ' . $e->getMessage());
    $data = $this->query('attendance');
  } else {
    error_log('Critical: Cannot read attendance.json and no backup exists');
    throw new Exception('Database error. Please contact administrator.');
  }
}
```

**Recovery Mechanisms:**
- Automatic backup restoration
- File permission repair
- JSON validation and repair
- Admin notification for critical errors

#### 5. Network Errors

**Error Types:**
- Server unreachable
- Request timeout
- 500 Internal Server Error
- 403 Forbidden

**Handling Strategy:**
```javascript
async function markAttendance(employeeId) {
  try {
    const response = await fetch('api/process-attendance.php', {
      method: 'POST',
      body: formData,
      timeout: 5000
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    return result;
    
  } catch (error) {
    if (error.name === 'TypeError') {
      displayError('Network error. Please check your connection and try again.');
    } else if (error.message.includes('timeout')) {
      displayError('Request timed out. Please try again.');
    } else {
      displayError('Server error. Please contact administrator.');
    }
    logError('Network', error);
    return {success: false, message: error.message};
  }
}
```

**Retry Strategy:**
- Automatic retry with exponential backoff
- Maximum 3 retry attempts
- User notification after failed retries

#### 6. Validation Errors

**Error Types:**
- Invalid email format
- Duplicate employee ID
- Missing required fields
- Out of range values

**Handling Strategy (PHP):**
```php
public function validateEmployeeData($data) {
  $errors = [];
  
  if (empty($data['employee_id'])) {
    $errors['employee_id'] = 'Employee ID is required';
  } elseif (!$this->isEmployeeIdUnique($data['employee_id'])) {
    $errors['employee_id'] = 'Employee ID already exists';
  }
  
  if (empty($data['email'])) {
    $errors['email'] = 'Email is required';
  } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
  }
  
  if (empty($data['first_name'])) {
    $errors['first_name'] = 'First name is required';
  }
  
  return [
    'valid' => empty($errors),
    'errors' => $errors
  ];
}
```

**User Feedback:**
- Field-specific error messages
- Inline validation as user types
- Clear indication of which fields have errors
- Suggestions for correction

#### 7. Session Errors

**Error Types:**
- Session expired
- Invalid session
- Session hijacking attempt
- Concurrent session conflict

**Handling Strategy (PHP):**
```php
public function validateSession() {
  if (!isset($_SESSION['user_id'])) {
    return false;
  }
  
  // Check session timeout
  if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > 8 * 3600) { // 8 hours
      session_destroy();
      return false;
    }
  }
  
  // Update last activity
  $_SESSION['last_activity'] = time();
  
  // Validate session fingerprint (prevent hijacking)
  $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
  if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint;
  } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
    session_destroy();
    error_log('Possible session hijacking attempt');
    return false;
  }
  
  return true;
}
```

**User Experience:**
- Graceful redirect to login page
- Preserve form data when possible
- Clear message about session expiration
- Auto-save drafts before timeout

#### 8. Model Training Errors

**Error Types:**
- Insufficient training images
- Face not detected in training images
- Model save failed
- Descriptor extraction failed

**Handling Strategy:**
```javascript
async function trainEmployee(employeeId) {
  try {
    const images = await loadDatasetImages(employeeId);
    
    if (images.length < 30) {
      throw new Error(`Insufficient images: ${images.length}. Minimum 30 required.`);
    }
    
    const descriptors = [];
    for (let img of images) {
      const descriptor = await extractDescriptor(img);
      if (descriptor) {
        descriptors.push(descriptor);
      }
    }
    
    if (descriptors.length < 20) {
      throw new Error(`Only ${descriptors.length} valid faces detected. Please recreate dataset.`);
    }
    
    const avgDescriptor = computeAverage(descriptors);
    await saveModel(employeeId, avgDescriptor);
    
    return {success: true, message: 'Training completed successfully'};
    
  } catch (error) {
    logError('Training', error);
    return {success: false, message: error.message};
  }
}
```

**Recovery Actions:**
- Suggest dataset recreation
- Provide quality guidelines for images
- Allow partial training with warning
- Admin notification for repeated failures

### Error Logging Strategy

#### Client-Side Logging (JavaScript)
```javascript
function logError(category, error) {
  const errorData = {
    category: category,
    message: error.message,
    stack: error.stack,
    timestamp: new Date().toISOString(),
    userAgent: navigator.userAgent,
    url: window.location.href
  };
  
  console.error(`[${category}]`, errorData);
  
  // Send to server for persistent logging
  fetch('api/log-error.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(errorData)
  }).catch(() => {
    // Silently fail if logging fails
  });
}
```

#### Server-Side Logging (PHP)
```php
function logError($category, $message, $context = []) {
  $logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'category' => $category,
    'message' => $message,
    'context' => $context,
    'user_id' => $_SESSION['user_id'] ?? 'anonymous',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'request_uri' => $_SERVER['REQUEST_URI']
  ];
  
  $logFile = 'logs/error-' . date('Y-m-d') . '.log';
  file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
  
  // Also log to PHP error log
  error_log("[{$category}] {$message}");
}
```

### User Feedback Patterns

#### Success Messages
- Green background (#d4edda)
- Checkmark icon
- Auto-dismiss after 3 seconds
- Example: "✅ Attendance marked successfully for John Doe at 09:15 AM"

#### Info Messages
- Blue background (#d1ecf1)
- Info icon
- Persistent until dismissed
- Example: "ℹ️ Camera is starting..."

#### Warning Messages
- Yellow background (#fff3cd)
- Warning icon
- Persistent until dismissed
- Example: "⚠️ Face model is outdated. Please retrain."

#### Error Messages
- Red background (#f8d7da)
- Error icon
- Persistent until dismissed
- Example: "❌ Face not recognized. Please try again."

## Testing Strategy

### Dual Testing Approach

The system requires both unit testing and property-based testing for comprehensive coverage:

**Unit Tests**: Verify specific examples, edge cases, and error conditions
**Property Tests**: Verify universal properties across all inputs

Together, these approaches provide comprehensive coverage where unit tests catch concrete bugs and property tests verify general correctness.

### Testing Technology Stack

#### Frontend Testing
- **Framework**: Jest + Testing Library
- **Property-Based Testing**: fast-check library
- **Browser Testing**: Puppeteer for E2E tests
- **Mocking**: Jest mocks for MediaDevices API

#### Backend Testing
- **Framework**: PHPUnit
- **Property-Based Testing**: Eris library for PHP
- **Database**: In-memory JSON for test isolation

### Property-Based Testing Configuration

All property tests must:
- Run minimum 100 iterations per test (due to randomization)
- Reference the design document property in a comment tag
- Tag format: `// Feature: face-recognition-attendance, Property {number}: {property_text}`

Example:
```javascript
// Feature: face-recognition-attendance, Property 6: Duplicate Attendance Prevention
test('duplicate attendance prevention', () => {
  fc.assert(
    fc.property(
      fc.string(), // employee_id
      fc.date(),   // date
      (employeeId, date) => {
        // Test that only one attendance per employee per day
        const first = markAttendance(employeeId, date);
        const second = markAttendance(employeeId, date);
        return first.success === true && second.success === false;
      }
    ),
    { numRuns: 100 }
  );
});
```

### Unit Test Coverage

#### 1. WebcamController Tests

```javascript
describe('WebcamController', () => {
  test('should request camera permissions on start', async () => {
    const mockGetUserMedia = jest.fn().mockResolvedValue(mockStream);
    navigator.mediaDevices.getUserMedia = mockGetUserMedia;
    
    const controller = new WebcamController(videoElement);
    await controller.startCamera();
    
    expect(mockGetUserMedia).toHaveBeenCalledWith({video: true});
  });
  
  test('should handle permission denial gracefully', async () => {
    const mockGetUserMedia = jest.fn().mockRejectedValue(
      new DOMException('Permission denied', 'NotAllowedError')
    );
    navigator.mediaDevices.getUserMedia = mockGetUserMedia;
    
    const controller = new WebcamController(videoElement);
    await expect(controller.startCamera()).rejects.toThrow('NotAllowedError');
  });
  
  test('should stop all tracks on camera stop', async () => {
    const mockTrack = {stop: jest.fn()};
    const mockStream = {getTracks: () => [mockTrack]};
    
    const controller = new WebcamController(videoElement);
    controller.stream = mockStream;
    controller.stopCamera();
    
    expect(mockTrack.stop).toHaveBeenCalled();
  });
});
```

#### 2. FaceDetectionController Tests

```javascript
describe('FaceDetectionController', () => {
  test('should detect face and return bounding box', async () => {
    const controller = new FaceDetectionController();
    await controller.loadModels();
    
    const imageData = loadTestImage('face.jpg');
    const detection = await controller.detectFace(imageData);
    
    expect(detection).not.toBeNull();
    expect(detection.box).toHaveProperty('x');
    expect(detection.box).toHaveProperty('y');
    expect(detection.box).toHaveProperty('width');
    expect(detection.box).toHaveProperty('height');
  });
  
  test('should return null when no face detected', async () => {
    const controller = new FaceDetectionController();
    await controller.loadModels();
    
    const imageData = loadTestImage('no-face.jpg');
    const detection = await controller.detectFace(imageData);
    
    expect(detection).toBeNull();
  });
});
```

#### 3. AttendanceService Tests (PHP)

```php
class AttendanceServiceTest extends TestCase {
  public function testMarkAttendanceCreatesRecord() {
    $db = new JsonDB('test-data/');
    $service = new AttendanceService($db);
    
    $result = $service->markAttendance('0001');
    
    $this->assertTrue($result['success']);
    $this->assertStringContainsString('marked', $result['message']);
    
    $attendance = $db->query('attendance');
    $this->assertCount(1, $attendance);
    $this->assertEquals('0001', $attendance[0]['employee_id']);
  }
  
  public function testDuplicateAttendanceRejected() {
    $db = new JsonDB('test-data/');
    $service = new AttendanceService($db);
    
    $first = $service->markAttendance('0001');
    $second = $service->markAttendance('0001');
    
    $this->assertTrue($first['success']);
    $this->assertFalse($second['success']);
    $this->assertStringContainsString('already marked', $second['message']);
  }
}
```

### Property-Based Test Examples

#### Property 1: Page Load Performance
```javascript
// Feature: face-recognition-attendance, Property 1: Page Load Performance
test('all pages load within 2 seconds', async () => {
  const pages = [
    '/dashboard.php',
    '/employees.php',
    '/webcam-attendance.php',
    '/reports.php'
  ];
  
  for (const page of pages) {
    const startTime = Date.now();
    await fetch(`http://localhost${page}`);
    const loadTime = Date.now() - startTime;
    
    expect(loadTime).toBeLessThan(2000);
  }
}, 100000); // 100 second timeout for multiple page loads
```

#### Property 6: Duplicate Attendance Prevention
```javascript
// Feature: face-recognition-attendance, Property 6: Duplicate Attendance Prevention
test('only one attendance per employee per day', () => {
  fc.assert(
    fc.property(
      fc.string({minLength: 4, maxLength: 10}), // employee_id
      fc.date({min: new Date('2024-01-01'), max: new Date('2024-12-31')}),
      (employeeId, date) => {
        const db = new TestJsonDB();
        const service = new AttendanceService(db);
        
        const dateStr = formatDate(date);
        const first = service.markAttendance(employeeId, dateStr);
        const second = service.markAttendance(employeeId, dateStr);
        
        const records = db.query('attendance').filter(r => 
          r.employee_id === employeeId && r._date === dateStr
        );
        
        return first.success === true && 
               second.success === false && 
               records.length === 1;
      }
    ),
    { numRuns: 100 }
  );
});
```

#### Property 8: Employee ID Uniqueness
```javascript
// Feature: face-recognition-attendance, Property 8: Employee ID Uniqueness
test('employee IDs must be unique', () => {
  fc.assert(
    fc.property(
      fc.array(fc.record({
        employee_id: fc.string({minLength: 4, maxLength: 10}),
        first_name: fc.string(),
        last_name: fc.string(),
        email: fc.emailAddress()
      }), {minLength: 2, maxLength: 10}),
      (employees) => {
        const db = new TestJsonDB();
        const service = new EmployeeService(db);
        
        const results = employees.map(emp => service.createEmployee(emp));
        const uniqueIds = new Set(employees.map(e => e.employee_id));
        
        // If all IDs are unique, all should succeed
        if (uniqueIds.size === employees.length) {
          return results.every(r => r.success === true);
        }
        
        // If there are duplicates, at least one should fail
        return results.some(r => r.success === false);
      }
    ),
    { numRuns: 100 }
  );
});
```

#### Property 12: Dataset Image Capture
```javascript
// Feature: face-recognition-attendance, Property 12: Dataset Image Capture
test('dataset captures exactly 50 images with correct format', async () => {
  fc.assert(
    fc.asyncProperty(
      fc.string({minLength: 4, maxLength: 10}), // employee_id
      async (employeeId) => {
        const controller = new DatasetController(employeeId, 50);
        await controller.startCapture(mockVideoElement, () => {});
        
        const files = fs.readdirSync(`datasets/${employeeId}`);
        const jpegFiles = files.filter(f => f.endsWith('.jpg'));
        
        // Check count
        if (jpegFiles.length !== 50) return false;
        
        // Check naming pattern
        for (let i = 1; i <= 50; i++) {
          if (!files.includes(`image_${i}.jpg`)) return false;
        }
        
        // Check dimensions
        for (const file of jpegFiles) {
          const img = await loadImage(`datasets/${employeeId}/${file}`);
          if (img.width !== 160 || img.height !== 160) return false;
        }
        
        return true;
      }
    ),
    { numRuns: 100 }
  );
});
```

#### Property 23: JSON Validation Before Write
```php
// Feature: face-recognition-attendance, Property 23: JSON Validation Before Write
public function testJsonValidationBeforeWrite() {
  $this->forAll(
    Generator\associative([
      'employee_id' => Generator\string(),
      'first_name' => Generator\string(),
      'last_name' => Generator\string()
    ])
  )->then(function($data) {
    $db = new JsonDB('test-data/');
    
    // Valid JSON should succeed
    $result = $db->insert('employees', $data);
    $this->assertIsInt($result);
    
    // Verify data is valid JSON
    $file = 'test-data/employees.json';
    $content = file_get_contents($file);
    $decoded = json_decode($content, true);
    $this->assertNotNull($decoded);
    $this->assertIsArray($decoded);
  });
}
```

#### Property 31: Password Hashing
```php
// Feature: face-recognition-attendance, Property 31: Password Hashing
public function testPasswordsAlwaysHashed() {
  $this->forAll(
    Generator\string()
  )->then(function($password) {
    $auth = new AuthenticationService($this->db);
    $hash = $auth->hashPassword($password);
    
    // Hash should not equal plain password
    $this->assertNotEquals($password, $hash);
    
    // Hash should start with bcrypt identifier
    $this->assertStringStartsWith('$2y$', $hash);
    
    // Verify should work
    $this->assertTrue($auth->verifyPassword($password, $hash));
    
    // Wrong password should fail
    $this->assertFalse($auth->verifyPassword($password . 'wrong', $hash));
  });
}
```

### Integration Testing

#### End-to-End Attendance Flow
```javascript
describe('Complete Attendance Flow', () => {
  test('employee can mark attendance through face recognition', async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // Navigate to attendance page
    await page.goto('http://localhost/webcam-attendance.php');
    
    // Start camera
    await page.click('#startBtn');
    await page.waitForSelector('#video[srcObject]');
    
    // Wait for face detection
    await page.waitForSelector('.face-detected', {timeout: 5000});
    
    // Capture and mark attendance
    await page.click('#captureBtn');
    
    // Verify success message
    const message = await page.waitForSelector('.status-success');
    const text = await message.textContent();
    expect(text).toContain('Attendance marked');
    
    await browser.close();
  });
});
```

### Performance Testing

```javascript
describe('Performance Requirements', () => {
  test('face detection runs at 10+ FPS', async () => {
    const controller = new FaceDetectionController();
    await controller.loadModels();
    
    const frameCount = 100;
    const startTime = Date.now();
    
    for (let i = 0; i < frameCount; i++) {
      await controller.detectFace(mockImageData);
    }
    
    const endTime = Date.now();
    const fps = frameCount / ((endTime - startTime) / 1000);
    
    expect(fps).toBeGreaterThanOrEqual(10);
  });
  
  test('JSON database reads complete within 100ms', async () => {
    const db = new JsonDB();
    const testData = generateLargeDataset(1000); // 1000 records
    await db.save('test', testData);
    
    const startTime = Date.now();
    const result = db.query('test');
    const readTime = Date.now() - startTime;
    
    expect(readTime).toBeLessThan(100);
    expect(result.length).toBe(1000);
  });
});
```

### Test Data Generators

```javascript
// Generate random employee data
function generateEmployee() {
  return {
    employee_id: fc.sample(fc.string({minLength: 4, maxLength: 10}), 1)[0],
    first_name: fc.sample(fc.string(), 1)[0],
    last_name: fc.sample(fc.string(), 1)[0],
    email: fc.sample(fc.emailAddress(), 1)[0],
    department: fc.sample(fc.constantFrom('Engineering', 'HR', 'Sales'), 1)[0]
  };
}

// Generate random face descriptor
function generateDescriptor(dimensions = 128) {
  return new Float32Array(dimensions).map(() => Math.random() * 2 - 1);
}

// Generate random attendance record
function generateAttendanceRecord() {
  const date = new Date();
  return {
    employee_id: fc.sample(fc.string({minLength: 4}), 1)[0],
    in_time: formatTime(date),
    _date: formatDate(date),
    face_recognition_entering: 'True'
  };
}
```

### Continuous Integration

```yaml
# .github/workflows/test.yml
name: Test Suite

on: [push, pull_request]

jobs:
  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
        with:
          node-version: '16'
      - run: npm install
      - run: npm test -- --coverage
      - run: npm run test:property # Run property-based tests
      
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - run: composer install
      - run: vendor/bin/phpunit --coverage-text
      
  e2e-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
      - run: npm install
      - run: npm run test:e2e
```

### Test Coverage Goals

- **Unit Test Coverage**: Minimum 80% code coverage
- **Property Test Coverage**: All 52 correctness properties must have corresponding property tests
- **Integration Test Coverage**: All critical user flows (login, attendance marking, employee management, training)
- **E2E Test Coverage**: Complete attendance workflow from camera access to database persistence

### Testing Best Practices

1. **Isolation**: Each test should be independent and not rely on other tests
2. **Cleanup**: Always clean up test data, files, and resources after tests
3. **Mocking**: Mock external dependencies (camera, network) for reliable tests
4. **Assertions**: Use specific assertions that clearly indicate what failed
5. **Documentation**: Comment complex test logic and edge cases
6. **Performance**: Keep unit tests fast (<100ms each), property tests can be slower
7. **Determinism**: Avoid flaky tests by controlling randomness and timing
8. **Coverage**: Aim for high coverage but focus on critical paths first

