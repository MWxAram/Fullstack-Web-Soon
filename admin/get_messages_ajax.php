<?php
session_start();
require '../db.php';

if (isset($_GET['user_id']) && isset($_SESSION['user_id'])) {
    $my_id = (int)$_SESSION['user_id'];
    $other_id = (int)$_GET['user_id'];

    $stmt = $pdo->prepare("
        SELECT * FROM private_messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$my_id, $other_id, $other_id, $my_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($messages);
}
?>