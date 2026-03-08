# Requirements Document

## Introduction

This document specifies the requirements for a browser-based face recognition attendance system that runs entirely in the web browser using PHP backend and JavaScript frontend. The system enables organizations to manage employee attendance through automated face recognition, eliminating manual attendance tracking. The system addresses current performance issues with MySQL by using JSON file storage for instant data access, and optimizes page load times to under 2 seconds.

## Glossary

- **System**: The complete browser-based face recognition attendance application
- **Web_Interface**: The browser-based user interface accessible via Chrome/Edge
- **Face_Detector**: The JavaScript component that detects faces in webcam feed using TensorFlow.js or face-api.js
- **Face_Recognizer**: The JavaScript component that identifies employees by matching detected faces against trained models
- **Attendance_Marker**: The PHP backend component that records attendance with timestamps
- **Employee_Manager**: The PHP backend component that handles employee CRUD operations
- **Dataset_Creator**: The component that captures multiple face photos per employee for training
- **Model_Trainer**: The component that trains face recognition models using captured datasets
- **Report_Generator**: The component that generates attendance reports for various time periods
- **JSON_Database**: The file-based storage system using JSON format for data persistence
- **Webcam_API**: The browser's MediaDevices API for accessing camera feed
- **Admin_User**: A user with privileges to manage employees and view reports
- **Employee_User**: A user who marks attendance using face recognition
- **Training_Dataset**: A collection of face images for a specific employee used for model training
- **Face_Model**: A trained machine learning model that can recognize a specific employee's face
- **Attendance_Record**: A data entry containing employee ID, timestamp, and recognition status
- **Session**: An authenticated user's active connection to the system

## Requirements

### Requirement 1: Web-Based Interface

**User Story:** As a user, I want to access the attendance system through a web browser, so that I can use it without installing desktop applications.

#### Acceptance Criteria

1. THE Web_Interface SHALL render all pages using HTML5, CSS3, and JavaScript
2. THE Web_Interface SHALL be accessible through Chrome browser version 90 or higher
3. THE Web_Interface SHALL be accessible through Edge browser version 90 or higher
4. THE Web_Interface SHALL run on Windows operating system with XAMPP server
5. THE Web_Interface SHALL use responsive design to adapt to different screen sizes
6. THE System SHALL serve all pages through PHP backend running on Apache server

### Requirement 2: Fast Page Loading

**User Story:** As a user, I want pages to load quickly, so that I can work efficiently without delays.

#### Acceptance Criteria

1. WHEN a user navigates to any page, THE System SHALL load and render the page within 2 seconds
2. THE System SHALL minimize HTTP requests by combining CSS and JavaScript files where possible
3. THE System SHALL use browser caching for static assets (CSS, JavaScript, images)
4. THE System SHALL optimize images to reduce file size without visible quality loss
5. THE System SHALL lazy-load non-critical resources after initial page render
6. WHEN measuring page load time, THE System SHALL include time to first contentful paint and time to interactive

### Requirement 3: Real-Time Face Detection

**User Story:** As an employee, I want the system to detect my face in real-time through the webcam, so that I can mark my attendance quickly.

#### Acceptance Criteria

1. WHEN the attendance page loads, THE Face_Detector SHALL request webcam access through the Webcam_API
2. WHEN webcam access is granted, THE Face_Detector SHALL display live video feed in the browser
3. WHILE the webcam is active, THE Face_Detector SHALL detect faces in the video feed at minimum 10 frames per second
4. WHEN a face is detected, THE Face_Detector SHALL highlight the detected face with a bounding box overlay
5. THE Face_Detector SHALL use TensorFlow.js or face-api.js library for face detection
6. IF no face is detected for 3 consecutive seconds, THEN THE System SHALL display a message prompting the user to position their face
7. THE Face_Detector SHALL process face detection entirely in the browser without server requests

### Requirement 4: Face Recognition

**User Story:** As an employee, I want the system to recognize my face automatically, so that I don't need to manually enter my ID.

#### Acceptance Criteria

