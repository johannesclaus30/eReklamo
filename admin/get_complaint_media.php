<?php
// ======================================================================
// get_complaint_media.php
// Returns attached media for a complaint.
// Enforces viewing rule: either one video (if present) OR up to 5 images.
// Response:
// { success: true, type: "video", url: "post_videos/..." }
// { success: true, type: "images", urls: ["post_photos/..."] }
// { success: true, type: "none", urls: [] } when no media
// ======================================================================

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

$complaintId = isset($_GET['complaint_id']) ? intval($_GET['complaint_id']) : 0;
if ($complaintId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid complaint_id']);
    exit;
}

$sql = "
    SELECT File_Path AS path, LOWER(File_Type) AS type
    FROM complaint_media
    WHERE Complaint_ID = ?
    ORDER BY Upload_Date ASC, Complaint_Media_ID ASC
";
$stmt = mysqli_prepare($connections, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($connections)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $complaintId);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}
mysqli_stmt_close($stmt);

if (empty($rows)) {
    echo json_encode(['success' => true, 'type' => 'none', 'urls' => []]);
    exit;
}

// Partition by type
$images = [];
$video = null;

// Detect via File_Type first; fallback to extension
foreach ($rows as $r) {
    $path = $r['path'];
    $type = $r['type'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    $isVideo = in_array($type, ['mp4','mov','webm','avi']) || in_array($ext, ['mp4','mov','webm','avi']);
    $isImage = in_array($type, ['jpg','jpeg','png','gif','webp']) || in_array($ext, ['jpg','jpeg','png','gif','webp']);

    if ($isVideo && $video === null) {
        $video = $path; // take the first video
    } elseif ($isImage) {
        $images[] = $path;
    }
}

// If any video, prefer it over images (as per rule)
if ($video !== null) {
    echo json_encode(['success' => true, 'type' => 'video', 'url' => $video]);
    exit;
}

// Else send up to 5 images
$images = array_slice($images, 0, 5);
echo json_encode(['success' => true, 'type' => 'images', 'urls' => $images]);