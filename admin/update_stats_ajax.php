<?php
session_start();
require '../db.php';

if (isset($_POST['result']) && isset($_SESSION['user_id'])) {
    $my_id = (int)$_SESSION['user_id'];
    $result = $_POST['result'];

    if ($result === 'win') {
        // Если победа: +1 к победам, +1 к киллам, +1 к сыгранным раундам
        $sql = "UPDATE users SET 
                win = win + 1, 
                kill_count = kill_count + 1, 
                rounds_played = rounds_played + 1,
                win_seria = win_seria + 1 
                WHERE id = ?";
    } else {
        // Если поражение: +1 к смертям, +1 к сыгранным раундам, сброс серии побед
        $sql = "UPDATE users SET 
                dead_count = dead_count + 1, 
                rounds_played = rounds_played + 1,
                win_seria = 0 
                WHERE id = ?";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$my_id]);
    
    // Можно добавить логику повышения уровня (lvl) здесь же, 
    // например: если win кратно 10, то lvl = lvl + 1
}
?>