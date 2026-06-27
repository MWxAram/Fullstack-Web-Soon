<?php
session_start();
require '../db.php';

// Проверка: вошел ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $userId = $_SESSION['user_id'];

    // 1. Проверки безопасности файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        die("Ошибка: Разрешены только форматы JPG, PNG и GIF.");
    }

    if ($file['size'] > 2 * 1024 * 1024) { // Лимит 2 МБ
        die("Ошибка: Файл слишком большой. Максимум 2 МБ.");
    }

    // 2. Создаем уникальное имя для файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = "avatar_" . $userId . "_" . time() . "." . $extension;
    
    // Путь, куда сохраняем физически
    $uploadDir = '../uploads/avatars/';
    $uploadPath = $uploadDir . $fileName;

    // Создаем папку, если её еще нет
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 3. Перемещаем файл и обновляем данные
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        
        // Обновляем путь в базе данных
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$uploadPath, $userId]);

        // ГЛАВНЫЙ МОМЕНТ: Обновляем путь в текущей сессии!
        // Теперь на всех страницах сразу будет новая картинка
        $_SESSION['avatar'] = $uploadPath;

        // Возвращаемся в профиль
        header('Location: ../profile.php?success=1');
        exit();
    } else {
        die("Ошибка при сохранении файла на сервере.");
    }
}