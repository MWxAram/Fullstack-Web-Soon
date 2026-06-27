<?php
session_start();
require '../db.php';

// Обязательно сообщаем браузеру, что это JSON
header('Content-Type: application/json');

$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($ticket_id === 0) {
    echo json_encode(['status' => 'error', 'messages' => []]);
    exit;
}

// 1. Получаем статус
$stmt = $pdo->prepare("SELECT status FROM support_tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

// 2. Получаем новые сообщения
$stmt = $pdo->prepare("SELECT m.*, u.username FROM ticket_messages m 
                       JOIN users u ON m.user_id = u.id 
                       WHERE m.ticket_id = ? AND m.id > ? 
                       ORDER BY m.created_at ASC");
$stmt->execute([$ticket_id, $last_id]);
$new_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Только JSON и ничего лишнего
echo json_encode([
    'status' => $ticket['status'] ?? 'pending',
    'messages' => $new_messages
]);