<?php
session_start();
require '../db.php';

// Проверка доступа
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();
if (!$me || $me['admin_level'] < 1) { header('Location: ../index.php'); exit(); }

// Обработка закрытия тикета (если нужно закрыть без чата)
if (isset($_POST['close_ticket'])) {
    $ticket_id = (int)$_POST['ticket_id'];
    $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed' WHERE id = ?");
    $stmt->execute([$ticket_id]);
    header("Location: admin_tickets.php?success=closed");
    exit();
}

// Запрос: выбираем только активные (Рассмотрение и В обработке)
// Тикеты со статусом 'closed_by_admin' исчезнут из этого списка автоматически
$query = "SELECT t.*, u.username as player_name 
          FROM support_tickets t
          JOIN users u ON t.user_id = u.id
          WHERE t.status IN ('pending', 'process')
          ORDER BY t.created_at ASC";
$tickets = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Тех. Поддержка — Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="text-warning text-uppercase fw-bold text-shadow" style="font-size: 2rem; letter-spacing: 3px;">🛡️ Поддержка</h2>
            <a href="feedback_hub.php" class="btn btn-outline-light px-4">Назад в Хаб</a>
        </div>

        <div class="row g-4"> 
            <?php if (empty($tickets)): ?>
                <div class="col-12">
                    <div class="alert alert-dark text-center border-secondary text-muted py-5">
                        Свитков с просьбами о помощи не найдено.
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($tickets as $tick): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card ticket-card p-2" style="cursor:pointer;" onclick="location.href='ticket_chat.php?id=<?= $tick['id'] ?>'">
                        
                        <div class="card-header border-0 d-flex justify-content-between align-items-center bg-transparent mt-2">
                            <span class="text-white-50 small">Игрок: <span class="text-white fw-bold"><?= htmlspecialchars($tick['player_name']) ?></span></span>
                            <span class="text-muted small">#<?= $tick['id'] ?></span>
                        </div>

                        <div class="px-3 d-flex align-items-center" style="gap: 8px; line-height: 1;">
                            <span class="small text-white-50">Статус:</span>
                            <?php 
                                $is_pending = ($tick['status'] === 'pending');
                                $st_class = $is_pending ? 'bg-pending' : 'bg-process';
                                $st_text = $is_pending ? 'В рассмотрении' : 'В обработке';
                            ?>
                            <span class="badge-status <?= $st_class ?>" style="font-size: 0.65rem; padding: 2px 8px; text-transform: uppercase;">
                                <?= $st_text ?>
                            </span>
                        </div>

                        <div class="card-body py-3">
                            <h5 class="text-warning mb-2 fw-bold text-uppercase" style="font-size: 1rem;"><?= htmlspecialchars($tick['subject']) ?></h5>
                            <div class="ticket-text-preview text-light opacity-50 small text-truncate">
                                <?= htmlspecialchars($tick['message']) ?>
                            </div>
                        </div>

                        <div class="card-footer border-0 bg-transparent d-flex justify-content-between align-items-center pb-3">
                            <form method="POST" class="m-0 stop-prop" onclick="event.stopPropagation();">
                                <input type="hidden" name="ticket_id" value="<?= $tick['id'] ?>">
                                <button type="submit" name="close_ticket" class="btn btn-sm btn-outline-danger" style="font-size: 0.7rem;">
                                    ЗАКРЫТЬ
                                </button>
                            </form>
                            
                            <small class="text-muted opacity-50" style="font-size: 0.7rem;">
                                <?= date('d.m.Y H:i', strtotime($tick['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>