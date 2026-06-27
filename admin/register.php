<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../profile.php');
    exit();
}

require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы (те самые name="...")
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass_raw = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($pass_raw)) {
        $password = password_hash($pass_raw, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        // МАГИЯ: Получаем ID только что созданного аккаунта
        $newUserId = $pdo->lastInsertId();

        // Сразу авторизуем пользователя, чтобы ему не пришлось входить заново
        session_start();
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['username'] = $username;

        // Перекидываем в профиль
        header('Location: ../profile.php'); 
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header('Location: ../reg.php?error=exists');
        } else {
            die("Ошибка базы данных: " . $e->getMessage());
        }
    }
    }
}
?>