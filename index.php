<?php
session_start();
require 'db.php';

// Загружаем настройки сайта
$settings_raw = $pdo->query("SELECT * FROM site_settings")->fetchAll();
$s = [];
foreach ($settings_raw as $row) { 
    $s[$row['setting_key']] = $row['setting_value']; 
}
// Получаем топ игроков
$top_win    = $pdo->query("SELECT * FROM users ORDER BY win DESC LIMIT 1")->fetch();
$top_kills  = $pdo->query("SELECT * FROM users ORDER BY kill_count DESC LIMIT 1")->fetch();
$top_deaths = $pdo->query("SELECT * FROM users ORDER BY dead_count DESC LIMIT 1")->fetch();
$top_streak = $pdo->query("SELECT * FROM users ORDER BY win_seria DESC LIMIT 1")->fetch();
$top_total  = $pdo->query("SELECT * FROM users ORDER BY rounds_played DESC LIMIT 1")->fetch();

$current_date = date('d.m.Y');
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($s['home_title'] ?? 'Magic Duel') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <div class="magic-background"></div>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
        <a href="wiki.php" class="btn btn-link btn-wiki-text ms-2">WIKI</a>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
          <a class="btn download-btn-custom" href="game.txt" download="MagicDuel_Launcher.txt">
              Скачать игру
          </a>
          </li>
        </ul>
        
        <div class="d-flex align-items-center">
          <button class="btn theme-btn-clean me-3" id="themeToggler" onclick="toggleTheme()">
            ☀️
          </button>
          <div class="auth-buttons">
              <?php if (isset($_SESSION['user_id'])): ?>
                  <a href="profile.php" class="profile-link">
                      <img src="<?php echo $_SESSION['avatar']; ?>" style="width: 40px; height: 40px; border-radius: 50%;">
                  </a>
              <?php else: ?>
                  <a href="login.php" class="btn btn-outline-light me-2">Войти</a>
                  <a href="reg.php" class="btn btn-primary">Регистрация</a>
              <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </nav>

<div class="main-content-section d-flex justify-content-between">
  
  <div class="game-info-container">
    <div id="gameInfoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="info-block">
              <h3>Мир Древней Магии</h3>
              <p><?= nl2br(htmlspecialchars($s['home_desc'] ?? 'Добро пожаловать в Magic Duel — захватывающую арену, где сталкиваются стихии льда и пламени. Исследуйте заброшенные храмы и мистические пустоши, где сама реальность пропитана энергией эфира. Здесь каждый камень помнит великие битвы прошлого, а каждый ваш шаг может пробудить спящее заклинание. Станьте тем, кто обуздает хаос и подчинит себе силу первородных стихий!')) ?></p>
          </div>
        </div>
          <div class="carousel-item">
            <div class="info-block">
              <h3><?= htmlspecialchars($s['home_title_2'] ?? 'Станьте Легендой Арены') ?></h3>
              
              <p>
                <?= nl2br(htmlspecialchars($s['home_desc_2'] ?? 'Соберите уникальную колоду из сотен магических способностей...')) ?>
              </p>
            </div>
          </div>
      </div>
    </div>
  </div>

  <div class="compact-slider">
    <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="materials/magic.png" class="slider-img">
        </div>
        <div class="carousel-item">
          <img src="materials/logo_part_1.png" class="slider-img">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="players-section mt-5">
  
  <div class="row justify-content-center mb-4">
    <div class="col-auto">
      <div class="player-card border-victory">
        <div class="main-stat text-center">
          <span class="badge rounded-pill bg-warning text-dark">🏆 Чемпион по Победам</span>
        </div>
        <div class="player-header d-flex align-items-center my-3">
          <img src="<?= $top_win['avatar'] ?? 'default.png' ?>" class="player-avatar me-3">
