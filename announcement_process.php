<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    // Get the logged-in user's AccountID
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $accountID = $user['AccountID'] ?? null;

    // Insert into announcements
    if ($title && $content && $accountID) {
        $insert = $conn->prepare("
            INSERT INTO announcements (title, content, AccountID, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $insert->bind_param("ssiss", $title, $content, $accountID, $start_date, $end_date);

        if ($insert->execute()) {
            echo "<script>
                alert('Announcement posted successfully!');
                window.location.href='admindb.php';
            </script>";
        } else {
            echo "<script>alert('Error posting announcement.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Please fill out all required fields.'); window.history.back();</script>";
    }
}
?>
