<?php
// Start session and include database configuration
session_start();
include("config.php");

// Redirect user to login page if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in username and display name
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "dashboard"; // Used to highlight active navbar link

// Fetch AccountID from session or database
$accountID = $_SESSION['accountID'] ?? null;

if (!$accountID) {
    // Query database to get AccountID for the current user
    $stmt = $conn->prepare("SELECT AccountID FROM Accounts WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $accountID = $row['AccountID'];
        $_SESSION['accountID'] = $accountID; // Store in session for future use
    }
    $stmt->close(); 
}

// Initialize counters for concern statuses
$total = 0;
$pending = 0;
$inProgress = 0;
$completed = 0; 

if ($accountID) {
    // Define queries for total and status-specific concerns
    $queries = [
        "total"      => "SELECT COUNT(*) AS total FROM Concerns WHERE AccountID = ?",
        "pending"    => "SELECT COUNT(*) AS pending FROM Concerns WHERE AccountID = ? AND Status = 'Pending'",
        "inProgress" => "SELECT COUNT(*) AS inProgress FROM Concerns WHERE AccountID = ? AND Status = 'In Progress'",
        "completed"   => "SELECT COUNT(*) AS completed FROM Concerns WHERE AccountID = ? AND Status = 'Completed'"
    ];

    // Execute each query and store results in corresponding variable
    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        ${$key} = $row[$key] ?? 0; // Dynamically set variable
        $stmt->close();
    }

    // Fetch recent concerns (Pending or In Progress) for display
    $recentConcerns = [];
    
    $stmt = $conn->prepare("
    SELECT ConcernID, Concern_Title, Room, Status, Assigned_To,
           DATE_FORMAT(Concern_Date, '%b %d, %Y') AS DateSubmittedFormatted
    FROM Concerns
    WHERE AccountID = ? 
      AND Status IN ('Pending', 'In Progress')
    ORDER BY Concern_Date DESC
    LIMIT 5");
    
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recentConcerns[] = $row; // Store in array for later table display
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<!-- FIXED: Better viewport for iOS devices -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no">
<title>My Dashboard | Concern Tracker</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- SweetAlert2 CSS for popups -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- FontAwesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<!-- Google Fonts Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* FIXED: Better base styles for mobile */
* {
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    box-sizing: border-box;
}

input, textarea, .form-control {
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}

/* General styling */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    background: #f9fafb;
    /* FIXED: Prevent horizontal scroll */
    overflow-x: hidden;
}

/* Navbar styling - FIXED for mobile */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #087830, #3c4142);
    padding: 12px 15px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    margin-right: 15px; 
}

.logo img {
    height: 35px; 
    width: auto; 
    object-fit: contain;
}

/* Navbar links */
.navbar .links {
    display: flex;
    gap: 12px;
    margin-right: auto;
}

.navbar .links a {
    color: white; 
    text-decoration: none;
    font-weight: bold; 
    font-size: 14px;
    padding: 8px 12px; 
    border-radius: 5px;
    transition: all 0.3s ease;
    /* FIXED: Better touch target */
    min-height: 44px;
    display: flex;
    align-items: center;
}

.navbar .links a.active {
    background: #4ba06f;
    border: 1px solid #07491f;
    box-shadow: 0 4px 6px rgba(0,0,0,0.4);
    color: white;
}

.navbar .links a:hover {
    background: #107040;
    color: white;
}

.navbar .links a i {
    margin-right: 5px;
}

/* Mobile menu toggle - FIXED */
.navbar-toggler {
    display: none;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 16px;
    margin-left: 10px;
    cursor: pointer;
}

/* Dropdown menu for username - FIXED for touch */
.dropdown {
    position: relative;
}

.dropdown-toggle {
    cursor: pointer;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 5px;
    color: white;
    background: transparent;
    border: none;
    /* FIXED: Better touch target */
    min-height: 44px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%; 
    right: 0;
    background: white;
    min-width: 180px;
    border-radius: 5px; 
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    z-index: 1000;
    overflow: hidden;
}

