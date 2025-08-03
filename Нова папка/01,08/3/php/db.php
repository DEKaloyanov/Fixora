<?php
session_start();

// Конфигурация на базата данни
define('DB_HOST', 'localhost');
define('DB_NAME', 'fixora');
define('DB_USER', 'root');
define('DB_PASS', '');

// Настройки на приложението
define('BASE_URL', 'http://localhost/fixora');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('DEFAULT_PROFILE_IMAGE', 'images/default-profile.png');

// Грешки и логване
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Връзка с базата данни
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Възникна грешка при връзката с базата данни. Моля, опитайте по-късно.");
}

// Функции за сигурност
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Проверка за логнат потребител
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Проверка за администратор
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}