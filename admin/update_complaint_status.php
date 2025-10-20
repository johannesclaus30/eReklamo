<?php
// ======================================================================
// update_complaint_status.php
// Robust status update endpoint that is resilient to Linux case-sensitivity
// differences and avoids JSON response corruption.
// ======================================================================

// Avoid emitting notices/warnings into JSON
@ini_set('display_errors', '0');

// Start buffering so any accidental output from includes can be cleared
if (!headers_sent()) { @ob_start(); }

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

// Clear anything that might have been echoed by includes
if (function_exists('ob_get_length') && ob_get_length() !== false) { @ob_clean(); }

// Read JSON, fallback to form POST
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { $data = $_POST; }

$id = isset($data['id']) ? intval($data['id']) : 0;
$statusRaw = isset($data['status']) ? trim((string)$data['status']) : '';

if ($id <= 0 || $statusRaw === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id or status']);
    exit;
}

// Normalize status: accept spaces/underscores/case variants
$normalized = strtolower(preg_replace('/[\s_]+/', '-', $statusRaw));
if ($normalized === 'archive') $normalized = 'archived';

$allowed = ['pending','in-progress','resolved','rejected','archived'];
if (!in_array($normalized, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported status value: ' . $statusRaw]);
    exit;
}

// Helpers that resolve actual table/column names with case-insensitive lookups
function resolveTableName($conn, $want) {
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND LOWER(TABLE_NAME) = LOWER(?)
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return $want;
    mysqli_stmt_bind_param($stmt, "s", $want);
    if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return $want; }
    mysqli_stmt_bind_result($stmt, $found);
    $name = $want;
    if (mysqli_stmt_fetch($stmt)) $name = $found;
    mysqli_stmt_close($stmt);
    return $name ?: $want;
}

function resolveColumnName($conn, $table, $want) {
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND LOWER(COLUMN_NAME) = LOWER(?)
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return $want;
    mysqli_stmt_bind_param($stmt, "ss", $table, $want);
    if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return $want; }
    mysqli_stmt_bind_result($stmt, $found);
    $name = $want;
    if (mysqli_stmt_fetch($stmt)) $name = $found;
    mysqli_stmt_close($stmt);
    return $name ?: $want;
}

$table = resolveTableName($connections, 'complaint');

// Resolve columns (case-insensitive). Falls back to requested names if not found.
$colStatus   = resolveColumnName($connections, $table, 'Complaint_Status');
$colId       = resolveColumnName($connections, $table, 'Complaint_ID');
$colProgress = resolveColumnName($connections, $table, 'Progress_Date');
$colResolved = resolveColumnName($connections, $table, 'Resolved_Date');

// Check existence of optional stamp columns again, safely
function columnActuallyExists($conn, $table, $column) {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return false; }
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);
    return $exists;
}

$hasProgress = columnActuallyExists($connections, $table, $colProgress);
$hasResolved = columnActuallyExists($connections, $table, $colResolved);

// Build the UPDATE with optional date stamping, quoting identifiers
$sql = "UPDATE `{$table}` SET `{$colStatus}` = ?";
$types = "s";
$params = [$normalized];

if ($hasProgress) {
    $sql .= ", `{$colProgress}` = CASE WHEN ? = 'in-progress' THEN CURDATE() ELSE `{$colProgress}` END";
    $types .= "s";
    $params[] = $normalized;
}
if ($hasResolved) {
    $sql .= ", `{$colResolved}` = CASE WHEN ? = 'resolved' THEN CURDATE() ELSE `{$colResolved}` END";
    $types .= "s";
    $params[] = $normalized;
}

$sql .= " WHERE `{$colId}` = ?";
$types .= "i";
$params[] = $id;

$stmt = mysqli_prepare($connections, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($connections)]);
    exit;
}

// Bind with references for call_user_func_array
$bindArgs = [];
$bindArgs[] = $stmt;
$bindArgs[] = $types;
foreach ($params as $k => $v) {
    $bindArgs[] = &$params[$k];
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

echo json_encode([
    'success' => true,
    'id' => $id,
    'status' => $normalized,
    'rowsAffected' => $affected
], JSON_UNESCAPED_UNICODE);