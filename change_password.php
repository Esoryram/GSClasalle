<?php
session_start();
include("config.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION['accountID'])){
    echo json_encode(['success'=>false,'message'=>'User not logged in.']);
    exit;
}

$accountID = $_SESSION['accountID'];
$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if(!$currentPassword || !$newPassword){
    echo json_encode(['success'=>false,'message'=>'Please provide all fields.']);
    exit;
}

// Fetch hashed password
$stmt = $conn->prepare("SELECT Password FROM Accounts WHERE AccountID=?");
$stmt->bind_param("i",$accountID);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    if(!password_verify($currentPassword, $row['Password'])){
        echo json_encode(['success'=>false,'message'=>'Current password is incorrect.']);
        exit;
    }

    // Hash new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmtUpdate = $conn->prepare("UPDATE Accounts SET Password=? WHERE AccountID=?");
    $stmtUpdate->bind_param("si", $newHash, $accountID);

    if($stmtUpdate->execute()){
        echo json_encode(['success'=>true,'message'=>'Password updated successfully!']);
    }else{
        echo json_encode(['success'=>false,'message'=>'Failed to update password.']);
    }
    $stmtUpdate->close();
}else{
    echo json_encode(['success'=>false,'message'=>'User not found.']);
}

$stmt->close();
$conn->close();
?>