<h5><?= htmlspecialchars($top_win['username'] ?? 'No player') ?></h5>
        </div>
          <div class="stat-main-center text-center mb-2">
              <div class="stat-item">🏆 Победы: <?= $top_win['win'] ?? 0 ?></div>
          </div>
          <div class="stats-grid">
            <div class="stat-item left">⚔️ Убийств: <?= $top_win['kill_count'] ?? 0 ?></div>
            <div class="stat-item right">💀 Смертей: <?= $top_win['dead_count'] ?? 0 ?></div>
            <div class="stat-item left">🔥 Серия: <?= $top_win['win_seria'] ?? 0 ?></div>
            <div class="stat-item right">🎮 Всего игр: <?= $top_win['rounds_played'] ?? 0 ?></div>
          </div>
        <div class="card-footer-date text-end mt-3">
          <small class="text-muted">Получено: <?= $current_date ?></small>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center g-4 mb-4">
    <div class="col-auto">
      <div class="player-card border-kills">
        <div class="main-stat text-center"><span class="badge bg-danger">⚔️ Мастер Убийств</span></div>
        <div class="player-header d-flex align-items-center my-3">
          <img src="<?= $top_kills['avatar'] ?? 'default.png' ?>" class="player-avatar me-3">
          <h5><?= htmlspecialchars($top_kills['username'] ?? 'No player') ?></h5>
        </div>
            <div class="stat-main-center text-center mb-2">
              <div class="stat-item">🏆 Победы: <?= $top_kills['win'] ?? 0 ?></div>
          </div>
        <div class="stats-grid">
          <div class="stat-item left">⚔️ Убийств: <?= $top_kills['kill_count'] ?? 0 ?></div>
          <div class="stat-item right">💀 Смертей: <?= $top_kills['dead_count'] ?? 0 ?></div>
          <div class="stat-item left">🔥 Серия: <?= $top_kills['win_seria'] ?? 0 ?></div>
          <div class="stat-item right">🎮 Всего игр: <?= $top_kills['rounds_played'] ?? 0 ?></div>
        </div>
        <div class="card-footer-date text-end mt-3"><small>Получено: <?= $current_date ?></small></div>
      </div>
    </div>
    <div class="col-auto">
  <div class="player-card border-deaths">
    <div class="main-stat text-center">
      <span class="badge bg-dark">💀 Максимум Смертей</span>
    </div>
    <div class="player-header d-flex align-items-center my-3">
<img src="<?= $top_deaths['avatar'] ?? 'default.png' ?>" class="player-avatar me-3">
<h5><?= htmlspecialchars($top_deaths['username'] ?? 'No player') ?></h5>
    </div>
          <div class="stat-main-center text-center mb-2">
            <div class="stat-item">🏆 Победы: <?= $top_deaths['win'] ?? 0 ?></div>
          </div>
    <div class="stats-grid">
<div class="stat-item left">⚔️ Убийств: <?= $top_deaths['kill_count'] ?? 0 ?></div>
<div class="stat-item right">💀 Смертей: <?= $top_deaths['dead_count'] ?? 0 ?></div>
<div class="stat-item left">🔥 Серия: <?= $top_deaths['win_seria'] ?? 0 ?></div>
<div class="stat-item right">🎮 Всего игр: <?= $top_deaths['rounds_played'] ?? 0 ?></div>
    </div>
    <div class="card-footer-date text-end mt-3">
      <small>Получено: <?= $current_date ?></small>
    </div>
  </div>
</div>
  </div>

  <div class="row justify-content-center g-4">
    <div class="col-auto">
      <div class="player-card border-streak">
        <div class="main-stat text-center"><span class="badge bg-info text-dark">🔥 Лучшая Серия</span></div>
        <div class="player-header d-flex align-items-center my-3">
<img src="<?= $top_streak['avatar'] ?? 'default.png' ?>" class="player-avatar me-3">
<h5><?= htmlspecialchars($top_streak['username'] ?? 'No player') ?></h5>
        </div>
            <div class="stat-main-center text-center mb-2">
              <div class="stat-item">🏆 Победы: <?= $top_streak['win'] ?? 0 ?></div>
          </div>
        <div class="stats-grid">
<div class="stat-item left">⚔️ Убийств: <?= $top_streak['kill_count'] ?? 0 ?></div>
<div class="stat-item right">💀 Смертей: <?= $top_streak['dead_count'] ?? 0 ?></div>
<div class="stat-item left">🔥 Серия: <?= $top_streak['win_seria'] ?? 0 ?></div>
<div class="stat-item right">🎮 Всего игр: <?= $top_streak['rounds_played'] ?? 0 ?></div>
        </div>
        <div class="card-footer-date text-end mt-3"><small>Получено: <?= $current_date ?></small></div>
      </div>
    </div>
    <div class="col-auto">
      <div class="player-card border-total">
        <div class="main-stat text-center"><span class="badge bg-dark">🎮 Ветеран Игры</span></div>
        <div class="player-header d-flex align-items-center my-3">
