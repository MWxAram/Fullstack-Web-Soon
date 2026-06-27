<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Берем данные ТОЛЬКО того человека, чей ID лежит в сессии
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Ошибка: Пользователь не найден.");
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Magic Duel — Профиль</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <style>
    body {
    margin: 0;
    padding: 0;
    background-color: #f8f9fa; /* Базовый светлый */
    transition: background 0.5s ease;
    overflow: hidden;
}
  </style>
  <div class="magic-background"></div>

  <nav class="navbar navbar-expand-lg mx-3">
    <div class="container-fluid">
      <div class="d-flex align-items-center">
          <!-- Логотип (ссылка на главную) -->
          <a class="navbar-brand d-flex align-items-center me-0" href="index.php">
              <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
          </a>
          <!-- Ссылка WIKI (теперь сразу после лого) -->
          <a href="wiki.php" class="btn btn-link btn-wiki-text px-2">WIKI</a>
          <a href="duel.php" class="btn btn-link btn-wiki-text px-2">MINI GAME</a>
      </div>
      <a class="btn btn-link btn-wiki-text ms-2">Profile</a>
        <div class="d-flex align-items-center">
            <button class="btn theme-btn-clean me-3" onclick="toggleTheme()">☀️</button>
            <?php if (isset($user['admin_level']) && $user['admin_level'] >= 1): ?>
            <a href="admin/admin.php" class="btn btn-warning me-3 fw-bold">
              🛡️ Админ-панель
            </a>
            <?php endif; ?>
            <a href="admin/logout.php" class="btn btn-danger">Выйти из аккаунта</a>
        </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row">
      <div class="col-lg-4 mb-4">
        <div class="profile-card text-center p-4">
          <div class="text-center mb-3">
            <div class="position-relative d-inline-block">
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                    id="avatar-preview" 
                    class="rounded-circle img-thumbnail" 
                    style="width: 150px; height: 150px; object-fit: cover;">
                <div class="level-badge">LVL <?php echo htmlspecialchars($user['lvl']); ?></div>
            </div>
        </div>
          <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h2>
          <p class="text-primary fw-bold mb-3 text-uppercase"><?php echo htmlspecialchars($user['titule']); ?></p>
          <hr class="opacity-10">
          <form action="admin/upload_avatar.php" method="POST" enctype="multipart/form-data" class="mt-3">
            <label for="avatar-input" class="btn download-btn-custom">Сменить облик</label>
            <input type="file" name="avatar" id="avatar-input" style="display: none;" onchange="this.form.submit()">
          </form>
            <div class="mt-3">
                <a href="dialogs.php" class="btn btn-primary w-100 p-3 fw-bold">
                    <i class="bi bi-chat-fill me-2"></i> Чаты
                    <?php
                    // Считаем непрочитанные сообщения
                    $unread = $pdo->prepare("SELECT COUNT(*) FROM private_messages WHERE receiver_id = ? AND is_read = 0");
                    $unread->execute([$_SESSION['user_id']]);
                    $count = $unread->fetchColumn();
                    if ($count > 0) echo "<span class='badge bg-danger ms-2'>$count</span>";
                    ?>
                </a>
                          <a href="support.php" class="btn btn-outline-info w-100 mt-2">
                 <i class="bi bi-headset"></i> Написать в поддержку
              </a>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="profile-card p-4">
          <h4 class="mb-4 fw-bold text-uppercase">Боевые заслуги</h4>
          
          <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-value text-warning"><?php echo htmlspecialchars($user['win']); ?></span>
                    <span class="stat-label">Побед</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-value text-danger"><?php echo htmlspecialchars($user['kill_count']); ?></span>
                    <span class="stat-label">Убийства</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-value text-info"><?php echo htmlspecialchars($user['rounds_played']); ?></span>
                    <span class="stat-label">Битвы</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-value text-success"><?php echo htmlspecialchars($user['win_seria']); ?></span>
                    <span class="stat-label">Победы подряд</span>
                </div>
            </div>
          </div>

          <h4 class="mt-5 mb-4 fw-bold text-uppercase">Любимые стихии</h4>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small">Огонь</span>
                <span class="small fw-bold">75%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-danger" style="width: 75%"></div>
            </div>
          </div>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small">Лёд</span>
                <span class="small fw-bold">40%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-info" style="width: 40%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>