<?php
session_start();
require_once 'db.php';
require_once 'favorites_utils.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit('Неоторизиран достъп.');
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = (int) $_POST['job_id'];
    removeFromFavorites($conn, $user_id, $job_id);
    echo 'removed';
    exit();
}

http_response_code(400);
exit('Невалидна заявка.');
?>
