<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$my_id = (int)$_SESSION['user_id']; 

// Данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$my_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Magic Duel — Сообщения</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="magic-background"></div>

<nav class="navbar navbar-expand-lg mx-3" style="height: 70px;">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <!-- Логотип (ссылка на главную) -->
            <a class="navbar-brand d-flex align-items-center me-0" href="index.php">
                <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
            </a>

            <!-- Ссылка WIKI (теперь сразу после лого) -->
            <a href="wiki.php" class="btn btn-link btn-wiki-text px-2">WIKI</a>
        </div>

            <span class="btn btn-link btn-wiki-text ms-2">Magic Duel</span>
        
        <div class="d-flex align-items-center">
            <!-- Переключатель темы -->
            <button class="btn theme-btn-clean me-4" id="themeToggler" onclick="toggleTheme()">☀️</button>
            
            <!-- Аватарка профиля -->
            <a href="profile.php" class="d-block">
                <img src="<?= $user['avatar'] ?: 'materials/avatar_default.png' ?>" 
                     alt="Profile" 
                     class="rounded-circle border border-2"
                     style="width: 40px; height: 40px; object-fit: cover; transition: transform 0.2s ease;"
                     onmouseover="this.style.transform='scale(1.1)'" 
                     onmouseout="this.style.transform='scale(1)'">
            </a>
        </div>
    </div>
</nav>

    <!-- Новая обертка-ограничитель -->
    <div class="container messenger-wrapper">
        <div class="row messenger-height">
            <!-- Список диалогов -->
            <div class="col-lg-4 h-100 pb-3">
                <div class="profile-card p-0">
                    <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">ДИАЛОГИ</h5>
                        <button class="btn btn-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#findUserModal">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="contacts-scroll" id="contacts-list">
                        <!-- Сюда JS будет подгружать контакты -->
                    </div>
                </div>
            </div>

            <!-- Чат -->
            <div class="col-lg-8 h-100 pb-3">
                <div class="profile-card p-0">
                    <div id="chat-top-bar" class="p-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center d-none">
                        <img id="chat-avatar" src="" class="rounded-circle me-3 border border-info" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-white fs-5" id="chat-username"></div>
                            <div class="status-header">● В СЕТИ</div>
                        </div>
                    </div>

                    <div class="chat-messages" id="messages-window">
                        <div class="text-center text-secondary mt-5">Выберите мага для общения</div>
                    </div>

                    <div id="input-area" class="p-3 border-top border-secondary border-opacity-25 d-none">
                        <form id="send-form" class="d-flex align-items-center gap-2">
                            <input type="text" id="message-text" class="form-control bg-dark border-secondary text-white py-2 shadow-none" placeholder="Напишите сообщение..." required autocomplete="off">
                            <button type="submit" class="btn btn-magic-send">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка поиска -->
    <div class="modal fade" id="findUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">ПОИСК МАГА</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="user-search" class="form-control bg-black border-primary text-white mb-3" placeholder="Введите ник...">
                    <div id="search-results"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        let activeChatId = null;
        const myId = <?= $my_id ?>;

        // Загрузка контактов (левая панель)
        function loadContacts() {
            fetch('admin/get_contacts_ajax.php')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('contacts-list').innerHTML = html;
                    if (activeChatId) {
                        const active = document.querySelector(`.dialog-item[data-user-id="${activeChatId}"]`);
                        if (active) active.classList.add('active');
                    }
                });
        }

        // Новая вспомогательная функция
        function markAsRead(userId) {
            const fd = new FormData();
            fd.append('user_id', userId);
            fetch('admin/mark_read_ajax.php', { method: 'POST', body: fd })
                .then(() => {
                    loadContacts(); // Перерисовываем список слева, чтобы цифра исчезла
                });
        }

        function openChat(id, name, avatar, el) {
            activeChatId = id;
            document.querySelectorAll('.dialog-item').forEach(i => i.classList.remove('active'));
            if(el) el.classList.add('active');

            document.getElementById('chat-top-bar').classList.remove('d-none');
            document.getElementById('input-area').classList.remove('d-none');
            document.getElementById('chat-username').innerText = name;
            document.getElementById('chat-avatar').src = avatar || 'materials/avatar_default.png';

            // Помечаем сообщения как прочитанные
            const fd = new FormData();
            fd.append('user_id', id);
            fetch('../mark_read_ajax.php', { method: 'POST', body: fd })
            .then(() => loadContacts()); // Обновляем список, чтобы убрать цифру
            markAsRead(id);
            loadMessages();
        }

        function loadMessages() {
    if(!activeChatId) return;
    fetch(`admin/get_messages_ajax.php?user_id=${activeChatId}`)
        .then(r => r.json())
        .then(data => {
            const win = document.getElementById('messages-window');
            const isAtBottom = win.scrollHeight - win.scrollTop <= win.clientHeight + 100;

            win.innerHTML = data.map(m => `
                <div class="d-flex mb-3 ${m.sender_id == myId ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="msg-bubble ${m.sender_id == myId ? 'outgoing' : 'incoming'} p-3">
                        <div class="small">${m.message}</div>
                        <div class="text-end" style="font-size:0.6rem; opacity:0.5; margin-top:4px;">
                            ${m.created_at.split(' ')[1].substr(0,5)}
                        </div>
                    </div>
                </div>
            `).join('');

            // Скроллим вниз только если мы и так были внизу или это только что открытый чат
            win.scrollTop = win.scrollHeight;
        });
      }

        document.getElementById('send-form').onsubmit = function(e) {
            e.preventDefault();
            const txt = document.getElementById('message-text');
            if (!txt.value.trim() || !activeChatId) return;

            const fd = new FormData();
            fd.append('receiver_id', activeChatId);
            fd.append('message', txt.value);

            fetch('admin/send_message_ajax.php', { method: 'POST', body: fd })
                .then(response => {
                    if (response.ok) {
                        txt.value = ''; // Очищаем поле
                        loadMessages(); // Обновляем чат
                        loadContacts(); // Обновляем список слева (ВАЖНО!)
                    }
                });
        };
        document.getElementById('user-search').addEventListener('input', function() {
            const val = this.value.trim();
            const res = document.getElementById('search-results');
            if(!val) { res.innerHTML = ''; return; }
            fetch(`../search_users_ajax.php?q=${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(users => {
                    res.innerHTML = users.map(u => `
                        <div class="search-result-item p-3 d-flex align-items-center" style="cursor:pointer;" 
                             onclick="openChat(${u.id}, '${u.username}', '${u.avatar}', null); bootstrap.Modal.getInstance(document.getElementById('findUserModal')).hide();">
                            <img src="${u.avatar || 'materials/avatar_default.png'}" class="rounded-circle me-3" width="40" height="40" style="object-fit:cover;">
                            <div>
                                <div class="fw-bold text-white">${u.username}</div>
                                <div class="text-info small">ID: ${u.id}</div>
                            </div>
                        </div>
                    `).join('');
                });
        });

        loadContacts(); // Первичная загрузка
        setInterval(loadMessages, 3000);
        setInterval(loadContacts, 10000);
    </script>
</body>
</html>