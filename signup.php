<?php
session_start();
include 'config.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'student_staff';
$roleTitle = ucfirst(str_replace("_", " ", $role));

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match');</script>"; 
    } else {
        $check = $conn->query("SELECT * FROM Accounts WHERE Username='$username'");
        if ($check->num_rows > 0) {
            echo "<script>alert('Username already exists');</script>"; 
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT); // keep hashed passwords
            $conn->query("INSERT INTO Accounts (Name, Username, Password, Role) 
                          VALUES ('$name','$username','$hashed','$role')");
            echo "<script>alert('Registration successful! You can now login'); window.location='login.php?role=$role';</script>"; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student/Staff Sign Up</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body { 
    font-family: 'Poppins', sans-serif; /* ✅ Apply Poppins font */
    font-weight: 600; /* SemiBold */
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
    min-height: 500px;
    padding: 30px 25px; 
    background: linear-gradient(135deg, #087830, #3c4142);
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    text-align: center;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.container img {
    width: 150px; 
    height: auto;
    margin: 0 auto 0px auto;
}

h2 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 15px;
}

.form-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 100%;
}

.input-container {
    position: relative;
    width: 100%;
    margin-bottom: 12px;
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
    padding: 12px 12px 12px 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #d9d9d9;
    color: #000;
    font-size: 14px;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
}

button.signup-btn {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    font-size: 16px;
    margin-top: 10px;
    transition: 0.3s;
}

button.signup-btn:hover {
    opacity: 0.9;
}

.toggle {
    margin-top: 5px;
    font-size: 14px;
}
.toggle a {
    color: white;
    text-decoration: underline;
    font-weight: bold;
}

/* Responsive Design for Different Screen Sizes */

/* For tablets and larger phones */
@media (max-width: 768px) {
    .container {
        max-width: 90%;
        padding: 25px 20px;
    }
    
    h2 {
        font-size: 24px;
    }
    
    .container img {
        width: 130px;
    }
}

/* For mobile phones */
@media (max-width: 480px) {
    body {
        padding: 15px;
    }
    
    .container {
        max-width: 100%;
        padding: 20px 15px;
        min-height: 450px;
    }
    
    h2 {
        font-size: 22px;
        margin-bottom: 10px;
    }
    
    .container img {
        width: 120px;
    }
    
    .input-container input {
        padding: 10px 10px 10px 35px;
        font-size: 13px;
    }
    
    .input-container i {
        font-size: 16px;
    }
    
    button.signup-btn {
        padding: 10px;
        font-size: 15px;
    }
    
    .toggle {
        font-size: 13px;
    }
}

/* For very small screens */
@media (max-width: 320px) {
    .container {
        padding: 15px 10px;
    }
    
    h2 {
        font-size: 20px;
    }
    
    .container img {
        width: 100px;
    }
    
    .input-container input {
        padding: 8px 8px 8px 30px;
    }
    
    .input-container i {
        font-size: 14px;
        left: 8px;
    }
}

/* For landscape orientation on mobile devices */
@media (max-height: 500px) and (orientation: landscape) {
    body {
        padding: 10px;
    }
    
    .container {
        min-height: auto;
        padding: 15px;
    }
    
    .container img {
        width: 100px;
        margin-bottom: 5px;
    }
    
    h2 {
        font-size: 20px;
        margin-bottom: 10px;
    }
    
    .input-container {
        margin-bottom: 8px;
    }
    
    .input-container input {
        padding: 8px 8px 8px 35px;
    }
}

/* Specific adjustments for iPhone XR, 12 Pro, 14, etc. */
@supports (-webkit-touch-callout: none) {
    /* iOS-specific styles */
    .input-container input {
        -webkit-appearance: none;
    }
    
    button.signup-btn {
        -webkit-appearance: none;
    }
}

/* For devices with notches */
@media only screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3),
       only screen and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3),
       only screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2),
       only screen and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) {
    body {
        padding-top: 40px; /* Extra padding for notch */
    }
}
</style>
</head>
<body>
<div class="container">
    <div>
        <img src="img/LSULogo.png" alt="Logo">
        <h2>Student/Staff Sign Up</h2>
    </div>
    <div class="form-wrapper">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?role=" . urlencode($role); ?>">
            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="name" placeholder="Name" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-circle-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            <button type="submit" name="signup" class="signup-btn">Sign Up</button>
        </form>
    </div>
    <div class="toggle">
        Already have an account? <a href="login.php?role=<?php echo $role; ?>">Login</a>
    </div>
</div>
</body>
</html>