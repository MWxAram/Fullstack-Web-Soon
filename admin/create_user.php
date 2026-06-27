<?php
session_start();
require '../db.php';

// 1. Проверка доступа
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();

if (!$me || $me['admin_level'] < 1) { header('Location: ../index.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $lvl = (int)$_POST['lvl'];
    $target_admin_level = (int)$_POST['admin_level'];

    // ПРОВЕРКА ИЕРАРХИИ
    if ($target_admin_level >= $me['admin_level']) {
        $error = "Недопустимый уровень власти!";
    } elseif (!empty($username) && !empty($password) && !empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Этот ник или Email уже заняты!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, email, password, lvl, admin_level, title, avatar, win, kill_count) VALUES (?, ?, ?, ?, ?, 'none', 'materials/default-avatar.png', 0, 0)");
            $ins->execute([$username, $email, $hashedPassword, $lvl, $target_admin_level]);
            header("Location: admin_users.php?created=1");
            exit();
        }
    } else {
        $error = "Заполните все обязательные поля!";
    }
}
?>

<!DOCTYPE html>
<!--
    ИСПРАВЛЕНИЕ: тема читается из localStorage ДО рендера страницы,
    чтобы не было мигания при загрузке.
-->
<html lang="ru" id="main-html" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Призыв мага — Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">

    <!-- Применяем тему до рендера, чтобы не было мигания -->
    <script>
        (function() {
            const saved = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', saved);
        })();
    </script>
</head>
<body>
    <div class="magic-background"></div>

    <div class="container mt-5">
        <div class="profile-card p-4 admin-container-dark mx-auto" style="max-width: 500px;">
            <h2 class="text-warning mb-4 text-uppercase fw-bold text-shadow">✨ Призыв нового мага</h2>

            <?php if($error): ?>
                <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase" style="color: #ffaa00;">Имя мага (Логин)</label>
                    <input type="text" name="username" class="form-control bg-dark text-light border-secondary" placeholder="Никнейм..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase" style="color: #ffaa00;">Магическая почта (Email)</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" placeholder="example@magic.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase" style="color: #ffaa00;">Магический ключ (Пароль)</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Пароль..." required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase" style="color: #ffaa00;">Уровень (LVL)</label>
                        <input type="number" name="lvl" class="form-control bg-dark text-light border-secondary" value="1" min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-uppercase" style="color: #ffaa00;">Власть (Admin Lvl)</label>
                        <select name="admin_level" class="form-select bg-dark text-light border-secondary">
                            <option value="0">0 — Игрок</option>
                            <?php for($i = 1; $i < $me['admin_level']; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> — Младший админ</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-warning fw-bold text-uppercase">Создать аккаунт</button>
                    <a href="admin_users.php" class="btn btn-outline-light btn-sm opacity-75">Вернуться в реестр</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>