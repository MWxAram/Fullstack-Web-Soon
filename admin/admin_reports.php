<?php
session_start();
require '../db.php';

// Проверка доступа (только для админов 1+)
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();
if (!$me || $me['admin_level'] < 1) { header('Location: ../index.php'); exit(); }

// Обработка закрытия (удаления) репорта
if (isset($_POST['close_report'])) {
    $report_id = (int)$_POST['report_id'];
    $stmt = $pdo->prepare("UPDATE reports SET status = 'closed' WHERE id = ?");
    $stmt->execute([$report_id]);
    header("Location: admin_reports.php?success=1");
    exit();
}

// Получаем список открытых репортов
$query = "SELECT r.*, u.username as reporter_name 
          FROM reports r
          JOIN users u ON r.reporter_id = u.id
          WHERE r.status = 'open'
          ORDER BY r.created_at ASC"; // Теперь старые будут вверху
$reports = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Репорты и Тикеты — Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning text-uppercase fw-bold text-shadow">📜 Свитки Обращений</h2>
            <a href="admin.php" class="btn btn-outline-light btn-sm">В админку</a>
        </div>

        <div class="row">
            <?php if (empty($reports)): ?>
                <div class="col-12">
                    <div class="alert alert-dark text-center border-secondary text-muted">
                        Новых магических посланий не обнаружено.
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($reports as $rep): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-dark text-light border-secondary shadow-sm h-100">
                        <div class="card-header border-secondary d-flex justify-content-between">
                            <span class="text-warning small fw-bold">ОТ: <?php echo htmlspecialchars($rep['reporter_name']); ?></span>
                            <span class="text-muted small">#<?php echo $rep['id']; ?></span>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block mb-1">Сообщение:</small>
                                <div class="report-text-container">
                                    <?php 
                                    $text = htmlspecialchars($rep['reason']);
                                    $max_length = 150; // Количество символов для превью
                                    
                                    if (mb_strlen($text) > $max_length): ?>
                                        <span class="text-short"><?php echo mb_substr($text, 0, $max_length); ?>...</span>
                                        <span class="text-full d-none"><?php echo nl2br($text); ?></span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-warning btn-toggle-text" onclick="toggleReportText(this)">Показать полностью</button>
                                    <?php else: ?>
                                        <?php echo nl2br($text); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-secondary bg-transparent d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($rep['created_at'])); ?></small>
                            <form method="POST">
                                <input type="hidden" name="report_id" value="<?php echo $rep['id']; ?>">
                                <button type="submit" name="close_report" class="btn btn-sm btn-outline-success">
                                    Прочитано
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
function toggleReportText(btn) {
    const container = btn.closest('.report-text-container');
    const shortText = container.querySelector('.text-short');
    const fullText = container.querySelector('.text-full');
    
    if (fullText.classList.contains('d-none')) {
        fullText.classList.remove('d-none');
        shortText.classList.add('d-none');
        btn.innerText = 'Скрыть';
    } else {
        fullText.classList.add('d-none');
        shortText.classList.remove('d-none');
        btn.innerText = 'Показать полностью';
    }
}
</script>
</body>
</html>