<?php
session_start();
require '../db.php';

// 1. Проверка доступа
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch();

if (!$me || $me['admin_level'] < 1) {
    header('Location: ../index.php');
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    // Если есть поиск, ищем по ID, нику или почте
    // % позволяет искать частичное совпадение (например, "admin" найдет "superadmin")
    $query = "SELECT * FROM users WHERE 
              id = ? OR 
              username LIKE ? OR 
              email LIKE ? 
              ORDER BY id ASC";
    $stmt = $pdo->prepare($query);
    $searchTerm = "%$search%";
    $stmt->execute([$search, $searchTerm, $searchTerm]);
} else {
    // Если поиска нет, выводим всех по порядку
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
}


$stmtMe = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtMe->execute([$_SESSION['user_id']]);
$meData = $stmtMe->fetch();

$allUsers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>База Магов — Admin Panel</title>
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
                <span class="btn-wiki-text ms-2 fw-bold">USERS</span>
            <div class="d-flex align-items-center">
                <button class="btn theme-btn-clean me-3" id="themeToggler" onclick="toggleTheme()">☀️</button>
                <a href="../profile.php" class="d-flex align-items-center text-decoration-none">
                    <img src="../<?php echo htmlspecialchars($meData['avatar']); ?>" 
             style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ffaa00; object-fit: cover;">
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="profile-card p-4 admin-container-dark">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-uppercase fw-bold text-shadow m-0" style="color: #ffaa00 !important;">
                    📜 Свиток всех магов
                </h2>
                <a href="create_user.php" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm" 
                   style="border-radius: 8px; font-size: 1.1rem;">+</a>
            </div>
        <div class="search-container mb-4">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control bg-dark text-light border-secondary" 
                    placeholder="Поиск по ID, нику или почте..." 
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-warning fw-bold">Найти</button>
                <?php if (!empty($search)): ?>
                    <a href="admin_users.php" class="btn btn-outline-secondary">Сброс</a>
                <?php endif; ?>
            </form>
        </div>
                    <div class="table-responsive">
    <table class="table table-hover align-middle custom-admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Облик</th>
                <th>Имя</th>
                <th>Почта</th> <th>LVL</th>
                <th>Власть</th> <th>Статистика (W/K)</th>
                <th>Ранг</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allUsers as $u): ?>
            <tr>
                <td class="fw-bold"><?php echo $u['id']; ?></td>
                <td>
                    <img src="../<?php echo htmlspecialchars($u['avatar']); ?>" 
                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #ffaa00;">
                </td>
                <td>
                    <span class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></span>
                    <?php if($u['admin_level'] >= 1): ?>
                        <span class="badge bg-warning text-dark ms-1">ADMIN</span>
                    <?php endif; ?>
                </td>
                
                <td class="small text-muted">
                    <?php echo htmlspecialchars($u['email']); ?>
                </td>

                <td><span class="badge bg-info"><?php echo $u['lvl']; ?></span></td>

                <td>
                    <?php if ($u['admin_level'] >= 5): ?>
                        <span class="badge bg-danger shadow-sm">Создатель</span>
                    <?php elseif ($u['admin_level'] >= 4): ?>
                        <span class="badge bg-primary shadow-sm">Гл. Админ</span>
                    <?php elseif ($u['admin_level'] >= 3): ?>
                        <span class="badge bg-primary shadow-sm">Тех. Админ</span>
                    <?php elseif ($u['admin_level'] >= 2): ?>
                        <span class="badge bg-primary shadow-sm">Админ</span>
                    <?php elseif ($u['admin_level'] >= 1): ?>
                        <span class="badge bg-success shadow-sm">Модератор</span>
                    <?php else: ?>
                        <span class="text-muted small">Игрок</span>
                    <?php endif; ?>
                </td>

                <td>
                    <span class="text-success fw-bold">W: <?php echo $u['win']; ?></span> / 
                    <span class="text-danger fw-bold">K: <?php echo $u['kill_count']; ?></span>
                </td>
                <td class="text-primary fw-bold small"><?php echo htmlspecialchars($u['title']); ?></td>
                <td>
                    <div class="btn-group shadow-sm">
                        <?php 
                            $isMe = ($u['id'] == $_SESSION['user_id']);
                            $canManage = ($isMe || $me['admin_level'] > $u['admin_level']);
                        ?>

                        <?php if ($canManage): ?>
                            <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">⚙️</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary disabled" title="Недостаточно прав для редактирования">⚙️</button>
                        <?php endif; ?>

                        <?php if ($canManage && !$isMe): ?>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="showDeleteModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>')">🗑️</button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary disabled" 
                                    title="<?php echo $isMe ? 'Себя нельзя удалить' : 'Недостаточно прав для удаления'; ?>">🗑️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-container-dark border-danger">
                <div class="modal-header border-0 text-center d-block">
                    <h5 class="modal-title text-danger fw-bold text-uppercase">⚠️ Изгнание мага</h5>
                </div>
                <div class="modal-body text-center">
                    <p>Вы действительно хотите навсегда удалить мага <br>
                    <span id="deleteUserName" class="text-warning fw-bold fs-4"></span>?</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Отмена</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger px-4 fw-bold">Удалить</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleTheme() {
        const html = document.getElementById('main-html');
        const btn = document.getElementById('themeToggler');
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', newTheme);
        btn.innerHTML = newTheme === 'light' ? '☀️' : '🌙';
        localStorage.setItem('theme', newTheme);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.getElementById('main-html').setAttribute('data-bs-theme', savedTheme);
        document.getElementById('themeToggler').innerHTML = savedTheme === 'light' ? '☀️' : '🌙';
    });

    function showDeleteModal(id, name) {
        const myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteUserName').innerText = name;
        document.getElementById('confirmDeleteBtn').href = 'delete_user.php?id=' + id;
        myModal.show();
    }
    </script>
</body>
</html>