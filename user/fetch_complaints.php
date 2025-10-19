<?php
session_start();
include("../connections.php");

if (!isset($_SESSION["User_ID"])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$User_ID = $_SESSION["User_ID"];

$query = "
    SELECT 
        c.Complaint_ID AS id,
        c.Complaint_TrackingNumber AS trackingNumber,
        c.Complaint_Category AS category,
        c.Complaint_SubCategory AS subcategory,
        c.Complaint_Description AS description,
        c.Complaint_Status AS status,
        c.Created_At AS dateSubmitted,
        COALESCE(c.Resolved_Date, c.Progress_Date, c.Pending_Date, c.Created_At) AS lastUpdated,
        CONCAT(l.Complaint_Barangay, ', ', l.Complaint_City) AS location
    FROM complaint c
    LEFT JOIN complaint_location l
        ON c.Complaint_Location_ID = l.Complaint_Location_ID
    WHERE c.User_ID = ?
    ORDER BY c.Created_At DESC
";

$stmt = $connections->prepare($query);
$stmt->bind_param("i", $User_ID);
$stmt->execute();
$result = $stmt->get_result();

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $row['status'] = strtolower($row['status']);
    $complaints[] = $row;
}

header('Content-Type: application/json');
echo json_encode($complaints);
?>
