<?php
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
session_start();


$host = '127.0.0.1';
$db   = 'magic_duel_db';
$user = 'root';
$pass = 'root'; // В MAMP пароль обычно root
$port = '8889'; // Стандартный порт MySQL в MAMP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Если будет ошибка подключения, мы её увидим
     die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>