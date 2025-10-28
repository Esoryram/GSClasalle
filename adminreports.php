<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "reports";

$filterRoom = isset($_GET['room']) ? mysqli_real_escape_string($conn, $_GET['room']) : '';
$filterAssignedTo = isset($_GET['assigned']) ? mysqli_real_escape_string($conn, $_GET['assigned']) : '';
$filterStatus = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$generateClicked = isset($_GET['generate']); // Check if Generate button was clicked

// Fetch unique room numbers for the dropdown
$roomsQuery = "SELECT DISTINCT Room FROM Concerns WHERE Room IS NOT NULL AND Room != '' ORDER BY Room ASC";
$roomsResult = mysqli_query($conn, $roomsQuery);
$roomOptions = [];
if ($roomsResult) {
    while ($row = mysqli_fetch_assoc($roomsResult)) {
        $roomOptions[] = $row['Room'];
    }
}

$assignedToQuery = "SELECT DISTINCT Assigned_to FROM Concerns WHERE Assigned_to IS NOT NULL AND Assigned_to != '' ORDER BY Assigned_to ASC";
$assignedToResult = mysqli_query($conn, $assignedToQuery);
$assignedOptions = [];
if ($assignedToResult) {
    while ($row = mysqli_fetch_assoc($assignedToResult)) {
        $assignedOptions[] = $row['Assigned_to'];
    }
}

// Only fetch concerns if Generate was clicked
$concernsData = [];
if ($generateClicked) {
    $query = "
        SELECT 
            c.ConcernID,
            c.Concern_Title,
            c.Room,
            c.Problem_Type,
            c.Priority,
            c.Concern_Date,
            c.Status,
            a.Name AS ReportedBy,
            c.Assigned_to
        FROM Concerns c
        LEFT JOIN Accounts a ON c.AccountID = a.AccountID
        WHERE 1=1 
    ";

    if (!empty($filterRoom) && $filterRoom !== 'All Rooms') {
        $query .= " AND c.Room = '$filterRoom'";
    }
    if (!empty($filterAssignedTo) && $filterAssignedTo !== 'All Personnel') {
        $query .= " AND c.Assigned_to = '$filterAssignedTo'";
    }
    if (!empty($filterStatus) && $filterStatus !== 'All Statuses') {
        $query .= " AND c.Status = '$filterStatus'";
    }

    $query .= " ORDER BY c.ConcernID ASC";

    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $concernsData[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* General body */
body {
    background-color: white;
    font-family: Arial, sans-serif;
    margin: 0;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
}

.logo {
    display: flex;
    align-items: center;
    margin-right: 25px;
}

.logo img {
    height: 40px;
    width: auto;
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
    padding: 6px 12px;
    border-radius: 5px;
    transition: 0.3s;
}

.navbar .links a.active {
    background-color: #4ba06f;
}

.navbar .links a:hover {
    background-color: #107040;
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

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
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
    background-color: #f1f1f1;
}

/* Page container */
.page-container {
    padding: 30px 40px;
}

/* Report controls (filters + buttons) */
.report-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.input-filter-width {
    width: 150px !important;
    padding: 10px 15px;
    font-weight: bold;
    border-radius: 8px;
    border: 1px solid #ced4da;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    background-color: white;
    font-size: 16px;
}

.input-search {
    width: 160px;
    padding: 10px 15px;
    font-weight: normal;
    border-radius: 8px;
    border: 1px solid #ced4da;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Generate button inside filter controls */
.btn-generate {
    background-color: #198754; 
    color: white;
    padding: 10px 20px;
    font-weight: bold;
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: background-color 0.3s;
    margin-left: auto; /* stays upper-right inside filter form */
}

.btn-generate:hover {
    background-color: #146c43;
}

/* Print button bottom-right */
.btn-print {
    position: fixed;
    bottom: 20px;
    right: 40px;
    background-color: #198754;
    color: white;
    padding: 5px 40px;
    font-weight: bold;
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: background-color 0.3s;
    z-index: 1000;
}

.btn-print:hover {
    background-color: #146c43;
}

/* Hide print button during print */
@media print {
    .btn-print {
        display: none;
    }
}

/* Table */
.table-container {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    position: relative;
}

.table thead {
    background-color: #198754;
    color: white;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

/* Print adjustments */
@media print {
    .btn-generate,
    .btn-print,
    .report-controls,
    .navbar {
        display: none !important;
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
        <a href="admindb.php" class="<?php echo ($activePage=="dashboard")?"active":""; ?>">Dashboard</a>
        <a href="adminconcerns.php" class="<?php echo ($activePage=="concerns")?"active":""; ?>">Concerns</a>
        <a href="adminreports.php" class="<?php echo ($activePage=="reports")?"active":""; ?>">Reports</a>
        <a href="adminfeedback.php" class="<?php echo ($activePage=="feedback")?"active":""; ?>">Feedback</a>
        <a href="adminannounce.php" class="<?php echo ($activePage=="announcements")?"active":""; ?>">Announcements</a>
    </div>
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">Change Password</a>
                <a href="login.php">Logout</a>
            </div>
        </span>
    </div>
</div>

<div class="page-container">
    <form method="GET" action="adminreports.php">
        <input type="hidden" name="generate" value="1">
        <div class="report-controls">
            <!-- Room Dropdown -->
            <select class="form-select input-filter-width" name="room" aria-label="Room filter">
                <option value="All Rooms" <?php echo ($filterRoom == 'All Rooms' || $filterRoom == '') ? 'selected' : ''; ?>>Room</option>
                <?php foreach ($roomOptions as $room): ?>
                    <option value="<?php echo htmlspecialchars($room); ?>" <?php echo ($filterRoom == $room) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($room); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Assigned To Dropdown -->
            <select class="form-select input-filter-width" name="assigned" aria-label="Assigned To filter">
                <option value="All Personnel" <?php echo ($filterAssignedTo == 'All Personnel' || $filterAssignedTo == '') ? 'selected' : ''; ?>>Assigned To</option>
                <?php foreach ($assignedOptions as $person): ?>
                    <option value="<?php echo htmlspecialchars($person); ?>" <?php echo ($filterAssignedTo == $person) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($person); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Status Dropdown -->
            <select class="form-select input-filter-width" name="status" aria-label="Status filter">
                <option value="All Statuses" <?php echo ($filterStatus == 'All Statuses' || $filterStatus == '') ? 'selected' : ''; ?>>Status</option>
                <option value="Pending" <?php echo ($filterStatus == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?php echo ($filterStatus == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?php echo ($filterStatus == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo ($filterStatus == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>

            <!-- Buttons -->
            <button class="btn-generate" type="submit">Generate</button>
            <?php if ($generateClicked): ?>
                <button type="button" class="btn-print" onclick="window.print()">Print</button>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($generateClicked): ?>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Concern Date</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($concernsData)): ?>
                            <?php foreach ($concernsData as $row): ?>
                                <tr>
                                    <td><?php echo $row['ConcernID']; ?></td>
                                    <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Room']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Problem_Type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Priority']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Concern_Date']); ?></td>
                                    <td>
                                        <?php 
                                            $statusClass = '';
                                            switch ($row['Status']) {
                                                case 'Completed': $statusClass = 'bg-success'; break;
                                                case 'In Progress': $statusClass = 'bg-warning text-dark'; break;
                                                case 'Pending': $statusClass = 'bg-danger'; break;
                                                case 'Cancelled': $statusClass = 'bg-secondary'; break;
                                                default: $statusClass = 'bg-info';
                                            }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill px-2 py-1"><?php echo htmlspecialchars($row['Status']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Assigned_to']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No concerns found matching the current filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
