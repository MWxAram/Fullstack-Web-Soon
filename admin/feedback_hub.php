<?php
session_start();
require '../db.php';

// Проверка доступа (админ 1+)
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();
if (!$me || $me['admin_level'] < 1) { header('Location: ../index.php'); exit(); }

// Безопасный подсчет активных записей
function getActiveCount($pdo, $table) {
    try {
        // Считаем все обращения, кроме окончательно закрытых
        return $pdo->query("SELECT COUNT(*) FROM $table WHERE status != 'closed'")->fetchColumn();
    } catch (Exception $e) { return 0; }
}

$repCount = getActiveCount($pdo, 'reports');
$tickCount = getActiveCount($pdo, 'support_tickets');

// Функция для определения цвета текста (Зеленый до 9, Красный от 10)
function getStatusColor($count) {
    return ($count > 9) ? 'text-danger' : 'text-success';
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Центр Обращений — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="text-warning text-uppercase fw-bold mb-1" style="letter-spacing: 4px; text-shadow: none;">Центр Обращений</h2>
            <p class="text-light opacity-50 small">Выберите категорию для просмотра свитков</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="hub-card p-4" onclick="location.href='admin_reports.php'">
                    <div class="hub-icon">📜</div>
                    <h3 class="hub-title m-0">Репорты</h3>
                    
                    <div class="count-container">
                        <span class="count-label">Количество:</span>
                        <div class="count-value <?= getStatusColor($repCount) ?>">
                            <?= $repCount ?>
                        </div>
                        <p class="text-muted small text-center px-4">Жалобы на баги, ошибки и предложения по улучшению мира</p>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="hub-card p-4" onclick="location.href='admin_tickets.php'">
                    <div class="hub-icon">🎧</div>
                    <h3 class="hub-title m-0">Поддержка</h3>
                    
                    <div class="count-container">
                        <span class="count-label">Количество:</span>
                        <div class="count-value <?= getStatusColor($tickCount) ?>">
                            <?= $tickCount ?>
                        </div>
                        <p class="text-muted small text-center px-4">Прямая связь с игроками и решение технических проблем</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="admin.php" class="btn btn-outline-light px-4 btn-sm opacity-50">
                ← Назад в админку
            </a>
        </div>
    </div>
</body>
</html>