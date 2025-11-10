<?php
session_start();
include("config.php");

// Check if role is set in URL parameter
$role = isset($_GET['role']) ? $_GET['role'] : null;

// Variables for alerts
$alert_type = '';
$alert_message = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Correct table name and column names
    $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Username = ? AND Role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ FIXED: use password_verify for hashed passwords
        if (password_verify($password, $user['Password'])) {
            $_SESSION['accountID'] = $user['AccountID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['role'] = $user['Role'];

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: " . $_SERVER['PHP_SELF'] . "?role=admin&verify=1");
                exit();
            } else {
                header("Location: userdb.php");
                exit();
            }
        } else {
            $alert_type = 'error';
            $alert_message = 'You have entered a wrong password. Please try again.';
        }
    } else {
        $alert_type = 'error';
        $alert_message = 'Username not found for the selected role.';
    }
    $stmt->close();
}

if (isset($_POST['verify'])) {
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Username = ? AND Role = 'admin'");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        $masterKey = "GSC"; // SET PW HERE
        if ($password === $masterKey) {
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'You have successfully logged in as Admin!';
            header("Location: admindb.php");
            exit();
        } else {
            $alert_type = 'error';
            $alert_message = 'Incorrect Master Password!';
        }
    } else {
        $alert_type = 'error';
        $alert_message = 'Admin user not found!';
    }
    $stmt->close();
}

// check for logout notification
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $alert_type = 'success';
    $alert_message = 'You have successfully logged out!';
}

// check for session alerts
if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
    $alert_type = $_SESSION['alert_type'];
    $alert_message = $_SESSION['alert_message'];
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
}

