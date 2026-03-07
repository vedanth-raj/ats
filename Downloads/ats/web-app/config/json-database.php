<?php
// JSON File Database - No MySQL needed!

class JsonDB {
    private $dataDir = 'data/';
    
    public function __construct() {
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }
    
    public function query($table) {
        $file = $this->dataDir . $table . '.json';
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    
    public function insert($table, $data) {
        $records = $this->query($table);
        $data['auto_id'] = count($records) + 1;
        $records[] = $data;
        $this->save($table, $records);
        return $data['auto_id'];
    }
    
    public function update($table, $id, $data) {
        $records = $this->query($table);
        foreach ($records as $key => $record) {
            if ($record['auto_id'] == $id) {
                $records[$key] = array_merge($record, $data);
                $this->save($table, $records);
                return true;
            }
        }
        return false;
    }
    
    public function delete($table, $id) {
        $records = $this->query($table);
        foreach ($records as $key => $record) {
            if ($record['auto_id'] == $id) {
                unset($records[$key]);
                $this->save($table, array_values($records));
                return true;
            }
        }
        return false;
    }
    
    private function save($table, $data) {
        $file = $this->dataDir . $table . '.json';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
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
}

// Initialize sample data
function initSampleData() {
    $db = new JsonDB();
    
    // Check if users exist
    $users = $db->query('users');
    if (empty($users)) {
        $db->insert('users', [
            'username' => 'admin',
            'password' => 'kuna123'
        ]);
    }
    
    // Check if employees exist
    $employees = $db->query('employees');
    if (empty($employees)) {
        $db->insert('employees', [
            'employee_id' => '0001',
            'first_name' => 'Gunarakulan',
            'last_name' => 'Gunaretnam',
            'dob' => '1997-01-11',
            'gender' => 'Male',
            'job_title' => 'Software Developer',
            'nic' => '970110720V',
            'phone_no' => '12435789',
            'address' => '345768',
            'email_address' => 'e43257689',
            'marital_status' => 'Single',
            'photo_path' => 'No_Image',
            'is_dataset_available' => 'True',
            'is_model_available' => 'True'
        ]);
        
        $db->insert('employees', [
            'employee_id' => '0002',
            'first_name' => 'David',
            'last_name' => 'Mike',
            'dob' => '1990-05-15',
            'gender' => 'Male',
            'job_title' => 'Manager',
            'nic' => '3245476453',
            'phone_no' => '456543542132',
            'address' => '56543542132',
            'email_address' => '35241435',
            'marital_status' => 'Single',
            'photo_path' => 'No_Image',
            'is_dataset_available' => 'False',
            'is_model_available' => 'False'
        ]);
    }
    
    // Check if attendance exists
    $attendance = $db->query('attendance');
    if (empty($attendance)) {
        $db->insert('attendance', [
            'employee_id' => '0001',
            'in_time' => '09:00:00 AM',
            'out_time' => '05:00:00 PM',
            '_date' => date('d-m-Y'),
            'face_recognition_entering' => 'True',
            'face_recognition_exiting' => 'True',
            'face_recognition_entering_img_path' => '',
            'face_recognition_exiting_img_path' => ''
        ]);
    }
}

initSampleData();
?>
