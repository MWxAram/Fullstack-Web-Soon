<?php
session_start();

// Если ID пользователя есть в сессии, значит он уже вошел
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark"> <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Magic Duel — Вход</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

  <div class="magic-background"></div>

  <div class="login-container">
    <div class="login-card p-4 text-center">
      <a href="index.php">
        <img src="materials/logo_part_3.png" alt="Logo" class="footer-logo mb-4">
      </a>
      <h3 class="mb-4 text-uppercase fw-bold">Вход на Арену</h3>
      
      <form action="admin/login_handler.php" method="POST" id="loginForm">
          <div class="mb-3 text-start">
              <label class="form-label small text-muted">Имя игрока или Email</label>
              <input name="username" type="text" class="form-control custom-input" placeholder="Witcher_777 или mage@example.com" required>
          </div>
          <div class="mb-4 text-start">
              <label class="form-label small text-muted">Магический пароль</label>
              <input name="password" type="password" class="form-control custom-input" placeholder="••••••••" required>
          </div>
          
          <button type="submit" class="btn download-btn-custom w-100 py-2 mb-3">Войти</button>

          <div id="loginError" class="text-danger small mb-3 text-center" 
         style="<?php echo isset($_GET['error']) ? 'display: block;' : 'display: none;'; ?> font-weight: bold;">
        <?php 
            if (isset($_GET['error']) && $_GET['error'] == 'wrong') {
                echo "❌ Неверный логин или пароль!";
            }
        ?>
          </div>
          
          <div class="d-flex justify-content-between mb-3">
              <a href="#" class="footer-link small">Забыли пароль?</a>
              <a href="reg.php" class="footer-link small">Регистрация</a>
          </div>
      </form>

      <hr class="opacity-10">
      <p class="text-muted small">Входя, вы принимаете правила Магического Кодекса</p>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>