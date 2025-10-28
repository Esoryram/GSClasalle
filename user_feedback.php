<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// Get AccountID
$userQuery = "SELECT AccountID FROM Accounts WHERE Username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userRow = $userResult->fetch_assoc();
$accountID = $userRow ? $userRow['AccountID'] : 0;

// Get ConcernID from GET
$concernID = isset($_GET['concern_id']) ? intval($_GET['concern_id']) : 0;

// Fetch Concern Details
$concernQuery = "SELECT * FROM Concerns WHERE ConcernID = ? AND AccountID = ?";
$stmt = $conn->prepare($concernQuery);
$stmt->bind_param("ii", $concernID, $accountID);
$stmt->execute();
$concernResult = $stmt->get_result();
$concern = $concernResult->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comments = trim($_POST['comments']);
    if (!empty($comments)) {
        $insertQuery = "INSERT INTO Feedback (ConcernID, AccountID, Comments, Date_Submitted) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iis", $concernID, $accountID, $comments);
        if ($stmt->execute()) {
            echo "<script>
                    alert('Feedback submitted successfully!');
                    window.location.href = 'user_archived.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('Error submitting feedback. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Comments cannot be empty.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 20px;
}

.navbar .logo img {
    height: 40px;
    width: auto;
    object-fit: contain;
}

.navbar .logo h2 {
    margin: 0;
    font-size: 22px;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
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

.main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 30px;
    overflow: auto;
}

.feedback-container {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 700px;
    max-height: 80vh;
    overflow-y: auto;
}

.accordion-button {
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    color: white;
    font-weight: 600;
}

.accordion-button:not(.collapsed) {
    color: white;
    background: linear-gradient(135deg, #1f9158, #275850, #1c4440, #163a37);
}

.accordion-body {
    background: #f8f9fa;
    padding: 20px;
}

.form-label {
    font-weight: 600;
    color: #163a37;
    margin-top: 8px; 
}

.form-control,
.form-select {
    border-radius: 8px;
}

textarea {
    resize: vertical;
}

.feedback-btn {
    background: #28a745;
    color: white;
    font-weight: bold;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.feedback-btn:hover {
    background: #218838;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Feedback</h2>
    </div>
    <button class="return-btn" onclick="window.history.back()">Return</button>
</div>

<div class="main">
    <div class="feedback-container">

        <?php if($concern) { ?>
        <div class="accordion" id="feedbackAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingConcern">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConcern" aria-expanded="false" aria-controls="collapseConcern">
                        Concern Details
                    </button>
                </h2>
                <div id="collapseConcern" class="accordion-collapse collapse" aria-labelledby="headingConcern" data-bs-parent="#feedbackAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Concern Title</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Concern_Title']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($concern['Description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Room'] ?? 'N/A') ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment / Facility</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Equipment'] ?? 'N/A') ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Problem Type</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Problem_Type']) ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Priority']) ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attachment (Photo/Video)</label>
                            <?php if(!empty($concern['Attachment'])): ?>
                                <a href="uploads/<?= htmlspecialchars($concern['Attachment']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">View Attachment</a>
                            <?php else: ?>
                                <input type="text" class="form-control" value="No attachment uploaded" readonly>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label for="comments" class="form-label">Comments</label>
                <textarea id="comments" name="comments" class="form-control" rows="3" placeholder="Enter your feedback here..."></textarea>
            </div>
            <button type="submit" class="feedback-btn">Submit Feedback</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
