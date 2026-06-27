<?php
session_start();
require 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Проверяем, есть ли у этого игрока открытый тикет
$stmt = $pdo->prepare("SELECT id FROM support_tickets WHERE user_id = ? AND status != 'closed' LIMIT 1");
$stmt->execute([$user_id]);
$active_ticket = $stmt->fetch();

if ($active_ticket) {
    // Если тикет найден — перекидываем в чат
    header("Location: player_chat.php?id=" . $active_ticket['id']);
} else {
    // Если тикетов нет — отправляем на создание нового
    header("Location: create_ticket.php");
}
exit();