// clear any existing session alerts when accessing the login page directly
if (!isset($_POST['login']) && !isset($_POST['verify']) && !isset($_GET['logout'])) {
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GSC HelpDesk</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body { 
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    background: linear-gradient(135deg, #087830, #4ec66a, #3c4142);
    display: flex; 
    justify-content: center; 
    align-items: center; 
    flex-direction: column;
    min-height: 100vh; 
    margin: 0; 
    text-align: center;
    color: white;
    padding: 20px;
}

.container {
    width: 100%;
    max-width: 400px; 
    min-height: 450px; /* Reduced height */
    padding: 20px 25px; /* Reduced padding */
    background: linear-gradient(135deg, #087830, #3c4142);
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    text-align: center;
    position: relative;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Start from top */
}

/* Specific styling for admin login container */
.container.admin-login {
    min-height: 420px; /* Even smaller for admin */
    padding: 15px 25px 20px; /* Less top padding */
}

.container img {
    width: 130px; 
    height: auto;
    margin: 5px auto 10px; 
}

img { 
    width: 200px; 
    margin-bottom: 30px; 
}

button {
    width: 200px;
    margin: 10px 0;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    font-size: 16px;
    transition: 0.3s;
}

button:hover {
    opacity: 0.8;
}

.back-btn {
    position: absolute;
    top: 15px;
    left: 15px;
    font-size: 20px;
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    width: auto;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    transition: 0.3s;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    opacity: 0.9;
}

.back-btn i {
    font-size: 18px;
}

h2 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 26px; /* Slightly smaller */
    margin-bottom: 20px; /* Reduced margin */
}

/* Smaller heading for admin */
.container.admin-login h2 {
    font-size: 24px;
    margin-bottom: 15px; /* Even less margin */
}

.input-container {
    position: relative;
    width: 100%;
    margin-bottom: 12px; /* Reduced spacing */
}

/* Tighter spacing for admin */
.container.admin-login .input-container {
    margin-bottom: 10px;
}

.input-container i {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    color: #555;
    font-size: 18px;
}

.input-container input {
    width: 100%;
    padding: 12px 45px 12px 40px; /* Added right padding for toggle button */
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #d9d9d9;
    color: #000;
    font-size: 14px;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
}

/* Password toggle button styles */
.password-toggle {
    position: absolute;
    right: 30px;
    top: 30%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #555;
    cursor: pointer;
    width: auto;
    padding: 5px;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: #333;
}

.password-toggle.active i::before {
    content: "\f070"; /* fa-eye-slash */
}

button.login-btn {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    margin-top: 8px; /* Reduced top margin */
    font-size: 16px;
    transition: 0.3s;
}

/* Higher button for admin */
.container.admin-login button.login-btn {
    margin-top: 5px;
}

.toggle {
    margin-top: 15px; /* Reduced margin */
    font-size: 14px;
}

/* Higher toggle for admin */
.container.admin-login .toggle {
    margin-top: 12px;
}

.toggle a {
    color: white;
    text-decoration: underline;
    font-weight: bold;
}

.error {
    color: red;
    font-size: 14px;
    margin-bottom: 15px;
}

.swal2-popup {
    font-size: 1rem !important;
    border-radius: 10px !important;
    padding: 1.5rem !important;
}

.swal2-title {
    font-size: 1.5rem !important;
    margin-bottom: 1rem !important;
}

.swal2-html-container {
    font-size: 1rem !important;
    margin: 1rem 0 !important;
}

.swal2-confirm {
    padding: 0.7rem 2rem !important;
    font-size: 1rem !important;
    border-radius: 5px !important;
}

/* Form wrapper to group form elements */
.form-group {
    margin-top: 10px;
}

/* Higher form group for admin */
.container.admin-login .form-group {
    margin-top: 5px;
}

/* Responsive Design for Different Screen Sizes */

/* For tablets and larger phones */
@media (max-width: 768px) {
    .container {
        max-width: 90%;
        padding: 20px 20px;
        min-height: 420px;
    }
    
    .container.admin-login {
        min-height: 380px;
        padding: 15px 20px 18px;
    }
    
    h2 {
        font-size: 24px;
    }
    
    .container.admin-login h2 {
        font-size: 22px;
        margin-bottom: 12px;
    }
    
    .container img {
        width: 120px;
    }
    
    .container.admin-login img {
        width: 110px;
        margin: 0 auto 3px;
    }
    
    img {
        width: 180px;
        margin-bottom: 25px;
    }
    
    button {
        width: 180px;
        padding: 11px;
        font-size: 15px;
    }
    
    .back-btn {
        font-size: 18px;
        padding: 7px 10px;
    }
    
    .back-btn i {
        font-size: 16px;
    }
    
    .input-container input {
        padding: 10px 40px 10px 35px;
    }
    
    .password-toggle {
        right: 8px;
        font-size: 15px;
    }
}

/* For mobile phones */
@media (max-width: 480px) {
    body {
        padding: 15px;
    }
    
    .container {
        max-width: 100%;
        padding: 18px 15px;
        min-height: 400px;
    }
    
    .container.admin-login {
        min-height: 360px;
        padding: 12px 15px 16px;
    }
    
    h2 {
        font-size: 22px;
        margin-bottom: 15px;
    }
    
    .container.admin-login h2 {
        font-size: 20px;
        margin-bottom: 10px;
    }
    
    .container img {
        width: 110px;
    }
    
    .container.admin-login img {
        width: 100px;
        margin: 0 auto 2px;
    }
    
    img {
        width: 160px;
        margin-bottom: 20px;
    }
    
    .input-container input {
        padding: 10px 35px 10px 35px;
        font-size: 13px;
    }
    
    .input-container i {
        font-size: 16px;
    }
    
    .password-toggle {
        right: 8px;
        font-size: 14px;
    }
    
    button {
        width: 160px;
        padding: 10px;
        font-size: 15px;
    }
    
    button.login-btn {
        padding: 10px;
        font-size: 15px;
    }
    
    .container.admin-login button.login-btn {
        margin-top: 3px;
    }
    
    .toggle {
        font-size: 13px;
    }
    
    .back-btn {
        font-size: 16px;
        padding: 6px 8px;
    }
    
    .back-btn i {
        font-size: 14px;
    }
    
    .form-group {
        margin-top: 8px;
    }
    
    .container.admin-login .form-group {
        margin-top: 3px;
    }
}

/* For very small screens */
@media (max-width: 320px) {
    .container {
        padding: 15px 10px;
        min-height: 380px;
    }
    
    .container.admin-login {
        min-height: 340px;
        padding: 10px 10px 14px;
    }
    
    h2 {
        font-size: 20px;
    }
    
    .container.admin-login h2 {
        font-size: 18px;
        margin-bottom: 8px;
    }
    
    .container img {
        width: 100px;
    }
    
    .container.admin-login img {
        width: 90px;
        margin: 0 auto 0px;
    }
    
    img {
        width: 140px;
        margin-bottom: 15px;
    }
    
    .input-container input {
        padding: 8px 30px 8px 30px;
    }
    
    .input-container i {
        font-size: 14px;
        left: 8px;
    }
    
    .password-toggle {
        right: 6px;
        font-size: 13px;
    }
    
    button {
        width: 140px;
        padding: 8px;
        font-size: 14px;
    }
    
    button.login-btn {
        padding: 8px;
        font-size: 14px;
    }
    
    .back-btn {
        font-size: 14px;
        top: 10px;
        left: 10px;
        padding: 5px 7px;
    }
    
    .back-btn i {
        font-size: 12px;
    }
}

/* For landscape orientation on mobile devices */
@media (max-height: 500px) and (orientation: landscape) {
    body {
        padding: 10px;
    }
    
    .container {
        min-height: auto;
        padding: 12px;
    }
    
    .container.admin-login {
        min-height: auto;
        padding: 10px 12px;
    }
    
    .container img {
        width: 80px;
        margin-bottom: 5px;
    }
    
    .container.admin-login img {
        width: 70px;
        margin: 0 auto 2px;
    }
    
    img {
        width: 120px;
        margin-bottom: 15px;
    }
    
    h2 {
        font-size: 18px;
        margin-bottom: 8px;
    }
    
    .container.admin-login h2 {
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .input-container {
        margin-bottom: 6px;
    }
    
    .container.admin-login .input-container {
        margin-bottom: 4px;
    }
    
    .input-container input {
        padding: 6px 25px 6px 30px;
    }
    
    .password-toggle {
        right: 5px;
        font-size: 13px;
    }
    
    button {
        margin: 3px 0;
    }
    
    .form-group {
        margin-top: 5px;
    }
    
    .container.admin-login .form-group {
        margin-top: 2px;
    }
}

/* Specific adjustments for iPhone XR, 12 Pro, 14, etc. */
@supports (-webkit-touch-callout: none) {
    .input-container input {
        -webkit-appearance: none;
    }
    
    button, button.login-btn {
        -webkit-appearance: none;
    }
}

/* For devices with notches */
@media only screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3),
       only screen and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3),
       only screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2),
       only screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) {
    body {
        padding-top: 40px;
    }
}
</style>
</head>
<body>

