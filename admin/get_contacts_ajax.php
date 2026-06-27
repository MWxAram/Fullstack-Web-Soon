<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) exit();

$my_id = (int)$_SESSION['user_id'];

// Этот запрос находит всех уникальных собеседников и их последнее сообщение с вами
$query = "
    SELECT 
        u.id, 
        u.username, 
        u.avatar, 
        m.message as last_text,
        m.sender_id as last_sender_id,
        m.id as last_msg_id,
        (SELECT COUNT(*) FROM private_messages WHERE receiver_id = ? AND sender_id = u.id AND is_read = 0) as unread_count
    FROM users u
    JOIN private_messages m ON m.id = (
        SELECT MAX(id) 
        FROM private_messages 
        WHERE (sender_id = u.id AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = u.id)
    )
    WHERE u.id != ?
    ORDER BY m.id DESC
";

try {
    $stmt = $pdo->prepare($query);
    // Теперь нам нужно передать my_id 4 раза (для ?, ?, ?, ?)
    $stmt->execute([$my_id, $my_id, $my_id, $my_id]);
    $chats = $stmt->fetchAll();
} catch (Exception $e) {
    echo '<div class="text-danger p-3 small">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}

if (empty($chats)) {
    echo '<div class="text-center text-secondary mt-3 small">Нет активных диалогов</div>';
    exit();
}

foreach ($chats as $chat) {
    $avatar = $chat['avatar'] ?: 'materials/avatar_default.png';
    $is_me = ($chat['last_sender_id'] == $my_id);
    
    $text = htmlspecialchars($chat['last_text'] ?? '');
    
    // Лимит 20 символов, обрезка на 15 + троеточие
    if (mb_strlen($text) > 20) {
        $text = mb_substr($text, 0, 15) . '...';
    }

    // Сине-голубой для ваших сообщений, серый для чужих
    $last_msg_html = $is_me 
        ? '<span style="color: #00d2ff;">Вы: ' . $text . '</span>' 
        : '<span style="color: #888;">' . $text . '</span>';

    $unread_badge = '';
    if (isset($chat['unread_count']) && $chat['unread_count'] > 0) {
        $display_count = $chat['unread_count'] > 99 ? '99+' : $chat['unread_count'];
        $unread_badge = '<span class="badge rounded-pill bg-danger ms-2" style="font-size: 0.7rem;">' . $display_count . '</span>';
    }

    echo '
    <div class="contact-item p-3 d-flex align-items-center border-bottom border-secondary border-opacity-10 dialog-item" 
         data-user-id="'.$chat['id'].'"
         onclick="openChat('.$chat['id'].', \''.htmlspecialchars($chat['username']).'\', \''.htmlspecialchars($avatar).'\', this)">
       <img src="'.htmlspecialchars($avatar).'" 
            class="rounded-circle me-3 border border-primary border-opacity-25" 
            style="width: 48px; height: 48px; object-fit: cover; flex-shrink: 0;">
       <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold text-light text-truncate" style="max-width: 80%;">'.htmlspecialchars($chat['username']).'</div>
                '.$unread_badge.'
            </div>
            <div class="small text-truncate" style="font-size: 0.85rem;">
                '.$last_msg_html.'
            </div>
       </div>
    </div>';
}