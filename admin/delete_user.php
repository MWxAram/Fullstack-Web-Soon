<?php
session_start();
require '../db.php';

// 1. Проверка авторизации и уровня доступа
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();

// Удалять может только админ (например, уровень 3 и выше)
if (!$me || $me['admin_level'] < 3) {
    header('Location: admin_users.php?error=no_permission');
    exit();
}

// 2. Получаем ID того, кого хотим удалить
if (isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];

    // Запрет на удаление самого себя
    if ($target_id === (int)$$_SESSION['user_id']) {
        header('Location: admin_users.php?error=self_delete');
        exit();
    }

    // Проверка иерархии: нельзя удалить админа равного или выше уровнем
    $target_check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
    $target_check->execute([$target_id]);
    $target = $target_check->fetch();

    if ($target) {
        if ($target['admin_level'] >= $me['admin_level']) {
            header('Location: admin_users.php?error=hierarchy_violation');
            exit();
        }

        // 3. Само удаление
        $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delete->execute([$target_id]);
        
        header('Location: admin_users.php?status=deleted');
        exit();
    }
}

header('Location: admin_users.php');
exit();