<?php
session_start();
require '../db.php';

// 1. Проверка доступа
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$check = $pdo->prepare("SELECT admin_level FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$me = $check->fetch();
if (!$me || $me['admin_level'] < 1) { header('Location: ../index.php'); exit(); }

// 2. Получаем данные мага
if (!isset($_GET['id'])) { header('Location: admin_users.php'); exit(); }
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);
$u = $stmt->fetch();
if (!$u) { die("Маг не найден."); }

// 3. Проверка иерархии
$isMe = ($u['id'] == $_SESSION['user_id']);
if (!$isMe && $me['admin_level'] <= $u['admin_level']) {
    header('Location: admin_users.php?error=access_denied');
    exit();
}



// 4. Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $lvl = (int)$_POST['lvl'];
    $win = (int)$_POST['win'];
    $kill_count = (int)$_POST['kill_count'];
    $dead_count = (int)$_POST['dead_count'];
    $win_seria = (int)$_POST['win_seria']; // Твое название в БД
    $rounds_played = (int)$_POST['rounds_played']; // Твое название в БД
    $title = trim($_POST['title']);
    $target_admin_level = (int)$_POST['admin_level'];
    $new_password = $_POST['new_password'] ?? '';

    if (!$isMe && $target_admin_level >= $me['admin_level']) {
        $error = "Недопустимый уровень власти!";
    } else {
        try {
            // Базовый запрос
            $sql = "UPDATE users SET 
                    username = ?, email = ?, lvl = ?, win = ?, 
                    kill_count = ?, dead_count = ?, win_seria = ?, 
                    rounds_played = ?, title = ?, admin_level = ?";
            $params = [
                $username, $email, $lvl, $win, 
                $kill_count, $dead_count, $win_seria, 
                $rounds_played, $title, $target_admin_level
            ];

            // Если админ 5+ уровня и ввел пароль — добавляем его в UPDATE
            if ($me['admin_level'] >= 5 && !empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $u['id'];

            $upd = $pdo->prepare($sql);
            $upd->execute($params);

            header("Location: admin_users.php?updated=1");
            exit();
        } catch (PDOException $e) {
            $error = "Ошибка: данные уже используются!";
        }
    }
}



?>

<!DOCTYPE html>
<html lang="ru" id="main-html" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Правка мага — Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="magic-background"></div>
    <div class="container mt-5">
        <div class="profile-card p-4 admin-container-dark mx-auto" style="max-width: 900px;">
            <h2 class="text-warning mb-4 text-uppercase fw-bold text-shadow text-center">⚙️ Настройка аккаунта</h2>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 px-3">
                        <h6 class="text-white border-bottom border-secondary pb-2 mb-3">👤 АККАУНТ</h6>
                        <div class="mb-3">
                            <label class="form-label small text-warning fw-bold">ЛОГИН</label>
                            <input type="text" name="username" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo htmlspecialchars($u['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-warning fw-bold">EMAIL</label>
                            <input type="email" name="email" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-warning fw-bold">ПАРОЛЬ (ТОЛЬКО 5+ LVL)</label>
                            <input type="password" name="new_password" 
                                   class="form-control bg-dark text-light border-secondary shadow-sm" 
                                   placeholder="<?php echo ($me['admin_level'] >= 5) ? 'Введите новый...' : 'Нет доступа'; ?>"
                                   <?php echo ($me['admin_level'] < 5) ? 'disabled' : ''; ?>>
                            <?php if($me['admin_level'] < 5): ?>
                                <div class="form-text text-muted" style="font-size: 0.7rem;">Нужен 5 уровень власти</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4 px-3 border-start border-end border-secondary">
                        <h6 class="text-white border-bottom border-secondary pb-2 mb-3">⚔️ БОЕВЫЕ ЗАСЛУГИ</h6>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-success fw-bold">ПОБЕДЫ</label>
                                <input type="number" name="win" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['win']; ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-danger fw-bold">СМЕРТИ</label>
                                <input type="number" name="dead_count" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['dead_count'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-info fw-bold">УБИЙСТВА</label>
                                <input type="number" name="kill_count" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['kill_count']; ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-warning fw-bold">СЕРИЯ (WS)</label>
                                <input type="number" name="win_seria" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['win_seria'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-white fw-bold">СЫГРАНО РАУНДОВ</label>
                            <input type="number" name="rounds_played" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['rounds_played'] ?? 0; ?>">
                        </div>
                    </div>

                    <div class="col-md-4 px-3">
                        <h6 class="text-white border-bottom border-secondary pb-2 mb-3">👑 ПОЛОЖЕНИЕ</h6>
                        <div class="mb-3">
                            <label class="form-label small text-info fw-bold">ИГРОВОЙ LVL</label>
                            <input type="number" name="lvl" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo $u['lvl']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-warning fw-bold">ТИТУЛ</label>
                            <input type="text" name="title" class="form-control bg-dark text-light border-secondary shadow-sm" value="<?php echo htmlspecialchars($u['title']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-danger fw-bold">УРОВЕНЬ ВЛАСТИ</label>
                            <select name="admin_level" class="form-select bg-dark text-light border-secondary shadow-sm">
                                <option value="0" <?php if($u['admin_level'] == 0) echo 'selected'; ?>>0 — Игрок</option>
                                <?php for($i = 1; $i < $me['admin_level']; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php if($u['admin_level'] == $i) echo 'selected'; ?>><?php echo $i; ?> — Админ</option>
                                <?php endfor; ?>
                                <?php if($isMe): ?><option value="<?php echo $me['admin_level']; ?>" selected><?php echo $me['admin_level']; ?> — ВЫ</option><?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4 justify-content-center border-top border-secondary pt-4">
                    <button type="submit" class="btn btn-warning fw-bold px-5 py-2 shadow-sm">СОХРАНИТЬ В СВИТОК</button>
                    <a href="admin_users.php" class="btn btn-outline-light px-4 py-2 opacity-75">ОТМЕНА</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.getElementById('main-html').setAttribute('data-bs-theme', savedTheme);
    </script>
</body>
</html>