1. WHEN a face is detected, THE Face_Recognizer SHALL extract facial features from the detected face
2. THE Face_Recognizer SHALL compare extracted features against all trained Face_Models in the database
3. WHEN a match is found with confidence above 0.75, THE Face_Recognizer SHALL identify the employee
4. WHEN a match is found, THE System SHALL display the employee's name and ID on screen
5. IF no match is found with sufficient confidence, THEN THE System SHALL display "Face not recognized" message
6. THE Face_Recognizer SHALL complete recognition process within 1 second of face detection
7. THE Face_Recognizer SHALL use the same library (TensorFlow.js or face-api.js) as the Face_Detector

### Requirement 5: Automatic Attendance Marking

**User Story:** As an employee, I want my attendance to be marked automatically when my face is recognized, so that I can complete the process quickly.

#### Acceptance Criteria

1. WHEN the Face_Recognizer identifies an employee, THE Attendance_Marker SHALL create an Attendance_Record
2. THE Attendance_Record SHALL include employee_id, current date, current time, and recognition status
3. THE Attendance_Marker SHALL store the timestamp in format "hh:mm:ss AM/PM"
4. THE Attendance_Marker SHALL store the date in format "dd-mm-yyyy"
5. WHEN creating an Attendance_Record, THE Attendance_Marker SHALL check if attendance already exists for the employee on the current date
6. IF attendance already exists for the current date, THEN THE System SHALL display "Attendance already marked today" message
7. WHEN attendance is successfully marked, THE System SHALL display confirmation message with employee name and timestamp
8. THE Attendance_Marker SHALL persist the Attendance_Record to JSON_Database within 500 milliseconds

### Requirement 6: Employee Management

**User Story:** As an admin, I want to add, edit, and delete employee records, so that I can maintain an accurate employee database.

#### Acceptance Criteria

1. THE Employee_Manager SHALL provide a form to add new employees with fields: employee_id, first_name, last_name, email, phone, department
2. WHEN adding an employee, THE Employee_Manager SHALL validate that employee_id is unique
3. WHEN adding an employee, THE Employee_Manager SHALL validate that email format is valid
4. THE Employee_Manager SHALL provide a list view displaying all employees with their details
5. WHEN viewing the employee list, THE Admin_User SHALL see edit and delete buttons for each employee
6. WHEN editing an employee, THE Employee_Manager SHALL pre-populate the form with existing data
7. WHEN deleting an employee, THE System SHALL prompt for confirmation before deletion
8. WHEN an employee is deleted, THE System SHALL also delete associated Training_Dataset and Face_Model
9. THE Employee_Manager SHALL persist all changes to JSON_Database immediately

### Requirement 7: Dataset Creation

**User Story:** As an admin, I want to capture multiple photos of each employee's face, so that the system can train an accurate recognition model.

#### Acceptance Criteria

1. THE Dataset_Creator SHALL provide an interface to select an employee and start webcam capture
2. WHEN dataset creation starts, THE Dataset_Creator SHALL activate the webcam through Webcam_API
3. THE Dataset_Creator SHALL capture 50 face images per employee for the Training_Dataset
4. THE Dataset_Creator SHALL capture images at intervals of 200 milliseconds
5. WHEN capturing images, THE Dataset_Creator SHALL detect face in each frame before saving
6. THE Dataset_Creator SHALL save captured images in JPEG format with dimensions 160x160 pixels
7. THE Dataset_Creator SHALL store images in directory structure: datasets/{employee_id}/image_{number}.jpg
8. THE Dataset_Creator SHALL display progress indicator showing number of images captured out of total required
9. WHEN all images are captured, THE System SHALL display "Dataset creation complete" message
10. THE Dataset_Creator SHALL update JSON_Database with dataset metadata including employee_id, image_count, and creation_date

### Requirement 8: Model Training

**User Story:** As an admin, I want to train face recognition models for employees, so that the system can identify them during attendance.

#### Acceptance Criteria

