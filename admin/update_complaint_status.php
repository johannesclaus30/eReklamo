<?php
// ======================================================================
// update_complaint_status.php
// Updates the complaint status (and stamps relevant date columns)
// Accepts JSON: { "id": <int>, "status": "pending|in-progress|resolved|rejected|archived" }
// ======================================================================

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    $data = $_POST;
}

$id = isset($data['id']) ? intval($data['id']) : 0;
$status = isset($data['status']) ? strtolower(trim($data['status'])) : '';

$allowed = ['pending','in-progress','resolved','rejected','archived'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id or status.']);
    exit;
}

$sql = "
    UPDATE complaint
    SET Complaint_Status = ?,
        Pending_Date   = CASE WHEN ? = 'pending'     THEN CURDATE() ELSE Pending_Date   END,
        Progress_Date  = CASE WHEN ? = 'in-progress' THEN CURDATE() ELSE Progress_Date  END,
        Resolved_Date  = CASE WHEN ? = 'resolved'    THEN CURDATE() ELSE Resolved_Date  END
    WHERE Complaint_ID = ?
";

$stmt = mysqli_prepare($connections, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($connections)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssssi', $status, $status, $status, $status, $id);
$ok = mysqli_stmt_execute($stmt);

if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}

mysqli_stmt_close($stmt);
echo json_encode(['success' => true]);