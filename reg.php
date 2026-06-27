<?php
session_start();

// Если ID пользователя есть в сессии, значит он уже вошел
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Magic Duel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

  <div class="magic-background"></div>

  <div class="login-container">
    <div class="login-card p-4 text-center">
      <a href="index.php">
        <img src="materials/logo_part_3.png" alt="Logo" class="footer-logo mb-3">
      </a>
      <h3 class="mb-3 text-uppercase fw-bold">Новый игрок</h3>
      <p class="text-muted small mb-4">Создай свой магический профиль, чтобы сохранять прогресс и победы</p>
      
      <form action="admin/register.php" method="POST" id="regForm">
        <div class="mb-3 text-start">
          <label class="form-label small text-muted">Имя героя (Nickname)</label>
          <input name="username" type="text" class="form-control custom-input" placeholder="Witcher_777" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label small text-muted">Email почта</label>
          <input name="email" type="email" class="form-control custom-input" placeholder="mage@example.com" required>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label small text-muted">Придумайте пароль</label>
            <input name="password" type="password" id="password" minlength="6" class="form-control custom-input" placeholder="••••••••" required>
        </div>

        <div class="mb-4 text-start">
            <label class="form-label small text-muted">Повторите пароль</label>
            <input type="password" id="confirm_password" minlength="6" class="form-control custom-input" placeholder="••••••••" required>
        </div>
        
        <button type="submit" id="submitBtn" class="btn download-btn-custom w-100 py-2 mb-3">Начать путь</button>
        <div id="globalError" class="text-danger small mb-3 text-center" 
         style="<?php echo isset($_GET['error']) ? 'display: block;' : 'display: none;'; ?> font-weight: bold;">
        <?php 
            if (isset($_GET['error']) && $_GET['error'] == 'exists') {
                echo "❌ Этот Никнейм или Email уже занят!";
            }
        ?>
    </div>
        <div class="text-center">
          <span class="text-muted small">Уже есть аккаунт?</span>
          <a href="login.php" class="footer-link small ms-1">Войти</a>
        </div>
      </form>

      <hr class="opacity-10 mt-4">
      <p class="text-muted" style="font-size: 0.75rem;">Регистрируясь, вы подтверждаете согласие с правилами Арены.</p>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>