1. THE Model_Trainer SHALL provide an interface to select employees and initiate training
2. WHEN training starts, THE Model_Trainer SHALL load all images from the employee's Training_Dataset
3. THE Model_Trainer SHALL use TensorFlow.js or face-api.js to extract facial embeddings from training images
4. THE Model_Trainer SHALL create a Face_Model by computing the average embedding vector from all training images
5. THE Model_Trainer SHALL save the Face_Model in JSON format for browser-based loading
6. THE Model_Trainer SHALL store Face_Models in directory: models/{employee_id}/model.json
7. THE Model_Trainer SHALL display training progress with percentage completion
8. WHEN training completes, THE System SHALL display "Training complete for {employee_name}" message
9. THE Model_Trainer SHALL update JSON_Database with training metadata including employee_id, training_date, and model_path
10. IF training fails due to insufficient images, THEN THE System SHALL display error message specifying minimum required images

### Requirement 9: Attendance Reports

**User Story:** As an admin, I want to view attendance reports for different time periods, so that I can track employee attendance patterns.

#### Acceptance Criteria

1. THE Report_Generator SHALL provide filters to select report type: daily, weekly, or monthly
2. WHEN daily report is selected, THE Report_Generator SHALL display attendance for the current date
3. WHEN weekly report is selected, THE Report_Generator SHALL display attendance for the past 7 days
4. WHEN monthly report is selected, THE Report_Generator SHALL display attendance for the current month
5. THE Report_Generator SHALL display reports in table format with columns: employee_id, name, date, in_time, status
6. THE Report_Generator SHALL calculate and display attendance statistics: total present, total absent, attendance percentage
7. THE Report_Generator SHALL provide export functionality to download reports as CSV files
8. WHEN exporting to CSV, THE System SHALL generate the file within 1 second
9. THE Report_Generator SHALL allow filtering by specific employee or department
10. THE Report_Generator SHALL load report data from JSON_Database without pagination for up to 1000 records

### Requirement 10: JSON File Database

**User Story:** As a system administrator, I want data stored in JSON files instead of MySQL, so that I can avoid connection timeout issues and achieve instant performance.

#### Acceptance Criteria

1. THE JSON_Database SHALL store all data in JSON files within the data/ directory
2. THE JSON_Database SHALL maintain separate JSON files for each entity: employees.json, attendance.json, datasets.json, training.json, users.json
3. WHEN reading data, THE JSON_Database SHALL load the entire JSON file into memory and parse it
4. WHEN writing data, THE JSON_Database SHALL use file locking to prevent concurrent write conflicts
5. THE JSON_Database SHALL complete read operations within 100 milliseconds for files up to 1MB
6. THE JSON_Database SHALL complete write operations within 200 milliseconds for files up to 1MB
7. THE JSON_Database SHALL validate JSON structure before writing to prevent corruption
8. IF a JSON file is corrupted, THEN THE System SHALL restore from backup file with .backup extension
9. THE JSON_Database SHALL create automatic backups before each write operation
10. THE JSON_Database SHALL provide query methods: query(table), insert(table, data), update(table, id, data), delete(table, id)

### Requirement 11: Session Management

**User Story:** As a user, I want to log in securely and stay logged in during my session, so that I can access the system without repeated authentication.

#### Acceptance Criteria

1. THE System SHALL provide a login page with username and password fields
2. WHEN a user submits login credentials, THE System SHALL verify them against the users table in JSON_Database
3. WHEN credentials are valid, THE System SHALL create a Session with unique session_id
4. THE System SHALL store session_id in a secure HTTP-only cookie
5. THE System SHALL store user_id and username in PHP session variables
6. WHEN a user accesses any protected page, THE System SHALL verify the Session exists and is valid
7. IF no valid Session exists, THEN THE System SHALL redirect to the login page
8. THE System SHALL provide a logout function that destroys the Session and clears cookies
9. THE System SHALL expire sessions after 8 hours of inactivity
10. THE System SHALL use password hashing with bcrypt algorithm for stored passwords

### Requirement 12: Error Handling and User Feedback

**User Story:** As a user, I want clear error messages and feedback, so that I understand what's happening and can resolve issues.

#### Acceptance Criteria

