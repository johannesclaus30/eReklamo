<?php
session_start();
include("connections.php");

if (isset($_SESSION["User_ID"])) {
    $User_ID = $_SESSION["User_ID"];
    // $get_record = mysqli_query($connections, "SELECT * FROM user WHERE User_ID='$User_ID'");
    // while($row_edit = mysqli_fetch_assoc($get_record)) {
    //     $User_Email = $row_edit["User_Email"];
    // }
    $stmt = mysqli_prepare($connections, "SELECT User_Email FROM user WHERE User_ID = ?");
    $stmt->bind_param("i", $User_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row_edit = $result->fetch_assoc();
    $stmt->close();
    $User_Email = $row_edit["User_Email"];


} else {
    $User_ID = 1; // Guest

    $User_Email = "Guest User";
}

    $target_dir = "post_photos/";
    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0755, true);
    }
    

const MAX_FILES = 5; // Max files per upload request
const MAX_BYTES_PER_FILE = 5 * 1024 * 1024; // 5 MB per file

$Complaint_Location_ID = $Complaint_Category_Name = $Complaint_SubCategory_Name = $Complaint_Description = $Complaint_TrackingNumber = $Complaint_Status = $Complaint_Region_Name = $Complaint_Province_Name = $Complaint_City_Name = $Complaint_Barangay_Name = $Complaint_Street = $Complaint_Landmark = $Complaint_ZIP = "";
$Complaint_CategoryErr = $Complaint_SubCategoryErr = $Complaint_DescriptionErr = $Complaint_RegionErr = $Complaint_ProvinceErr = $Complaint_CityErr = $Complaint_BarangayErr = $Complaint_StreetErr = "";
$success_message = $error_message = "";
$Complaint_ID = $File_Path = $File_Type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $Complaint_Category_Name = $_POST["Complaint_Category_Name"] ?? '';
    $Complaint_SubCategory_Name = $_POST["Complaint_SubCategory_Name"] ?? '';
    $Complaint_Description = $_POST["Complaint_Description"] ?? '';
    $Complaint_Region_Name = $_POST["Complaint_Region_Name"] ?? '';
    $Complaint_Province_Name = $_POST["Complaint_Province_Name"] ?? '';
    $Complaint_City_Name = $_POST["Complaint_City_Name"] ?? '';
    $Complaint_Barangay_Name = $_POST["Complaint_Barangay_Name"] ?? '';
    $Complaint_Street = $_POST["Complaint_Street"] ?? '';
    $Complaint_Landmark = $_POST["Complaint_Landmark"] ?? '';
    $Complaint_ZIP = $_POST["Complaint_ZIP"] ?? '';

    // Validate required fields
    if (empty($Complaint_Category_Name)) {
        $Complaint_CategoryErr = "Category is required!";
    }
    if (empty($Complaint_SubCategory_Name)) {
        $Complaint_SubCategoryErr = "Subcategory is required!";
    }
    if (empty($Complaint_Description)) {
        $Complaint_DescriptionErr = "Description is required!";
    }
    if (empty($Complaint_Region_Name)) {
        $Complaint_RegionErr = "Region is required!";
    }
    if (empty($Complaint_Province_Name)) {
        $Complaint_ProvinceErr = "Province is required!";
    }
    if (empty($Complaint_City_Name)) {
        $Complaint_CityErr = "City/Municipality is required!";
    }
    if (empty($Complaint_Barangay_Name)) {
        $Complaint_BarangayErr = "Barangay is required!";
    }
    if (empty($Complaint_Street)) {
        $Complaint_StreetErr = "Street/Road is required!";
    }

    // Proceed if no validation errors
    if ($Complaint_Category_Name && $Complaint_SubCategory_Name && $Complaint_Description) {
        // Insert into complaint_location
        $stmt = mysqli_prepare($connections, "INSERT INTO complaint_location (Complaint_Region, Complaint_Province, Complaint_City, Complaint_Barangay, Complaint_Street, Complaint_Landmark, Complaint_ZIP) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssss", $Complaint_Region_Name, $Complaint_Province_Name, $Complaint_City_Name, $Complaint_Barangay_Name, $Complaint_Street, $Complaint_Landmark, $Complaint_ZIP);
        mysqli_stmt_execute($stmt);
        $Complaint_Location_ID = mysqli_insert_id($connections);
        mysqli_stmt_close($stmt);

        // Handle subcategory for "Others"
        if ($Complaint_Category_Name == "Others") {
            $Complaint_SubCategory_Name = $_POST["Complaint_OtherSubcategory"] ?? '';
        } else {
            $Complaint_SubCategory_Name = $_POST["Complaint_SubCategory_Name"] ?? '';
        }

        // Insert complaint
        $stmt = mysqli_prepare($connections, "INSERT INTO complaint (User_ID, Complaint_Location_ID, Complaint_Category, Complaint_SubCategory, Complaint_Description, Complaint_TrackingNumber, Complaint_Status, Created_At) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        date_default_timezone_set("Asia/Manila");
        $current_time = date('Y-m-d H:i:s');
        $Complaint_TrackingNumber = "ERK-" . strtoupper(bin2hex(random_bytes(5)));
        $Complaint_Status = "pending";
        mysqli_stmt_bind_param($stmt, "iissssss", $User_ID, $Complaint_Location_ID, $Complaint_Category_Name, $Complaint_SubCategory_Name, $Complaint_Description, $Complaint_TrackingNumber, $Complaint_Status, $current_time);
        mysqli_stmt_execute($stmt);
        $Complaint_ID = mysqli_insert_id($connections); // Store Complaint_ID
        mysqli_stmt_close($stmt);

        $uploadOk = 0;

// === 1️⃣ VIDEO UPLOAD HANDLING ===
if (isset($_FILES['videoInput']) && $_FILES['videoInput']['error'] === UPLOAD_ERR_OK) {
    $videoDir = "post_videos/";
    $videoFile = $_FILES['videoInput'];
    $videoName = basename($videoFile['name']);
    $videoTmpName = $videoFile['tmp_name'];
    $videoSize = $videoFile['size'];
    $videoExt = strtolower(pathinfo($videoName, PATHINFO_EXTENSION));
    $allowedVideoExt = ['mp4', 'mov', 'avi', 'wmv'];

    if (in_array($videoExt, $allowedVideoExt)) {
        if ($videoSize <= 50 * 1024 * 1024) { // 50MB max
            $newVideoName = uniqid('video_', true) . '.' . $videoExt;
            $videoPath = $videoDir . $newVideoName;

            if (move_uploaded_file($videoTmpName, $videoPath)) {
                $insertVideo = $connections->prepare(
                    "INSERT INTO complaint_media (Complaint_ID, File_Path, File_Type, Upload_Date) VALUES (?, ?, ?, NOW())"
                );
                $insertVideo->bind_param("iss", $Complaint_ID, $videoPath, $videoExt);
                $insertVideo->execute();
            }
        } else {
            echo "<script>alert('Video file exceeds 50MB limit.');</script>";
        }
    } else {
        echo "<script>alert('Invalid video format. Allowed: MP4, MOV, AVI, WMV.');</script>";
    }
}

// === 2️⃣ PHOTO UPLOAD HANDLING ===
if (isset($_FILES['photoInput'])) {
    $photoDir = "post_photos/";
    $allowedPhotoExt = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($_FILES['photoInput']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['photoInput']['error'][$key] === UPLOAD_ERR_OK) {
            $photoName = basename($_FILES['photoInput']['name'][$key]);
            $photoExt = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
            $photoSize = $_FILES['photoInput']['size'][$key];

            if (in_array($photoExt, $allowedPhotoExt) && $photoSize <= 10 * 1024 * 1024) {
                $newPhotoName = uniqid('photo_', true) . '.' . $photoExt;
                $photoPath = $photoDir . $newPhotoName;

                if (move_uploaded_file($tmpName, $photoPath)) {
                    $insertPhoto = $connections->prepare(
                        "INSERT INTO complaint_media (Complaint_ID, File_Path, File_Type, Upload_Date) VALUES (?, ?, ?, NOW())"
                    );
                    $insertPhoto->bind_param("iss", $Complaint_ID, $photoPath, $photoExt);
                    $insertPhoto->execute();
                }
            }
        }
    }
}
    $_SESSION["Complaint_ID"] = $Complaint_ID;
    }
}
?>

