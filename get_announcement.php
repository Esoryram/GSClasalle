<?php
include("config.php");

$query = "SELECT Title, Content, Created_At FROM announcements ORDER BY Created_At DESC";
$result = mysqli_query($conn, $query);

$announcements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = [
        "title" => htmlspecialchars($row['Title']),
        "details" => nl2br(htmlspecialchars($row['Content'])),
        "date" => date("F d, Y", strtotime($row['Created_At'])) // ✅ Date only
    ];
}

header('Content-Type: application/json');
echo json_encode($announcements);
?>
