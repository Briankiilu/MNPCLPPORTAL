<?php


require_once 'dbconnect.php'; 

$session_username = $_SESSION['username'] ?? '';


$week_words = [
    1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty'
];

$log_id_param = $_GET['log_id'] ?? null;
$current_log_id = 'new';
$previous_log_id = 'new';

if (is_numeric($log_id_param) && $log_id_param > 0) {
    $current_log_id = (int)$log_id_param;
    
    $previous_log_id = ($current_log_id > 1) ? $current_log_id - 1 : 'new';
}

$back_disabled = ($current_log_id == 'new' || $current_log_id == 1) ? 'disabled' : '';

$student_name = "_________________________";
$student_adm = "_________________________"; 
$student_department = "_________________________";
$student_class = "_________________________"; 

if (!empty($session_username) && isset($connection) && $connection) {
    $safe_username = mysqli_real_escape_string($connection, $session_username);
    
    
    $query = "SELECT full_name, adm_no, department, course FROM trainees WHERE username = '$safe_username' LIMIT 1"; 
    $result = mysqli_query($connection, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $student_data = mysqli_fetch_assoc($result);
            $student_name = htmlspecialchars($student_data['full_name'] ?? 'N/A');
            $student_adm = htmlspecialchars($student_data['adm_no'] ?? 'N/A');
            $student_department = htmlspecialchars($student_data['department'] ?? 'N/A');
            $student_class = htmlspecialchars($student_data['course'] ?? 'N/A'); 
        } else {
            
            error_log("Mentoring Tool Error: User '$session_username' not found in trainees table.");
        }
    } else {
        
        error_log("Mentoring Tool DB Query Failed: " . mysqli_error($connection));
    }


    if (isset($result) && $result) {
        mysqli_free_result($result);
    }
}