<style>
    img {
        height: 150px;
    }
</style>


<style>
    .error{
        color:red;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- JQuery for Address Selector -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Submit Complaint - eReklamo</title>
    <link rel="stylesheet" href="add_complaint_design.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
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
                <div class="header-right">
                    <span class="user-status" id="userStatus"><?php echo $User_Email; ?></span>
                    <?php if ($User_Email === "Guest User") {
                        echo '
                            <a href="index" class="btn btn-outline">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                                Back
                            </a>
                        ';
                    } else {
                        echo '
                            <a href="user/user_dashboard" class="btn btn-outline">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                                Back
                            </a>
                        ';
                    }                   
                    ?>
                    
                </div>
            </div>
        </div>
    </header>

    <!-- Progress Indicator -->
    <div class="progress-container">
        <div class="container">
            <div class="progress-steps">
                <div class="progress-step active">
                    <div class="step-circle">1</div>
                    <span class="step-label">Complaint Details</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step active">
                    <div class="step-circle">2</div>
                    <span class="step-label">Upload Evidence</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step active">
                    <div class="step-circle">3</div>
                    <span class="step-label">Review & Submit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Section -->
    <main class="main-content">
        <div class="container">
            <div class="form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Submit a Complaint</h2>
                    <p class="form-description">Fill out the form below to report an issue in your community. All fields marked with * are required.</p>
                </div>

                <form id="complaintForm" class="complaint-form" method="POST" action="add_complaint.php" enctype="multipart/form-data">
                    <!-- Category Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            Category Information
                        </h3>

                        <!-- Hidden input fields for text values -->
                            <input type="hidden" name="Complaint_Category_Name" id="Complaint_Category_Name">
                            <input type="hidden" name="Complaint_SubCategory_Name" id="Complaint_SubCategory_Name">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="Complaint_Category" value="<?php echo $Complaint_Category; ?>" required>
                                    <option value="">Select a category</option>
                                    <option value="infrastructure">Infrastructure</option>
                                    <option value="environment">Environment</option>
                                    <option value="peace_and_order">Peace and Order</option>
                                    <option value="health_and_sanitation">Health and Sanitation</option>
                                    <option value="public_safety">Public Safety</option>
                                    <option value="traffic_and_transportation">Traffic and Transportation</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="subcategory">Subcategory *</label>
                                <select id="subcategory" name="Complaint_SubCategory" value="<?php echo $Complaint_SubCategory; ?>" required disabled>
                                    <option value="">Select a subcategory</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="otherCategoryGroup" style="display: none;">
                            <label for="otherCategory">Please specify your complaint *</label>
                            <input 
                                type="text" 
                                id="otherCategory" 
                                name="Complaint_OtherSubcategory"
                                placeholder="Please describe your concern in brief"
                            >
                            <p class="field-hint">This field is required when "Others" category is selected</p>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea 
                                id="description" 
                                name="Complaint_Description" 
                                rows="5" 
                                placeholder="Describe your complaint in detail. Include as much information as possible to help us understand and resolve the issue."
                                value="<?php echo $Complaint_Description; ?>"
                                required
                            ></textarea>
                            <div class="char-counter">
                                <span id="charCount">0</span> characters
                            </div>
                        </div>

                        <!-- Dynamic Location Dropdowns - Implement cascading logic with Fetch API -->
                        <!-- Hidden input fields for text values -->
                            <input type="hidden" name="Complaint_Region_Name" id="Complaint_Region_Name">
                            <input type="hidden" name="Complaint_Province_Name" id="Complaint_Province_Name">
                            <input type="hidden" name="Complaint_City_Name" id="Complaint_City_Name">
                            <input type="hidden" name="Complaint_Barangay_Name" id="Complaint_Barangay_Name">

                        <div class="form-group">
                            <label for="region">Region *</label>
                            <select id="region" name="Complaint_Region" value="<?php echo $Complaint_Region; ?>" required></select>
                            <p class="field-hint">Select the region</p>
                        </div>

                        <div class="form-group">
                            <label for="province">Province *</label>
                            <select id="province" name="Complaint_Province" value="<?php echo $Complaint_Province; ?>" required></select>
                            <p class="field-hint">Select the province</p>
                        </div>

                        <div class="form-group">
                            <label for="city">City/Municipality *</label>
                            <select id="city" name="Complaint_City" value="<?php echo $Complaint_City; ?>" required></select>
                            <p class="field-hint">Select the city or municipality</p>
                        </div>

                        <div class="form-group">
                            <label for="barangay">Barangay *</label>
                            <select id="barangay" name="Complaint_Barangay" value="<?php echo $Complaint_Barangay; ?>" required></select>
                            <p class="field-hint">Select the barangay</p>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="street">Street/Road *</label>
                                <input 
                                    type="text" 
                                    id="street" 
                                    name="Complaint_Street" 
                                    placeholder="e.g., Main Street"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="landmark">Landmark</label>
                                <input 
                                    type="text" 
                                    id="landmark" 
                                    name="Complaint_Landmark" 
                                    placeholder="e.g., Near Brgy. Hall"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="zipCode">ZIP Code</label>
                            <input 
                                type="text" 
                                id="zipCode" 
                                name="Complaint_ZIP" 
                                placeholder="e.g., 1000"
                                maxlength="4"
                            >
                            <p class="field-hint">Postal code (optional)</p>
                        </div>
                    </div>

                    <!-- Script for Address Selector -->
                    <script src="ph-address-selector.js"></script>

                    <!-- Upload Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Evidence & Attachments
                        </h3>

                        <div class="form-group">
                            <div class="upload-header">
                                <label>
                                    Photos 
                                    <span id="photoCount" class="upload-count">0/5</span>
                                </label>
                                
                            </div>
                            <div class="upload-area" id="photoUpload">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p class="upload-text">Click to upload photos or drag and drop</p>
                                <p class="upload-hint">PNG, JPG up to 10MB each</p>
                                <input type="file" id="photoInput" name="photoInput[]" multiple accept=".jpg,.jpeg,.png,.gif" style="display: none;">
                            </div>
                            <div id="photoPreview" class="preview-grid"></div>
                            <p class="upload-info" id="photoInfo" style="display: none;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="info-icon">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                Click the × button on each photo to remove it
                            </p>
                        </div>

                        <div class="form-group">
                            <div class="upload-header">
                                <label>Video (Maximum 1)</label>
                                
                            </div>
                            <div class="upload-area" id="videoUpload">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                </svg>
                                <p class="upload-text">Click to upload video or drag and drop</p>
                                <p class="upload-hint">MP4, MOV up to 50MB</p>
                                <input type="file" id="videoInput" name="videoInput" accept=".mp4,.mov,.avi,.wmv" style="display:none;">
                            </div>
                            <div id="videoPreview"></div>
                        </div>
                    </div>

                    <!-- Notification Preferences -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            Notification Preferences
                        </h3>

                        <div class="notification-card" id="notificationSection">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="emailUpdates" name="emailUpdates" disabled>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">
                                        <strong>Email Notifications</strong>
                                        <small>Receive updates via email about your complaint status</small>
                                    </span>
                                </label>

                                <label class="checkbox-label">
                                    <input type="checkbox" id="smsUpdates" name="smsUpdates" disabled>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">
                                        <strong>SMS Notifications</strong>
                                        <small>Receive text message updates on your phone</small>
                                    </span>
                                </label>
                            </div>

                            <div class="notification-notice">
                                <svg class="notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <p>
                                    <a href="sign_in" class="link">Sign in</a> to your account to enable email and SMS notifications
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="window.location.href='index'">
                            Cancel
                        </button>
                        <button type="submit" name="btnSubmit" class="btn btn-primary btn-large" id="submitBtn">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="9 11 12 14 22 4"></polyline>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                            Submit Complaint
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="add_complaint2.js"></script>

    <script>
    function setText(nameSel, hiddenId){
        const opt = document.querySelector(nameSel + " option:checked");
        document.getElementById(hiddenId).value = opt ? opt.text : "";
    }
    ["#category", "#subcategory", "#region", "#province", "#city", "#barangay"].forEach((sel, i) => {
        const ids = ["Complaint_Category_Name","Complaint_SubCategory_Name","Complaint_Region_Name","Complaint_Province_Name","Complaint_City_Name","Complaint_Barangay_Name"];
        document.querySelector(sel)?.addEventListener("change", () => setText(sel, ids[i]));
        // initialize on load too
        setText(sel, ids[i]);
    });
    </script>

    <script src="../Admin/js/jQuery.js"></script>

</script>

<!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                <?php if ($alert == "empty"): ?>
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter a tracking number.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                <?php elseif ($alert == "added"): ?>
                Swal.fire({
                    title: 'Success!',
                    text: 'Complaint Submitted! Generating Tracking ID...',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => {
                    window.location.href = 'tracking_page.php';
                });
                <?php elseif ($alert == "notfound"): ?>
                Swal.fire({
                    title: 'Not Found!',
                    text: 'No complaint found with that tracking number.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                <?php endif; ?>
            </script>
        <?php endif; ?>

        </script>


</body>
</html>
