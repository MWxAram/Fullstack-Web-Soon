<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$my_id = $_SESSION['user_id'];

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Выбираем ID, ник и аватарку. 
// ВАЖНО: убедись, что колонка в таблице users называется 'avatar'
$stmt = $pdo->prepare("
    SELECT id, username, avatar FROM users 
    WHERE (username LIKE :q OR id = :id) AND id != :my_id 
    LIMIT 10
");

$stmt->execute([
    'q' => "%$query%",
    'id' => is_numeric($query) ? (int)$query : -1,
    'my_id' => $my_id
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка аватарок: если её нет, ставим стандартную
foreach ($results as &$user) {
    if (empty($user['avatar'])) {
        $user['avatar'] = 'uploads/avatars/no_avatar.png'; // Путь к дефолтной картинке
    }
}

$my_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);

if (!empty($message)) {
    $stmt = $pdo->prepare("INSERT INTO private_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$my_id, $receiver_id, $message]);
}

echo json_encode($results);