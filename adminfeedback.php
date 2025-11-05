<?php
session_start();
include("config.php");

// Only allow admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle admin response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['response'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);

    if (!empty($response)) {
        $stmt = $conn->prepare(
            "UPDATE Feedback SET Admin_Response = ?, Date_Responded = NOW() WHERE FeedbackID = ?"
        );
        $stmt->bind_param("si", $response, $feedback_id);
        $stmt->execute();
    }
}

// Fetch all feedback with concerns and users
$query = "
    SELECT f.FeedbackID, f.Comments, f.Date_Submitted,
           c.Concern_Title, c.Description, c.Room, c.Problem_Type, c.Priority,
           a.Username, a.Name
    FROM Feedback f
    JOIN Concerns c ON f.ConcernID = c.ConcernID
    JOIN Accounts a ON f.AccountID = a.AccountID
    ORDER BY f.Date_Submitted DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Feedback</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
    padding-top: 80px;
}

.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    color: white;
    padding: 15px 30px;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 20px;
}

.navbar .logo img {
    height: 40px;
    width: auto;
}

.navbar .logo h2 {
    margin: 0;
    font-size: 22px;
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

.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Admin Feedback</h2>
    </div>
    <button class="return-btn" onclick="window.history.back()">Return</button>
</div>

<div class="container table-container">
    <h2>All User Feedback</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Concern Title</th>
                <th>Description</th>
                <th>Room</th>
                <th>Problem Type</th>
                <th>Priority</th>
                <th>Comments</th>
                <th>Date Submitted</th>
                <th>Response</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['FeedbackID'] ?></td>
                        <td><?= htmlspecialchars($row['Name'] ?? $row['Username']) ?></td>
                        <td><?= htmlspecialchars($row['Concern_Title']) ?></td>
                        <td><?= htmlspecialchars($row['Description']) ?></td>
                        <td><?= htmlspecialchars($row['Room'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['Problem_Type']) ?></td>
                        <td><?= htmlspecialchars($row['Priority']) ?></td>
                        <td><?= htmlspecialchars($row['Comments']) ?></td>
                        <td><?= $row['Date_Submitted'] ?></td>
                        <td><?= htmlspecialchars($row['Admin_Response'] ?? 'No response yet') ?></td>
                        <td>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#responseModal<?= $row['FeedbackID'] ?>">Respond</button>

                            <!-- Modal -->
                            <div class="modal fade" id="responseModal<?= $row['FeedbackID'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['FeedbackID'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel<?= $row['FeedbackID'] ?>">Respond to Feedback #<?= $row['FeedbackID'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <textarea name="response" class="form-control" rows="4" placeholder="Enter your response here..."><?= htmlspecialchars($row['Admin_Response'] ?? '') ?></textarea>
                                            <input type="hidden" name="feedback_id" value="<?= $row['FeedbackID'] ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Submit Response</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="text-center">No feedback available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
