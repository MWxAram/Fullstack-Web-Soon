<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Проверяем, принадлежит ли этот тикет игроку
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) { die("Доступ запрещен или тикет не найден."); }

// Отправка сообщения игроком
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $ins = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 0)");
        $ins->execute([$ticket_id, $user_id, $msg]);
        header("Location: player_chat.php?id=" . $ticket_id);
        exit();
    }
}

if (isset($_POST['finish_ticket'])) {
    $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed' WHERE id = ?");
    $stmt->execute([$ticket_id]);
    header("Location: profile.php");
    exit();
}

// Загрузка сообщений
$messages = $pdo->prepare("SELECT m.*, u.username FROM ticket_messages m JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$messages->execute([$ticket_id]);
$all_msgs = $messages->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Связь с Магами — Magic Duel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <div class="container">
        <div class="chat-container">
<div class="chat-header d-flex justify-content-between align-items-center">
    <div>
        <h5 class="text-warning mb-1 fw-bold text-uppercase" style="font-size: 1rem;">
            <?= htmlspecialchars($ticket['subject']) ?>
        </h5>
        
        <?php 
            $status_map = [
                'pending' => ['text' => 'Рассмотрение', 'class' => 'bg-pending'],
                'process' => ['text' => 'В обработке', 'class' => 'bg-process'],
                'closed_by_admin' => ['text' => 'Решено', 'class' => 'bg-closed-admin']
            ];
            $current = $status_map[$ticket['status']];
        ?>
        <span class="badge-status <?= $current['class'] ?>" style="font-size: 0.7rem;">
            <?= $current['text'] ?>
        </span>
    </div>

    <div class="chat-actions">
        <a href="profile.php" class="btn-back-sm">
            ← Назад
        </a>
        
        <form method="POST" class="m-0">
            <button type="submit" name="finish_ticket" class="btn btn-sm btn-finish fw-bold" style="font-size: 0.75rem; padding: 5px 12px;">
                <?= ($ticket['status'] == 'closed_by_admin') ? 'ПОДТВЕРДИТЬ' : 'ЗАКРЫТЬ РЕПОРТ' ?>
            </button>
        </form>
    </div>
</div>

            <div class="chat-messages" id="chatBox">
                <div class="msg msg-left">
                    <div class="small text-warning fw-bold mb-1">Ваш запрос:</div>
                    <?= nl2br(htmlspecialchars($ticket['message'])) ?>
                </div>

                <?php foreach ($all_msgs as $m): ?>
                    <div class="msg <?= $m['is_admin'] ? 'msg-right' : 'msg-left' ?>">
                        <div class="small fw-bold mb-1" style="color: <?= $m['is_admin'] ? '#ffaa00' : '#aaa' ?>;">
                            <?= $m['is_admin'] ? 'Мастер Арены' : 'Вы' ?>
                        </div>
                        <?= nl2br(htmlspecialchars($m['message'])) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-input-area">
                <form method="POST" id="chatForm" class="d-flex gap-2">
                    <textarea name="message" class="form-control chat-form-control" rows="2" placeholder="Напишите ответ..." required></textarea>
                    <button type="submit" name="send_msg" class="btn btn-warning fw-bold px-4">ОТПРАВИТЬ</button>
                    <input type="hidden" name="send_msg" value="1">
                </form>
            </div>
        </div>
    </div>

<script>
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;

    let lastMsgId = <?= count($all_msgs) > 0 ? end($all_msgs)['id'] : 0 ?>;
    const ticketId = <?= $ticket_id ?>;

    function checkUpdates() {
        // У игрока путь обычно 'get_chat_updates.php' (без ../), если файл в корне
        fetch(`../get_chat_updates.php?ticket_id=${ticketId}&last_id=${lastMsgId}`)
            .then(response => response.json())
            .then(data => {
                // 1. Обработка новых сообщений
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const fromAdmin = (msg.is_admin == 1);
                        const msgHtml = `
                            <div class="msg ${fromAdmin ? 'msg-right' : 'msg-left'}">
                                <div class="small fw-bold mb-1" style="color: ${fromAdmin ? '#ffaa00' : '#aaa'};">
                                    ${fromAdmin ? 'Мастер Арены' : 'Вы'}
                                </div>
                                ${msg.message.replace(/\n/g, '<br>')}
                            </div>`;
                        chatBox.insertAdjacentHTML('beforeend', msgHtml);
                        lastMsgId = msg.id;
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }

                // 2. Логика статуса (только если badge существует на странице)
                const badge = document.querySelector('.badge-status');
                if (badge && data.status) {
                    const statusMap = {
                        'pending': 'рассмотрение',
                        'process': 'в обработке',
                        'closed_by_admin': 'решено',
                        'closed': 'закрыто'
                    };

                    const currentText = badge.innerText.toLowerCase();
                    const targetText = statusMap[data.status];
                    
                    if (targetText && !currentText.includes(targetText)) {
                        // Перезагружаем, чтобы обновить кнопки и PHP-логику
                        window.location.reload(); 
                    }
                }
            })
            .catch(err => console.error('Ошибка связи с сервером:', err));
    }

    // AJAX отправка формы
    document.getElementById('chatForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('send_msg', '1'); // Добавляем флаг для PHP
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(() => {
            this.reset();
            checkUpdates(); 
        });
    };

    // Опрос сервера каждые 3 секунды
    setInterval(checkUpdates, 3000);
</script>
</body>
</html>