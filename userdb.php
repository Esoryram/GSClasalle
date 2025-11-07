<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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
$total = 0;
$pending = 0;
$inProgress = 0;

if ($accountID) {
    $queries = [
        "total"      => "SELECT COUNT(*) AS total FROM Concerns WHERE AccountID = ?",
        "pending"    => "SELECT COUNT(*) AS pending FROM Concerns WHERE AccountID = ? AND Status = 'Pending'",
        "inProgress" => "SELECT COUNT(*) AS inProgress FROM Concerns WHERE AccountID = ? AND Status = 'In Progress'"
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

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

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}

.logo img {
    height: 40px;
    margin-right: 20px;
}

.navbar .links {
    display: flex;
    gap: 20px;
    margin-right: auto;
}

.navbar .links a {
    color: white;
    font-weight: bold;
    font-size: 16px;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 5px;
}

.navbar .links a.active {
    background: #4ba06f;
    border: 1px solid #07491f;
}

.navbar .links a:hover {
    background: #107040;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: flex;
    align-items: center;
}

.dropdown .username {
    font-weight: bold;
    font-size: 16px;
    padding: 6px 12px;
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
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.dropdown-menu a {
    display: block;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
}

.dropdown-menu a:hover {
    background: #f1f1f1;
}

/* Dashboard Layout */
.container {
    padding: 40px 60px;
}

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
    background: white;
    border-radius: 12px;
    padding: 25px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.dashboard-card:hover {
    transform: translateY(-3px);
}

.card-icon {
    font-size: 24px;
    margin-bottom: 10px;
}

.card-value {
    font-size: 44px;
    font-weight: 700;
    margin: 0;
}

.card-label {
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
}

.card-total { color: #275850; }
.card-pending { background: #fffbeb; color: #b45309; }
.card-inprogress { background: #ecfdf5; color: #047857; }

/* Announcements */
.announcements-panel {
    background: white;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.announcements-panel h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f9158;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
}

.announcement-item {
    background: #f9fafb;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 10px;
    font-size: 14px;
    border-left: 3px solid #1f9158;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>

    <div class="links">
        <a href="userdb.php" class="active">Dashboard</a>
        <a href="usersubmit.php">Submit New Concern</a>
        <a href="userconcerns.php">Concerns</a>
    </div>

    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a>
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

            <div id="announcementsContainer" style="max-height: 130px; overflow-y: auto; scroll-behavior: smooth;">
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
                container.innerHTML = '<div class="announcement-item text-muted">No active announcements.</div>';
                return;
            }

            announcements.forEach(a => {
                const btn = document.createElement('button');
                btn.className = 'announcement-item w-100 text-start border-0 bg-transparent';
                btn.innerHTML = `
                    <div class="fw-bold" style="color:#275850;">${a.title}</div>
                    <div class="text-muted small mb-1" style="font-size:11px;">${a.date}</div>
                `;
                btn.addEventListener('click', () => showAnnouncementModal(a));
                container.appendChild(btn);
            });
        })
        .catch(() => {
            document.getElementById('announcementsContainer').innerHTML =
                '<div class="announcement-item text-danger">Error loading announcements.</div>';
        });
}

function showAnnouncementModal(a) {
    const modalTitle = document.getElementById('announcementModalLabel');
    const modalBody = document.getElementById('announcementModalBody');

    modalTitle.textContent = a.title;
    modalBody.innerHTML = `
        <p class="text-muted" style="font-size:13px;">Posted on ${a.date}</p>
        <div style="white-space:pre-line;">${a.details}</div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
    modal.show();
}

loadAnnouncements();
setInterval(loadAnnouncements, 30000); // auto-refresh every 30 seconds
</script>

<!-- Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1f9158; color:white;">
        <h5 class="modal-title" id="announcementModalLabel">Announcement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="announcementModalBody" style="font-size:14px;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

 <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#1f9158; color:white;">
            <h5 class="modal-title" id="changePasswordLabel">Change Password</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="changePasswordForm">
              <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" required>
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" required>
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="savePasswordBtn">Change Password</button>
          </div>
        </div>
      </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e){
    e.preventDefault();
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if(newPassword !== confirmPassword){
        Swal.fire('Error','Passwords do not match!','error');
        return;
    }

    fetch('change_password.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({currentPassword,newPassword})
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire(data.success ? 'Success':'Error', data.message, data.success?'success':'error');
        if(data.success){
            document.getElementById('changePasswordForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
        }
    })
    .catch(()=> Swal.fire('Error','Something went wrong.','error'));
});
</script>

</body>
</html>
