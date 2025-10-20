<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../connections.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (!$connections) {
    echo "ERROR: DB connect failed: " . mysqli_connect_error();
    exit;
}

$res = mysqli_query($connections, "SHOW COLUMNS FROM complaint LIKE 'Complaint_Status'");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    echo "Complaint_Status type: " . ($row['Type'] ?? 'unknown') . PHP_EOL;
}

$sql = "UPDATE complaint SET Complaint_Status = Complaint_Status WHERE 0";
if (mysqli_query($connections, $sql)) {
    echo "OK: UPDATE privilege present (0 rows affected)" . PHP_EOL;
} else {
    echo "ERROR on UPDATE: " . mysqli_error($connections) . PHP_EOL;
}