<?php
if (!$role) {
?>
    <img src="img/LSULogo.png" alt="LSU Logo">
    <button onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?role=student_staff'">Student/Staff</button>
    <button onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?role=admin'">Admin</button>
<?php
} elseif ($role === 'admin' && isset($_GET['verify'])) {
?>
    <div class="container admin-login">
        <button class="back-btn" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <img src="img/LSULogo.png" alt="Logo">
        <h2>System Verification</h2>
        <p>For added security, please input your Master Password</p>
        <form method="POST">
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="masterPassword" placeholder="Enter Master Password" required>
                <button type="button" class="password-toggle" data-target="masterPassword">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <button type="submit" name="verify">Verify</button>
        </form>
    </div>
<?php
} else {
    $containerClass = ($role === 'admin') ? 'container admin-login' : 'container';
?>
    <div class="<?php echo $containerClass; ?>">
        <button class="back-btn" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <img src="img/LSULogo.png" alt="Logo">
        <h2><?php echo $role === 'admin' ? 'Admin Login' : 'Student/Staff Login'; ?></h2>
        <div class="form-group">
            <form method="POST">
                <div class="input-container">
                    <i class="fa-solid fa-circle-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-container">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                    <button type="button" class="password-toggle" data-target="loginPassword">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <button type="submit" name="login" class="login-btn">Login</button>
            </form>
        </div>
        <?php if ($role === 'student_staff') { ?>
        <div class="toggle">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
        <?php } ?>
    </div>
<?php
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// Password visibility toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.add('active');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('active');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            
            // Focus back on the input for better UX
            passwordInput.focus();
        });
    });

    // Prevent form submission when clicking password toggle buttons
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
        
        button.addEventListener('touchstart', function(e) {
            e.preventDefault();
        });
    });

    // Auto-focus on username field when login form loads
    const usernameInput = document.querySelector('input[name="username"]');
    if (usernameInput) {
        usernameInput.focus();
    }

    // Auto-focus on master password field when verification form loads
    const masterPasswordInput = document.getElementById('masterPassword');
    if (masterPasswordInput) {
        masterPasswordInput.focus();
    }
});

<?php if ($alert_type && $alert_message): ?>
    Swal.fire({
        icon: '<?php echo $alert_type == "success" ? "success" : "error"; ?>',
        title: '<?php echo $alert_type == "success" ? "Success!" : "Error!"; ?>',
        text: '<?php echo $alert_message; ?>',
        confirmButtonColor: '<?php echo $alert_type == "success" ? "#1f9158" : "#d33"; ?>',
        confirmButtonText: 'OK'
    }).then((result) => {
        // Auto-focus on appropriate field after alert is closed
        if (result.isConfirmed) {
            const usernameInput = document.querySelector('input[name="username"]');
            const passwordInput = document.getElementById('loginPassword');
            const masterPasswordInput = document.getElementById('masterPassword');
            
            if (usernameInput && '<?php echo $alert_type; ?>' === 'error') {
                usernameInput.focus();
            } else if (passwordInput && '<?php echo $alert_type; ?>' === 'error') {
                passwordInput.focus();
            } else if (masterPasswordInput && '<?php echo $alert_type; ?>' === 'error') {
                masterPasswordInput.focus();
            }
        }
    });
<?php endif; ?>
</script>

</body>
</html>