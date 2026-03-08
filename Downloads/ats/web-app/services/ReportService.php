<?php
/**
 * ReportService - Generates attendance reports
 * Provides daily, weekly, monthly reports with filtering and CSV export
 */

require_once __DIR__ . '/../config/json-database.php';
require_once __DIR__ . '/AttendanceService.php';

class ReportService {
    private $db;
    private $attendanceService;
    
    public function __construct(JsonDB $db) {
        $this->db = $db;
        $this->attendanceService = new AttendanceService($db);
    }
    
    /**
     * Generate daily attendance report
     * @param string|null $date Date in dd-mm-yyyy format (defaults to today)
     * @return array Report data with records and statistics
     */
    public function generateDailyReport($date = null) {
        $date = $date ?? $this->attendanceService->formatDate(new DateTime());
        $records = $this->attendanceService->getAttendanceByDate($date);
        $statistics = $this->attendanceService->calculateStatistics($records);
        
        return [
            'type' => 'daily',
            'date' => $date,
            'records' => $records,
            'statistics' => $statistics
        ];
    }
    
    /**
     * Generate weekly attendance report (last 7 days)
     * @return array Report data with records and statistics
     */
    public function generateWeeklyReport() {
        $endDate = new DateTime();
        $startDate = (clone $endDate)->modify('-6 days');
        
        $startDateStr = $this->attendanceService->formatDate($startDate);
        $endDateStr = $this->attendanceService->formatDate($endDate);
        
        $records = $this->attendanceService->getAttendanceByDateRange($startDateStr, $endDateStr);
        $statistics = $this->attendanceService->calculateStatistics($records);
        
        return [
            'type' => 'weekly',
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'records' => $records,
            'statistics' => $statistics
        ];
    }
    
