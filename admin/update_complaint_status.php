<?php
// ======================================================================
// update_complaint_status.php
// Updates complaint status; accepts JSON { id, status } where status is
// one of: pending | in-progress | resolved | rejected | archived
// Compatible with shared hosting PHP (bind_param with references).
// ======================================================================

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

// Read JSON, fallback to form POST
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { $data = $_POST; }

$id = isset($data['id']) ? intval($data['id']) : 0;
$statusRaw = isset($data['status']) ? strtolower(trim($data['status'])) : '';

if ($id <= 0 || $statusRaw === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id or status']);
    exit;
}

// Normalize to lowercase-hyphen keys
$statusKey = preg_replace('/[\s_]+/', '-', $statusRaw);

// Allowed UI keys (your DB column is VARCHAR so these are fine)
$allowed = ['pending','in-progress','resolved','rejected','archived'];
if (!in_array($statusKey, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported status value: ' . $statusRaw]);
    exit;
}

// Helper: check if a column exists (for optional stamping)
function columnExists($conn, $table, $column) {
    $tableEsc = mysqli_real_escape_string($conn, $table);
    $columnEsc = mysqli_real_escape_string($conn, $column);
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

// Build SQL with optional date stamping
$sql = "UPDATE complaint SET Complaint_Status = ?";
$types = "s";
$params = [$statusKey];

if ($hasProgress) {
    $sql .= ", Progress_Date = CASE WHEN ? = 'in-progress' THEN CURDATE() ELSE Progress_Date END";
    $types .= "s";
    $params[] = $statusKey;
}
if ($hasResolved) {
    $sql .= ", Resolved_Date = CASE WHEN ? = 'resolved' THEN CURDATE() ELSE Resolved_Date END";
    $types .= "s";
    $params[] = $statusKey;
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

// Bind with references (shared hosting safe)
$bindArgs = [];
$bindArgs[] = $stmt;
$bindArgs[] = $types;
foreach ($params as $k => $v) {
    $bindArgs[] = &$params[$k]; // pass by reference
}
if (!call_user_func_array('mysqli_stmt_bind_param', $bindArgs)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'bind_param failed: ' . mysqli_error($connections)]);
    mysqli_stmt_close($stmt);
    exit;
}

if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}

$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

// Return success; 0 rows affected is fine if no change
echo json_encode([
    'success' => true,
    'id' => $id,
    'status' => $statusKey,
    'rowsAffected' => $affected
], JSON_UNESCAPED_UNICODE);