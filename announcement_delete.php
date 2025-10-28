<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Delete announcement by ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // sanitize input

    $deleteQuery = "DELETE FROM Announcements WHERE AnnouncementID = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: adminannouncement.php");
        exit();
    } else {
        echo "Error deleting announcement: " . mysqli_error($conn);
    }
} else {
    header("Location: adminannouncement.php");
    exit();
}
