<?php
require '../db.php';
$pass = 'root'; // В MAMP пароль обычно root
$port = '8889'; // Стандартный порт MySQL в MAMP
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';

$response = ['username_exists' => false, 'email_exists' => false];

if (!empty($username)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) $response['username_exists'] = true;
}

if (!empty($email)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $response['email_exists'] = true;
}

header('Content-Type: application/json');
echo json_encode($response);