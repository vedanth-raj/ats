# Implementation Plan: Face Recognition Attendance System

## Overview

This implementation plan breaks down the face recognition attendance system into discrete coding tasks. The system uses PHP for backend services with JSON file storage, and JavaScript with face-api.js for frontend face detection and recognition. The implementation follows a three-phase workflow: Dataset Creation → Model Training → Attendance Marking.

## Tasks

- [x] 1. Set up core infrastructure and database layer
  - Create JsonDB class with query, insert, update, delete, and count methods
  - Implement file locking mechanism for concurrent write protection
  - Implement backup creation before each write operation
  - Implement JSON validation before write operations
  - Create data directory structure (data/, datasets/, models/, photos/)
  - Initialize JSON database files (employees.json, attendance.json, users.json, datasets.json, training.json, settings.json)
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 15.1, 15.2, 15.5, 15.7_

- [ ]* 1.1 Write property test for JSON database operations
  - **Property 21: JSON Database Read Performance**
  - **Property 22: JSON Database Write Performance**
  - **Property 23: JSON Validation Before Write**
  - **Property 24: Backup Creation Before Write**
  - **Property 25: Atomic File Operations**
  - **Validates: Requirements 10.5, 10.6, 10.7, 15.1, 15.2, 15.5, 15.7_

- [ ] 2. Implement backend services layer
  - [x] 2.1 Create EmployeeService with CRUD operations
    - Implement getAllEmployees, getEmployee, createEmployee, updateEmployee, deleteEmployee methods
    - Implement validateEmployeeData with email and employee_id validation
    - Implement isEmployeeIdUnique check
    - Implement cascade delete for datasets and models
    - _Requirements: 6.1, 6.2, 6.3, 6.8, 6.9_

  - [ ]* 2.2 Write property tests for EmployeeService
    - **Property 8: Employee ID Uniqueness**
    - **Property 9: Input Validation**
    - **Property 10: Cascade Delete**
    - **Property 11: Immediate Persistence**
    - **Validates: Requirements 6.2, 6.3, 6.8, 6.9_

  - [x] 2.3 Create AttendanceService with attendance logic
    - Implement markAttendance method with duplicate check
    - Implement checkDuplicateToday method
    - Implement getTodayAttendance, getAttendanceByDate, getAttendanceByDateRange methods
    - Implement getEmployeeAttendance method
    - Implement calculateStatistics method
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.8, 17.1, 17.2, 17.3, 17.4, 17.5, 17.6_

  - [ ]* 2.4 Write property tests for AttendanceService
    - **Property 5: Attendance Record Structure**
    - **Property 6: Duplicate Attendance Prevention**
    - **Property 7: Attendance Persistence Performance**
    - **Property 41: In-Time Recording**
    - **Property 42: Out-Time Update**
    - **Property 43: Time Validation**
    - **Property 44: Hours Calculation**
    - **Validates: Requirements 5.2, 5.3, 5.4, 5.5, 5.6, 5.8, 17.1, 17.2, 17.3, 17.4, 17.5, 17.6_

  - [x] 2.4 Create AuthenticationService with session management
    - Implement login method with bcrypt password verification
    - Implement logout method with session cleanup
    - Implement isAuthenticated and getCurrentUser methods
    - Implement hasRole method for role-based access control
    - Implement session timeout validation (8 hours)
    - Implement session fingerprinting for hijacking prevention
    - _Requirements: 11.1, 11.2, 11.3, 11.5, 11.6, 11.7, 11.8, 11.9, 11.10, 19.2, 19.3, 19.4, 19.6, 19.7_

  - [ ]* 2.5 Write property tests for AuthenticationService
    - **Property 27: Session Creation on Login**
    - **Property 28: Session Validation on Protected Pages**
    - **Property 29: Session Cleanup on Logout**
    - **Property 30: Session Timeout**
    - **Property 31: Password Hashing**
    - **Property 48: Role-Based Access Control**
    - **Property 49: Dual Authorization Validation**
    - **Validates: Requirements 11.2, 11.3, 11.5, 11.6, 11.7, 11.8, 11.9, 11.10, 19.2, 19.3, 19.4, 19.6, 19.7_

  - [x] 2.6 Create ReportService for attendance reports
    - Implement generateDailyReport, generateWeeklyReport, generateMonthlyReport methods
    - Implement exportToCSV method
    - Implement filterByEmployee and filterByDepartment methods
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9, 9.10_

  - [ ]* 2.7 Write property tests for ReportService
    - **Property 17: Report Date Filtering**
    - **Property 18: Attendance Statistics Calculation**
    - **Property 19: Report Filtering**
    - **Property 20: Report Data Loading Capacity**
    - **Validates: Requirements 9.2, 9.3, 9.4, 9.6, 9.9, 9.10_

