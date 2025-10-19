<?php
session_start();
include("connections.php");

// Initialize variables to avoid "undefined" warnings
$Tracking_Number   = '';
$Complaint_Status  = '';
$Created_At        = null;
$Progress_Date     = null;
$Resolved_Date     = null;

// Helper: safe format date or return empty
function formatDateSafe($dateStr, $format = "F j, Y, g:i a") {
    if (!$dateStr || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') return '';
    $ts = strtotime($dateStr);
    if ($ts === false) return '';
    return date($format, $ts);
}

// Determine lookup key: Complaint_ID, tracking number in session, or query param ?tn
$byId = null;
$byTracking = null;

if (isset($_SESSION["Complaint_ID"])) {
    $byId = intval($_SESSION["Complaint_ID"]);
} elseif (isset($_SESSION["Complaint_TrackingNumber"])) {
    $byTracking = trim($_SESSION["Complaint_TrackingNumber"]);
} elseif (isset($_GET['tn'])) {
    $byTracking = trim($_GET['tn']);
}

// If no key present, redirect home
if ($byId === null && (!$byTracking || $byTracking === '')) {
    header("Location: index.php");
    exit();
}

// Fetch the complaint row
$row = null;
if ($byId !== null) {
    $sql = "SELECT Complaint_ID, Complaint_TrackingNumber, Complaint_Status, Created_At, Progress_Date, Resolved_Date
            FROM complaint WHERE Complaint_ID = ? LIMIT 1";
    if ($stmt = mysqli_prepare($connections, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $byId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
} else {
    $sql = "SELECT Complaint_ID, Complaint_TrackingNumber, Complaint_Status, Created_At, Progress_Date, Resolved_Date
            FROM complaint WHERE Complaint_TrackingNumber = ? LIMIT 1";
    if ($stmt = mysqli_prepare($connections, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $byTracking);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

// If not found, redirect home
if (!$row) {
    header("Location: index.php");
    exit();
}

// Populate variables safely
$Tracking_Number  = $row['Complaint_TrackingNumber'] ?? '';
$Complaint_Status = strtolower($row['Complaint_Status'] ?? '');
$Created_At       = $row['Created_At'] ?? null;
$Progress_Date    = $row['Progress_Date'] ?? null;
$Resolved_Date    = $row['Resolved_Date'] ?? null;

// Pre-format display dates
$complaintReceived   = formatDateSafe($Created_At, "F j, Y, g:i a");
$complaintInProgress = formatDateSafe($Progress_Date, "F j, Y");
$complaintResolved   = formatDateSafe($Resolved_Date, "F j, Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Submitted - eReklamo</title>
    <link rel="stylesheet" href="tracking_page_design.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img class="ereklamo-logo" src="logos/eReklamo_White.png" />
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="success-wrapper">
                <!-- Success Animation -->
                <div class="success-animation">
                    <div class="checkmark-circle">
                        <svg class="checkmark" viewBox="0 0 52 52">
                            <circle class="checkmark-circle-bg" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                </div>

                <div class="success-card">
                    <h2 class="success-title">Complaint Submitted Successfully!</h2>
                    <p class="success-description">
                        Your complaint has been received and is being processed. 
                        Please save your tracking number to monitor the status of your complaint.
                    </p>

                    <!-- Tracking Number Display -->
                    <div class="tracking-container">
                        <p class="tracking-label">Your Tracking Number</p>
                        <div class="tracking-number-box">
                            <span class="tracking-number" id="trackingNumber"><?php echo htmlspecialchars($Tracking_Number); ?></span>
                            <button class="copy-button" onclick="copyTrackingNumber()" title="Copy to clipboard">
                                <svg id="copyIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="next-steps">
                        <h3 class="next-steps-title">
                            <svg class="title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            What happens next?
                        </h3>
                        <ul class="steps-list">
                            <li>
                                <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Our team will review your complaint within 24-48 hours</span>
                            </li>
                            <li>
                                <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>You will receive updates on the status via your chosen notification method</span>
                            </li>
                            <li>
                                <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Use your tracking number to check the status anytime</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Status Timeline Preview -->
                    <div class="timeline-preview">
                        <h3 class="timeline-title">Complaint Status Timeline</h3>
                        <div class="timeline">
                            <?php if ($Complaint_Status) {
                                if ($Complaint_Status === "pending") {
                                    echo '
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Complaint Received</strong>
                                                <span>' . htmlspecialchars($complaintReceived ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <div class="marker-dot"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Under Review</strong>
                                                <span>Pending</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <div class="marker-dot"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>In Progress</strong>
                                                <span>Pending</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <div class="marker-dot"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Resolved</strong>
                                                <span>Pending</span>
                                            </div>
                                        </div>
                                    ';
                                } elseif ($Complaint_Status === "in-progress") {
                                    echo '
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Complaint Received</strong>
                                                <span>' . htmlspecialchars($complaintReceived ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Under Review</strong>
                                                <span>' . htmlspecialchars($complaintInProgress ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <div class="marker-dot"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>In Progress</strong>
                                                <span>' . htmlspecialchars($complaintInProgress ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <div class="marker-dot"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Resolved</strong>
                                                <span>Pending</span>
                                            </div>
                                        </div>
                                    ';
                                } elseif ($Complaint_Status === "resolved") {
                                    echo '
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Complaint Received</strong>
                                                <span>' . htmlspecialchars($complaintReceived ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Under Review</strong>
                                                <span>' . htmlspecialchars($complaintInProgress ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>In Progress</strong>
                                                <span>' . htmlspecialchars($complaintInProgress ?: '—') . '</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="timeline-content">
                                                <strong>Resolved</strong>
                                                <span>' . htmlspecialchars($complaintResolved ?: '—') . '</span>
                                            </div>
                                        </div>
                                    ';
                                } else {
                                    echo '<center><h1>Unlisted Complaint</h1><p>Your complaint may be rejected or archived.</p><i>Please submit a new complaint.</i></center>';
                                }
                            } else {
                                echo '<center><p>Unable to determine complaint status.</p></center>';
                            } ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="window.location.href='add_complaint'">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8z"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Submit Another Complaint
                        </button>
                        <?php
                        $User_ID = isset($_SESSION["User_ID"]) ? intval($_SESSION["User_ID"]) : 1; // 1 = Guest
                        if ($User_ID === 1) {
                            echo '
                            <button class="btn btn-outline" onclick="window.location.href=\'index\'">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Back to Home
                            </button>
                            ';
                        } else {
                            echo '
                            <button class="btn btn-outline" onclick="window.location.href=\'user/user_dashboard\'">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Back to Dashboard
                            </button>
                            ';
                        }
                        ?>
                    </div>

                    <div class="info-box">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <p>
                            <strong>Need help?</strong> Contact our support team at support@ereklamo.com or call +63 912 345 6789
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="tracking_page.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($alert)): ?>
    <script>
        <?php if ($alert == "empty"): ?>
        Swal.fire({ title: 'Error!', text: 'Please enter a tracking number.', icon: 'error', confirmButtonText: 'OK' });
        <?php elseif ($alert == "found"): ?>
        Swal.fire({ title: 'Success!', text: 'Complaint found! Redirecting...', icon: 'success', showConfirmButton: false, timer: 1500 })
        .then(() => { window.location.href = 'tracking_page'; });
        <?php elseif ($alert == "notfound"): ?>
        Swal.fire({ title: 'Not Found!', text: 'No complaint found with that tracking number.', icon: 'warning', confirmButtonText: 'OK' });
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>