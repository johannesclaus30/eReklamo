<?php
include("connections.php");
$User_ID = $User_FirstName = $User_LastName = $User_Email = $User_PhoneNumber = $User_Password = $User_ConfirmPassword = $User_Type = $User_Region_Name = $User_Province_Name = $User_City_Name = $User_Barangay_Name = $User_Street =  $User_HouseNo = $User_ZIP = "";

$User_FirstNameErr = $User_LastNameErr = $User_EmailErr = $User_PhoneNumberErr = $User_PasswordErr = $User_ConfirmPasswordErr = $User_RegionErr = $User_ProvinceErr = $User_CityErr = $User_BarangayErr = $User_StreetErr =  $User_HouseNoErr  = $User_ZIPErr = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Always read values first
    $User_FirstName       = trim($_POST["User_FirstName"]        ?? '');
    $User_LastName        = trim($_POST["User_LastName"]         ?? '');
    $User_Email           = trim($_POST["User_Email"]            ?? '');
    $User_PhoneNumber     = trim($_POST["User_PhoneNumber"]      ?? '');
    $User_Password        = $_POST["User_Password"]              ?? '';
    $User_ConfirmPassword = $_POST["User_ConfirmPassword"]       ?? '';
    $User_Region_Name          = trim($_POST["User_Region_Name"]           ?? '');
    $User_Province_Name        = trim($_POST["User_Province_Name"]         ?? '');
    $User_City_Name            = trim($_POST["User_City_Name"]             ?? '');
    $User_Barangay_Name        = trim($_POST["User_Barangay_Name"]         ?? '');
    $User_Street               = trim($_POST["User_Street"]           ?? '');
    $User_HouseNo              = trim($_POST["User_HouseNo"]          ?? '');
    $User_ZIP                  = trim($_POST["User_ZIP"]              ?? '');

    // Then validate and set errors
    if ($User_FirstName === '')   { $User_FirstNameErr = "First Name is required"; }
    if ($User_LastName === '')    { $User_LastNameErr  = "Last Name is required"; }
    if ($User_Email === '')       { $User_EmailErr     = "Email is required"; }
    if ($User_Password === '')    { $User_PasswordErr  = "Password is required"; }
    if ($User_Region_Name === '')      { $User_RegionErr    = "Region is required"; }
    if ($User_Province_Name === '')    { $User_ProvinceErr  = "Province is required"; }
    if ($User_City_Name === '')         { $User_CityErr     = "City is required"; }
    if ($User_Barangay_Name === '')     { $User_BarangayErr = "Barangay is required"; }
    if ($User_Street === '')            { $User_StreetErr   = "Street is required"; }

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
    } else {
        // Check for existing email
        $check_email = mysqli_query($connections, "SELECT * FROM user WHERE User_Email = '$User_Email'");
        $check_email_row = mysqli_num_rows($check_email);

        if($check_email_row > 0) {
            $User_EmailErr = "Email is already registered!";
        }
    }

    if ($User_Password && strlen($User_Password) < 6) {
        $User_PasswordErr = "Password must be at least 6 characters!";
    }
    if ($User_Password !== $User_ConfirmPassword) {
        $User_ConfirmPasswordErr = "Password did not match!";
    }

    // Proceed only if there are no errors
    $hasErrors = $User_FirstNameErr || $User_LastNameErr || $User_EmailErr || $User_PasswordErr || $User_ConfirmPasswordErr || $User_RegionErr || $User_ProvinceErr || $User_StreetErr;

    if (!$hasErrors) {
        // IMPORTANT: hash the password
        $hash = password_hash($User_Password, PASSWORD_DEFAULT);

        // Use prepared statements for the user table
        $stmt = mysqli_prepare($connections, "INSERT INTO user (User_FirstName, User_LastName, User_Email, User_PhoneNumber, User_Password, User_Type) VALUES (?, ?, ?, ?, ?, ?)");
        $userType = '2'; // Assuming User_Type is a string or integer
        mysqli_stmt_bind_param($stmt, "ssssss", $User_FirstName, $User_LastName, $User_Email, $User_PhoneNumber, $hash, $userType);
        mysqli_stmt_execute($stmt);

        // Get the last inserted User_ID
        $User_ID = mysqli_insert_id($connections);

        // Use prepared statements for the user_address table
        $stmt = mysqli_prepare($connections, "INSERT INTO user_address (User_ID, User_Region, User_Province, User_City, User_Barangay, User_Street, User_HouseNo, User_ZIP) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssssss", $User_ID, $User_Region_Name, $User_Province_Name, $User_City_Name, $User_Barangay_Name, $User_Street, $User_HouseNo, $User_ZIP);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "<script>alert('New Record has been inserted!');</script>";
        echo "<script>window.location.href='sign_in';</script>";
        exit;
    } else {
        // Optional: avoid echoing a generic "Walang laman." here; rely on field errors instead
        // echo "Walang laman.";
    }
}
?>

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
    <title>Sign Up - eReklamo</title>
    <link rel="stylesheet" href="signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img class="ereklamo-logo" src="logos/eReklamo_White.png" /> 
                </div>
                <a href="index" class="btn btn-outline">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="auth-wrapper">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="auth-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </div>
                        <h2 class="auth-title">Create an Account</h2>
                        <p class="auth-description">Join eReklamo and start making a difference in your community</p>
                    </div>

                    <form id="signUpForm" class="auth-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input 
                                    type="text" 
                                    id="firstName" 
                                    name="User_FirstName" 
                                    placeholder="Juan"
                                    value="<?php echo $User_FirstName; ?>"
                                    required
                                >
                                <span class="error"><?php echo $User_FirstNameErr; ?></span>
                            </div>

                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input 
                                    type="text" 
                                    id="lastName" 
                                    name="User_LastName" 
                                    placeholder="Dela Cruz"
                                    value="<?php echo $User_LastName; ?>"
                                    required
                                >
                                <span class="error"><?php echo $User_LastNameErr; ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="User_Email" 
                                    placeholder="your.email@example.com"
                                    value="<?php echo $User_Email; ?>"   
                                >
                                <span class="error"><?php echo $User_EmailErr; ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number (Optional)</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="User_PhoneNumber" 
                                    placeholder="+63 912 345 6789"
                                    value="<?php echo $User_PhoneNumber; ?>"
                                >
                            </div>
                            <p class="field-hint">Required for SMS notifications</p>
                        </div>

                        <div class="address-section">
                            <h3 class="section-title">Complete Address *</h3>

                            <!-- Hidden input fields for text values -->
                            <input type="hidden" name="User_Region_Name" id="User_Region_Name">
                            <input type="hidden" name="User_Province_Name" id="User_Province_Name">
                            <input type="hidden" name="User_City_Name" id="User_City_Name">
                            <input type="hidden" name="User_Barangay_Name" id="User_Barangay_Name">
                            
                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select type="text" id="region" name="User_Region" value="<?php echo $User_Region; ?>" ></select>
                            </div>

                            <div class="form-group">
                                <label for="province">Province *</label>
                                <select id="province" name="User_Province" value="<?php echo $User_Province; ?>" >
                                    <option value="">Select province</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="city">City/Municipality *</label>
                                <select id="city" name="User_City" value="<?php echo $User_City; ?>" >
                                    <option value="">Select city/municipality</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="barangay">Barangay *</label>
                                <select id="barangay" name="User_Barangay" value="<?php echo $User_Barangay; ?>" >
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
                                    value="<?php echo $User_Street; ?>"
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
                                        value="<?php echo $User_HouseNo; ?>"
                                        placeholder="e.g., 123"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="zipCode">ZIP Code</label>
                                    <input 
                                        type="text" 
                                        id="zipCode" 
                                        name="User_ZIP" 
                                        placeholder="e.g., 1000"
                                        maxlength="4"
                                        pattern="[0-9]{4}"
                                        value="<?php echo $User_ZIP; ?>"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="User_Password" 
                                    placeholder="At least 6 characters"
                                    value="<?php echo $User_Password; ?>"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password', 'eyeIcon1')">
                                    <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password *</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input 
                                    type="password" 
                                    id="confirmPassword" 
                                    name="User_ConfirmPassword" 
                                    placeholder="Re-enter your password"
                                    value="<?php echo $User_ConfirmPassword; ?>"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', 'eyeIcon2')">
                                    <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="checkbox-wrapper">
                            <label class="checkbox-label">
                                <input type="checkbox" id="terms" required>
                                <span class="checkbox-text">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                            Create Account
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="sign_in">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JQuery for Address Selector -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <!-- Script for Address Selector -->
    <script src="ph-address-selector.js"></script>
    
    <script src="sign_up2.js"></script>

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


</body>
</html>
