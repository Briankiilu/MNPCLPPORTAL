<?php

require_once 'dbconnect.php'; 

$student_adm = $_SESSION['student_adm'] ?? null; 


$week_words = [
    1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty'
];

if (empty($student_adm) || !isset($connection) || !$connection) {
    echo '<div class="alert-danger">Error: Student admission number or database connection missing. Cannot display saved documents.</div>';
    return; 
}

$safe_adm = mysqli_real_escape_string($connection, $student_adm);


$logs_query = "SELECT 
                   log_id, 
                   MIN(log_date) AS start_date,
                   MAX(log_date) AS end_date,
                   COUNT(id) AS entry_count
               FROM mentoring_logs 
               WHERE student_adm = '$safe_adm' 
               GROUP BY log_id 
               ORDER BY log_id DESC";
               
$logs_result = mysqli_query($connection, $logs_query);

?>

<div class="saved-documents-container">
    <h2><i class="fas fa-folder-open"></i> Saved Weekly Mentoring Logs</h2>
    <p>Below is a list of all your previously saved weekly mentoring tool submissions. you can only print once all 5 days are filled.</p>
    
    <?php if (!$logs_result): ?>
        <div class="alert-danger">Database Error: Could not retrieve logs. Please ensure the `mentoring_logs` table exists. <?php echo mysqli_error($connection); ?></div>
    <?php elseif (mysqli_num_rows($logs_result) == 0): ?>
        <div class="alert-info">You have not saved any weekly mentoring logs yet.</div>
    <?php else: ?>
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Week</th> <th>Period Covered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = mysqli_fetch_assoc($logs_result)): 
                    $log_id = (int)$log['log_id'];
                    $week_label = $week_words[$log_id] ?? $log_id; 
                    
                    
                    $entry_count = (int)$log['entry_count'];
                    $is_complete = $entry_count === 5;
                    
                    
                    $print_class = $is_complete ? 'btn-print' : 'disabled-print';
                    $print_onclick = $is_complete ? '' : "alert('Cannot print: The weekly log is incomplete. Please ensure all 5 days are filled and saved.'); return false;";
                    $print_title = $is_complete ? 'Print this log document.' : 'Must complete all 5 days (' . $entry_count . '/5) before printing.';
                    $status_text = $is_complete 
                        ? '<span class="status-complete">Complete (Ready for Print)</span>' 
                        : '<span class="status-pending">Pending (' . $entry_count . '/5 entries)</span>';

                    
                    $start_date = date('d M Y', strtotime($log['start_date']));
                    $end_date = date('d M Y', strtotime($log['end_date']));
                    $period = $start_date . ' - ' . $end_date;
                ?>
                <tr>
                    <td><?php echo $week_label; ?></td> <td><?php echo $period; ?></td>
                    <td><?php echo $status_text; ?></td>
                    <td class="action-links">
                        <a href="student_dashboard.php?view=mentoring&log_id=<?php echo $log_id; ?>" class="btn-action btn-edit">Edit/View</a>
                        
                        <a href="student_dashboard.php?view=mentoring&log_id=<?php echo $log_id; ?>&print=true" 
                           class="btn-action <?php echo $print_class; ?>" 
                           onclick="<?php echo $print_onclick; ?>" 
                           title="<?php echo $print_title; ?>"
                        >
                            <i class="fas fa-print"></i> Print
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>

.saved-documents-container {
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.h2{
    color: green;
}
.logs-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.logs-table th, .logs-table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}
.logs-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
}
.action-links {
    display: flex;
    gap: 8px;
}
.btn-action {
    display: inline-block;
    padding: 6px 10px;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.2s, opacity 0.2s;
    font-weight: normal;
    font-size: 0.9em;
    pointer-events: auto; /* Default state */
}
.btn-edit {
    background-color: #007bff;
}
.btn-edit:hover {
    background-color: #0056b3;
}
.btn-print {
    background-color: #6c757d; /* Gray for print (Active) */
}
.btn-print:hover {
    background-color: #5a6268;
}


.disabled-print {
    background-color: #ccc !important; 
    cursor: not-allowed;
    opacity: 0.6;
    pointer-events: auto; 
}


.status-complete {
    color: #28a745;
    font-weight: bold;
}
.status-pending {
    color: red;
    font-weight: bold;
}
.alert-danger, .alert-info {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-weight: bold;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>