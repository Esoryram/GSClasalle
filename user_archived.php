<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// Determine return page
if (isset($_GET['return'])) {
    $returnPage = $_GET['return'];
} else {
    $referrer = isset($_SERVER['HTTP_REFERER']) ? basename($_SERVER['HTTP_REFERER']) : '';
    if ($referrer === 'userconcerns.php') {
        $returnPage = 'userconcerns.php';
    } else {
        $returnPage = 'userdb.php';
    }
}

// Get AccountID of the logged-in user
$userQuery = "SELECT AccountID FROM Accounts WHERE Username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userRow = $userResult->fetch_assoc();
$accountID = $userRow ? $userRow['AccountID'] : 0;
$stmt->close();

// Get only completed or cancelled concerns of the logged-in user
$concernsQuery = "SELECT * FROM Concerns WHERE AccountID = ? AND (Status = 'Completed' OR Status = 'Cancelled') ORDER BY Concern_Date DESC";
$stmt2 = $conn->prepare($concernsQuery);
$stmt2->bind_param("i", $accountID);
$stmt2->execute();
$concernsResult = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Archived Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 20px; /* space between logo and text */
}

.navbar .logo img {
    height: 40px;
    width: auto;
    object-fit: contain;
}

.navbar .logo h2 {
    margin: 0;
    font-size: 22px; /* slightly larger text */
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.navbar h2 {
    margin: 0;
    font-size: 20px;
}

.return-btn {
    background: #107040;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.return-btn:hover {
    background: #07532e;
}

/* Main Layout */
.main {
    padding: 10px;
    text-align: center;
}

/* Submit Button (outside the box) */
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

/* Concern Box */
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

/* Header inside box */
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

/* Accordion */
.accordion-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.accordion-button {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    border: 1px solid #dee2e6;
    border-radius: 8px;
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

/* Status Badges */
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 12px;
    margin-left: 280px;
}

/* Concern Fields */
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

/* Submit Feedback Button */
.feedback-btn {
    background: #28a745;
    color: white;
    font-weight: bold;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin-left: 10px;
    font-size: 16px;
    width: 250px;
    height: 45px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.feedback-btn:hover {
    background: #218838;
}

.status-completed {
    background: #d1edff;
    color: #0c5460;
}
.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.badge {
    border-radius: 0.75rem;
    padding: 6px 10px;
    font-size: 0.8rem;
}

</style>
</head>
<body>

<!-- Navbar -->
 
<div class="navbar">
    <div class="logo">
         <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Archived Concerns</h2>
    </div>
    <button class="return-btn" onclick="window.location.href='<?php echo htmlspecialchars($returnPage); ?>'">
        Return
    </button>
</div>
<!-- Main Content -->
<div class="main">

    <!-- Submit Button Outside Box -->
    <div class="d-flex justify-content-end mb-3" style="max-width: 850px; margin: 0 auto;">
        <button class="submit-btn-top" onclick="window.location.href='usersubmit.php'">
            + Submit New Concern
        </button>
    </div>

    <div class="concern-container">
        <div class="concern-header">Concerns Details</div>

        <div class="accordion" id="concernsAccordion">
            <?php
            if ($concernsResult && $concernsResult->num_rows > 0) {
                $index = 1;
                while ($row = $concernsResult->fetch_assoc()) {
                    $status = isset($row['Status']) ? $row['Status'] : 'Completed';
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
                    $concernID = $row['ConcernID'];

                    // status classes only for archived page (Completed / Cancelled)
                    switch ($row['Status']) {
                        case 'Completed':
                            $statusClass = 'bg-success text-white';
                            break;
                        case 'Cancelled':
                            $statusClass = 'bg-secondary text-white';
                            break;
                        default:
                            $statusClass = 'bg-light text-dark';
                    }
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#concern<?= $index ?>" 
                                aria-expanded="false">
                                <?= $date ?>
                                <span class="badge <?php echo $statusClass; ?>" style="margin-left:10px;">
                                    <?php echo htmlspecialchars($row['Status']); ?>
                                </span>
                            </button>
                            <button class="feedback-btn" 
                                onclick="window.location.href='user_feedback.php?concern_id=<?= $concernID ?>'">
                                Submit Feedback
                            </button>
                        </h2>
                        <div id="concern<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#concernsAccordion">
                            <div class="accordion-body">
                                <div class="form-field">
                                    <label>Concern Title</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Concern_Title']) ?></div>
                                </div>
                                <div class="form-field">
                                    <label>Description</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Description']) ?></div>
                                </div>
                                <div class="form-field">
                                    <label>Problem Type</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Problem_Type']) ?></div>
                                </div>
                                <div class="form-field">
                                    <label>Priority</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Priority']) ?></div>
                                </div>
                                <div class="form-field">
                                    <label>Assigned To</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Assigned_to']) ?></div>
                                </div>
                                <div class="form-field">
                                    <label>Attachment</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Attachment']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $index++;
                }
            } else {
                echo '<div class="alert alert-info">No completed concerns to display.</div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
