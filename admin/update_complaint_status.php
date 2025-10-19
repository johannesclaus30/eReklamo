<?php
// ======================================================================
// update_complaint_status.php
// Updates the complaint status and (optionally) stamps date columns
// Accepts JSON: { "id": <int>, "status": "pending|in-progress|resolved|rejected|archived" }
// Resilient to missing columns like Pending_Date (e.g., if you've dropped it).
// ======================================================================

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

// Read JSON or fallback to form-encoded POST
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

// Helper: check if a column exists in a table
function columnExists($conn, $table, $column) {
    $tableEsc = mysqli_real_escape_string($conn, $table);
    $columnEsc = mysqli_real_escape_string($conn, $column);
    // INFORMATION_SCHEMA is portable across MySQL/MariaDB
    $sql = "
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '$tableEsc'
          AND COLUMN_NAME = '$columnEsc'
        LIMIT 1
    ";
    $res = mysqli_query($conn, $sql);
    if (!$res) return false;
    $exists = mysqli_num_rows($res) > 0;
    mysqli_free_result($res);
    return $exists;
}

$hasProgress = columnExists($connections, 'complaint', 'Progress_Date');
$hasResolved = columnExists($connections, 'complaint', 'Resolved_Date');

// Build SQL dynamically based on which columns exist
$sql = "UPDATE complaint SET Complaint_Status = ?";
$types = "s";
$params = [$status];

if ($hasProgress) {
    $sql .= ", Progress_Date = CASE WHEN ? = 'in-progress' THEN CURDATE() ELSE Progress_Date END";
    $types .= "s";
    $params[] = $status;
}
if ($hasResolved) {
    $sql .= ", Resolved_Date = CASE WHEN ? = 'resolved' THEN CURDATE() ELSE Resolved_Date END";
    $types .= "s";
    $params[] = $status;
}

$sql .= " WHERE Complaint_ID = ?";
$types .= "i";
$params[] = $id;

$stmt = mysqli_prepare($connections, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($connections)]);
    exit;
}

// Use PHP 8+ spread to bind dynamic params
if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'bind_param failed: ' . mysqli_error($connections)]);
    mysqli_stmt_close($stmt);
    exit;
}

$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}

mysqli_stmt_close($stmt);
echo json_encode(['success' => true]);