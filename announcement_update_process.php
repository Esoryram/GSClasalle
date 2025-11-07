<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['AnnouncementID'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $sql = "UPDATE Announcements 
            SET title=?, content=?, start_date=?, end_date=? 
            WHERE AnnouncementID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $content, $start, $end, $id);

    if ($stmt->execute()) {
        echo "Announcement updated successfully!";
    } else {
        echo "Error updating announcement.";
    }
    $stmt->close();
}
?>
