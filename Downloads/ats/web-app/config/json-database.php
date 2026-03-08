<?php
// JSON File Database - No MySQL needed!
// Enhanced with file locking, backup, validation, and atomic operations

class JsonDB {
    private $dataDir = 'data/';
    
    public function __construct($dataDir = 'data/') {
        $this->dataDir = $dataDir;
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }
    
    /**
     * Query all records from a table
     * @param string $table Table name
     * @return array Array of records
     */
    public function query($table) {
        $file = $this->dataDir . $table . '.json';
        if (!file_exists($file)) {
            return [];
        }
        
        try {
            $data = file_get_contents($file);
            $decoded = json_decode($data, true);
            
            // Validate JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error in {$table}.json: " . json_last_error_msg());
                // Try to restore from backup
                if ($this->restore($table)) {
                    return $this->query($table);
                }
                return [];
            }
            
            return $decoded ?: [];
        } catch (Exception $e) {
            error_log("Error reading {$table}.json: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Insert a new record into a table
     * @param string $table Table name
     * @param array $data Record data
     * @return int Auto-generated ID
     */
    public function insert($table, $data) {
        $records = $this->query($table);
        
        // Generate auto_id
        $maxId = 0;
        foreach ($records as $record) {
            if (isset($record['auto_id']) && $record['auto_id'] > $maxId) {
                $maxId = $record['auto_id'];
            }
        }
        $data['auto_id'] = $maxId + 1;
        
        // Add timestamps if not present
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $records[] = $data;
        $this->save($table, $records);
        return $data['auto_id'];
    }
    
    /**
     * Update a record in a table
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function update($table, $id, $data) {
        $records = $this->query($table);
        $updated = false;
        
        foreach ($records as $key => $record) {
            if ($record['auto_id'] == $id) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $records[$key] = array_merge($record, $data);
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $this->save($table, $records);
            return true;
        }
        return false;
    }
    
    /**
     * Delete a record from a table
     * @param string $table Table name
     * @param int $id Record ID
     * @return bool Success status
     */
    public function delete($table, $id) {
        $records = $this->query($table);
        $deleted = false;
        
        foreach ($records as $key => $record) {
            if ($record['auto_id'] == $id) {
                unset($records[$key]);
                $deleted = true;
                break;
            }
        }
        
        if ($deleted) {
            $this->save($table, array_values($records));
            return true;
        }
        return false;
    }
    
    /**
     * Count records in a table with optional filter
     * @param string $table Table name
     * @param array $where Filter conditions
     * @return int Record count
     */
    public function count($table, $where = []) {
        $records = $this->query($table);
        if (empty($where)) {
            return count($records);
        }
        
        $count = 0;
        foreach ($records as $record) {
            $match = true;
            foreach ($where as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) $count++;
        }
        return $count;
    }
    
    /**
     * Save data to a table with backup and validation
     * @param string $table Table name
     * @param array $data Data to save
     * @return bool Success status
     */
    private function save($table, $data) {
        $file = $this->dataDir . $table . '.json';
        
        try {
            // Create backup before write
            $this->backup($table);
            
            // Validate JSON encoding
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON encode error: " . json_last_error_msg());
            }
            
            // Atomic write using temporary file
            $tempFile = $file . '.tmp';
            $fp = fopen($tempFile, 'w');
            if (!$fp) {
                throw new Exception("Cannot open temporary file for writing");
            }
            
            // Acquire exclusive lock
            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                throw new Exception("Cannot acquire file lock");
            }
            
            // Write data
            fwrite($fp, $jsonData);
            fflush($fp);
            
            // Release lock and close
            flock($fp, LOCK_UN);
            fclose($fp);
            
            // Atomic rename
            if (!rename($tempFile, $file)) {
                throw new Exception("Cannot rename temporary file");
            }
            
            // Set file permissions
            chmod($file, 0644);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error saving {$table}.json: " . $e->getMessage());
            
            // Clean up temporary file if exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            // Try to restore from backup
            $this->restore($table);
            
            throw $e;
        }
    }
    
    /**
     * Create backup of a table
     * @param string $table Table name
     * @return bool Success status
     */
    private function backup($table) {
        $file = $this->dataDir . $table . '.json';
        $backupFile = $this->dataDir . $table . '.backup.json';
        
        if (file_exists($file)) {
            try {
                copy($file, $backupFile);
                return true;
            } catch (Exception $e) {
                error_log("Error creating backup for {$table}.json: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
    
    /**
     * Restore table from backup
     * @param string $table Table name
     * @return bool Success status
     */
    private function restore($table) {
        $file = $this->dataDir . $table . '.json';
        $backupFile = $this->dataDir . $table . '.backup.json';
        
        if (file_exists($backupFile)) {
            try {
                copy($backupFile, $file);
                error_log("Restored {$table}.json from backup");
                return true;
            } catch (Exception $e) {
                error_log("Error restoring {$table}.json from backup: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
    
    /**
     * Find records matching criteria
     * @param string $table Table name
     * @param array $where Filter conditions
     * @return array Matching records
     */
    public function find($table, $where = []) {
        $records = $this->query($table);
        if (empty($where)) {
            return $records;
        }
        
        $results = [];
        foreach ($records as $record) {
            $match = true;
            foreach ($where as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $record;
            }
        }
        return $results;
    }
    
    /**
     * Find a single record by ID
     * @param string $table Table name
     * @param int $id Record ID
     * @return array|null Record or null if not found
     */
    public function findById($table, $id) {
        $records = $this->query($table);
        foreach ($records as $record) {
            if ($record['auto_id'] == $id) {
                return $record;
            }
        }
        return null;
    }
}

// Initialize sample data
function initSampleData() {
    // Initialization moved to init-database.php
    // This function is kept for backward compatibility but does nothing
}

initSampleData();
?>