1. WHEN an error occurs, THE System SHALL display a user-friendly error message describing the issue
2. THE System SHALL use color-coded status messages: green for success, blue for info, red for errors
3. WHEN a long-running operation is in progress, THE System SHALL display a loading indicator
4. WHEN webcam access is denied, THE System SHALL display instructions to enable camera permissions
5. WHEN face detection fails, THE System SHALL provide guidance on proper positioning
6. WHEN network request fails, THE System SHALL display "Connection error, please try again" message
7. THE System SHALL log detailed error information to browser console for debugging
8. THE System SHALL log PHP errors to server error log file
9. WHEN attendance is successfully marked, THE System SHALL display confirmation for 2 seconds before auto-refreshing
10. THE System SHALL validate all form inputs and display field-specific error messages

### Requirement 13: Performance Optimization

**User Story:** As a user, I want the system to respond quickly to my actions, so that I can complete tasks efficiently.

#### Acceptance Criteria

1. THE System SHALL process attendance marking requests within 1 second from face recognition to confirmation
2. THE System SHALL load employee list page with up to 100 employees within 1.5 seconds
3. THE System SHALL render webcam feed with maximum 100 milliseconds latency
4. THE System SHALL use asynchronous JavaScript for all server requests to prevent UI blocking
5. THE System SHALL compress JSON responses from PHP backend using gzip compression
6. THE System SHALL minimize DOM manipulations by batching updates
7. THE System SHALL preload face recognition models on page load to avoid delays during recognition
8. THE System SHALL use Web Workers for computationally intensive face recognition tasks when possible
9. THE System SHALL debounce rapid user inputs to prevent excessive processing
10. THE System SHALL measure and log performance metrics for page load time, recognition time, and database operations

### Requirement 14: Browser Compatibility and Webcam Access

**User Story:** As a user, I want the system to work reliably with my browser and webcam, so that I can use all features without technical issues.

#### Acceptance Criteria

1. THE System SHALL detect if the browser supports MediaDevices API before attempting webcam access
2. IF MediaDevices API is not supported, THEN THE System SHALL display a message to upgrade the browser
3. THE System SHALL request webcam permissions using navigator.mediaDevices.getUserMedia()
4. WHEN webcam access is granted, THE System SHALL display the video stream with autoplay enabled
5. THE System SHALL handle multiple webcams by allowing user to select preferred camera device
6. WHEN the user navigates away from webcam page, THE System SHALL stop all webcam tracks to release the camera
7. THE System SHALL handle webcam disconnection gracefully and display appropriate error message
8. THE System SHALL work with webcam resolutions from 640x480 to 1920x1080
9. THE System SHALL use playsinline attribute for video element to prevent fullscreen on mobile browsers
10. THE System SHALL test and verify functionality on Chrome 90+, Edge 90+, and Firefox 88+ browsers

### Requirement 15: Data Integrity and Backup

**User Story:** As a system administrator, I want data to be protected against corruption and loss, so that attendance records remain accurate and recoverable.

#### Acceptance Criteria

1. WHEN writing to JSON_Database, THE System SHALL create a backup copy of the existing file before modification
2. THE System SHALL store backup files with naming pattern: {filename}.backup.json
3. THE System SHALL maintain the most recent backup for each JSON file
4. IF a write operation fails, THEN THE System SHALL restore data from the backup file
5. THE System SHALL validate JSON syntax after each write operation
6. IF JSON validation fails, THEN THE System SHALL restore from backup and log the error
7. THE System SHALL use atomic file operations to prevent partial writes
8. THE System SHALL set appropriate file permissions (644) on JSON files to prevent unauthorized access
9. THE System SHALL provide a manual backup function in admin settings to export all data
10. WHEN manual backup is triggered, THE System SHALL create a timestamped ZIP archive of all JSON files and images

### Requirement 16: Face Recognition Model Management

**User Story:** As an admin, I want to manage face recognition models efficiently, so that the system maintains accurate recognition capabilities.

#### Acceptance Criteria

1. THE System SHALL store one Face_Model per employee in JSON format
2. THE Face_Model SHALL contain facial embedding vectors with 128 or 512 dimensions depending on the library used
3. THE System SHALL load all Face_Models into browser memory on attendance page load
4. THE System SHALL provide functionality to retrain a Face_Model if recognition accuracy degrades
5. WHEN an employee's Training_Dataset is updated, THE System SHALL mark the existing Face_Model as outdated
6. THE System SHALL display a warning indicator for employees with outdated Face_Models
7. THE System SHALL allow batch training of multiple Face_Models sequentially
8. THE System SHALL prevent attendance marking for employees without trained Face_Models
9. THE System SHALL store model metadata including training_date, accuracy_score, and image_count
10. THE System SHALL provide functionality to delete Face_Models for employees who have left the organization

