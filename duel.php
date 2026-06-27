<?php 
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$playerAvatar = $user['avatar'] ?: 'materials/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Magic Duel — Арена</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1a2e 0%, #0f0f1a 100%);
            color: white;
            min-height: 100vh;
        }
        
        .navbar {
            border: 1px solid #dee2e6 !important; 
            border-radius: 8px;
            margin-top: 10px;
            padding: 0.5rem 1rem;
        }

        /* Стили для твоего навбара */
        .custom-logo {
            height: 40px;
            width: auto;
        }

        .btn-wiki-text {
            color: #ffaa00 !important;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-wiki-text:hover {
            text-shadow: 0 0 10px rgba(255, 170, 0, 0.5);
        }

        .arena-container {
            background: rgba(0, 0, 0, 0.85);
            border: 2px solid #ffaa00;
            border-radius: 20px;
            padding: 40px;
            margin-top: 20px;
            box-shadow: 0 0 30px rgba(255, 170, 0, 0.2);
        }

        .stats-bar { height: 25px; background: #222; border-radius: 12px; overflow: hidden; border: 1px solid #444; margin-bottom: 5px; }
        .hp-fill { height: 100%; transition: width 0.4s ease; text-align: center; line-height: 25px; font-weight: bold; color: white; font-size: 0.8rem; }
        .mana-fill { height: 100%; transition: width 0.4s ease; background: #00d2ff; text-align: center; line-height: 25px; font-weight: bold; color: white; font-size: 0.8rem; }
        
        .log-box {
            height: 150px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
        }

        .mage-img { 
            width: 160px; height: 160px; object-fit: cover;
            border-radius: 50%; border: 4px solid #00d2ff;
        }
        
        .enemy-img { 
            width: 120px; height: 120px; border-radius: 50%; 
            border: 4px solid #ff4444;
            box-shadow: 0 0 15px rgba(255, 68, 68, 0.5);
        }

        .attack-p { animation: dash-p 0.5s; }
        @keyframes dash-p { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(50px); } }
        .attack-e { animation: dash-e 0.5s; }
        @keyframes dash-e { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(-50px); } }
    </style>
</head>
<body>

<!-- Твой стандартный навбар -->
<nav class="navbar navbar-expand-lg mx-3">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center me-0" href="index.php">
                <img src="materials/logo_part_3.png" alt="Logo" class="custom-logo">
            </a>
            <a href="wiki.php" class="btn btn-link btn-wiki-text px-2">WIKI</a>
        </div>
        <a class="btn btn-link btn-wiki-text ms-2">MINI GAME</a>
        <div class="d-flex align-items-center">
                <a href="../profile.php" class="d-flex align-items-center text-decoration-none me-3">
                    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" 
                         alt="Profile" 
                         style="width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ffaa00; object-fit: cover;">
                </a>
            </div>
    </div>
</nav>

<div class="container">
    <div class="arena-container mx-auto col-lg-10 shadow-lg">
        <div class="row align-items-center mb-4">
            <div class="col-md-5 text-center">
                <div id="p-sprite">
                    <img src="<?= htmlspecialchars($playerAvatar) ?>" class="mage-img" alt="Player">
                </div>
                <h4 class="fw-bold mt-2 text-info"><?= htmlspecialchars($user['username']) ?></h4>
                <div class="stats-bar"><div id="p-hp" class="hp-fill bg-success" style="width: 100%;">100 HP</div></div>
                <div class="stats-bar"><div id="p-mana" class="mana-fill" style="width: 100%;">100 MP</div></div>
            </div>

            <div class="col-md-2 text-center"><h1 class="display-4 fw-bold text-warning">VS</h1></div>

            <div class="col-md-5 text-center">
                <div id="e-sprite">
                    <div class="enemy-img mx-auto mb-3 d-flex align-items-center justify-content-center bg-black text-danger fs-1">💀</div>
                </div>
                <h4 class="fw-bold mt-2 text-danger">Теневой Лорд</h4>
                <div class="stats-bar"><div id="e-hp" class="hp-fill bg-danger" style="width: 100%;">150 HP</div></div>
            </div>
        </div>

        <div id="battle-log" class="log-box mb-4"></div>

        <div id="controls" class="row g-2">
            <div class="col-md-3"><button class="btn btn-outline-light w-100" onclick="cast('fire')">🔥 Огонь (20MP)</button></div>
            <div class="col-md-3"><button class="btn btn-outline-light w-100" onclick="cast('ice')">❄️ Лед (15MP)</button></div>
            <div class="col-md-3"><button class="btn btn-outline-light w-100" onclick="cast('void')">🌑 Бездна (45MP)</button></div>
            <div class="col-md-3"><button class="btn btn-outline-light w-100" onclick="cast('heal')">✨ Лечение (30MP)</button></div>
            <div class="col-md-3"><button class="btn btn-warning w-100 text-dark fw-bold" onclick="skipTurn()">⏳ Пропустить</button></div>
        </div>

        <div id="after" class="text-center d-none mt-4">
            <h2 id="res-txt" class="fw-bold mb-3"></h2>
            <a href="profile.php" class="btn btn-warning px-5 rounded-pill">В профиль</a>
        </div>
    </div>
