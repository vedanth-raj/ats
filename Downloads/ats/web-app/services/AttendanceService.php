<?php
/**
 * AttendanceService - Manages attendance records and business rules
 * Handles attendance marking, duplicate checking, and statistics
 */

require_once __DIR__ . '/../config/json-database.php';

class AttendanceService {
    private $db;
    
    public function __construct(JsonDB $db) {
        $this->db = $db;
    }
    
    /**
     * Mark attendance for an employee
     * @param string $employeeId Employee ID
     * @param string|null $date Date in dd-mm-yyyy format (defaults to today)
     * @param string|null $time Time in hh:mm:ss AM/PM format (defaults to now)
     * @return array Result with success status and message
     */
    public function markAttendance($employeeId, $date = null, $time = null) {
        // Default to current date and time
        $date = $date ?? $this->formatDate(new DateTime());
        $time = $time ?? $this->formatTime(new DateTime());
        
        // Check if employee exists
        $employees = $this->db->find('employees', ['employee_id' => $employeeId]);
        if (empty($employees)) {
            return [
                'success' => false,
                'message' => 'Employee not found'
            ];
        }
        
        $employee = $employees[0];
        
        // Check if employee has trained model
        if ($employee['is_model_available'] !== 'True') {
            return [
                'success' => false,
                'message' => 'Employee does not have a trained face recognition model'
            ];
        }
        
        // Check for duplicate attendance today
        if ($this->checkDuplicateToday($employeeId, $date)) {
            return [
                'success' => false,
                'message' => 'Attendance already marked for today'
            ];
        }
        
        // Create attendance record
        $attendanceData = [
            'employee_id' => $employeeId,
            'in_time' => $time,
            'out_time' => null,
            '_date' => $date,
            'face_recognition_entering' => 'True',
            'face_recognition_exiting' => 'False',
            'face_recognition_entering_img_path' => '',
            'face_recognition_exiting_img_path' => '',
            'total_hours' => null,
            'status' => 'Present'
        ];
        
        try {
            $this->db->insert('attendance', $attendanceData);
            return [
                'success' => true,
                'message' => "Attendance marked for {$employee['first_name']} {$employee['last_name']} at {$time}",
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'time' => $time,
                'date' => $date
            ];
        } catch (Exception $e) {
            error_log("Error marking attendance: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update out-time for an existing attendance record
     * @param string $employeeId Employee ID
     * @param string|null $date Date in dd-mm-yyyy format (defaults to today)
     * @param string|null $time Time in hh:mm:ss AM/PM format (defaults to now)
     * @return array Result with success status and message
     */
    public function markOutTime($employeeId, $date = null, $time = null) {
        $date = $date ?? $this->formatDate(new DateTime());
        $time = $time ?? $this->formatTime(new DateTime());
        
        // Find today's attendance record
        $records = $this->db->find('attendance', [
            'employee_id' => $employeeId,
            '_date' => $date
        ]);
        
        if (empty($records)) {
            return [
                'success' => false,
                'message' => 'No attendance record found for today'
            ];
        }
        
        $record = $records[0];
        
        // Validate out-time is after in-time
        $inTime = DateTime::createFromFormat('h:i:s A', $record['in_time']);
        $outTime = DateTime::createFromFormat('h:i:s A', $time);
        
        if ($outTime <= $inTime) {
            return [
                'success' => false,
                'message' => 'Out-time must be after in-time'
            ];
        }
        
        // Calculate total hours
        $diff = $inTime->diff($outTime);
        $totalHours = $diff->h + ($diff->i / 60);
        $totalHours = round($totalHours, 2);
        
        // Update record
        try {
            $this->db->update('attendance', $record['auto_id'], [
                'out_time' => $time,
                'face_recognition_exiting' => 'True',
                'total_hours' => $totalHours
            ]);
            
            return [
                'success' => true,
                'message' => "Out-time marked at {$time}. Total hours: {$totalHours}",
                'time' => $time,
                'total_hours' => $totalHours
            ];
        } catch (Exception $e) {
            error_log("Error marking out-time: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to mark out-time: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if attendance already exists for employee today
     * @param string $employeeId Employee ID
     * @param string|null $date Date in dd-mm-yyyy format (defaults to today)
     * @return bool True if duplicate exists
     */
    public function checkDuplicateToday($employeeId, $date = null) {
        $date = $date ?? $this->formatDate(new DateTime());
        
        $records = $this->db->find('attendance', [
            'employee_id' => $employeeId,
            '_date' => $date
        ]);
        
        return !empty($records);
    }
    
    /**
     * Get today's attendance records
     * @return array List of attendance records
     */
    public function getTodayAttendance() {
        $today = $this->formatDate(new DateTime());
        return $this->getAttendanceByDate($today);
    }
    
    /**
     * Get attendance records for a specific date
     * @param string $date Date in dd-mm-yyyy format
     * @return array List of attendance records with employee details
     */
    public function getAttendanceByDate($date) {
        $records = $this->db->find('attendance', ['_date' => $date]);
        
        // Enrich with employee details
        foreach ($records as &$record) {
            $employees = $this->db->find('employees', ['employee_id' => $record['employee_id']]);
            if (!empty($employees)) {
                $employee = $employees[0];
                $record['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                $record['department'] = $employee['department'] ?? 'N/A';
                $record['job_title'] = $employee['job_title'] ?? 'N/A';
            }
        }
        
        return $records;
    }
    
    /**
     * Get attendance records for a date range
     * @param string $startDate Start date in dd-mm-yyyy format
     * @param string $endDate End date in dd-mm-yyyy format
     * @return array List of attendance records
     */
    public function getAttendanceByDateRange($startDate, $endDate) {
        $allRecords = $this->db->query('attendance');
        $filtered = [];
        
        $start = DateTime::createFromFormat('d-m-Y', $startDate);
        $end = DateTime::createFromFormat('d-m-Y', $endDate);
        
        foreach ($allRecords as $record) {
            $recordDate = DateTime::createFromFormat('d-m-Y', $record['_date']);
            if ($recordDate >= $start && $recordDate <= $end) {
                // Enrich with employee details
                $employees = $this->db->find('employees', ['employee_id' => $record['employee_id']]);
                if (!empty($employees)) {
                    $employee = $employees[0];
                    $record['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                    $record['department'] = $employee['department'] ?? 'N/A';
                }
                $filtered[] = $record;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get attendance records for a specific employee
     * @param string $employeeId Employee ID
     * @param string $startDate Start date in dd-mm-yyyy format
     * @param string $endDate End date in dd-mm-yyyy format
     * @return array List of attendance records
     */
    public function getEmployeeAttendance($employeeId, $startDate, $endDate) {
        $allRecords = $this->getAttendanceByDateRange($startDate, $endDate);
        
        return array_filter($allRecords, function($record) use ($employeeId) {
            return $record['employee_id'] === $employeeId;
        });
    }
    
    /**
     * Calculate attendance statistics
     * @param array $attendance List of attendance records
     * @return array Statistics with total, present, absent, percentage
     */
    public function calculateStatistics($attendance) {
        $total = count($attendance);
        $present = 0;
        
        foreach ($attendance as $record) {
            if ($record['status'] === 'Present') {
                $present++;
            }
        }
        
        $absent = $total - $present;
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'percentage' => $percentage
        ];
    }
    
    /**
     * Format time to hh:mm:ss AM/PM
     * @param DateTime $date DateTime object
     * @return string Formatted time
     */
    public function formatTime($date) {
        return $date->format('h:i:s A');
    }
    
    /**
     * Format date to dd-mm-yyyy
     * @param DateTime $date DateTime object
     * @return string Formatted date
     */
    public function formatDate($date) {
        return $date->format('d-m-Y');
    }
    
    /**
     * Mark incomplete attendance at end of day
     * Updates records without out-time to "incomplete" status
     * @param string|null $date Date to check (defaults to today)
     * @return int Number of records marked incomplete
     */
    public function markIncompleteAttendance($date = null) {
        $date = $date ?? $this->formatDate(new DateTime());
        $records = $this->db->find('attendance', ['_date' => $date]);
        
        $count = 0;
        foreach ($records as $record) {
            if (empty($record['out_time']) || $record['out_time'] === null) {
                $this->db->update('attendance', $record['auto_id'], [
                    'status' => 'Incomplete'
                ]);
                $count++;
            }
        }
        
        return $count;
    }
}
?>
