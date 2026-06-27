<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt = $pdo->prepare("SELECT * FROM wiki_content WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();

if (!$current) { header('Location: wiki.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM wiki_content WHERE parent_id = ?");
$stmt->execute([$id]);
$sub_items = $stmt->fetchAll();

$userAvatar = 'materials/default-avatar.png';
if (isset($_SESSION['user_id'])) {
    $uStmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $uData = $uStmt->fetch();
    if($uData) $userAvatar = $uData['avatar'];
}
?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($current['title']) ?> — Magic Duel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>
    <style>
        body{
            overflow: auto;
        }
    </style>
    <nav class="navbar navbar-expand-lg mx-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="wiki.php">
                <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
                <span class="btn-wiki-text ms-2 fw-bold text-uppercase">Wiki Base</span>
            </a>
            <div class="d-flex align-items-center">
                <button class="btn theme-btn-clean me-3" id="themeToggler" onclick="toggleTheme()">☀️</button>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">
                        <img src="<?= htmlspecialchars($userAvatar) ?>" 
                             style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ffaa00; object-fit: cover;">
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn download-btn-custom btn-sm">Вход</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- ВЕРНУЛИ: Основной заголовок и описание статьи -->
        <div class="text-center mb-5">
            <div class="display-3 mb-2"><?= $current['icon'] ?></div>
            <h1 class="display-4 fw-bold faq-title"><?= htmlspecialchars($current['title']) ?></h1>
            <p class="text-muted"><?= htmlspecialchars($current['short_desc']) ?></p>
            <hr class="w-25 mx-auto border-warning">
        </div>

        <!-- ВЕРНУЛИ: Текст статьи, Фото и Видео -->
        <?php if (!empty($current['full_text'])): ?>
            <div class="profile-card p-4 admin-container-dark text-light mb-5 shadow-lg">
                <div class="row">
                    <div class="<?= ($current['image_url'] || $current['video_url']) ? 'col-lg-7' : 'col-12' ?>">
                        <div class="wiki-text-content" style="font-size: 1.1rem; line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($current['full_text'])) ?>
                        </div>
                    </div>
                    
                    <?php if ($current['image_url'] || $current['video_url']): ?>
                    <div class="col-lg-5 mt-4 mt-lg-0">
                        <?php if($current['image_url']): ?>
                            <img src="<?= htmlspecialchars($current['image_url']) ?>" class="img-fluid rounded border border-warning mb-3 shadow">
                        <?php endif; ?>
                        
                        <?php if($current['video_url']): ?>
                            <div class="ratio ratio-16x9 rounded overflow-hidden border border-warning shadow">
                                <iframe src="<?= str_replace('watch?v=', 'embed/', htmlspecialchars($current['video_url'])) ?>" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Поиск и Подразделы -->
        <?php if ($sub_items): ?>
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <input type="text" id="subWikiSearch" class="form-control bg-dark border-warning text-white shadow-none" 
                           placeholder="🔍 Поиск в этом разделе..." 
                           style="border-radius: 50px; padding: 10px 20px;">
                </div>
            </div>

            <div class="row g-4 mb-5" id="subItemsContainer">
                <?php foreach ($sub_items as $item): ?>
                    <div class="col-md-4 wiki-sub-item">
                        <div class="wiki-category-card" onclick="location.href='wiki_view.php?id=<?= $item['id'] ?>'" style="cursor: pointer;">
                            <div class="wiki-icon"><?= $item['icon'] ?></div>
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="small opacity-75"><?= htmlspecialchars($item['short_desc']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center pb-5">
            <a href="wiki.php" class="btn btn-outline-warning px-4">← К разделам Wiki</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    
    <script>
        const searchInput = document.getElementById('subWikiSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const items = document.querySelectorAll('.wiki-sub-item');
                
                items.forEach(item => {
                    const title = item.querySelector('h3').innerText.toLowerCase();
                    const desc = item.querySelector('p').innerText.toLowerCase();
                    
                    if (title.includes(query) || desc.includes(query)) {
                        item.style.display = 'block';
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                        setTimeout(() => { 
                            if(item.style.opacity === '0') item.style.display = 'none'; 
                        }, 200);
                    }
                });
            });
        }
    </script>
</body>
</html>