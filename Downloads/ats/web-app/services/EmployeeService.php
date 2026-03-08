<?php
/**
 * EmployeeService - Handles employee management operations
 * Provides CRUD operations with validation and cascade delete
 */

require_once __DIR__ . '/../config/json-database.php';

class EmployeeService {
    private $db;
    
    public function __construct(JsonDB $db) {
        $this->db = $db;
    }
    
    /**
     * Get all employees
     * @return array List of all employees
     */
    public function getAllEmployees() {
        return $this->db->query('employees');
    }
    
    /**
     * Get a single employee by ID
     * @param int $id Employee auto_id
     * @return array|null Employee data or null if not found
     */
    public function getEmployee($id) {
        return $this->db->findById('employees', $id);
    }
    
    /**
     * Get employee by employee_id
     * @param string $employeeId Employee ID
     * @return array|null Employee data or null if not found
     */
    public function getEmployeeByEmployeeId($employeeId) {
        $results = $this->db->find('employees', ['employee_id' => $employeeId]);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Create a new employee
     * @param array $data Employee data
     * @return array Result with success status, message, and ID
     */
    public function createEmployee($data) {
        // Validate employee data
        $validation = $this->validateEmployeeData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors']
            ];
        }
        
        // Check if employee_id is unique
        if (!$this->isEmployeeIdUnique($data['employee_id'])) {
            return [
                'success' => false,
                'message' => 'Employee ID already exists',
                'errors' => ['employee_id' => 'Employee ID must be unique']
            ];
        }
        
        // Set default values
        $data['is_dataset_available'] = $data['is_dataset_available'] ?? 'False';
        $data['is_model_available'] = $data['is_model_available'] ?? 'False';
        $data['dataset_image_count'] = $data['dataset_image_count'] ?? 0;
        $data['dataset_creation_date'] = $data['dataset_creation_date'] ?? null;
        $data['model_training_date'] = $data['model_training_date'] ?? null;
        $data['photo_path'] = $data['photo_path'] ?? 'photos/' . $data['employee_id'] . '.jpg';
        
        try {
            $id = $this->db->insert('employees', $data);
            return [
                'success' => true,
                'message' => 'Employee created successfully',
                'id' => $id
            ];
        } catch (Exception $e) {
            error_log("Error creating employee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing employee
     * @param int $id Employee auto_id
     * @param array $data Updated employee data
     * @return array Result with success status and message
     */
    public function updateEmployee($id, $data) {
        // Check if employee exists
        $existing = $this->getEmployee($id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Employee not found'
            ];
        }
        
        // If employee_id is being changed, validate uniqueness
        if (isset($data['employee_id']) && $data['employee_id'] !== $existing['employee_id']) {
            if (!$this->isEmployeeIdUnique($data['employee_id'])) {
                return [
                    'success' => false,
                    'message' => 'Employee ID already exists',
                    'errors' => ['employee_id' => 'Employee ID must be unique']
                ];
            }
        }
        
        // Validate updated data (only validate fields that are being updated)
        $validation = $this->validateEmployeeData($data, true);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors']
            ];
        }
        
        try {
            $this->db->update('employees', $id, $data);
            return [
                'success' => true,
                'message' => 'Employee updated successfully'
            ];
        } catch (Exception $e) {
            error_log("Error updating employee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an employee with cascade delete
     * Deletes employee record, dataset images, and trained model
     * @param int $id Employee auto_id
     * @return array Result with success status and message
     */
    public function deleteEmployee($id) {
        // Check if employee exists
        $employee = $this->getEmployee($id);
        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found'
            ];
        }
        
        $employeeId = $employee['employee_id'];
        
        try {
            // Delete dataset images
            $datasetPath = __DIR__ . '/../datasets/' . $employeeId;
            if (is_dir($datasetPath)) {
                $this->deleteDirectory($datasetPath);
            }
            
            // Delete trained model
            $modelPath = __DIR__ . '/../models/' . $employeeId;
            if (is_dir($modelPath)) {
                $this->deleteDirectory($modelPath);
            }
            
            // Delete photo
            $photoPath = __DIR__ . '/../' . $employee['photo_path'];
            if (file_exists($photoPath) && $employee['photo_path'] !== 'No_Image') {
                unlink($photoPath);
            }
            
            // Delete dataset record
            $datasets = $this->db->find('datasets', ['employee_id' => $employeeId]);
            foreach ($datasets as $dataset) {
                $this->db->delete('datasets', $dataset['auto_id']);
            }
            
            // Delete training record
            $trainings = $this->db->find('training', ['employee_id' => $employeeId]);
            foreach ($trainings as $training) {
                $this->db->delete('training', $training['auto_id']);
            }
            
            // Delete employee record
            $this->db->delete('employees', $id);
            
            return [
                'success' => true,
                'message' => 'Employee and associated data deleted successfully'
            ];
        } catch (Exception $e) {
            error_log("Error deleting employee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate employee data
     * @param array $data Employee data to validate
     * @param bool $partial Whether this is a partial update (not all fields required)
     * @return array Validation result with valid flag and errors array
     */
    public function validateEmployeeData($data, $partial = false) {
        $errors = [];
        
        // Required fields for new employee
        if (!$partial) {
            $requiredFields = ['employee_id', 'first_name', 'last_name', 'email'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Validate employee_id format (if provided)
        if (isset($data['employee_id'])) {
            if (!preg_match('/^[A-Z0-9]{4,10}$/i', $data['employee_id'])) {
                $errors['employee_id'] = 'Employee ID must be 4-10 alphanumeric characters';
            }
        }
        
        // Validate email format (if provided)
        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
        }
        
        // Validate phone (if provided)
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $data['phone'])) {
                $errors['phone'] = 'Invalid phone number format';
            }
        }
        
        // Validate date of birth (if provided)
        if (isset($data['dob']) && !empty($data['dob'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['dob']);
            if (!$date || $date->format('Y-m-d') !== $data['dob']) {
                $errors['dob'] = 'Invalid date format (use YYYY-MM-DD)';
            }
        }
        
        // Validate gender (if provided)
        if (isset($data['gender']) && !empty($data['gender'])) {
            $validGenders = ['Male', 'Female', 'Other'];
            if (!in_array($data['gender'], $validGenders)) {
                $errors['gender'] = 'Gender must be Male, Female, or Other';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Check if employee_id is unique
     * @param string $employeeId Employee ID to check
     * @param int|null $excludeId Auto ID to exclude from check (for updates)
     * @return bool True if unique, false if already exists
     */
    public function isEmployeeIdUnique($employeeId, $excludeId = null) {
        $employees = $this->db->find('employees', ['employee_id' => $employeeId]);
        
        if (empty($employees)) {
            return true;
        }
        
        // If excluding an ID (for updates), check if the only match is the excluded record
        if ($excludeId !== null) {
            return count($employees) === 1 && $employees[0]['auto_id'] == $excludeId;
        }
        
        return false;
    }
    
    /**
     * Recursively delete a directory and its contents
     * @param string $dir Directory path
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
?>
