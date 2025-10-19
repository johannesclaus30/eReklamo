<?php
session_start();
include("connections.php");

if (!isset($_SESSION["User_ID"])) {
    header("Location: ../login");
    exit("User not logged in");
}

$User_ID = $_SESSION["User_ID"];

$User_FirstName = $User_LastName = $User_Email = $User_PhoneNumber = $User_Region_Name = $User_Province_Name = $User_City_Name = $User_Barangay_Name = $User_Street = $User_HouseNo = $User_ZIP = "";
$User_Region_Code = $User_Province_Code = $User_City_Code = $User_Barangay_Code = "";

$User_FirstNameErr = $User_LastNameErr = $User_EmailErr = $User_PhoneNumberErr = $User_RegionErr = $User_ProvinceErr = $User_CityErr = $User_BarangayErr = $User_StreetErr = $User_HouseNoErr = $User_ZIPErr = "";
$Current_PasswordErr = $New_PasswordErr = $Confirm_PasswordErr = "";

// Fetch user data
if (isset($_SESSION["User_ID"])) {

    $User_ID = $_SESSION["User_ID"];

    $stmt = mysqli_prepare($connections, "SELECT User_FirstName, User_LastName, User_Email, User_PhoneNumber FROM user WHERE User_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $User_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $User_FirstName = $row['User_FirstName'];
        $User_LastName = $row['User_LastName'];
        $User_Email = $row['User_Email'];
        $User_PhoneNumber = $row['User_PhoneNumber'];
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($connections, "SELECT User_Region, User_Province, User_City, User_Barangay, User_Street, User_HouseNo, User_ZIP FROM user_address WHERE User_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $User_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $User_Region_Name = $row['User_Region'];
        $User_Province_Name = $row['User_Province'];
        $User_City_Name = $row['User_City'];
        $User_Barangay_Name = $row['User_Barangay'];
        $User_Street = $row['User_Street'];
        $User_HouseNo = $row['User_HouseNo'];
        $User_ZIP = $row['User_ZIP'];
    }
    mysqli_stmt_close($stmt);

    // Fetch codes for pre-filling dropdowns (you may need to map names to codes)
    $User_Region_Code = ($User_Region_Name === 'NATIONAL CAPITAL REGION (NCR)') ? '13' : ''; // Adjust based on region.json
    $User_Province_Code = ($User_Province_Name === 'NCR') ? '137400000' : '';
    $User_City_Code = ($User_City_Name === 'QUEZON CITY') ? '137404000' : '';
    $User_Barangay_Code = ($User_Barangay_Name === 'BAGONG PAG-ASA') ? '137404010' : ''; // Adjust based on barangay.json
}

// Handle Personal Info Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'personal') {
    $User_FirstName = trim($_POST["User_FirstName"] ?? '');
    $User_LastName = trim($_POST["User_LastName"] ?? '');
    $User_Email = trim($_POST["User_Email"] ?? '');
    $User_PhoneNumber = trim($_POST["User_PhoneNumber"] ?? '');

    if ($User_FirstName === '') { $User_FirstNameErr = "First Name is required"; }
    if ($User_LastName === '') { $User_LastNameErr = "Last Name is required"; }
    if ($User_Email === '') { $User_EmailErr = "Email is required"; }
    if ($User_FirstName && !preg_match("/^[a-zA-Z-' ]*$/", $User_FirstName)) {
        $User_FirstNameErr = "Only valid characters are allowed!";
    }
    if ($User_LastName && !preg_match("/^[a-zA-Z-' ]*$/", $User_LastName)) {
        $User_LastNameErr = "Only valid characters are allowed!";
    }
    if ($User_LastName && strlen($User_LastName) === 1) {
        $User_LastNameErr = "At least 2 characters required";
    }
    if ($User_Email && !filter_var($User_Email, FILTER_VALIDATE_EMAIL)) {
        $User_EmailErr = "Invalid email format";
    }

    if (!$User_FirstNameErr && !$User_LastNameErr && !$User_EmailErr) {
        $stmt = mysqli_prepare($connections, "UPDATE user SET User_FirstName = ?, User_LastName = ?, User_Email = ?, User_PhoneNumber = ? WHERE User_ID = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $User_FirstName, $User_LastName, $User_Email, $User_PhoneNumber, $User_ID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<script>alert('Personal information updated successfully!');</script>";
    } else {
        echo "<script>alert('Please correct the errors in the form.');</script>";
    }
} else {
    echo "<script>console.log('Form type: " . ($_POST['form_type'] ?? 'none') . "');</script>";
}

// Handle Address Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'address') {
    $User_Region_Name = trim($_POST["User_Region_Name"] ?? '');
    $User_Province_Name = trim($_POST["User_Province_Name"] ?? '');
    $User_City_Name = trim($_POST["User_City_Name"] ?? '');
    $User_Barangay_Name = trim($_POST["User_Barangay_Name"] ?? '');
    $User_Street = trim($_POST["User_Street"] ?? '');
    $User_HouseNo = trim($_POST["User_HouseNo"] ?? '');
    $User_ZIP = trim($_POST["User_ZIP"] ?? '');

    if ($User_Region_Name === '') { $User_RegionErr = "Region is required"; }
    if ($User_Province_Name === '') { $User_ProvinceErr = "Province is required"; }
    if ($User_City_Name === '') { $User_CityErr = "City is required"; }
    if ($User_Barangay_Name === '') { $User_BarangayErr = "Barangay is required"; }
    if ($User_Street === '') { $User_StreetErr = "Street is required"; }

    if (!$User_RegionErr && !$User_ProvinceErr && !$User_CityErr && !$User_BarangayErr && !$User_StreetErr) {
        $stmt = mysqli_prepare($connections, "UPDATE user_address SET User_Region = ?, User_Province = ?, User_City = ?, User_Barangay = ?, User_Street = ?, User_HouseNo = ?, User_ZIP = ? WHERE User_ID = ?");
        mysqli_stmt_bind_param($stmt, "sssssssi", $User_Region_Name, $User_Province_Name, $User_City_Name, $User_Barangay_Name, $User_Street, $User_HouseNo, $User_ZIP, $User_ID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<script>alert('Address updated successfully!');</script>";
    }
}

// Handle Password Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'password') {

    $stmt = mysqli_prepare($connections, "SELECT User_Password FROM user WHERE User_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $User_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $currentPassword = $_POST["currentPassword"] ?? '';
    $newPassword = $_POST["newPassword"] ?? '';
    $confirmPassword = $_POST["confirmPassword"] ?? '';

    if (empty($currentPassword) || !password_verify($currentPassword, $row['User_Password'])) {
        echo "<script>alert('Incorrect or missing current password. Please try again.');</script>";
    }

    if ($newPassword === '') { $New_PasswordErr = "New password is required"; }
    if ($confirmPassword === '') { $Confirm_PasswordErr = "Confirm password is required"; }
    if ($newPassword && strlen($newPassword) < 6) {
        $New_PasswordErr = "Password must be at least 6 characters!";
    }
    if ($newPassword !== $confirmPassword) {
        $Confirm_PasswordErr = "Passwords do not match!";
    }

    if (!$Current_PasswordErr && !$New_PasswordErr && !$Confirm_PasswordErr) {
        $stmt = mysqli_prepare($connections, "SELECT User_Password FROM user WHERE User_ID = ?");
        mysqli_stmt_bind_param($stmt, "i", $User_ID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if (password_verify($currentPassword, $row['User_Password'])) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($connections, "UPDATE user SET User_Password = ? WHERE User_ID = ?");
            mysqli_stmt_bind_param($stmt, "si", $hash, $User_ID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo "<script>alert('Password changed successfully!');</script>";
        } else {
            $Current_PasswordErr = "Incorrect current password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - eReklamo</title>
    <link rel="stylesheet" href="account_settings.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h1 class="logo-text">eReklamo</h1>
                </div>
                <a href="user/user_dashboard" class="btn btn-outline">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2 class="page-title">Account Settings</h2>
                <p class="page-description">Manage your account information and preferences</p>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="personal">
                        <svg class="tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Personal Info
                    </button>
                    <button class="tab-btn" data-tab="address">
                        <svg class="tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Address
                    </button>
                    <button class="tab-btn" data-tab="password">
                        <svg class="tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Password
                    </button>
                </div>

                <!-- Personal Information Tab -->
                <div class="tab-content active" id="personal-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Personal Information</h3>
                            <p class="card-description">Update your personal details</p>
                        </div>
                        <div class="card-content">
                            <form id="personalInfoForm" class="settings-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validatePersonalForm()">
                                <input type="hidden" name="form_type" value="personal">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName">First Name *</label>
                                        <input 
                                            type="text" 
                                            id="firstName" 
                                            name="User_FirstName" 
                                            placeholder=""
                                            value="<?php echo $User_FirstName; ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="lastName">Last Name *</label>
                                        <input 
                                            type="text" 
                                            id="lastName" 
                                            name="User_LastName" 
                                            placeholder=""
                                            value="<?php echo $User_LastName; ?>"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="User_Email" 
                                        placeholder=""
                                        value="<?php echo $User_Email; ?>"
                                        required
                                    >
                                    <p class="field-hint">Required for Notifications</p>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input 
                                        type="tel" 
                                        id="phone" 
                                        name="User_PhoneNumber" 
                                        placeholder=""
                                        value="<?php echo $User_PhoneNumber; ?>"
                                    >
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Address Tab -->
                <div class="tab-content" id="address-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Address Information</h3>
                            <p class="card-description">Update your complete address</p>
                        </div>
                        <div class="card-content">
                            <form id="addressForm" class="settings-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validateAddressForm()">
                                <input type="hidden" name="form_type" value="address">

                                <!-- Hidden input fields for text values -->
                                <input type="hidden" name="User_Region_Name" id="User_Region_Name">
                                <input type="hidden" name="User_Province_Name" id="User_Province_Name">
                                <input type="hidden" name="User_City_Name" id="User_City_Name">
                                <input type="hidden" name="User_Barangay_Name" id="User_Barangay_Name">

                            <div class="form-group">
                            <label>Current Address</label>
                            <div class="current-address-box">
                                <strong>
                                <?php
                                    echo htmlspecialchars("$User_HouseNo, $User_Street, $User_Barangay_Name, $User_City_Name, $User_Province_Name, $User_Region_Name, Philippines");
                                ?>
                                </strong>
                            </div>
                            <small style="color:#6c757d;">You can update your address using the dropdowns below.</small>
                            </div>

                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select type="text" id="region" name="User_Region" value="<?php echo $User_Region; ?>">
                              
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="province">Province *</label>
                                <select id="province" name="User_Province" >
                                    <option value="">Select province</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="city">City/Municipality *</label>
                                <select id="city" name="User_City" >
                                    <option value="">Select city/municipality</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="barangay">Barangay *</label>
                                <select id="barangay" name="User_Barangay" >
                                    <option value="">Select barangay</option>
                                </select>
                            </div>

                                <div class="form-group">
                                    <label for="street">Street/Road *</label>
                                    <input 
                                        type="text" 
                                        id="street" 
                                        name="User_Street" 
                                        placeholder="e.g., Rizal Street"
                                        value="<?php echo $User_Street;?>"
                                        required
                                    >
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="houseNumber">House/Building No.</label>
                                        <input 
                                            type="text" 
                                            id="houseNumber" 
                                            name="User_HouseNo" 
                                            placeholder="e.g., 123"
                                            value="<?php echo $User_HouseNo;?>"
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="zipCode">ZIP Code</label>
                                        <input 
                                            type="text" 
                                            id="zipCode" 
                                            name="User_ZIP" 
                                            placeholder="e.g., 1106"
                                            value="<?php echo $User_ZIP;?>"
                                            maxlength="4"
                                            pattern="[0-9]{4}"
                                        >
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Tab -->
                <div class="tab-content" id="password-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Change Password</h3>
                            <p class="card-description">Update your account password</p>
                        </div>
                        <div class="card-content">
                            <form id="passwordForm" class="settings-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validatePasswordForm()">
                                <input type="hidden" name="form_type" value="password">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password *</label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="password" 
                                            id="currentPassword" 
                                            name="currentPassword" 
                                            placeholder="Enter your current password"
                                            required
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('currentPassword', 'eyeIcon1')">
                                            <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword">New Password *</label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="password" 
                                            id="newPassword" 
                                            name="newPassword" 
                                            placeholder="At least 6 characters"
                                            required
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword', 'eyeIcon2')">
                                            <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password *</label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="password" 
                                            id="confirmPassword" 
                                            name="confirmPassword" 
                                            placeholder="Re-enter your new password"
                                            required
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', 'eyeIcon3')">
                                            <svg id="eyeIcon3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <h4 class="info-box-title">Password Requirements:</h4>
                                    <ul class="info-box-list">
                                        <li>• At least 6 characters long</li>
                                        <li>• Should be unique and not easily guessable</li>
                                        <li>• Consider using a mix of letters, numbers, and symbols</li>
                                    </ul>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JQuery for Address Selector -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <!-- Script for Address Selector -->
    <script src="ph-address-selector.js"></script>

    <script>
    function setText(nameSel, hiddenId){
        const opt = document.querySelector(nameSel + " option:checked");
        document.getElementById(hiddenId).value = opt ? opt.text : "";
    }
    ["#region", "#province", "#city", "#barangay"].forEach((sel, i) => {
        const ids = ["User_Region_Name","User_Province_Name","User_City_Name","User_Barangay_Name"];
        document.querySelector(sel)?.addEventListener("change", () => setText(sel, ids[i]));
        // initialize on load too
        setText(sel, ids[i]);
    });
    </script>

    <script src="account_settings.js"></script>
</body>
</html>
