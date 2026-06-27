<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../profile.php');
    exit();
}
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // В переменную $login записываем то, что ввел пользователь в первое поле
    $login = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? '';

    if (!empty($login) && !empty($password)) {
        try {
            // Ищем совпадение либо в колонке username, либо в колонке email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$login, $login]); // Передаем введенное значение дважды
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Если пароль подошел, сохраняем данные в сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['avatar'] = $user['avatar'];
                
                header('Location: ../profile.php');
                exit();
            } else {
                // Если совпадений нет или пароль не тот
                header('Location: ../login.php?error=wrong');
                exit();
            }
        } catch (Exception $e) {
            // Техническая ошибка (например, база упала)
            header('Location: ../login.php?error=server');
            exit();
        }
    }
}
?>