.dropdown:hover .dropdown-menu,
.dropdown:focus-within .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: block;
    padding: 12px 16px; 
    color: #333;
    text-decoration: none;
    font-size: 14px; 
    /* FIXED: Better touch targets */
    min-height: 44px;
    display: flex;
    align-items: center;
}

.dropdown-menu a:hover {
    background: #f1f1f1;
}

/* Container for dashboard content */
.container {
    padding: 15px;
    width: 100%;
    box-sizing: border-box;
}

/* Top dashboard layout: cards + announcements */
.top-dashboard-grid {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 15px; 
    margin-bottom: 20px;
}

/* Status cards wrapper */
.status-cards-wrapper {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 12px;
}

/* Individual dashboard card */
.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 15px; 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
    transition: transform 0.2s, box-shadow 0.2s;
    text-align: left;
    min-height: 100px;
    border: 1px solid #e5e7eb; 
    /* FIXED: Better mobile sizing */
    width: 100%;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}
.card-icon { 
    font-size: 20px; 
    opacity: 0.7; 
    margin-bottom: 8px; 
}

.card-value { 
    font-size: 32px; 
    font-weight: 700; 
    margin: 0; 
    line-height: 1; 
}

.card-label { 
    font-size: 13px; 
    font-weight: 500; 
    color: #6b7280; 
    margin-top: 5px; 
    text-transform: capitalize; 
}

/* Cards color based on status */
.card-total { 
    color: #275850; 
}

.card-total .card-icon { 
    color: #1f9158; 
}

.card-pending { 
    background-color: #fffbeb; 
    color: #b45309; 
}

.card-pending .card-icon { 
    color: #f59e0b; 
}

.card-inprogress { 
    background-color: #e0f2fe; 
    color: #075985; 
}

.card-inprogress .card-icon { 
    color: #38bdf8; 
}

.card-completed { 
    background-color: #ecfdf5; 
    color: #087830; 
}

.card-completed .card-icon { 
    color: #087830; 
}

/* Call-to-action section */
.cta-section {
    text-align: center;
    margin: 20px 0;
}

#submitConcernBtn {
    padding: 14px 25px; 
    font-size: 16px; 
    font-weight: 700;
    border-radius: 10px;
    background: #1f9158; 
    color: white;
    border: none;
    text-decoration: none; 
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s, transform 0.1s;
    box-shadow: 0 4px 10px rgba(31, 145, 88, 0.4);
    /* FIXED: Better touch target */
    min-height: 50px;
}

#submitConcernBtn:hover {
    background: #107040;
    transform: translateY(-2px);
}

/* Recent concerns panel */
.recent-concerns-panel {
    background: white;
    border-radius: 12px; 
    padding: 15px; 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
    margin-top: 20px;
    overflow-x: auto;
    width: 100%;
}

/* Table styling */
.table {
    min-width: 600px;
}

.table th, .table td { 
    vertical-align: middle; 
    font-size: 14px; 
    padding: 10px 8px;
}

/* Status pill styling */
.status-pill {
    padding: 5px 10px;
    border-radius: 50px;
    font-size: 11px;
    min-width: 80px;
    display: inline-block;
    text-align: center;
}

.status-pending { 
    background: #fef3c7; 
    color: #b45309; 
}

.status-in-progress { 
    background: #bfdbfe; 
    color: #1e40af; 
}

.status-completed { 
    background: #d1fae5; 
    color: #065f46; 
}

