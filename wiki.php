<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Magic Duel — Wiki</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">

</head>
<body>
  <div class="magic-background"></div>

  <!-- Исправленный Navbar -->
  <nav class="navbar navbar-expand-lg mx-3">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center me-0" href="index.php">
                <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
            </a>
            <a href="wiki.php" class="btn btn-link btn-wiki-text px-2 text-decoration-none" style="color: #00d2ff; font-weight: bold;">WIKI</a>
        </div>

        <div class="d-flex align-items-center">
            <button class="btn theme-btn-clean me-3" id="themeToggler" onclick="toggleTheme()">☀️</button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="d-flex align-items-center text-decoration-none profile-nav-link">
                    <img src="<?php echo !empty($_SESSION['avatar']) ? htmlspecialchars($_SESSION['avatar']) : 'materials/default-avatar.png'; ?>" 
                        alt="Profile" 
                        style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #00d2ff; object-fit: cover;">
                    <span class="ms-2 text-light fw-bold d-none d-sm-inline">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn download-btn-custom">Вход</a>
            <?php endif; ?>
        </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="text-center mb-5">
      <h1 class="display-4 fw-bold faq-title">База Знаний</h1>
      <p class="text-muted">Изучите секреты древней магии, чтобы превзойти соперников на арене</p>
      
      <!-- НОВОЕ: Поле живого поиска -->
      <div class="col-md-6 mx-auto mt-4">
        <input type="text" id="wikiSearch" class="form-control bg-dark border-info text-white shadow-none" 
               placeholder="🔍 Начните вводить название раздела (Стихии, Заклинания...)" 
               style="border-radius: 50px; padding: 12px 25px;">
      </div>
    </div>

    <!-- Контейнер с категориями -->
    <div class="row g-4" id="wiki-container">
      <div class="col-md-4 wiki-item">
        <div class="wiki-category-card">
          <div class="wiki-icon">🔥</div>
          <h3>Стихии</h3>
          <p class="text-secondary small">Огонь, Вода, Воздух и Земля. Узнайте о сильных и слабых сторонах каждой школы.</p>
          <a href="wiki_view.php?id=1" class="btn btn-outline-info btn-sm rounded-pill mt-2">Открыть раздел</a>
        </div>
      </div>

      <div class="col-md-4 wiki-item">
        <div class="wiki-category-card">
          <div class="wiki-icon">📜</div>
          <h3>Заклинания</h3>
          <p class="text-secondary small">Полный список магических комбо и секретных техник для опытных дуэлянтов.</p>
          <a href="wiki_view.php?id=2" class="btn btn-outline-info btn-sm rounded-pill mt-2">Открыть раздел</a>
        </div>
      </div>

      <div class="col-md-4 wiki-item">
        <div class="wiki-category-card">
          <div class="wiki-icon">💎</div>
          <h3>Артефакты</h3>
          <p class="text-secondary small">Магические предметы, которые могут изменить исход любой битвы.</p>
          <a href="wiki_view.php?id=3" class="btn btn-outline-info btn-sm rounded-pill mt-2">Открыть раздел</a>
        </div>
      </div>
    </div>
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
          <li class="mb-2"><a href="#" class="footer-link">Discord</a></li>
          <li class="mb-2"><a href="#" class="footer-link">Telegram</a></li>
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
  
  <script>
    // СКРИПТ ЖИВОГО ПОИСКА
    document.getElementById('wikiSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const items = document.querySelectorAll('.wiki-item');
        
        items.forEach(item => {
            const title = item.querySelector('h3').innerText.toLowerCase();
            const desc = item.querySelector('p').innerText.toLowerCase();
            
            if (title.includes(query) || desc.includes(query)) {
                item.style.display = 'block';
                item.style.opacity = '1';
                item.style.transform = 'scale(1)';
            } else {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.9)';
                setTimeout(() => { if(item.style.opacity === '0') item.style.display = 'none'; }, 300);
            }
        });
    });
  </script>
</body>
</html>