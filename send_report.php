<?php
session_start();
require 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason']);
    
    if (mb_strlen($reason) < 5) {
        $error = "Послание слишком короткое! Напишите подробнее.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reports (reporter_id, reason) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $reason]);
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Обратная связь</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>
    <div class="container mt-5">
        <div class="profile-card p-4 admin-container-dark mx-auto" style="max-width: 600px;">
            <h2 class="text-warning text-center fw-bold mb-4">✉️ ОБРАТНАЯ СВЯЗЬ</h2>

            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    Ваше послание отправлено в архив администрации!
                    <br><a href="index.php" class="btn btn-sm btn-success mt-2">Вернуться назад</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-light">Суть обращения / Описание бага:</label>
                        <textarea name="reason" class="form-control bg-dark text-light border-secondary" 
                                  rows="5" placeholder="Опишите вашу проблему или предложение..." required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold w-100">ОТПРАВИТЬ СВИТОК</button>
                        <a href="index.php" class="btn btn-outline-light w-50">ОТМЕНА</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>