    /**
     * Generate monthly attendance report
     * @param int|null $month Month (1-12, defaults to current month)
     * @param int|null $year Year (defaults to current year)
     * @return array Report data with records and statistics
     */
    public function generateMonthlyReport($month = null, $year = null) {
        $month = $month ?? (int)date('m');
        $year = $year ?? (int)date('Y');
        
        // Get first and last day of month
        $startDate = new DateTime("{$year}-{$month}-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        
        $startDateStr = $this->attendanceService->formatDate($startDate);
        $endDateStr = $this->attendanceService->formatDate($endDate);
        
        $records = $this->attendanceService->getAttendanceByDateRange($startDateStr, $endDateStr);
        $statistics = $this->attendanceService->calculateStatistics($records);
        
        return [
            'type' => 'monthly',
            'month' => $month,
            'year' => $year,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'records' => $records,
            'statistics' => $statistics
        ];
    }
    
    /**
     * Generate custom date range report
     * @param string $startDate Start date in dd-mm-yyyy format
     * @param string $endDate End date in dd-mm-yyyy format
     * @return array Report data with records and statistics
     */
    public function generateCustomReport($startDate, $endDate) {
        $records = $this->attendanceService->getAttendanceByDateRange($startDate, $endDate);
        $statistics = $this->attendanceService->calculateStatistics($records);
        
        return [
            'type' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'records' => $records,
            'statistics' => $statistics
        ];
    }
    
    /**
     * Filter report data by employee
     * @param array $data Report data
     * @param string $employeeId Employee ID
     * @return array Filtered report data
     */
    public function filterByEmployee($data, $employeeId) {
        $filtered = array_filter($data['records'], function($record) use ($employeeId) {
            return $record['employee_id'] === $employeeId;
        });
        
        $data['records'] = array_values($filtered);
        $data['statistics'] = $this->attendanceService->calculateStatistics($data['records']);
        $data['filter'] = ['employee_id' => $employeeId];
        
        return $data;
    }
    
    /**
     * Filter report data by department
     * @param array $data Report data
     * @param string $department Department name
     * @return array Filtered report data
     */
    public function filterByDepartment($data, $department) {
        $filtered = array_filter($data['records'], function($record) use ($department) {
            return isset($record['department']) && $record['department'] === $department;
        });
        
        $data['records'] = array_values($filtered);
        $data['statistics'] = $this->attendanceService->calculateStatistics($data['records']);
        $data['filter'] = ['department' => $department];
        
        return $data;
    }
    
    /**
     * Export report data to CSV format
     * @param array $data Report data
     * @param string $filename Filename for the CSV
     * @return string CSV content
     */
    public function exportToCSV($data, $filename = 'attendance_report.csv') {
        $csv = [];
        
        // Add header row
        $csv[] = [
            'Employee ID',
            'Employee Name',
            'Department',
            'Date',
            'In Time',
            'Out Time',
            'Total Hours',
            'Status'
        ];
        
        // Add data rows
        foreach ($data['records'] as $record) {
            $csv[] = [
                $record['employee_id'],
                $record['employee_name'] ?? 'N/A',
                $record['department'] ?? 'N/A',
                $record['_date'],
                $record['in_time'],
                $record['out_time'] ?? 'N/A',
                $record['total_hours'] ?? 'N/A',
                $record['status']
            ];
        }
        
        // Add statistics row
        $csv[] = [];
        $csv[] = ['Statistics'];
        $csv[] = ['Total Records', $data['statistics']['total']];
        $csv[] = ['Present', $data['statistics']['present']];
        $csv[] = ['Absent', $data['statistics']['absent']];
        $csv[] = ['Attendance %', $data['statistics']['percentage'] . '%'];
        
        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        return $output;
    }
    
    /**
     * Get employee attendance summary
     * @param string $employeeId Employee ID
     * @param string $startDate Start date in dd-mm-yyyy format
     * @param string $endDate End date in dd-mm-yyyy format
     * @return array Summary with attendance count, total hours, etc.
     */
    public function getEmployeeSummary($employeeId, $startDate, $endDate) {
        $records = $this->attendanceService->getEmployeeAttendance($employeeId, $startDate, $endDate);
        
        $totalHours = 0;
        $daysPresent = 0;
        $lateArrivals = 0;
        
        // Get settings for late arrival threshold
        $settings = $this->db->query('settings');
        $lateThreshold = !empty($settings) ? $settings[0]['late_arrival_threshold_minutes'] : 15;
        $workStartTime = !empty($settings) ? $settings[0]['working_hours_start'] : '09:00:00';
        
        foreach ($records as $record) {
            if ($record['status'] === 'Present') {
                $daysPresent++;
                
                if (isset($record['total_hours']) && $record['total_hours'] !== null) {
                    $totalHours += (float)$record['total_hours'];
                }
                
                // Check for late arrival
                $inTime = DateTime::createFromFormat('h:i:s A', $record['in_time']);
                $expectedTime = DateTime::createFromFormat('H:i:s', $workStartTime);
                
                if ($inTime && $expectedTime) {
                    $diff = ($inTime->getTimestamp() - $expectedTime->getTimestamp()) / 60;
                    if ($diff > $lateThreshold) {
                        $lateArrivals++;
                    }
                }
            }
        }
        
        $avgHours = $daysPresent > 0 ? round($totalHours / $daysPresent, 2) : 0;
        
        return [
            'employee_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_present' => $daysPresent,
            'total_hours' => round($totalHours, 2),
            'average_hours' => $avgHours,
            'late_arrivals' => $lateArrivals,
            'records' => $records
        ];
    }
    
    /**
     * Get department-wise attendance summary
     * @param string $startDate Start date in dd-mm-yyyy format
     * @param string $endDate End date in dd-mm-yyyy format
     * @return array Summary by department
     */
    public function getDepartmentSummary($startDate, $endDate) {
        $records = $this->attendanceService->getAttendanceByDateRange($startDate, $endDate);
        $departments = [];
        
        foreach ($records as $record) {
            $dept = $record['department'] ?? 'Unknown';
            
            if (!isset($departments[$dept])) {
                $departments[$dept] = [
                    'department' => $dept,
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0
                ];
            }
            
            $departments[$dept]['total']++;
            if ($record['status'] === 'Present') {
                $departments[$dept]['present']++;
            } else {
                $departments[$dept]['absent']++;
            }
        }
        
        // Calculate percentages
        foreach ($departments as &$dept) {
            $dept['percentage'] = $dept['total'] > 0 
                ? round(($dept['present'] / $dept['total']) * 100, 2) 
                : 0;
        }
        
        return array_values($departments);
    }
}
?>