</div>

<script>
    let pHP = 100, pMP = 100, eHP = 150;
    let turn = true;

    function addLog(t, c = "white") {
        const l = document.getElementById('battle-log');
        l.innerHTML += `<div style="color:${c}">> ${t}</div>`;
        l.scrollTop = l.scrollHeight;
    }

    function updateUI() {
        document.getElementById('p-hp').style.width = pHP + "%";
        document.getElementById('p-hp').innerText = pHP + " HP";
        document.getElementById('p-mana').style.width = pMP + "%";
        document.getElementById('p-mana').innerText = pMP + " MP";
        document.getElementById('e-hp').style.width = (eHP/150*100) + "%";
        document.getElementById('e-hp').innerText = eHP + " HP";
    }

    function cast(type) {
        if (!turn) return;
        let cost = 0, dmg = 0;

        if (type === 'fire') { 
            cost = 20; 
            dmg = Math.floor(Math.random() * (30 - 20 + 1)) + 20;
        }
        if (type === 'ice') { 
            cost = 15; 
            dmg = Math.floor(Math.random() * (20 - 10 + 1)) + 10;
        }
        if (type === 'void') { 
            cost = 45; 
            // Исправленная формула для урона 30-60
            dmg = Math.floor(Math.random() * (60 - 30 + 1)) + 30;
        }
        if (type === 'heal') { 
            cost = 30; 
            if (pMP >= cost) { 
                let healAmt = Math.floor(Math.random() * (40 - 25 + 1)) + 25;
                pHP = Math.min(100, pHP + healAmt); pMP -= cost; 
                addLog(`Вы восстановили ${healAmt} HP`, "#28a745");
                updateUI(); turn = false; setTimeout(enemyTurn, 1000); return;
            }
        }

        if (pMP < cost) { addLog("Мана закончилась!", "red"); return; }
        
        pMP -= cost; eHP = Math.max(0, eHP - dmg);
        addLog(`Вы использовали ${type}! Урон: ${dmg}`, "#00d2ff");
        
        document.getElementById('p-sprite').classList.add('attack-p');
        setTimeout(() => document.getElementById('p-sprite').classList.remove('attack-p'), 500);

        updateUI();
        if (eHP <= 0) return endGame('win');
        turn = false;
        setTimeout(enemyTurn, 1200);
    }

    function enemyTurn() {
        let dmg = Math.random() > 0.8 ? 35 : 15;
        pHP = Math.max(0, pHP - dmg);
        addLog(`Теневой Лорд атакует! Урон: ${dmg}`, "#ff4444");
        
        document.getElementById('e-sprite').classList.add('attack-e');
        setTimeout(() => document.getElementById('e-sprite').classList.remove('attack-e'), 500);

        pMP = Math.min(100, pMP + 15); 
        
        updateUI();
        if (pHP <= 0) return endGame('loss');
        turn = true;
    }

    function saveBattleResult(result) {
    // Формируем данные для отправки
    const data = new FormData();
    data.append('result', result); // 'win' или 'loss'

    fetch('admin/update_stats_ajax.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addLog("Статистика сохранена в облаке!", "#ffd700");
        } else {
            addLog("Ошибка сохранения статистики: " + data.error, "red");
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

    function endGame(res) {
        document.getElementById('controls').classList.add('d-none');
        document.getElementById('after').classList.remove('d-none');
        document.getElementById('res-txt').innerText = res === 'win' ? "🏆 ПОБЕДА" : "💀 ПОРАЖЕНИЕ";
    
        saveBattleResult(res);
    }
    
    function skipTurn() {
    if (!turn) return;
    
    let manaRegen = 20;
    pMP = Math.min(100, pMP + manaRegen); // Восстанавливаем 20 маны при пропуске
    
    addLog(`Вы пропустили ход и восстановили ${manaRegen} MP`, "#ffaa00");
    updateUI();
    
    turn = false;
    setTimeout(enemyTurn, 1000);
}

    addLog("Битва началась! Теневой Лорд ждет вашего хода.", "#ffaa00");
    updateUI();
</script>
</body>
</html>