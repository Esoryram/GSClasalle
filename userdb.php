<?php
session_start();
include("config.php");

if(!isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}
$username = $_SESSION['username']; 
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "dashboard"; 

// Assume AccountID is stored in the session upon login, which is best practice.
// If it's not, you'll need to fetch it first:
$accountID = $_SESSION['accountID'] ?? null; 

if (!$accountID) {
    // Fetch AccountID based on username if it's not in the session
    $accountQuery = "SELECT AccountID FROM Accounts WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $accountQuery);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $accountResult = mysqli_stmt_get_result($stmt);
    $accountRow = mysqli_fetch_assoc($accountResult);
    $accountID = $accountRow['AccountID'] ?? null;
    // Store in session for future use
    if ($accountID) {
        $_SESSION['accountID'] = $accountID;
    }
}

// Initialize counts
$total = 0;
$pending = 0;
$inProgress = 0;

if ($accountID && isset($conn)) {
    // Total concerns for the logged-in user
    $totalQuery = "SELECT COUNT(*) AS total FROM Concerns WHERE AccountID = ?";
    $stmtTotal = mysqli_prepare($conn, $totalQuery);
    mysqli_stmt_bind_param($stmtTotal, "i", $accountID);
    mysqli_stmt_execute($stmtTotal);
    $totalResult = mysqli_stmt_get_result($stmtTotal);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $total = $totalRow['total'] ?? 0;

    // Pending concerns for the logged-in user
    $pendingQuery = "SELECT COUNT(*) AS pending FROM Concerns WHERE AccountID = ? AND Status = 'Pending'";
    $stmtPending = mysqli_prepare($conn, $pendingQuery);
    mysqli_stmt_bind_param($stmtPending, "i", $accountID);
    mysqli_stmt_execute($stmtPending);
    $pendingResult = mysqli_stmt_get_result($stmtPending);
    $pendingRow = mysqli_fetch_assoc($pendingResult);
    $pending = $pendingRow['pending'] ?? 0;

    // In Progress concerns for the logged-in user
    $inProgressQuery = "SELECT COUNT(*) AS inProgress FROM Concerns WHERE AccountID = ? AND Status = 'In Progress'";
    $stmtInProgress = mysqli_prepare($conn, $inProgressQuery);
    mysqli_stmt_bind_param($stmtInProgress, "i", $accountID);
    mysqli_stmt_execute($stmtInProgress);
    $inProgressResult = mysqli_stmt_get_result($stmtInProgress);
    $inProgressRow = mysqli_fetch_assoc($inProgressResult);
    $inProgress = $inProgressRow['inProgress'] ?? 0;
}

// NOTE: The AJAX data fetching for announcements will use the existing get_announcement.php
// The AJAX data fetching for concerns status will be removed as the PHP now calculates it.
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* --- Core Styles from original userdb.php --- */
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f9fafb; /* Updated background to match admindb */
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

.navbar .logo {
    display: flex;
    align-items: center;
    margin-right: 25px; 
}
.navbar .logo img {
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
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
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
    position: relative;
    display: inline-block;
    color: white;
}
.dropdown:hover .dropdown-menu {
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

.container {
    padding: 40px 60px; /* Adjusted padding to match admindb */
    gap: 30px;
}
/* --- END Core Styles --- */

/* --- Styles imported from admindb.php --- */

.top-dashboard-grid {
    display: grid;
    grid-template-columns: 3fr 1fr; /* 3-part cards vs 1-part announcements */
    gap: 30px;
    margin-bottom: 30px;
}

/* Card Styling */
.status-cards-wrapper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
    transition: transform 0.2s, box-shadow 0.2s;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 120px;
    border: 1px solid #e5e7eb;
}

.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.card-icon {
    font-size: 24px;
    opacity: 0.7;
    margin-bottom: 10px;
}

