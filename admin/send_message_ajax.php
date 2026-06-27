<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $sender_id = (int)$_SESSION['user_id'];
    $receiver_id = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO private_messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$sender_id, $receiver_id, $message]);
        echo "success";
    }
}
?>