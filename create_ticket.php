<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (!empty($subject) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $subject, $message]);
        
        // После создания сразу кидаем в чат
        $ticket_id = $pdo->lastInsertId();
        header("Location: player_chat.php?id=" . $ticket_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Техподдержка — Magic Duel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center mb-5">
                    <h2 class="text-warning text-uppercase fw-bold no-shadow">Свиток поддержки</h2>
                    <p class="text-light opacity-50 small">Опишите вашу проблему, и маги помогут вам</p>
                </div>

                <div class="ticket-form-card">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label text-warning small fw-bold text-uppercase">Тема обращения</label>
                            <input type="text" name="subject" class="form-control form-control-lg" placeholder="Напр: Проблема с оплатой" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-warning small fw-bold text-uppercase">Ваше сообщение</label>
                            <textarea name="message" class="form-control" rows="6" placeholder="Подробно опишите ситуацию..." required></textarea>
                        </div>
                        <button type="submit" name="send_ticket" class="btn btn-warning w-100 fw-bold py-3 text-uppercase">Отправить свиток</button>
                    </form>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted text-decoration-none small">← Вернуться на главную</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>