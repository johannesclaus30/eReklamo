<?php

include("connections.php");

// // Redirect logged-in users
// if (isset($_SESSION["User_Email"])) {
//     $User_Email = $_SESSION["User_Email"];
//     $stmt = mysqli_prepare($connections, "SELECT User_Type FROM user WHERE User_Email = ?");
//     mysqli_stmt_bind_param($stmt, "s", $User_Email);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $user = mysqli_fetch_assoc($result);
//     mysqli_stmt_close($stmt);

//     if ($user) {
//         $account_type = $user["User_Type"];
//         if ($account_type == 1) {
//             header("Location: Admin");
//             exit;
//         } else {
//             header("Location: User/user_dashboard");
//             exit;
//         }
//     }
// }

// Initialize variables
$User_Email = $User_Password = "";
$User_EmailErr = $User_PasswordErr = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Read and validate inputs
    $User_Email = trim($_POST["User_Email"] ?? '');
    $User_Password = $_POST["User_Password"] ?? '';

    if (empty($User_Email)) {
        $User_EmailErr = "Email is required";
    }
    if (empty($User_Password)) {
        $User_PasswordErr = "Password is required";
    }

    // Proceed if no validation errors
    if ($User_Email && $User_Password) {
        // Check if email exists using prepared statement
        $stmt = mysqli_prepare($connections, "SELECT User_ID, User_Email, User_Password, User_Type FROM user WHERE User_Email = ?");
        mysqli_stmt_bind_param($stmt, "s", $User_Email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $check_row = mysqli_num_rows($result);

        if ($check_row > 0) {
            $user = mysqli_fetch_assoc($result);
            $db_password = $user["User_Password"];
            $account_type = $user["User_Type"];

            // Redirect based on account type
                if ($account_type == 1) {
                    if($db_password == $User_Password) {
   
                        // Optionally store User_ID or other details in session
                        session_start();
                        $_SESSION["User_ID"] = $user["User_ID"];

                        header("Location: Admin/admin_dashboard");
                    } else {
                        $User_PasswordErr = "Incorrect password!";
                    }

                } else {
                    if (password_verify($User_Password, $db_password)) {
                    
                    session_start();
                    $_SESSION["User_Email"] = $User_Email;
                    // Optionally store User_ID or other details in session
                    $_SESSION["User_ID"] = $user["User_ID"];

                    header("Location: User/user_dashboard");
                } else {
                    $User_PasswordErr = "Incorrect password!";
                }
                
            }
        } else {
            $User_EmailErr = "Email not found!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<style>
    .error{
        color:red;
        margin-top: 5px;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - eReklamo</title>
    <link rel="stylesheet" href="signin.css">
    <link rel="icon" type="image/png" href="logos/eReklamo_Icon.png">
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
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <h2 class="auth-title">Welcome Back</h2>
                        <p class="auth-description">Sign in to your account to continue</p>
                    </div>

                    <form id="signInForm" class="auth-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="User_Email"
                                    value="<?php echo htmlspecialchars($User_Email); ?>"
                                    placeholder="your.email@example.com"
                                    required
                                >
                            </div>
                            <span class="error"><?php echo $User_EmailErr; ?></span>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="User_Password" 
                                    value="<?php echo htmlspecialchars($User_Password); ?>"
                                    placeholder="Enter your password"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <span class="error"><?php echo $User_PasswordErr; ?></span>
                        </div>
                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" id="remember">
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            Sign In
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>OR</span>
                    </div>

                    <button type="button" class="btn btn-outline btn-full" onclick="window.location.href='add_complaint'">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Continue as Guest
                    </button>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="sign_up">Sign Up</a></p>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="sign_in2.js"></script>
</body>
</html>
