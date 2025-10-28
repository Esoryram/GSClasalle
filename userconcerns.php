<?php
session_start();
include("config.php");

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; 
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "concerns"; 

// Get AccountID of the logged-in user
$userQuery = "SELECT AccountID FROM Accounts WHERE Username = '$username'";
$userResult = mysqli_query($conn, $userQuery);
$userRow = mysqli_fetch_assoc($userResult);
$accountID = $userRow ? $userRow['AccountID'] : 0;

// Get concerns of the logged-in user
$concernsQuery = "SELECT * FROM Concerns WHERE AccountID = '$accountID' AND Status != 'Completed' ORDER BY Concern_Date DESC";
$concernsResult = mysqli_query($conn, $concernsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Concerns</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}

.logo {
    display: flex;
    align-items: center;
    margin-right: 25px; 
}
.logo img {
    height: 40px;
    width: auto; 
    object-fit: contain;
}

.navbar .links {
    display: flex;
    gap: 20px;
    margin-right: auto;
}

.navbar .links a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 16px;
    padding: 6px 12px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.navbar .links a.active {
    background: #4ba06f;
    border: 1px solid #07491f;
    box-shadow: 0 4px 6px rgba(0,0,0,0.4);
}

.navbar .links a:hover {
    background: #107040;
}

.dropdown {
    position: relative;
    display: flex;
    align-items: center;
    gap: 5px;
}

.dropdown .username {
    font-weight: bold;
    font-size: 16px;
    padding: 6px 12px;
}

.dropdown-toggle {
    cursor: pointer;
    font-size: 16px;
    padding: 6px 8px;
    border-radius: 5px;
    display: inline-block;
    color: white;
}

.dropdown-toggle:hover .dropdown-menu {
    display: block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 180px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    border-radius: 5px;
    overflow: hidden;
    z-index: 10;
}

.dropdown-menu a {
    display: block;
    padding: 12px 16px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
}

.dropdown-menu a:hover {
    background: #f1f1f1;
}

.main {
    padding: 10px;
    text-align: center;
}

.submit-btn-top {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    border: none;
    padding: 5px 18px;
    border-radius: 8px;
    margin-bottom: 0px;
    transition: all 0.3s ease;
}
.submit-btn-top:hover {
    background: linear-gradient(90deg, #1f9158, #163a37);
    transform: translateY(-1px);
}

.concern-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 850px;
    margin: 0 auto;
    max-height: 550px; 
    overflow-y: auto; 
}

.concern-header {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    padding: 8px;
    border-radius: 10px;
    font-size: 18px;
    margin-bottom: 20px;
    text-align: center;
}

.accordion-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.accordion-button {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    border: none;
    padding: 10px 20px;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(90deg, #1f9158, #163a37);
    color: white;
    box-shadow: none;
}

.accordion-body {
    background: #f8f9fa;
    padding: 20px;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 12px;
    margin-left: 475px;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-inprogress {
    background: #cce7ff;
    color: #004085;
}

.status-completed {
    background: #d1edff;
    color: #0c5460;
}

.form-field {
    margin-bottom: 15px;
    text-align: left;
}

.form-field label {
    font-weight: bold;
    color: #163a37;
    margin-bottom: 8px;
    display: block;
}

.form-field .form-control {
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 10px 15px;
    font-size: 14px;
    color: #495057;
    width: 100%;
    box-sizing: border-box;
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>

    <div class="links">
        <a href="userdb.php" class="<?php echo ($activePage=='dashboard')?'active':''; ?>">Dashboard</a>
        <a href="usersubmit.php" class="<?php echo ($activePage=='newconcerns')?'active':''; ?>">Submit New Concerns</a>
        <a href="userconcerns.php" class="<?php echo ($activePage=='concerns')?'active':''; ?>">Concerns</a>
    </div>
    
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">Change Password</a>
                <a href="user_archived.php">Archived Concerns</a>
                <a href="login.php">Logout</a>
            </div>
        </span>
    </div>
</div>


<div class="main">
    <div class="d-flex justify-content-end mb-3" style="max-width: 850px; margin: 0 auto;">
    <button class="submit-btn-top" onclick="window.location.href='usersubmit.php'">
        + Submit New Concern
    </button>
</div>

    <div class="concern-container">
        <div class="concern-header">Your Submitted Concerns</div>

        <div class="accordion" id="concernsAccordion">
            <?php
            if (mysqli_num_rows($concernsResult) > 0) {
                $index = 1;
                while ($row = mysqli_fetch_assoc($concernsResult)) {
                    $status = isset($row['Status']) ? $row['Status'] : 'Unknown';
                    $statusClass = strtolower(str_replace(' ', '', $status));
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
                    echo "
                    <div class='accordion-item'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#concern{$index}' aria-expanded='false'>
                                {$date} <span class='status-badge status-{$statusClass}'>" . htmlspecialchars($status) . "</span>
                            </button>
                        </h2>
                        <div id='concern{$index}' class='accordion-collapse collapse' data-bs-parent='#concernsAccordion'>
                            <div class='accordion-body'>
                                <div class='form-field'>
                                    <label>Concern Title</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Concern_Title']) . "</div>
                                </div>
                                <div class='form-field'>
                                    <label>Description</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Description']) . "</div>
                                </div>
                                <div class='form-field'>
                                    <label>Problem Type</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Problem_Type']) . "</div>
                                </div>
                                <div class='form-field'>
                                    <label>Priority</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Priority']) . "</div>
                                </div>
                                <div class='form-field'>
                                    <label>Assigned To</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Assigned_to']) . "</div>
                                </div>
                                <div class='form-field'>
                                    <label>Attachment</label>
                                    <div class='form-control'>" . htmlspecialchars($row['Attachment']) . "</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ";
                    $index++;
                }
            } else {
                echo "<div class='alert alert-info'>You have not submitted any concerns yet.</div>";
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
