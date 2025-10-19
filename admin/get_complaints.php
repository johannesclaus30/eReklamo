<?php
// ======================================================================
// get_complaints.php (Admin Version)
// Fetches ALL complaints with location + user info
// ======================================================================

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include("../connections.php");

// Optional: ensure admin is logged in
// session_start();
// if (!isset($_SESSION['Admin_ID'])) {
//     echo json_encode(["error" => "Unauthorized"]);
//     exit();
// }

$query = "
    SELECT 
        c.Complaint_ID AS id,
        c.Complaint_TrackingNumber AS trackingNumber,
        c.Complaint_Category AS category,
        c.Complaint_SubCategory AS subcategory,
        c.Complaint_Description AS description,
        c.Complaint_Status AS status,
        c.Created_At AS dateSubmitted,
        CONCAT(l.Complaint_Street, ', ', l.Complaint_Barangay, ', ', l.Complaint_City, ', ', l.Complaint_Province, ', ', l.Complaint_Region) AS location,
        l.Complaint_Region AS region,
        l.Complaint_Province AS province,
        l.Complaint_City AS city,
        l.Complaint_Barangay AS barangay,
        CONCAT(u.User_FirstName, ' ', u.User_LastName) AS submittedBy
    FROM complaint c
    INNER JOIN complaint_location l ON c.Complaint_Location_ID = l.Complaint_Location_ID
    INNER JOIN user u ON c.User_ID = u.User_ID
    ORDER BY c.Created_At DESC
";

$result = mysqli_query($connections, $query);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . mysqli_error($connections)]);
    exit;
}

$complaints = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['status'] = strtolower($row['status']);
    $complaints[] = $row;
}

echo json_encode($complaints, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
