<?php
session_start();
session_unset(); // Удаляем все переменные
session_destroy(); // Уничтожаем саму сессию

// Перекидываем на главную
header('Location: ../index.php');
exit();