<img src="<?= $top_total['avatar'] ?? 'default.png' ?>" class="player-avatar me-3">
<h5><?= htmlspecialchars($top_total['username'] ?? 'No player') ?></h5>
        </div>
          <div class="stat-main-center text-center mb-2">
              <div class="stat-item">🏆 Победы: <?= $top_total['win'] ?? 0 ?></div>
          </div>
        <div class="stats-grid">
<div class="stat-item left">⚔️ Убийств: <?= $top_total['kill_count'] ?? 0 ?></div>
<div class="stat-item right">💀 Смертей: <?= $top_total['dead_count'] ?? 0 ?></div>
<div class="stat-item left">🔥 Серия: <?= $top_total['win_seria'] ?? 0 ?></div>
<div class="stat-item right">🎮 Всего игр: <?= $top_total['rounds_played'] ?? 0 ?></div>
        </div>
        <div class="card-footer-date text-end mt-3"><small>Получено: <?= $current_date ?></small></div>
      </div>
    </div>
  </div>
</div>

<div class="faq-section mt-5 mb-5">
  <h2 class="text-center faq-title mb-4">FAQ — Часто задаваемые вопросы</h2>
  
  <div class="accordion custom-faq" id="gameFaq">
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          🔥 Как открыть новые заклинания стихий?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#gameFaq">
        <div class="accordion-body">
          Новые заклинания открываются за <strong>очки мастерства</strong>, которые вы получаете после каждой дуэли.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          ⚔️ Что дает статус "Ветеран Игры"?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#gameFaq">
        <div class="accordion-body">
          Статус Ветерана получают игроки, сыгравшие более <strong>1000 матчей</strong>.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          📱 Можно ли играть в Magic Duel с мобильных устройств?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#gameFaq">
        <div class="accordion-body">
          <strong>К сожелению нет</strong>, но в скором времнини это будет возможно.
        </div>
      </div>
    </div>
  </div>
</div>

<div class="wiki-section text-center mt-5 mb-5">
  <p class="text-muted">Хотите узнать больше о тактиках и заклинаниях?</p>
  <a href="wiki.php" class="btn btn-outline-primary btn-lg px-5 py-3 wiki-main-btn">
    📖 Открыть Wiki игры
  </a>
</div>

<footer class="game-footer mt-5">
  <div class="container">
    <div class="row py-5">
      <div class="col-lg-4 mb-4">
        <img src="materials/logo_part_3.png" alt="Logo" class="footer-logo mb-3">
        <p class="text-muted small">Magic Duel — это не просто игра, это арена для тех, кто готов бросить вызов стихиям. Присоединяйся к тысячам игроков и стань мастером древней магии.</p>
      </div>

      <div class="col-6 col-lg-2 offset-lg-2 mb-4">
        <h6 class="text-uppercase fw-bold mb-3">Ресурсы</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="wiki.php" class="footer-link">Wiki</a></li>
          <li class="mb-2"><a href="#" class="footer-link">Обновления</a></li>
          <li class="mb-2"><a href="#" class="footer-link">Карты способностей</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2 mb-4">
        <h6 class="text-uppercase fw-bold mb-3">Сообщество</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= htmlspecialchars($s['discord_link'] ?? '#') ?>" class="footer-link">Discord</a></li>
          <li class="mb-2"><a href="<?= htmlspecialchars($s['telegram_link'] ?? '#') ?>" class="footer-link">Telegram</a></li>
          <li class="mb-2"><a href="#" class="footer-link">VK</a></li>
        </ul>
      </div>
    </div>

    <div class="border-top border-secondary pt-4 pb-3">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <span class="text-muted small">© 2026 Magic Duel Team. Все права защищены.</span>
        </div>
        <div class="mt-2">
            <a href="send_report.php" class="text-warning text-decoration-none fw-bold">
                ✉️ Обратная связь
            </a>
            <a href="support.php" class="text-info text-decoration-none fw-bold ms-3">
                🎧 Тех. поддержка
            </a>
        </div>
        <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
          <a href="#" class="text-muted small text-decoration-none me-3">Политика конфиденциальности</a>
          <a href="#" class="text-muted small text-decoration-none">Оферта</a>
        </div>
      </div>
    </div>
  </div>
</footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>