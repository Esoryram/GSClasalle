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

// Fetch AccountID
$accountID = $_SESSION['accountID'] ?? null; 
if (!$accountID) {
    $stmt = $conn->prepare("SELECT AccountID FROM Accounts WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $accountID = $row['AccountID'];
        $_SESSION['accountID'] = $accountID;
    }
}

// Counts
$total = $pending = $inProgress = 0;

if ($accountID) {
    // Define the queries for each count
    $queries = [
        "total" => "SELECT COUNT(*) AS total FROM Concerns WHERE AccountID = ?",
        "pending" => "SELECT COUNT(*) AS pending FROM Concerns WHERE AccountID = ? AND Status = 'Pending'",
        "inProgress" => "SELECT COUNT(*) AS inProgress FROM Concerns WHERE AccountID = ? AND Status = 'In Progress'"
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Dynamically assign the value to the variable ($total, $pending, $inProgress)
        ${$key} = $row[$key] ?? 0;
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f9fafb;
}
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}
.logo img { height: 40px; margin-right: 20px; }
.navbar .links { display: flex; gap: 20px; margin-right: auto; }
.navbar .links a {
    color: white; text-decoration: none; font-weight: bold;
    font-size: 16px; padding: 6px 12px; border-radius: 5px;
}
.navbar .links a.active { background: #4ba06f; border: 1px solid #07491f; }
.navbar .links a:hover { background: #107040; }
.dropdown { position: relative; display: flex; align-items: center; }
.dropdown .username { font-weight: bold; font-size: 16px; padding: 6px 12px; }
.dropdown:hover .dropdown-menu { display: block; }
.dropdown-menu {
    display: none; position: absolute; top: 100%; right: 0;
    background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    border-radius: 5px; overflow: hidden; min-width: 180px; z-index: 10;
}
.dropdown-menu a { display: block; padding: 12px 16px; text-decoration: none; color: #333; }
.dropdown-menu a:hover { background: #f1f1f1; }

.container { padding: 40px 60px; gap: 30px; }
.top-dashboard-grid {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}
.status-cards-wrapper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.dashboard-card {
    background: white; border-radius: 12px; padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb; transition: 0.3s;
}
.dashboard-card:hover { transform: translateY(-3px); }
.card-icon { font-size: 24px; margin-bottom: 10px; }
.card-value { font-size: 44px; font-weight: 700; margin: 0; }
.card-label { font-size: 16px; font-weight: 500; color: #6b7280; }
.card-total { color: #275850; } .card-pending { background: #fffbeb; color: #b45309; } .card-inprogress { background: #ecfdf5; color: #047857; }

.announcements-panel {
    background: white; border-radius: 12px; padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;
}
.announcements-panel h3 {
    font-size: 18px; font-weight: 600; color: #1f9158;
    margin-bottom: 15px; border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}
.announcement-item {
    background: #f9fafb; border-radius: 8px; padding: 10px 15px;
    margin-bottom: 10px; font-size: 14px;
    border-left: 3px solid #1f9158;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
#announcementsContainer::-webkit-scrollbar {
    width: 6px;
}
#announcementsContainer::-webkit-scrollbar-thumb {
    background-color: #1f9158;
    border-radius: 10px;
}
#announcementsContainer::-webkit-scrollbar-track {
    background-color: #f0f0f0;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo"><img src="img/LSULogo.png" alt="LSU Logo"></div>
    <div class="links">
        <a href="userdb.php" class="active">Dashboard</a>
        <a href="usersubmit.php">Submit New Concern</a>
        <a href="userconcerns.php">Concerns</a>
    </div>
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <div class="dropdown-menu">
            <a href="#">Change Password</a>
            <a href="user_archived.php">Archived Concerns</a>
            <a href="login.php">Logout</a>
        </div>
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

        <!-- Inside your announcements section -->
<div class="announcements-panel">
    <h3>Announcements</h3>
    <!-- 👇 only the first announcement is visible without scrolling -->
    <div id="announcementsContainer" 
     style="max-height: 130px; overflow-y: auto; scroll-behavior: smooth;">
        <div class="announcement-item">Loading announcements...</div>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadAnnouncements() {
    fetch('get_announcement.php')
        .then(response => response.json())
        .then(announcements => {
            const container = document.getElementById('announcementsContainer');
            container.innerHTML = '';
            if (!announcements.length) {
                container.innerHTML = '<div class="announcement-item text-muted">No announcements yet.</div>';
                return;
            }

            announcements.forEach(a => {
                const div = document.createElement('div');
                div.className = 'announcement-item';
                div.innerHTML = `
                    <div class="fw-bold" style="color:#275850;">${a.title}</div>
                    <div class="text-muted small mb-1" style="font-size:11px;">${a.date}</div>
                    <div style="color:#374151;">${a.details}</div>
                `;
                container.appendChild(div);
            });
        })
        .catch(() => {
            document.getElementById('announcementsContainer').innerHTML = 
                '<div class="announcement-item text-danger">Error loading announcements.</div>';
        });
}
loadAnnouncements();
setInterval(loadAnnouncements, 30000);
</script>
</body>
</html>
