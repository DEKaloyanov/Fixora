<?php
session_start();
require('db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

$user = $_SESSION['user'];

?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <?php include 'navbar.php'; ?>
    <meta charset="UTF-8">
    <title>Моят Профил - Fixora</title>
    <link rel="stylesheet" href="../css/profil.css?v=<?php echo time(); ?>">
</head>
<body>
<main>
    <div class="profile-container">
        <!--<div class="profile-container">-->
            <div class="profile-left">
                <img src="<?php echo !empty($user['profile_image']) ? '../uploads/'.$user['profile_image'] : '../img/default-user.png'; ?>" class="profile-image" alt="Профилна снимка">
            </div>
            <div class="profile-info">
                <p><span class="label">Потребителско име:</span> <span class="value"><?= htmlspecialchars($user['username']) ?></span></p>
                <p><span class="label">Име:</span> <span class="value"><?= htmlspecialchars($user['ime']) ?></span></p>
                <p><span class="label">Фамилия:</span> <span class="value"><?= htmlspecialchars($user['familiq']) ?></span></p>
                
                <?php if ($user['show_email']) echo '<p><span class="label">Имейл:</span> <span class="value">' . htmlspecialchars($user['email']) . '</span></p>'; ?>
                <?php if ($user['show_phone']) echo '<p><span class="label">Телефон:</span> <span class="value">' . htmlspecialchars($user['telefon']) . '</span></p>'; ?>
                <?php if ($user['show_city']) echo '<p><span class="label">Град:</span> <span class="value">' . htmlspecialchars($user['city']) . '</span></p>'; ?>
                <?php if ($user['show_age']) echo '<p><span class="label">Години:</span> <span class="value">' . htmlspecialchars($user['age']) . '</span></p>'; ?>
                <a href="edit_profile.php" class="edit-profile-button"> Редактирай профила</a>
                <form action="/Fixora/php/logout.php" method="post">
                    <button type="submit" style="margin-top: 15px;">Изход</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Начало на секцията с бутони и обяви -->
    <div class="job-controls">
        <button data-filter="all">Всички обяви</button>
        <button data-filter="add">Добави обява</button>
        <div class="job-sub-buttons all">
            <button id="btn-offer">Предлагам работа</button>
            <button id="btn-seek">Търся работа</button>
        </div>
        <div class="job-sub-buttons add">
            <button id="btn-add-offer">Добави работа</button>
            <button id="btn-add-seek">Добави екип</button>
        </div>
    </div>

    <!-- Място за показване на формите за добавяне -->
    <div id="jobFormContainer"></div>

    <!-- Място за зареждане на обявите -->
    <div id="jobList"></div>

<!-- Останалата част от HTML кода остава същата -->

    <div id="all-jobs" class="content-section active"><div id="jobList"></div></div>
    <div id="offer-jobs" class="content-section"><div id="offerJobList"></div></div>
    <div id="seek-jobs" class="content-section"><div id="seekJobList"></div></div>

    <div id="add-job-section" class="content-section">
        <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
            <!-- Тук се зарежда формата с JS -->
        </form>
        <div id="add-job-container" style="display: none;"></div>
    </div>
</main>

<div class="footer-contacts">
    <p>Контакти: support@fixora.bg | Телефон: 0888 123 456</p>
</div>

<script>
// Филтриране
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

        const filter = this.dataset.filter;
        const sectionId = filter === 'add' ? 'add-job-section' : `${filter}-jobs`;
        const section = document.getElementById(sectionId);
        if (section) section.classList.add('active');
    });
});

// Зареждане на форми
document.getElementById('btn-add-offer').addEventListener('click', () => {
    const form = document.getElementById('jobForm');
    form.innerHTML = `
        <input type="hidden" name="job_type" value="offer">
        <label>Тип работа:</label>
        <select name="profession" required>
            <option value="">Избери тип работа</option>
            <option value="boqjdiq">Бояджия</option>
            <option value="zidar">Зидар</option>
            <option value="kofraj">Кофражист</option>
            <option value="elektrikar">Електротехник</option>
        </select>
        <label>Населено място:</label>
        <input type="text" name="location" required placeholder="Изберете град">
        <label>Надник:</label>
        <input type="number" name="price_per_day" placeholder="Въведете надник">
        <label>Цена на квадрат:</label>
        <input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">
        <label>Снимки:</label>
        <input type="file" name="images[]" multiple accept="image/*">
        <label>Описание:</label>
        <textarea name="description" placeholder="Описание (незадължително)"></textarea>
        <button type="submit">Запази обявата</button>
    `;
});

document.getElementById('btn-add-seek').addEventListener('click', () => {
    const form = document.getElementById('jobForm');
    form.innerHTML = `
        <input type="hidden" name="job_type" value="seek">
        <label>Тип работа:</label>
        <select name="profession" required>
            <option value="">Избери тип работа</option>
            <option value="boqjdiq">Бояджия</option>
            <option value="zidar">Зидар</option>
            <option value="kofraj">Кофражист</option>
            <option value="elektrikar">Електротехник</option>
        </select>
        <label>Населено място:</label>
        <input type="text" name="city" required placeholder="Изберете град">
        <label>Брой работници:</label>
        <input type="number" name="team_size" id="teamSize" min="1" max="20" value="1" required>
        <div id="teamMemberFields"></div>
        <label>Надник:</label>
        <input type="number" name="price_per_day" placeholder="Въведете надник">
        <label>Цена на квадрат:</label>
        <input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">
        <label>Описание:</label>
        <textarea name="description" placeholder="Описание (незадължително)"></textarea>
        <button type="submit">Запази обявата</button>
    `;
    document.getElementById('teamSize').addEventListener('input', function () {
        const container = document.getElementById('teamMemberFields');
        container.innerHTML = '';
        for (let i = 1; i <= this.value; i++) {
            container.innerHTML += `<input type="text" name="team_member_${i}" placeholder="Име на работник ${i}" required>`;
        }
    });
});

// Зареждане на обяви
function loadJobs(type = '') {
    let target = 'jobList';
    if (type === 'offer') target = 'offerJobList';
    if (type === 'seek') target = 'seekJobList';
    fetch(`fetch_jobs.php${type ? '?type=' + type : ''}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById(target).innerHTML = html;
        });
}
document.addEventListener("DOMContentLoaded", () => {
    loadJobs();
    loadJobs('offer');
    loadJobs('seek');
});
</script>
<script src="../js/profil.js?v=<?php echo time(); ?>"></script>
</body>
</html>