.card-value {
    font-size: 44px;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.card-label {
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
    margin-top: 5px;
}

.card-total {
    color: #275850;
}
.card-total .card-icon { color: #1f9158; }

.card-pending {
    background-color: #fffbeb;
    color: #b45309;
}
.card-pending .card-icon { color: #f59e0b; }

.card-inprogress {
    background-color: #ecfdf5;
    color: #047857;
}
.card-inprogress .card-icon { color: #10b981; }

.announcements-panel {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    min-height: 200px;
    border: 1px solid #e5e7eb;
}

.announcements-panel h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f9158;
    margin-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}

.announcement-item {
    background: #f9fafb;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 10px;
    text-align: left;
    font-size: 14px;
    border-left: 3px solid #1f9158;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .top-dashboard-grid {
        grid-template-columns: 2fr 1fr;
    }
}
@media (max-width: 768px) {
    .top-dashboard-grid {
        grid-template-columns: 1fr; /* Stack cards and announcements */
    }
    .status-cards-wrapper {
        grid-template-columns: 1fr; /* Stack cards vertically on mobile */
    }
    .container {
        padding: 20px;
    }
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>
    <div class="links">
        <a href="userdb.php" class="<?php echo ($activePage=="dashboard")?"active":""; ?>">Dashboard</a>
        <a href="usersubmit.php" class="<?php echo ($activePage=="newconcerns")?"active":""; ?>">Submit New Concerns</a>
        <a href="userconcerns.php" class="<?php echo ($activePage=="concerns")?"active":""; ?>">Concerns</a>
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

<div class="container">
    
    <div class="top-dashboard-grid">
        <div class="status-cards-wrapper">
            
            <div class="dashboard-card card-total">
                <div class="card-icon"><i class="fas fa-boxes"></i></div>
                <h1 class="card-value"><?php echo $total; ?></h1>
                <p class="card-label">Total Concerns</p>
            </div>
            
            <div class="dashboard-card card-pending">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <h1 class="card-value"><?php echo $pending; ?></h1>
                <p class="card-label">Pending</p>
            </div>
            
            <div class="dashboard-card card-inprogress">
                <div class="card-icon"><i class="fas fa-tasks"></i></div>
                <h1 class="card-value"><?php echo $inProgress; ?></h1>
                <p class="card-label">In Progress</p>
            </div>
        </div>

        <div class="announcements-panel">
            <h3>Announcements</h3>
            <div id="announcementsContainer">
                <div class="announcement-item">Loading announcements...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
// Function to fetch and display announcements (MODIFIED to use admindb style)
function loadAnnouncements() {
    fetch('get_announcement.php') // Revert to your original file name
        .then(response => response.json())
        .then(announcements => {
            const container = document.getElementById('announcementsContainer');
            container.innerHTML = '';
            
            if (announcements.length === 0) {
                container.innerHTML = '<div class="announcement-item text-muted">No announcements yet.</div>';
                return;
            }
            
            announcements.forEach(announcement => {
                const announcementDiv = document.createElement('div');
                announcementDiv.className = 'announcement-item';
                // Using the admindb style for the inner HTML structure
                announcementDiv.innerHTML = `
                    <div class="fw-bold" style="color:#275850;">${announcement.title || 'No Title'}</div>
                    <div class="text-muted small mb-1" style="font-size:11px;">${announcement.date || 'Unknown Date'}</div>
                    <div style="color:#6b7280;">${announcement.details || 'No details provided.'}</div>
                `;
                container.appendChild(announcementDiv);
            });
        })
        .catch(error => {
            console.error('Error loading announcements:', error);
            document.getElementById('announcementsContainer').innerHTML = 
                '<div class="announcement-item text-danger">Error loading announcements.</div>';
        });
}

// Data is now loaded via PHP, only load announcements via AJAX
loadAnnouncements();

// Update announcements data every 30 seconds
setInterval(loadAnnouncements, 30000);
</script>

</body>
</html>