if ($student_adm === "_________________________") {
    
    die('<div style="padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px auto; max-width: 600px;">
            <h3 style="margin-top: 0;">Error Retrieving Student Data</h3>
            <p>Could not retrieve student admission number. This usually means your username is not linked to a student record in the database.</p>
            <p><strong>Please contact the system administrator immediately and mention the username you are logged in as: ' . htmlspecialchars($session_username) . '</strong></p>
         </div>');
}

$existing_log_entries = [];
if ($current_log_id !== 'new') {
    $safe_adm = mysqli_real_escape_string($connection, $student_adm);
    
    
    $log_query = "SELECT log_day, activity, log_date FROM mentoring_logs WHERE student_adm = '$safe_adm' AND log_id = $current_log_id ORDER BY id ASC";
    $log_result = mysqli_query($connection, $log_query);

    if ($log_result) {
        while ($row = mysqli_fetch_assoc($log_result)) {
            
            $existing_log_entries[$row['log_day']] = [
                'activity' => htmlspecialchars($row['activity'] ?? ''),
                'date' => htmlspecialchars($row['log_date'] ?? '')
            ];
        }
        mysqli_free_result($log_result);
    } else {
       
        error_log("Mentoring Log Retrieval Failed: " . mysqli_error($connection));
    }
}

?>

<div class="document-container">
    <form id="mentoringForm" action="save_mentoring_data.php" method="POST">
        <input type="hidden" name="log_id" value="<?php echo htmlspecialchars($current_log_id); ?>">
        <input type="hidden" name="student_adm" value="<?php echo htmlspecialchars($student_adm); ?>">

        <div class="header-section">
            <div class="logo-wrapper">
                <img src="images/logo.png" alt="MNP Logo" class="logo"> 
                <p class="subtitle">THE MERU NATIONAL POLYTECHNIC</p>
                <p class="subtitle">"Technology for Innovation & Developement"</p>
            </div>
            <p class="contact-info">P.O. BOX: 111 - 60200</p>
            <p class="contact-info">CONTACTS: +254719347059, info@mnp.ac.ke</p>
        </div>

        <h3 class="week-title">
            <?php 
                if ($current_log_id !== 'new') {
                    
                    $week_label = $week_words[$current_log_id] ?? "#{$current_log_id}";
                    echo "WEEK {$week_label} - MENTORING LOG DOCUMENT";
                } else {
                    echo "NEW WEEKLY MENTORING LOG DOCUMENT";
                }
            ?>
        </h3>

        <div class="student-details">
            <div>NAME: <u><?php echo $student_name; ?></u></div>
            <div>ADM NO: <u><?php echo $student_adm; ?></u></div>
            <div>DEPARTMENT: <u><?php echo $student_department; ?></u></div>
            <div>CLASS: <u><?php echo $student_class; ?></u></div>
        </div>

        <table class="activity-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Activity</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                foreach ($days as $index => $day) {
                   
                    $entry = $existing_log_entries[$day] ?? ['activity' => '', 'date' => ''];
                    $activity_content = $entry['activity']; 
                    $date_content = $entry['date']; 

                    echo "<tr>";
                    echo "<td>{$day}</td>";
                    echo "<td><textarea name='activity[]' data-day='{$day}'>{$activity_content}</textarea></td>";
                    echo "<td><input type='date' name='date[]' data-day='{$day}' value='{$date_content}' class='date-input'></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="supervisor-section">
            <div class="signature-line">Supervisors Name:_________________________________</div>
            <div class="stamp-area">Official Stamp</div>
            <div class="signature-line">Signature:________________________________________</div>
        
        </div>

        <div class="action-buttons">
            <button 
                type="button" 
                class="btn btn-back" 
                onclick="window.location.href = 'student_dashboard.php?view=mentoring&log_id=<?php echo urlencode($previous_log_id); ?>';"
                <?php echo $back_disabled; ?>
            >
                <i class="fas fa-arrow-left"></i> Previous Week
            </button>
            
            <button type="button" class="btn btn-new" onclick="window.location.href = 'student_dashboard.php?view=mentoring&log_id=new';">
                <i class="fas fa-plus"></i> New Week Log
            </button>
            
            <button type="button" class="btn btn-save" onclick="saveData()">
                <i class="fas fa-save"></i> Save Data
            </button>
            
            <button type="button" class="btn btn-primary" onclick="window.location.href = 'student_dashboard.php?view=saved_docs';">
                <i class="fas fa-folder-open"></i> View Saved Logs
            </button>
        </div>
    </form>
</div>

<style>
    
    .document-container {
        width: 100%; 
        background-color: white;
        border: 1px solid #333;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px 30px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        position: relative;
        margin-bottom: 30px; 
    }
    
    .week-title {
        text-align: center;
        margin-top: 10px;
        margin-bottom: 25px;
        font-size: 1.3em;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
    }
    
    .header-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
        text-align: center;
        
    }

    .header-section .logo-wrapper {
        margin-bottom: 10px;
        color: black;
    }

    .header-section .logo {
        width: 200px;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    .header-section .subtitle {
        font-size: 0.9em;
        color: black;
        margin: 0;
    }
    
    .contact-info {
        font-size: 1em;
        font-weight: bold;
        margin-top: 15px;
        text-align: center;
        text-decoration: underline;
    }

    .student-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 20px;
        margin-top: 25px;
        margin-bottom: 20px;
        font-size: 1.1em;
        font-weight: bold;
    }

    .student-details div {
        padding: 5px 0;
        
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        font-size: 1.1em;
    }

    .activity-table th,
    .activity-table td {
        border: 1px solid #000;
        padding: 0; 
        text-align: center;
        vertical-align: top;
    }
    
    .activity-table textarea,
    .activity-table input[type="date"] { 
        width: 100%;
        height: 100%;
        min-height: 40px; 
        padding: 8px 10px;
        border: none;
        box-sizing: border-box;
        font-family: inherit;
        font-size: inherit;
        line-height: 1.3;
        background-color: transparent; 
    }

    .activity-table textarea {
        resize: none;
        overflow: hidden; 
    }
    
    .activity-table input[type="date"] {
        line-height: 1; 
        padding: 9px 10px; 
        background-color: white;
    }

    .activity-table td:nth-child(1) { 
        padding: 8px 10px;
        text-transform: capitalize;
        font-weight: bold;
        width: 15%; 
    }

    .activity-table th {
        background-color: #007bff;
        font-weight: bold;
        text-transform: capitalize;
        padding: 8px 10px;
        color: black; 
    }

    .activity-table td:nth-child(2) { width: 55%; }
    .activity-table td:nth-child(3) { width: 30%; }

    .supervisor-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px 20px;
        margin-top: 30px;
        font-size: 1.1em;
        font-weight: bold;
    }

    .supervisor-section div {
        padding: 5px 0;
       
    }

    .signature-line {
        grid-column: 1 / 2;
        display: flex;
        align-items: flex-end;
        min-height: 40px;
    }

    .stamp-area {
        grid-column: 2 / 3;
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        text-align: right;
        min-height: 40px;
        padding: 5px 0;
    }
    
    .action-buttons {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
        margin-top: 20px;
        width: 100%;
    }

    .btn {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-new {
        background-color: #ffc107; 
        color: #333;
    }
    .btn-new:hover:not(:disabled) {
        background-color: #e0a800;
    }

    .btn-back {
        background-color: #6c757d; /* Gray */
        color: white;
    }
    .btn-back:hover:not(:disabled) {
        background-color: #5a6268;
    }

    .btn-save {
        background-color: #28a745;
        color: white;
    }
    .btn-save:hover:not(:disabled) {
        background-color: #1e7e34;
    }

    .btn-primary { 
        background-color: #17a2b8; /* Cyan/Teal */
        color: white;
    }
    .btn-primary:hover:not(:disabled) {
        background-color: #138496;
    }

    
    @media print {
    /* 1. Eliminate default browser headers (URL, Date, Title) */
    @page {
        margin: 0;
    }

    /* 2. Set the page background and margins */
    body {
        margin: 0;
        padding: 0;
        background-color: white;
    }

    /* 3. Hide all screen elements by default */
    body * {
        visibility: hidden;
    }

    /* 4. Show only the document container and our custom header */
    .document-container,
    .document-container *,
    .custom-print-header,
    .custom-print-header * {
        visibility: visible !important;
    }

    /* 5. Center the document with professional spacing */
    .document-container {
        position: absolute;
        top: 2cm;  /* Push down to make room for header */
        left: 1.5cm;
        right: 1.5cm;
        width: calc(100% - 3cm);
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }

    /* 6. THE SOLUTION: Custom Header in the Circled Positions */
    .custom-print-header {
        display: flex !important;
        justify-content: space-between; /* Pushes one name to left, one to right */
        position: fixed;
        top: 0.5cm;  /* Position near the top edge */
        left: 1.5cm; /* Align with left margin */
        right: 1.5cm;/* Align with right margin */
        width: calc(100% - 3cm);
        font-size: 10pt;
        font-family: Arial, sans-serif;
        color: #333;
        z-index: 9999;
    }

    /* Hide buttons */
    .action-buttons, .btn-save, .nav-menu {
        display: none !important;
    }
}

/* Ensure this header never shows on the computer screen */
@media screen {
    .custom-print-header {
        display: none;
    }
}

       
        .action-buttons { 
            display: none !important; 
        } 
        
       
        .activity-table textarea,
        .activity-table input[type="date"] { 
            border: none; 
            background: none; 
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>


<script>
    
    function autoGrow(element) {
        element.style.height = "5px"; 
        element.style.height = (element.scrollHeight) + "px";
    }

    
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea').forEach(textarea => {
            
            autoGrow(textarea); 
            
            
            textarea.addEventListener('input', () => {
                autoGrow(textarea);
            });
        });

       
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('print') && urlParams.get('print') === 'true') {
            window.print();
        }
    });

    
    function saveData() {
        const form = document.getElementById('mentoringForm');
        const formData = new FormData(form);

        
        let hasData = false;
        const activities = form.querySelectorAll('textarea[name="activity[]"]');
        const dates = form.querySelectorAll('input[name="date[]"]');

        activities.forEach((activity, index) => {
            
            if (activity.value.trim() !== "" && dates[index].value.trim() !== "") {
                hasData = true;
            }
        });

        if (form.querySelector('input[name="log_id"]').value === 'new' && !hasData) {
             alert('Cannot save a new log with zero entries. Please fill at least one Activity and Date pair to start a new log.');
             return;
        }
        


        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
             
             if (!response.ok) {
               
                return response.text().then(text => { 
                    try {
                        
                        const jsonError = JSON.parse(text);
                        throw new Error(jsonError.message || 'Server error occurred.');
                    } catch (e) {
                       
                        throw new Error('Server returned invalid data (likely HTML error): ' + text.substring(0, 100) + '...'); 
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            const logIdInput = form.querySelector('input[name="log_id"]');
            const saveButton = document.querySelector('.btn-save');
            
            if (data.success) {
               
                alert('Success! ' + data.message); 
                saveButton.style.backgroundColor = '#28a745'; // Green
                
                const wasNewRecord = logIdInput.value === 'new';
                
                
                if (wasNewRecord && data.log_id) {
                    logIdInput.value = data.log_id;
                    
                    
                    window.location.href = 'student_dashboard.php?view=saved_docs';
                }
                
                

            } else {
                alert('Error: ' + data.message);
                saveButton.style.backgroundColor = '#dc3545'; // Red
            }
        })
        .catch(error => {
            console.error('Save failed:', error);
            alert('An unexpected error occurred while saving the data: ' + error.message);
        });
    }
</script>