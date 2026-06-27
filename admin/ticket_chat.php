<?php
session_start();
require '../db.php'; // Выходим из папки admin к базе

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Получаем данные тикета
$stmt = $pdo->prepare("SELECT t.*, u.username FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) { die("Тикет не найден."); }

// 2. ОБРАБОТКА: Смена статуса админом
if (isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    $upd = $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
    $upd->execute([$new_status, $ticket_id]);
    header("Location: ticket_chat.php?id=" . $ticket_id);
    exit();
}

// 3. ОБРАБОТКА: Отправка сообщения
if (isset($_POST['send_msg'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $ins = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 1)");
        $ins->execute([$ticket_id, $_SESSION['user_id'], $msg]);
        header("Location: ticket_chat.php?id=" . $ticket_id);
        exit();
    }
}

// 4. Получаем сообщения
$messages = $pdo->prepare("SELECT m.*, u.username FROM ticket_messages m JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$messages->execute([$ticket_id]);
$all_msgs = $messages->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Управление тикетом #<?= $ticket_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <div class="container">
        <div class="chat-container">
            <div class="chat-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="text-warning mb-1 fw-bold text-uppercase"><?= htmlspecialchars($ticket['subject']) ?> #<?= $ticket_id ?></h5>
                    <?php 
                        $badge_class = "bg-" . str_replace('_', '-', $ticket['status']);
                        $status_names = [
                            'pending' => 'Рассмотрение',
                            'process' => 'В обработке',
                            'closed_by_admin' => 'Ожидает закрытия игроком',
                            'closed' => 'Закрыто'
                        ];
                    ?>
                    <span class="badge-status <?= $badge_class ?>"><?= $status_names[$ticket['status']] ?></span>
                </div>

                <form method="POST" id="statusForm">
                    <select name="new_status" class="magic-select" onchange="this.form.submit()">
                        <option value="pending" <?= $ticket['status'] == 'pending' ? 'selected' : '' ?>>Рассмотрение</option>
                        <option value="process" <?= $ticket['status'] == 'process' ? 'selected' : '' ?>>В обработке</option>
                        <option value="closed_by_admin" <?= $ticket['status'] == 'closed_by_admin' ? 'selected' : '' ?>>Решено (Закрыть)</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>

                <a href="admin_tickets.php" class="btn btn-outline-light btn-sm">Назад</a>
            </div>

            <div class="chat-messages" id="chatBox">
                <div class="msg msg-left">
                    <div class="small text-warning fw-bold mb-1">Вопрос от <?= htmlspecialchars($ticket['username']) ?>:</div>
                    <?= nl2br(htmlspecialchars($ticket['message'])) ?>
                </div>

                <?php foreach ($all_msgs as $m): ?>
                    <div class="msg <?= $m['is_admin'] ? 'msg-right' : 'msg-left' ?>">
                        <div class="small fw-bold mb-1" style="color: <?= $m['is_admin'] ? '#ffaa00' : '#aaa' ?>;">
                            <?= $m['is_admin'] ? 'Вы (Администратор)' : htmlspecialchars($m['username']) ?>
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
            fetch(`get_chat_updates.php?ticket_id=${ticketId}&last_id=${lastMsgId}`)
                .then(response => response.json())
                .then(data => {
                    // 1. Новые сообщения
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            // Для админа "свои" — это те, где is_admin = 1
                            const isMyMsg = (msg.is_admin == 1);
                            const msgHtml = `
                                <div class="msg ${isMyMsg ? 'msg-right' : 'msg-left'}">
                                    <div class="small fw-bold mb-1" style="color: ${isMyMsg ? '#ffaa00' : '#aaa'};">
                                        ${isMyMsg ? 'Вы (Администратор)' : msg.username}
                                    </div>
                                    ${msg.message.replace(/\n/g, '<br>')}
                                </div>`;
                            chatBox.insertAdjacentHTML('beforeend', msgHtml);
                            lastMsgId = msg.id;
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }

                    // 2. Проверка изменения статуса
                    const currentBadge = document.querySelector('.badge-status');
                    const currentStatusText = currentBadge.innerText.toLowerCase();
                    
                    // Сопоставляем статус из БД с текстом на экране
                    const statusNames = {
                        'pending': 'рассмотрение',
                        'process': 'в обработке',
                        'closed_by_admin': 'ожидает закрытия',
                        'closed': 'закрыто'
                    };

                    if (statusNames[data.status] && !currentStatusText.includes(statusNames[data.status])) {
                        location.reload(); 
                    }
                })
                .catch(err => console.error('Ошибка обновления:', err));
        }

        // Автоматическая смена статуса через AJAX (у админа)
document.querySelector('.magic-select').onchange = function() {
    const newStatus = this.value;
    const formData = new FormData();
    formData.append('new_status', newStatus);
    formData.append('update_status', '1');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(() => {
        // ВИЗУАЛЬНОЕ ОБНОВЛЕНИЕ ПЛАШКИ У АДМИНА
        const badge = document.querySelector('.badge-status');
        const statusNames = {
            'pending': 'Рассмотрение',
            'process': 'В обработке',
            'closed_by_admin': 'Ожидает закрытия игроком',
            'closed': 'Закрыто'
        };
        
        // Обновляем текст и класс (цвет)
        badge.innerText = statusNames[newStatus];
        badge.className = 'badge-status bg-' + newStatus.replace(/_/g, '-');
        
        checkUpdates();
    });
};

// Отправка сообщений через AJAX
document.getElementById('chatForm').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('send_msg', '1'); // Явно указываем действие
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(() => {
        this.reset();
        checkUpdates();
    });
};

        // Проверка каждые 3 секунды
        setInterval(checkUpdates, 3000);
    </script>
</body>
</html>