- [ ] 3. Checkpoint - Backend services complete
  - Ensure all backend service tests pass, ask the user if questions arise.

- [ ] 4. Implement frontend JavaScript controllers
  - [x] 4.1 Create WebcamController for camera management
    - Implement startCamera method with MediaDevices API
    - Implement stopCamera method with track cleanup
    - Implement captureFrame method
    - Implement getDevices and switchCamera methods
    - Implement error handling for NotAllowedError, NotFoundError, NotReadableError
    - _Requirements: 3.1, 3.2, 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7, 14.8_

  - [ ]* 4.2 Write unit tests for WebcamController
    - Test camera permission request
    - Test permission denial handling
    - Test track cleanup on stop
    - Test device enumeration
    - **Validates: Requirements 3.1, 3.2, 14.1, 14.2, 14.3, 14.4, 14.5, 14.6_

  - [ ]* 4.3 Write property tests for WebcamController
    - **Property 34: Webcam Track Cleanup**
    - **Property 35: Webcam Resolution Support**
    - **Property 36: MediaDevices API Detection**
    - **Validates: Requirements 14.6, 14.8, 14.1_

  - [x] 4.4 Create FaceDetectionController using face-api.js
    - Implement loadModels method to load SSD MobileNet models
    - Implement detectFace method returning bounding box
    - Implement detectFaceWithLandmarks method
    - Implement startDetectionLoop at 10+ FPS
    - Implement stopDetectionLoop method
    - Implement drawDetection method for canvas overlay
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 4.1_

  - [ ]* 4.5 Write unit tests for FaceDetectionController
    - Test face detection with valid face image
    - Test null return when no face detected
    - Test bounding box coordinates
    - Test detection loop FPS
    - **Validates: Requirements 3.3, 3.4, 3.5_

  - [ ]* 4.6 Write property test for face detection pipeline
    - **Property 2: Face Detection Pipeline**
    - **Validates: Requirements 3.3, 3.4, 4.1_

  - [x] 4.7 Create FaceRecognitionController for employee identification
    - Implement loadModels method for face recognition models
    - Implement loadEmployeeModels method to fetch all models/*.json
    - Implement extractDescriptor method returning 128-dimensional Float32Array
    - Implement recognizeFace method with Euclidean distance comparison
    - Implement computeDistance method
    - Implement setThreshold method (default 0.75)
    - _Requirements: 4.2, 4.3, 4.4, 4.5, 4.6, 16.3_

  - [ ]* 4.8 Write property tests for FaceRecognitionController
    - **Property 3: Face Recognition Confidence Threshold**
    - **Property 4: Face Recognition Performance**
    - **Property 38: Model Loading on Attendance Page**
    - **Validates: Requirements 4.3, 4.6, 16.3_

  - [ ] 4.9 Create DatasetController for training image capture
    - Implement startCapture method with 200ms interval
    - Implement stopCapture method
    - Implement captureImage method with face detection check
    - Implement uploadImage method to save datasets/{employeeId}/image_{n}.jpg
    - Implement finalizeDataset method to update employee record
    - Resize images to 160x160 pixels
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8, 7.9, 7.10_

  - [ ]* 4.10 Write property tests for DatasetController
    - **Property 12: Dataset Image Capture**
    - **Property 13: Face Detection Before Capture**
    - **Property 14: Dataset Metadata Recording**
    - **Validates: Requirements 7.3, 7.4, 7.5, 7.6, 7.7, 7.10_

  - [ ] 4.11 Create TrainingController for model training
    - Implement trainEmployee method for single employee
    - Implement trainMultiple method for batch training
    - Implement loadDatasetImages method
    - Implement extractDescriptors method
    - Implement computeAverageDescriptor method
    - Implement saveModel method to models/{employeeId}/model.json
    - Validate minimum 30 images before training
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 8.9_

  - [ ]* 4.12 Write property tests for TrainingController
    - **Property 15: Model Training Pipeline**
    - **Property 16: Training Metadata Recording**
    - **Property 37: Face Model Storage Format**
    - **Validates: Requirements 8.2, 8.4, 8.5, 8.6, 8.9, 16.1, 16.2, 16.9_

  - [x] 4.13 Create AttendanceController for attendance marking
    - Implement markAttendance method with API call
    - Implement checkDuplicate method
    - Implement getTodayAttendance method
    - Implement formatTime and formatDate methods
    - Implement error handling and user feedback
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.7_

  - [ ]* 4.14 Write unit tests for AttendanceController
    - Test attendance marking success flow
    - Test duplicate detection
    - Test time and date formatting
    - Test error handling
    - **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 5. Checkpoint - Frontend controllers complete
  - Ensure all frontend controller tests pass, ask the user if questions arise.

- [ ] 6. Create backend API endpoints
  - [x] 6.1 Create process-attendance.php endpoint
    - Accept POST request with employee_id
    - Validate employee exists and has trained model
    - Check for duplicate attendance today
    - Create attendance record with current timestamp
    - Return JSON response with success/error message
    - Implement timeout prevention (complete within 5 seconds)
    - _Requirements: 5.1, 5.5, 5.6, 5.8, 16.8, 17.1, 17.2_

  - [ ] 6.2 Create employee-api.php endpoint
    - Implement GET /employees - list all employees
    - Implement GET /employees/{id} - get single employee
    - Implement POST /employees - create employee
    - Implement PUT /employees/{id} - update employee
    - Implement DELETE /employees/{id} - delete employee with cascade
    - Validate all inputs and return structured JSON responses
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8_

  - [ ] 6.3 Create dataset-api.php endpoint
    - Implement POST /dataset/upload - upload training image
    - Implement POST /dataset/finalize - mark dataset complete
    - Implement GET /dataset/{employeeId} - get dataset info
    - Implement DELETE /dataset/{employeeId} - delete dataset
    - _Requirements: 7.7, 7.8, 7.9, 7.10_

  - [ ] 6.4 Create training-api.php endpoint
    - Implement POST /training/train - train single employee model
    - Implement POST /training/batch - train multiple employees
    - Implement GET /training/{employeeId} - get training status
    - Implement GET /models/{employeeId}/model.json - serve model file
    - _Requirements: 8.1, 8.5, 8.6, 8.9, 16.1, 16.3_

  - [ ] 6.5 Create report-api.php endpoint
    - Implement GET /reports/daily - daily attendance report
    - Implement GET /reports/weekly - weekly attendance report
    - Implement GET /reports/monthly - monthly attendance report
    - Implement GET /reports/export - CSV export
    - Implement query parameters for filtering (employee_id, department, date range)
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.7, 9.8, 9.9_

  - [ ] 6.6 Create settings-api.php endpoint
    - Implement GET /settings - get all settings
    - Implement PUT /settings - update settings
    - Validate setting values (confidence threshold 0.0-1.0, dataset count 30-100)
    - Apply settings immediately without restart
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 20.7, 20.8, 20.9_

  - [ ]* 6.7 Write property tests for settings validation
    - **Property 50: Settings Validation**
    - **Property 51: Dynamic Settings Application**
    - **Property 52: Settings Persistence**
    - **Validates: Requirements 20.4, 20.5, 20.7, 20.8, 20.9_

- [ ] 7. Checkpoint - API endpoints complete
  - Ensure all API endpoints work correctly, ask the user if questions arise.

- [ ] 8. Create UI pages
  - [ ] 8.1 Create login.php page
    - Create login form with username and password fields
    - Integrate with AuthenticationService
    - Display error messages for invalid credentials
    - Redirect to dashboard on successful login
    - _Requirements: 11.1, 11.2, 11.3, 12.1_

  - [ ] 8.2 Create dashboard.php page
    - Display today's attendance summary
    - Show quick stats (total employees, present today, absent today)
    - Add navigation to all features
    - Implement session validation
    - _Requirements: 2.1, 11.6, 11.7_

  - [ ] 8.3 Create employees.php page
    - Display employee list in table format
    - Add create/edit/delete employee functionality
    - Show dataset and model status indicators
    - Implement client-side validation
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7_

  - [ ] 8.4 Create dataset-creator.php page
    - Add employee selection dropdown
    - Integrate WebcamController for camera access
    - Integrate DatasetController for image capture
    - Display progress indicator (X/50 images)
    - Show live video preview with face detection overlay
    - Display success message on completion
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.8, 7.9_

  - [ ] 8.5 Create model-training.php page
    - Add employee selection (single or multiple)
    - Integrate TrainingController
    - Display training progress
    - Show success/error messages
    - Update employee model status on completion
    - _Requirements: 8.1, 8.3, 8.7, 8.8_

  - [x] 8.6 Create webcam-attendance.php page (fix existing timeout issue)
    - Integrate WebcamController for camera access
    - Integrate FaceDetectionController for real-time detection
    - Integrate FaceRecognitionController for identification
    - Integrate AttendanceController for marking
    - Display live video with face detection overlay
    - Show recognition status and confidence score
    - Display success message with employee name and time
    - Handle duplicate attendance gracefully
    - Optimize to prevent timeout (complete recognition within 1 second)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 5.1, 5.5, 5.6, 13.1_

  - [ ] 8.7 Create reports.php page
    - Add date range selector
    - Add filter options (employee, department)
    - Display attendance records in table
    - Show statistics (total, present, absent, percentage)
    - Add CSV export button
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9_

  - [ ]* 8.8 Write property test for page load performance
    - **Property 1: Page Load Performance**
    - **Validates: Requirements 2.1, 13.1, 13.2_

- [ ] 9. Checkpoint - UI pages complete
  - Ensure all pages load correctly and integrate with controllers, ask the user if questions arise.

- [ ] 10. Implement error handling and user feedback
  - [ ] 10.1 Create error handling utilities
    - Create displayError, displaySuccess, displayWarning, displayInfo functions
    - Implement auto-dismiss for success messages (3 seconds)
    - Implement client-side error logging to console
    - Implement server-side error logging to log files
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7, 12.8_

  - [ ] 10.2 Add specific error handlers
    - Webcam access errors (NotAllowedError, NotFoundError, NotReadableError)
    - Face detection errors (no face, multiple faces, face too small)
    - Face recognition errors (no match, low confidence, model not loaded)
    - Database errors (file not found, JSON parse error, write permission denied)
    - Network errors (timeout, server unreachable, 500 error)
    - Validation errors (invalid email, duplicate ID, missing fields)
    - Session errors (expired, invalid, hijacking attempt)
    - Training errors (insufficient images, face not detected, save failed)
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.9, 12.10_

  - [ ]* 10.3 Write property test for error message display
    - **Property 32: Error Message Display**
    - **Property 33: Error Logging**
    - **Validates: Requirements 12.1, 12.7, 12.8_

- [ ] 11. Implement security features
  - [ ] 11.1 Add input sanitization
    - Sanitize all user inputs to prevent SQL injection (even though using JSON)
    - Sanitize all outputs to prevent XSS attacks
    - Validate file uploads (JPEG only, max size 5MB)
    - _Requirements: 19.8, 19.9, 19.10_

  - [ ] 11.2 Add role-based access control
    - Implement frontend role checks to hide/show UI elements
    - Implement backend role checks on all API endpoints
    - Ensure Admin_User can access all features
    - Ensure Employee_User can only access attendance marking
    - _Requirements: 19.2, 19.3, 19.4, 19.5, 19.6, 19.7_

  - [ ]* 11.3 Write property tests for security
    - **Property 48: Role-Based Access Control**
    - **Property 49: Dual Authorization Validation**
    - **Validates: Requirements 19.2, 19.3, 19.4, 19.6, 19.7_

- [ ] 12. Implement backup and recovery features
  - [ ] 12.1 Add automatic backup functionality
    - Implement backup creation before each write (already in JsonDB)
    - Implement automatic restore from backup on corruption detection
    - Maintain only most recent backup per file
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 15.7, 15.8_

  - [ ] 12.2 Add manual backup functionality
    - Create backup-api.php endpoint
    - Implement manual backup creation with timestamped ZIP archive
    - Include all JSON files and image directories
    - Provide download link for backup archive
    - _Requirements: 15.9, 15.10_

- [ ] 13. Implement settings management
  - [ ] 13.1 Create settings interface
    - Add settings page with form for all configurable values
    - Working hours (start/end time)
    - Late arrival threshold (minutes)
    - Face recognition confidence threshold (0.0-1.0)
    - Dataset image count (30-100)
    - Dataset capture interval (milliseconds)
    - Session timeout (hours)
    - Face detection FPS (5-30)
    - Image dimensions (width/height)
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 20.6, 20.7, 20.8, 20.9_

  - [ ] 13.2 Integrate settings with controllers
    - Load settings on page load
    - Apply settings to WebcamController, FaceDetectionController, FaceRecognitionController
    - Apply settings to DatasetController and TrainingController
    - Apply settings to AttendanceService
    - _Requirements: 20.6, 20.9_

- [ ] 14. Implement additional attendance features
  - [ ] 14.1 Add out-time recording
    - Modify process-attendance.php to handle out-time updates
    - Update existing attendance record instead of creating new one
    - Calculate total hours worked
    - Validate out-time is after in-time
    - _Requirements: 17.4, 17.5, 17.6_

  - [ ] 14.2 Add incomplete attendance handling
    - Create scheduled task to mark incomplete attendance at end of day
    - Update status to "incomplete" for records without out-time
    - _Requirements: 17.7_

  - [ ] 14.3 Add admin attendance editing
    - Create admin interface to edit attendance records
    - Implement audit trail logging for edits
    - Record admin_id, timestamp, and reason for each edit
    - _Requirements: 17.9, 17.10_

  - [ ]* 14.4 Write property tests for attendance features
    - **Property 42: Out-Time Update**
    - **Property 43: Time Validation**
    - **Property 44: Hours Calculation**
    - **Property 45: Incomplete Attendance Status**
    - **Property 46: Date Validation**
    - **Property 47: Audit Trail for Admin Edits**
    - **Validates: Requirements 17.4, 17.5, 17.6, 17.7, 17.8, 17.10_

- [ ] 15. Implement model management features
  - [ ] 15.1 Add model status tracking
    - Display model status on employee list (Available, Outdated, Not Available)
    - Mark models as outdated when dataset is updated
    - Prevent attendance marking for employees without models
    - _Requirements: 16.4, 16.5, 16.7, 16.8_

  - [ ] 15.2 Add model deletion
    - Create endpoint to delete employee models
    - Update employee record when model is deleted
    - _Requirements: 16.6_

  - [ ]* 15.3 Write property tests for model management
    - **Property 39: Model Status Tracking**
    - **Property 40: Attendance Without Model Prevention**
    - **Validates: Requirements 16.5, 16.8_

- [ ] 16. Final integration and testing
  - [ ] 16.1 Test complete workflow end-to-end
    - Create employee → Create dataset → Train model → Mark attendance
    - Verify all data persists correctly
    - Verify performance meets requirements
    - _Requirements: All_

  - [ ] 16.2 Fix process-attendance.php timeout issue
    - Profile the endpoint to identify bottleneck
    - Optimize face recognition loading and comparison
    - Ensure completion within 5 seconds
    - _Requirements: 5.8, 13.1_

  - [ ] 16.3 Optimize performance
    - Ensure page loads < 2 seconds
    - Ensure face detection at 10+ FPS
    - Ensure face recognition < 1 second
    - Ensure JSON reads < 100ms, writes < 200ms
    - _Requirements: 2.1, 3.3, 4.6, 10.5, 10.6, 13.1, 13.2, 13.3_

  - [ ]* 16.4 Run all property-based tests
    - Execute all 52 property tests with 100 iterations each
    - Fix any failures discovered
    - **Validates: All correctness properties_

- [ ] 17. Final checkpoint - Complete system verification
  - Ensure all tests pass, all features work correctly, and performance meets requirements. Ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP delivery
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation and allow for user feedback
- Property tests validate universal correctness properties across all inputs
- Unit tests validate specific examples and edge cases
- The implementation assumes face-api.js library is already available in assets/lib/
- The implementation assumes face-api.js models are already downloaded to models/face-api/
- Focus on fixing the existing process-attendance.php timeout issue in task 16.2
- Existing working components (fast-cam.php, json-database.php, login system) should be preserved and integrated