### Requirement 17: Attendance Validation and Business Rules

**User Story:** As an admin, I want attendance records to follow business rules, so that the data remains consistent and meaningful.

#### Acceptance Criteria

1. THE System SHALL allow only one attendance entry per employee per day
2. WHEN an employee attempts to mark attendance multiple times in one day, THE System SHALL reject subsequent attempts
3. THE System SHALL record in_time when attendance is first marked
4. THE System SHALL allow recording out_time as a separate action on the same attendance record
5. THE System SHALL validate that out_time is later than in_time for the same attendance record
6. THE System SHALL calculate total hours worked by computing difference between in_time and out_time
7. THE System SHALL mark attendance as "incomplete" if out_time is not recorded by end of day
8. THE System SHALL prevent backdating or future-dating of attendance records
9. THE System SHALL allow admin users to manually edit attendance records with audit trail
10. WHEN admin edits an attendance record, THE System SHALL log the change with admin_id, timestamp, and reason

### Requirement 18: User Interface Consistency

**User Story:** As a user, I want a consistent interface across all pages, so that I can navigate easily and understand the system quickly.

#### Acceptance Criteria

1. THE Web_Interface SHALL use a consistent color scheme across all pages
2. THE Web_Interface SHALL display a navigation sidebar on all authenticated pages
3. THE Web_Interface SHALL highlight the current page in the navigation menu
4. THE Web_Interface SHALL use consistent button styles for primary, secondary, and danger actions
5. THE Web_Interface SHALL display a header with system title and user information on all pages
6. THE Web_Interface SHALL use consistent typography with defined font sizes for headings, body text, and labels
7. THE Web_Interface SHALL use consistent spacing and padding following an 8-pixel grid system
8. THE Web_Interface SHALL display form fields with consistent styling including labels, inputs, and validation messages
9. THE Web_Interface SHALL use consistent icons for common actions: add, edit, delete, view, export
10. THE Web_Interface SHALL provide breadcrumb navigation for multi-step processes like dataset creation and training

### Requirement 19: Security and Access Control

**User Story:** As a system administrator, I want to control access to sensitive features, so that only authorized users can perform administrative actions.

#### Acceptance Criteria

1. THE System SHALL define two user roles: Admin_User and Employee_User
2. THE Admin_User SHALL have access to all features including employee management, reports, and settings
3. THE Employee_User SHALL have access only to attendance marking features
4. WHEN a user attempts to access a restricted page, THE System SHALL verify their role
5. IF the user lacks required permissions, THEN THE System SHALL redirect to an access denied page
6. THE System SHALL protect all PHP backend endpoints with session validation
7. THE System SHALL validate user role on both frontend and backend for sensitive operations
8. THE System SHALL prevent SQL injection by using parameterized queries (even though using JSON, validate inputs)
9. THE System SHALL sanitize all user inputs to prevent XSS attacks
10. THE System SHALL use HTTPS for all communications in production environment

### Requirement 20: System Configuration and Settings

**User Story:** As an admin, I want to configure system settings, so that I can customize the system behavior for my organization.

#### Acceptance Criteria

1. THE System SHALL provide a settings page accessible only to Admin_User
2. THE System SHALL allow configuration of working hours (start time and end time)
3. THE System SHALL allow configuration of late arrival threshold in minutes
4. THE System SHALL allow configuration of face recognition confidence threshold (0.0 to 1.0)
5. THE System SHALL allow configuration of dataset image count (minimum 30, maximum 100)
6. THE System SHALL allow configuration of session timeout duration in hours
7. THE System SHALL store all settings in settings.json file in JSON_Database
8. WHEN settings are updated, THE System SHALL validate values are within acceptable ranges
9. THE System SHALL apply new settings immediately without requiring server restart
10. THE System SHALL provide a "Reset to Defaults" button to restore factory settings
