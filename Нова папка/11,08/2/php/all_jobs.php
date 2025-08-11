<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Всички обяви | Fixora</title>
    <!--<link rel="stylesheet" href="../css/style.css">-->
    <link rel="stylesheet" href="../css/all_jobs.css">
    <script src="../js/favorites.js" defer></script>
    <script src="../js/all_jobs.js" defer></script>
    <?php include 'navbar.php'; ?>

</head>
<body>

<div class="jobs-wrapper">
    <div class="filters">
        <select id="typeFilter">
            <option value="">Всички типове</option>
            <option value="offer">Предлагам работа</option>
            <option value="seek">Търся работа</option>
        </select>
        <?php $professions = include __DIR__ . '/professions.php'; ?>
<select id="professionFilter">
    <option value="">Всички професии</option>
    <?php foreach ($professions as $k => $label): ?>
        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
    <?php endforeach; ?>
</select>

    </div>

    <div id="all-jobs-container"></div>
</div>

<script>
function loadAllJobs() {
    const type = document.getElementById('typeFilter').value;
    const profession = document.getElementById('professionFilter').value;

    fetch(`fetch_all_jobs.php?type=${type}&profession=${profession}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('all-jobs-container').innerHTML = data;
            attachFavoriteListeners();
        });
}

document.getElementById('typeFilter').addEventListener('change', loadAllJobs);
document.getElementById('professionFilter').addEventListener('change', loadAllJobs);

// Зареждаме всички обяви при отваряне
window.addEventListener('DOMContentLoaded', loadAllJobs);
</script>

</body>
</html>