/* Announcements panel */
.announcements-panel {
    background: white;
    border-radius: 12px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.announcements-panel h3 {
    font-size: 15px;
    font-weight: 600;
    color: #1f9158;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
}

.announcement-item {
    background: #f9fafb;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 8px;
    font-size: 12px;
    border-left: 3px solid #1f9158;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    cursor: pointer;
    transition: background 0.2s;
    /* FIXED: Better touch target */
    min-height: 44px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.announcement-item:hover {
    background: #f0f4f8;
}

/* Scrollbar styling for announcements */
#announcementsContainer {
    max-height: 120px;
    overflow-y: auto;
    scroll-behavior: smooth;
}

#announcementsContainer::-webkit-scrollbar {
    width: 4px; 
}

#announcementsContainer::-webkit-scrollbar-thumb { 
    background-color: #1f9158; 
    border-radius: 10px; 
}

#announcementsContainer::-webkit-scrollbar-track { 
    background-color: #f0f0f0; 
}

/* FIXED: Mobile-specific styles for iPhone 12 Pro (390px) */
@media (max-width: 480px) {
    .navbar {
        flex-wrap: wrap;
        padding: 10px 12px;
    }
    
    .navbar-toggler {
        display: block;
        order: 2;
    }
    
    .navbar .links {
        display: none;
        width: 100%;
        flex-direction: column;
        gap: 8px;
        margin-top: 10px;
        order: 3;
    }
    
    .navbar .links.show {
        display: flex;
    }
    
    .navbar .links a {
        padding: 12px 15px;
        text-align: center;
        font-size: 15px;
        justify-content: center;
    }
    
    .logo {
        order: 1;
        margin-right: auto;
    }
    
    .dropdown {
        order: 2;
        margin-left: auto;
    }
    
    .container {
        padding: 10px;
    }
    
    .top-dashboard-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .status-cards-wrapper {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .dashboard-card {
        padding: 12px;
        min-height: 90px;
    }
    
    .card-value {
        font-size: 28px;
    }
    
    .card-label {
        font-size: 12px;
    }
    
    .card-icon {
        font-size: 18px;
    }
    
    .recent-concerns-panel {
        padding: 12px;
    }
    
    .table th, .table td {
        font-size: 11px;
        padding: 6px 4px;
    }
    
    .btn-sm {
        padding: 4px 6px;
        font-size: 11px;
        min-height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .announcements-panel {
        padding: 10px;
    }
    
    .announcement-item {
        padding: 8px 10px;
        font-size: 11px;
    }
    
    #submitConcernBtn {
        padding: 12px 20px;
        font-size: 15px;
        min-height: 44px;
        width: 100%;
    }
}

/* FIXED: Specific styles for iPhone 12 Pro (390px) */
@media (max-width: 390px) {
    .navbar .links a {
        font-size: 14px;
        padding: 10px 12px;
    }
    
    .dropdown-toggle {
        font-size: 13px;
        padding: 6px 10px;
    }
    
    .status-cards-wrapper {
        grid-template-columns: 1fr;
    }
    
    .dashboard-card {
        padding: 10px;
        min-height: 85px;
    }
    
    .card-value {
        font-size: 26px;
    }
    
    .container {
        padding: 8px;
    }
    
    .table th, .table td {
        font-size: 10px;
        padding: 5px 3px;
    }
    
    .status-pill {
        font-size: 10px;
        min-width: 70px;
        padding: 4px 8px;
    }
}

/* FIXED: Tablet styles */
@media (min-width: 481px) and (max-width: 768px) {
    .navbar {
        padding: 12px 15px;
    }
    
    .navbar .links a {
        font-size: 13px;
        padding: 6px 10px;
    }
    
    .status-cards-wrapper {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .dashboard-card {
        padding: 15px;
    }
    
    .container {
        padding: 12px;
    }
    
    .top-dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* FIXED: Larger mobile devices */
@media (min-width: 769px) and (max-width: 992px) {
    .top-dashboard-grid {
        grid-template-columns: 2fr 1fr;
    }
    
    .status-cards-wrapper {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* FIXED: Button styles for mobile */
.btn {
    /* FIXED: Better touch targets for all buttons */
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-sm {
    min-height: 36px;
}

/* FIXED: Modal improvements for mobile */
.modal-dialog {
    margin: 10px;
}

.modal-content {
    border-radius: 12px;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 5px;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>

    <!-- Links -->
    <div class="links" id="navbarLinks">
        <a href="userdb.php" class="<?php echo ($activePage=='dashboard')?'active':''; ?>">
            <i class="fas fa-home me-1"></i> Dashboard
        </a>
        <a href="usersubmit.php" class="<?php echo ($activePage=='newconcerns')?'active':''; ?>">
            <i class="fas fa-plus-circle me-1"></i> Submit New Concern
        </a>
        <a href="userconcerns.php" class="<?php echo ($activePage=='concerns')?'active':''; ?>">
            <i class="fas fa-list-ul me-1"></i> All Concerns
        </a>
    </div>

    <!-- Mobile menu toggle -->
    <button class="navbar-toggler" type="button" id="navbarToggle" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>

    <!-- User dropdown -->
    <div class="dropdown ms-auto">
        <button class="btn dropdown-toggle username-btn" aria-expanded="false" aria-haspopup="true">
            <?= htmlspecialchars($name) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key me-2"></i>Change Password
            </a></li>
            <li><a class="dropdown-item" href="user_archived.php">
                <i class="fas fa-archive me-2"></i>Archived Concerns
            </a></li>
            <li><a class="dropdown-item" href="login.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a></li>
        </ul>
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
            
            <div class="dashboard-card card-completed">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <h1 class="card-value"><?php echo $completed; ?></h1>
                <p class="card-label">Completed</p>
            </div>
        </div>

        <div class="announcements-panel">
            <h3>Announcements</h3>
            <div id="announcementsContainer">
                <div class="announcement-item">Loading announcements...</div>
            </div>
        </div>
    </div>

    <div class="cta-section">
        <a href="usersubmit.php" id="submitConcernBtn">
            <i class="fas fa-plus me-2"></i> Report a New Concern
        </a>
    </div>

    <div class="recent-concerns-panel">
        <h3>My Recent Concerns</h3>

        <?php if (!empty($recentConcerns)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Room/Area</th>
                            <th scope="col">Date Submitted</th>
                            <th scope="col">Assigned To</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentConcerns as $concern): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($concern['ConcernID']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($concern['Concern_Title']); ?></td>
                                <td><?php echo htmlspecialchars($concern['Room']); ?></td>
                                <td><?php echo htmlspecialchars($concern['DateSubmittedFormatted']); ?></td>
                                <td><?php echo htmlspecialchars($concern['Assigned_To']); ?></td>
                                <td>
                                    <?php
                                    $status = htmlspecialchars($concern['Status']);
                                    $statusClass = strtolower(str_replace(' ', '-', $status));
                                    echo '<span class="status-pill status-' . $statusClass . '">' . $status . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <!-- Updated View link with anchor -->
                                    <a href="userconcerns.php#concern-<?php echo $concern['ConcernID']; ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="userconcerns.php" class="btn btn-sm btn-outline-secondary">
                    View All Concerns <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                You haven't submitted any concerns yet. Click the button above to report an issue!
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// FIXED: Better mobile menu toggle with touch support
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarLinks = document.getElementById('navbarLinks');
    
    if (navbarToggle) {
        navbarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navbarLinks.classList.toggle('show');
            
            // FIXED: Prevent body scroll when menu is open on mobile
            if (navbarLinks.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const navbar = document.querySelector('.navbar');
        if (!navbar.contains(event.target) && navbarLinks.classList.contains('show')) {
            navbarLinks.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // FIXED: Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.navbar .links a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navbarLinks.classList.remove('show');
            document.body.style.overflow = '';
        });
    });
});

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
        <p class="text-muted" style="font-size:12px;">Posted on ${a.date}</p>
        <div style="white-space:pre-line; font-size:14px;">${a.details}</div>
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
    
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
document.getElementById('savePasswordBtn').addEventListener('click', function(){
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