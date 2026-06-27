<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$meData = $stmt->fetch();

if (!$meData || $meData['admin_level'] < 3) { header('Location: ../index.php'); exit(); }

$section = $_GET['section'] ?? 'menu';
$subsection = $_GET['subsection'] ?? 'main';

if (isset($_POST['add_wiki'])) {
    $parent = (int)$_POST['parent_id'];
    $title = $_POST['title'];
    $icon = $_POST['icon'];
    $image = $_POST['image_url'];
    $video = $_POST['video_url'];
    $short = $_POST['short_desc'];
    $full = $_POST['full_text'];

    $ins = $pdo->prepare("INSERT INTO wiki_content (parent_id, title, icon, image_url, video_url, short_desc, full_text) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$parent, $title, $icon, $image, $video, $short, $full]);
    header("Location: admin_editor.php?section=wiki&subsection=add&success=1");
    exit();
}

$section = $_GET['section'] ?? 'menu';
$subsection = $_GET['subsection'] ?? 'main';
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;

// Обработка СОХРАНЕНИЯ изменений при редактировании
if (isset($_POST['update_wiki'])) {
    $target_id = (int)$_POST['target_id'];
    $title = $_POST['title'];
    $icon = $_POST['icon'];
    $image = $_POST['image_url'];
    $video = $_POST['video_url'];
    $short = $_POST['short_desc'];
    $full = $_POST['full_text'];
    $parent = (int)$_POST['parent_id'];

    $upd = $pdo->prepare("UPDATE wiki_content SET title=?, icon=?, image_url=?, video_url=?, short_desc=?, full_text=?, parent_id=? WHERE id=?");
    $upd->execute([$title, $icon, $image, $video, $short, $full, $parent, $target_id]);
    header("Location: admin_editor.php?section=wiki&subsection=edit&cat_id=$parent&msg=updated");
    exit();
}

// Удаление карточки (по желанию)
if (isset($_GET['delete_id'])) {
    $del = $pdo->prepare("DELETE FROM wiki_content WHERE id = ?");
    $del->execute([(int)$_GET['delete_id']]);
    // Перенаправляем обратно в ту же категорию, чтобы обновить список
    $redirect_cat = (int)$_GET['cat_id'];
    header("Location: admin_editor.php?section=wiki&subsection=edit&cat_id=$redirect_cat");
    exit();
}

if (isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    header("Location: admin_editor.php?section=site&msg=saved");
    exit();
}

// Загружаем текущие настройки в массив для удобства
$settings_raw = $pdo->query("SELECT * FROM site_settings")->fetchAll();
$s = [];
foreach ($settings_raw as $row) { $s[$row['setting_key']] = $row['setting_value']; }
?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Панель Управления — Magic Duel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>

    <nav class="navbar navbar-expand-lg mx-3">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <!-- Логотип (ссылка на главную) -->
                <a class="navbar-brand d-flex align-items-center me-0" href="../index.php">
                    <img src="../materials/logo_part_3.png" alt="Logo" class="custom-logo">
                </a>

                <!-- Ссылка WIKI (теперь сразу после лого) -->
                <a href="../wiki.php" class="btn btn-link btn-wiki-text px-2">WIKI</a>
            </div>
            <span class="btn-wiki-text ms-2 fw-bold">ADMIN EDITOR</span>
            <div class="d-flex align-items-center">
                <button class="btn theme-btn-clean me-3" id="themeToggler" onclick="toggleTheme()">☀️</button>
                <a href="admin/admin.php" class="btn btn-warning me-3 fw-bold">
                    🛡️ Админ-панель
                </a>
                <a href="../profile.php">
                    <img src="../<?= htmlspecialchars($meData['avatar']) ?>" 
                         style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ffaa00; object-fit: cover;">
                </a>
                
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if ($section === 'menu'): ?>
            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=site'">
                        <div class="wiki-icon">⚙️</div>
                        <h3>Управление Сайтом</h3>
                        <p>Редактирование главных страниц, соцсетей и новостей.</p>
                        <a href="admin_editor.php?section=site" class="btn btn-warning w-100 fw-bold">ОТКРЫТЬ</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki'">
                        <div class="wiki-icon">📜</div>
                        <h3>Редактор WIKI</h3>
                        <a href="admin_editor.php?section=wiki" class="btn btn-warning w-100 fw-bold">ОТКРЫТЬ</a>
                    </div>
                </div>
            </div>

        <?php elseif ($section === 'wiki' && $subsection === 'main'): ?>
            <div class="row g-4 justify-content-center">
                <div class="col-md-12 text-center mb-4">
                    <h2 class="text-warning text-uppercase fw-bold">Управление Wiki</h2>
                    <a href="admin_editor.php" class="btn btn-sm btn-outline-light">← Назад в главное меню</a>
                </div>
                <div class="col-md-5">
                    <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki&subsection=edit'">
                        <div class="wiki-icon">⚙️</div>
                        <h3>Редактирование</h3>
                        <a href="admin_editor.php?section=wiki&subsection=edit" class="btn btn-warning w-100 fw-bold">Редактировать</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki&subsection=add'">
                        <div class="wiki-icon">➕</div>
                        <h3>Добавление карточек</h3>
                        <a href="admin_editor.php?section=wiki&subsection=add" class="btn btn-warning w-100 fw-bold">Добавить</a>
                    </div>
                </div>
            </div>

        <?php elseif ($section === 'wiki' && $subsection === 'add'): ?>
            <div class="profile-card p-4 admin-container-dark mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-warning m-0">✨ Новый Свиток</h2>
                    <a href="admin_editor.php?section=wiki" class="btn btn-outline-light btn-sm">Назад</a>
                </div>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Раздел</label>
                            <select name="parent_id" class="form-select bg-dark text-light">
                                <option value="1">Стихии</option>
                                <option value="2">Заклинания</option>
                                <option value="3">Артефакты</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Название</label>
                            <input type="text" name="title" class="form-control bg-dark text-light" placeholder="Напр: Магия Воды" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Иконка (Emoji)</label>
                            <input type="text" name="icon" class="form-control bg-dark text-light" placeholder="💧">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Картинка (URL)</label>
                            <input type="text" name="image_url" class="form-control bg-dark text-light" placeholder="materials/img.jpg">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Видео (YouTube URL)</label>
                            <input type="text" name="video_url" class="form-control bg-dark text-light" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое описание</label>
                        <textarea name="short_desc" class="form-control bg-dark text-light" rows="2" placeholder="Видно в списке..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Полный текст</label>
                        <textarea name="full_text" class="form-control bg-dark text-light" rows="6" placeholder="Детальное описание..."></textarea>
                    </div>
                    <button type="submit" name="add_wiki" class="btn btn-warning w-100 fw-bold py-3">Добавить карточку</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($section === 'wiki' && $subsection === 'edit'): ?>
    <div class="mb-4">
        <a href="admin_editor.php?section=wiki" class="btn btn-sm btn-outline-light">← К выбору действия</a>
    </div>

    <?php if (!$cat_id && !$edit_id): ?>
        <h2 class="text-warning text-center mb-4">Выберите раздел для редактирования</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki&subsection=edit&cat_id=1'">
                    <div class="wiki-icon">🔥</div>
                    <h3>Стихии</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki&subsection=edit&cat_id=2'">
                    <div class="wiki-icon">📜</div>
                    <h3>Заклинания</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wiki-category-card" onclick="location.href='admin_editor.php?section=wiki&subsection=edit&cat_id=3'">
                    <div class="wiki-icon">💎</div>
                    <h3>Артефакты</h3>
                </div>
            </div>
        </div>

    <?php elseif ($cat_id && !$edit_id): ?>
        <?php
            $stmt = $pdo->prepare("SELECT * FROM wiki_content WHERE parent_id = ?");
            $stmt->execute([$cat_id]);
            $cards = $stmt->fetchAll();
        ?>
        <h2 class="text-warning mb-4">Карточки в этом разделе</h2>
        <div class="table-responsive profile-card p-3 admin-container-dark">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Иконка</th>
                        <th>Название</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cards as $c): ?>
                    <tr>
                        <td><span class="fs-4"><?= htmlspecialchars($c['icon']) ?></span></td>
                        <td class="fw-bold"><?= htmlspecialchars($c['title']) ?></td>
                        <td>
                            <a href="admin_editor.php?section=wiki&subsection=edit&edit_id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">⚙️</a>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="showDeleteWikiModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['title']) ?>', <?= $cat_id ?>)">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$cards): ?>
                        <tr><td colspan="3" class="text-center opacity-50">Здесь пока пусто...</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="admin_editor.php?section=wiki&subsection=edit" class="btn btn-outline-secondary w-100">Назад к разделам</a>
        </div>

    <?php elseif ($edit_id): ?>
        <?php
            $stmt = $pdo->prepare("SELECT * FROM wiki_content WHERE id = ?");
            $stmt->execute([$edit_id]);
            $item = $stmt->fetch();
        ?>
        <div class="profile-card p-4 admin-container-dark mb-5">
            <h2 class="text-warning mb-4">✍️ Редактирование: <?= htmlspecialchars($item['title']) ?></h2>
            <form method="POST">
                <input type="hidden" name="target_id" value="<?= $item['id'] ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Перенести в раздел</label>
                        <select name="parent_id" class="form-select bg-dark text-light">
                            <option value="1" <?= $item['parent_id'] == 1 ? 'selected' : '' ?>>Стихии</option>
                            <option value="2" <?= $item['parent_id'] == 2 ? 'selected' : '' ?>>Заклинания</option>
                            <option value="3" <?= $item['parent_id'] == 3 ? 'selected' : '' ?>>Артефакты</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" name="title" class="form-control bg-dark text-light" value="<?= htmlspecialchars($item['title']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Иконка</label>
                        <input type="text" name="icon" class="form-control bg-dark text-light" value="<?= htmlspecialchars($item['icon']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Картинка (URL)</label>
                        <input type="text" name="image_url" class="form-control bg-dark text-light" value="<?= htmlspecialchars($item['image_url']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Видео (YouTube URL)</label>
                        <input type="text" name="video_url" class="form-control bg-dark text-light" value="<?= htmlspecialchars($item['video_url']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Краткое описание</label>
                    <textarea name="short_desc" class="form-control bg-dark text-light" rows="2"><?= htmlspecialchars($item['short_desc']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Полный текст</label>
                    <textarea name="full_text" class="form-control bg-dark text-light" rows="8"><?= htmlspecialchars($item['full_text']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="update_wiki" class="btn btn-warning flex-grow-1 fw-bold">СОХРАНИТЬ ИЗМЕНЕНИЯ</button>
                    <a href="admin_editor.php?section=wiki&subsection=edit&cat_id=<?= $item['parent_id'] ?>" class="btn btn-outline-light">ОТМЕНА</a>
                </div>
            </form>
            <?php endif; ?>
            <?php elseif ($section === 'site'): ?>
    <div class="profile-card p-4 admin-container-dark">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning m-0">⚙️ Настройки портала</h2>
            <a href="admin_editor.php" class="btn btn-outline-light btn-sm">Назад</a>
        </div>

        <form method="POST">
            <h5 class="text-light border-bottom border-secondary pb-2 mb-3">Главная страница</h5>
            <div class="mb-3">
                <label class="form-label">Заголовок сайта (Title)</label>
                <input type="text" name="settings[home_title]" class="form-control bg-dark text-light" 
                       value="<?= htmlspecialchars($s['home_title'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Главное описание</label>
                <textarea name="settings[home_desc]" class="form-control bg-dark text-light" rows="3"><?= htmlspecialchars($s['home_desc'] ?? '') ?></textarea>
            </div>
            <h5 class="text-light border-bottom border-secondary pb-2 mt-4 mb-3">Второй слайд карусели</h5>
            <div class="mb-3">
                <label class="form-label">Заголовок второго блока</label>
                <input type="text" name="settings[home_title_2]" class="form-control bg-dark text-light" 
                    value="<?= htmlspecialchars($s['home_title_2'] ?? 'Станьте Легендой Арены') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Описание второго блока</label>
                <textarea name="settings[home_desc_2]" class="form-control bg-dark text-light" rows="3"><?= htmlspecialchars($s['home_desc_2'] ?? '') ?></textarea>
            </div>
            <h5 class="text-light border-bottom border-secondary pb-2 mt-4 mb-3">Социальные сети</h5>
            <div class="row g-3"> <div class="col-12 col-md-6"> <label class="form-label text-warning small">Discord Link</label>
                    <input type="text" name="settings[discord_link]" 
                        class="form-control bg-dark text-light border-secondary" 
                        style="overflow: hidden; text-overflow: ellipsis;"
                        value="<?= htmlspecialchars($s['discord_link'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-warning small">Telegram Link</label>
                    <input type="text" name="settings[telegram_link]" 
                        class="form-control bg-dark text-light border-secondary" 
                        value="<?= htmlspecialchars($s['telegram_link'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" name="update_settings" class="btn btn-warning w-100 fw-bold py-3 mt-3">
                ПРИМЕНИТЬ МАГИЮ
            </button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        function toggleTheme() {
            const html = document.getElementById('main-html');
            const btn = document.getElementById('themeToggler');
            const newTheme = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            btn.innerHTML = newTheme === 'light' ? '☀️' : '🌙';
            localStorage.setItem('theme', newTheme);
            
            // Динамическое изменение классов полей ввода для светлой темы
            document.querySelectorAll('.form-control, .form-select').forEach(el => {
                if(newTheme === 'light') {
                    el.classList.remove('bg-dark', 'text-light');
                } else {
                    el.classList.add('bg-dark', 'text-light');
                }
            });
        }

        function showDeleteWikiModal(id, title, catId) {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteWikiModal'));
        document.getElementById('deleteWikiName').innerText = title;
        
        // Формируем ссылку для удаления
        // Мы передаем и delete_id, и cat_id, чтобы после удаления остаться в той же категории
        document.getElementById('confirmDeleteWikiBtn').href = 
            `admin_editor.php?section=wiki&subsection=edit&cat_id=${catId}&delete_id=${id}`;
            
        deleteModal.show();
    }
        
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('theme') || 'dark';
            document.getElementById('main-html').setAttribute('data-bs-theme', saved);
            if(saved === 'light') toggleTheme(); // Синхронизируем поля
        });

    </script>

    <div class="modal fade" id="deleteWikiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-container-dark border-danger shadow-lg">
            <div class="modal-header border-0 text-center d-block">
                <h5 class="modal-title text-danger fw-bold text-uppercase">⚠️ Сжигание свитка</h5>
            </div>
            <div class="modal-body text-center">
                <p class="text-light">Вы действительно хотите навсегда стереть знания о <br>
                <span id="deleteWikiName" class="text-warning fw-bold fs-4"></span>?</p>
                <p class="small text-muted">Это действие нельзя будет отменить магией.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Сохранить</button>
                <a id="confirmDeleteWikiBtn" href="#" class="btn btn-danger px-4 fw-bold shadow">Уничтожить</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>