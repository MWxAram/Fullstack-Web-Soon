<?php
session_start();
require '../db.php';

if (isset($_POST['user_id']) && isset($_SESSION['user_id'])) {
    $my_id = (int)$_SESSION['user_id'];
    $other_id = (int)$_POST['user_id'];

    // Обнуляем счетчик: ставим is_read = 1 для всех сообщений, где отправитель — собеседник, а получатель — ТЫ
    $stmt = $pdo->prepare("
        UPDATE private_messages 
        SET is_read = 1 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$other_id, $my_id]);
    echo "success";
}
?>