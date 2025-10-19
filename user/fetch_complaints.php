<?php
session_start();
include("../connections.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["User_ID"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$User_ID = $_SESSION["User_ID"];

// Pending_Date was removed. Use only existing columns for lastUpdated.
$query = "
    SELECT 
        c.Complaint_ID AS id,
        c.Complaint_TrackingNumber AS trackingNumber,
        c.Complaint_Category AS category,
        c.Complaint_SubCategory AS subcategory,
        c.Complaint_Description AS description,
        c.Complaint_Status AS status,
        c.Created_At AS dateSubmitted,
        COALESCE(c.Resolved_Date, c.Progress_Date, c.Created_At) AS lastUpdated,
        CONCAT(l.Complaint_Barangay, ', ', l.Complaint_City) AS location
    FROM complaint c
    LEFT JOIN complaint_location l
        ON c.Complaint_Location_ID = l.Complaint_Location_ID
    WHERE c.User_ID = ?
    ORDER BY c.Created_At DESC
";

$stmt = $connections->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Prepare failed: " . $connections->error]);
    exit();
}

$stmt->bind_param("i", $User_ID);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    $stmt->close();
    exit();
}

$result = $stmt->get_result();
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "get_result failed: " . $stmt->error]);
    $stmt->close();
    exit();
}

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $row['status'] = strtolower($row['status'] ?? '');
    $complaints[] = $row;
}

$stmt->close();

echo json_encode($complaints, JSON_UNESCAPED_UNICODE);