<?php
// ======================================================================
// update_complaint_status.php (resilient + logs)
// - Maps UI statuses (pending, in-progress, etc.) to DB ENUM values if needed
// - Avoids JSON corruption
// - Logs errors to admin/update_status.log for diagnostics
// ======================================================================

@ini_set('display_errors', '0');
if (!headers_sent()) { @ob_start(); }

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

// Clear any output from includes
if (function_exists('ob_get_length') && ob_get_length() !== false) { @ob_clean(); }

// --- Logging helper (delete the log file after debugging) ---
function us_log($msg) {
    $file = __DIR__ . '/update_status.log';
    $line = '[' . date('c') . '] ' . $msg . PHP_EOL;
    @error_log($line, 3, $file);
}

mysqli_report(MYSQLI_REPORT_OFF);
if (!$connections) {
    http_response_code(500);
    $err = 'DB connect failed: ' . mysqli_connect_error();
    us_log($err);
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
}

// --- Input ---
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id = isset($data['id']) ? intval($data['id']) : 0;
$statusRaw = isset($data['status']) ? trim((string)$data['status']) : '';

if ($id <= 0 || $statusRaw === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id or status']);
    exit;
}

// Normalize to UI key: lowercase + hyphen
$uiKey = strtolower(preg_replace('/[\s_]+/', '-', $statusRaw));
if ($uiKey === 'archive') $uiKey = 'archived';

$uiAllowed = ['pending','in-progress','resolved','rejected','archived'];
if (!in_array($uiKey, $uiAllowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => "Unsupported status value: $statusRaw"]);
    exit;
}

// --- Resolve table/columns (case-insensitive) ---
function resolveTableName($conn, $want) {
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = LOWER(?) LIMIT 1";
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
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND LOWER(COLUMN_NAME) = LOWER(?)
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
$colStatus   = resolveColumnName($connections, $table, 'Complaint_Status');
$colId       = resolveColumnName($connections, $table, 'Complaint_ID');
$colProgress = resolveColumnName($connections, $table, 'Progress_Date');
$colResolved = resolveColumnName($connections, $table, 'Resolved_Date');

// --- If Complaint_Status is ENUM, map to the exact ENUM value ---
function getEnumValues($conn, $table, $column) {
    $sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return [];
    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return []; }
    mysqli_stmt_bind_result($stmt, $ctype);
    $got = mysqli_stmt_fetch($stmt) ? $ctype : '';
    mysqli_stmt_close($stmt);
    // COLUMN_TYPE for ENUM looks like: enum('Pending','In Progress','Resolved',...)
    if (stripos($got, 'enum(') !== 0) return [];
    $inside = substr($got, 5, -1); // strip enum( ... )
    $parts = preg_split("/,(?=(?:[^']*'[^']*')*[^']*$)/", $inside);
    $vals = [];
    foreach ($parts as $p) {
        $v = trim($p);
        if ($v[0] === "'" && substr($v, -1) === "'") $v = substr($v, 1, -1);
        $vals[] = $v;
    }
    return $vals;
}
function normKey($s) { return strtolower(preg_replace('/[\s_]+/', '-', (string)$s)); }

$enumVals = getEnumValues($connections, $table, $colStatus);
$dbStatus = $uiKey;
if (!empty($enumVals)) {
    // Build mapping based on normalized keys
    $map = [];
    foreach ($enumVals as $ev) { $map[normKey($ev)] = $ev; }
    if (!isset($map[$uiKey])) {
        http_response_code(400);
        us_log("Unsupported UI status for ENUM: uiKey=$uiKey; ENUM=" . implode('|', $enumVals));
        echo json_encode(['success' => false, 'error' => "Unsupported status for live database: $statusRaw"]);
        exit;
    }
    $dbStatus = $map[$uiKey];
}

// --- Optional columns existence check ---
function columnExists($conn, $table, $column) {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
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
$hasProgress = columnExists($connections, $table, $colProgress);
$hasResolved = columnExists($connections, $table, $colResolved);

// --- Build UPDATE ---
$sql = "UPDATE `{$table}` SET `{$colStatus}` = ?";
$types = "s";
$params = [$dbStatus];

if ($hasProgress) {
    $sql .= ", `{$colProgress}` = CASE WHEN ? = 'in-progress' THEN CURDATE() ELSE `{$colProgress}` END";
    $types .= "s";
    $params[] = $uiKey; // logic based on normalized UI key
}
if ($hasResolved) {
    $sql .= ", `{$colResolved}` = CASE WHEN ? = 'resolved' THEN CURDATE() ELSE `{$colResolved}` END";
    $types .= "s";
    $params[] = $uiKey;
}

$sql .= " WHERE `{$colId}` = ?";
$types .= "i";
$params[] = $id;

us_log("Attempting update: id=$id uiKey=$uiKey dbStatus=\"$dbStatus\" table=$table cols=[$colStatus,$colId]");

$stmt = mysqli_prepare($connections, $sql);
if (!$stmt) {
    $err = 'Prepare failed: ' . mysqli_error($connections);
    us_log($err);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
}

// Bind with references
$bindArgs = [$stmt, $types];
foreach ($params as $k => $v) { $bindArgs[] = &$params[$k]; }
if (!call_user_func_array('mysqli_stmt_bind_param', $bindArgs)) {
    $err = 'bind_param failed: ' . mysqli_error($connections);
    us_log($err);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $err]);
    mysqli_stmt_close($stmt);
    exit;
}

if (!mysqli_stmt_execute($stmt)) {
    $err = 'Execute failed: ' . mysqli_stmt_error($stmt);
    us_log($err);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $err]);
    mysqli_stmt_close($stmt);
    exit;
}

$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'id' => $id,
    'status' => $uiKey,
    'rowsAffected' => $affected
], JSON_UNESCAPED_UNICODE);