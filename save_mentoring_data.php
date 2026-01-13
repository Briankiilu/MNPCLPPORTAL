<?php
session_start();
require_once 'dbconnect.php';


$week_words = [
    1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty'
];

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized access or invalid request.']));
}

$log_id = $_POST['log_id'] ?? 'new';
$activities = $_POST['activity'] ?? [];
$dates = $_POST['date'] ?? [];
$student_adm = $_POST['student_adm'] ?? ''; 

if (empty($student_adm)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing student admission number.']));
}

$db_success = true;
$new_log_id = null;
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
$safe_adm = mysqli_real_escape_string($connection, $student_adm);


mysqli_begin_transaction($connection);

try {
    if ($log_id === 'new') {
       
        $query_max_id = "SELECT MAX(log_id) AS max_id FROM mentoring_logs WHERE student_adm = '$safe_adm'";
        $result_max_id = mysqli_query($connection, $query_max_id);
        $row = mysqli_fetch_assoc($result_max_id);
        $new_log_id = ($row['max_id'] ?? 0) + 1;
        $week_label = $week_words[$new_log_id] ?? "#" . $new_log_id; 
        
        $records_inserted = 0;
        
        
        foreach ($activities as $index => $activity) {
            $date_entry = mysqli_real_escape_string($connection, trim($dates[$index]));
            $activity_entry = mysqli_real_escape_string($connection, trim($activity));
            
           
            if (empty($date_entry) || empty($activity_entry)) continue; 

            $sql = "INSERT INTO mentoring_logs (log_id, student_adm, log_day, activity, log_date) 
                    VALUES ('$new_log_id', '$safe_adm', '{$days[$index]}', '$activity_entry', '$date_entry')";
            
            if (!mysqli_query($connection, $sql)) {
                $db_success = false;
                break; 
            }
            $records_inserted++;
        }
        
        if ($db_success && $records_inserted > 0) {
            mysqli_commit($connection);
         
            echo json_encode(['success' => true, 'log_id' => $new_log_id, 'message' => 'New Week Log (Week ' . $week_label . ') saved successfully with ' . $records_inserted . ' entries.']);
        } elseif ($db_success && $records_inserted === 0) {
           
            mysqli_rollback($connection); 
            http_response_code(200);
            die(json_encode(['success' => false, 'message' => 'No data provided to save. Please fill at least one Activity and Date pair to start a new log.']));
        } else {
            mysqli_rollback($connection);
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database error during new record insertion.']));
        }

    } else {
       
        $existing_log_id = (int)$log_id;
        $week_label = $week_words[$existing_log_id] ?? "#" . $existing_log_id; 
        $updates_performed = 0;
        
        
        foreach ($activities as $index => $activity) {
            $date_entry = mysqli_real_escape_string($connection, trim($dates[$index]));
            $activity_entry = mysqli_real_escape_string($connection, trim($activity));
            $log_day = $days[$index];
            
            if (empty($date_entry) || empty($activity_entry)) {
                
                $delete_sql = "DELETE FROM mentoring_logs 
                               WHERE student_adm = '$safe_adm' AND log_id = $existing_log_id AND log_day = '$log_day'";
                
                if (!mysqli_query($connection, $delete_sql)) {
                    $db_success = false;
                    break;
                }
                
                $updates_performed++; 
            } else {
               
                $sql = "INSERT INTO mentoring_logs (log_id, student_adm, log_day, activity, log_date) 
                        VALUES ($existing_log_id, '$safe_adm', '$log_day', '$activity_entry', '$date_entry')
                        ON DUPLICATE KEY UPDATE 
                        activity = '$activity_entry', 
                        log_date = '$date_entry'";
                
                if (!mysqli_query($connection, $sql)) {
                    $db_success = false;
                    break;
                }
                $updates_performed++;
            }
        }
        
        if ($db_success) {
            mysqli_commit($connection);
            
            $message = $updates_performed > 0 
                ? 'Week Log (Week ' . $week_label . ') updated successfully.' 
                : 'No changes detected for Week Log (Week ' . $week_label . ').';

            echo json_encode(['success' => true, 'log_id' => $existing_log_id, 'message' => $message]);
        } else {
            mysqli_rollback($connection);
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database error during update.']));
        }
    }
} catch (Exception $e) {
    mysqli_rollback($connection);
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'An unexpected server error occurred: ' . $e->getMessage()]));
}
if (isset($connection)) {
    mysqli_close($connection);
}
?>