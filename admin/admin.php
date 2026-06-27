<?php
session_start();
require '../db.php'; // Правильный путь к базе

// 1. Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 2. Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
// Считаем тикеты и репорты для админки
$new_tickets = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'")->fetchColumn();
$total_reports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

// 3. Если не админ — выкидываем в корень
if (!$user || $user['admin_level'] < 1) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — Magic Duel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet"> </head>
<body>
    <div class="magic-background"></div>

    <nav class="navbar navbar-expand-lg mx-3">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <!-- Логотип (ссылка на главную) -->
                <a class="navbar-brand d-flex align-items-center me-0" href="../index.php">
                    <img src="../materials/logo_part_3.png" alt="Logo" class="custom-logo">
                </a>

                <!-- Ссылка WIKI (теперь сразу после лого) -->
                <a href="wiki.php" class="btn btn-link btn-wiki-text px-2">WIKI</a>
            </div>
            <span class="btn-wiki-text ms-2 text-uppercase">Admin Panel</span>
            <div class="d-flex align-items-center">
                <button class="btn theme-btn-clean me-3" onclick="toggleTheme()">☀️</button>

                <a href="../profile.php" class="d-flex align-items-center text-decoration-none me-3">
                    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" 
                         alt="Profile" 
                         style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ffaa00; object-fit: cover;">
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="profile-card p-4 text-center admin-container-dark"> 
            <h1 class="text-warning mb-4 text-shadow">🛡️ Панель Управления Магистра</h1>
            
            <div class="mb-5">
                <h4 class="fw-bold text-uppercase mb-3" style="font-size: 0.9rem; letter-spacing: 2px; opacity: 0.7;">Быстрые действия</h4>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="../index.php" class="btn btn-outline-light px-4">На главную</a>
                    <a href="../profile.php" class="btn btn-outline-warning px-4">В профиль</a>
                    <a href="../logout.php" class="btn btn-outline-danger px-4">Выход</a>
                </div>
            </div>

            <hr class="opacity-10 mb-5">

            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                
                <div class="col">
                    <a href="admin_users.php" class="text-decoration-none">
                        <div class="stat-box p-4 admin-card-clickable">
                            <h5 class="text-muted mb-2 text-uppercase small" style="letter-spacing: 1px;">База данных магов</h5>
                            <p class="small text-light opacity-75 mb-1">Количество аккаунтов</p>
                            <?php
                                $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
                                $userCount = $countStmt->fetchColumn();
                            ?>
                            <p class="display-5 text-warning fw-bold mb-2"><?php echo $userCount; ?></p>
                            <span class="text-light opacity-50 small">Управление аккаунтами →</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="feedback_hub.php" class="text-decoration-none">
                        <div class="stat-box p-4 admin-card-clickable">
                            <h5 class="text-muted mb-2 text-uppercase small" style="letter-spacing: 1px;">Центр сообщений</h5>
                            <p class="small text-light opacity-75 mb-1">Всего активных запросов:</p>
                            <?php
                                // Считаем сумму из двух таблиц
                                $countRep = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'open'")->fetchColumn();
                                $countTick = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('pending', 'process')")->fetchColumn();
                                $totalActive = $countRep + $countTick;
                                
                                $colorClass = ($totalActive < 10) ? 'text-success' : 'text-danger';
                            ?>
                            <p class="display-5 <?= $colorClass; ?> fw-bold mb-2"><?= $totalActive; ?></p>
                            <span class="text-light opacity-50 small">Открыть управление →</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="admin_editor.php" class="text-decoration-none">
                        <div class="stat-box p-4 admin-card-clickable d-flex flex-column justify-content-center" style="min-height: 165px;">
                            <h5 class="text-muted mb-3 text-uppercase small" style="letter-spacing: 1px;">Редактор мира</h5>
                            <p class="h2 text-info fw-bold mb-2">⚙️</p>
                            <span class="text-light opacity-50 small">
                                Только Тех.Админ и выше </span>
                            <span class="text-light opacity-50 small">Изменить контент →</span>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script.js"></script> </body>
</html>