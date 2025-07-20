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
    <meta charset="UTF-8">
    <title>Моят Профил - Fixora</title>
    <link rel="stylesheet" href="../css/profil.css">
    <style>
        .profile-container { display: flex; margin: 20px; gap: 30px; }
        .profile-image { width: 250px; height: 250px; object-fit: cover; border-radius: 8px; }
        .job-filter-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        .filter-btn {
            padding: 10px 20px;
            background: #ddd;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #002147;
            color: white;
        }
        .content-section {
            display: none;
            padding: 200px;
            min-height: 300px;
        }
        .content-section.active {
            display: block;
        }
        .job-form input, .job-form select, .job-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        .footer-contacts {
            background: #002147;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: 40px;
        }
        #add-job-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        #add-job-options button {
            padding: 10px 20px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <a href="../index.php"><img src="../img/logo.png" alt="Fixora Logo" class="logo-small"></a>
    <nav>
        <ul class="navbar">
            <li><a href="all_jobs.php">Обяви</a></li>
            <li><a href="../pages/obqvi.html">Работодатели</a></li>
            <li><a href="../pages/chat.html">Чат</a></li>
            <li><a href="../pages/kalkulator.html">Калкулатор</a></li>
            <li><a href="../pages/za-nas.html">За нас</a></li>
            <li><a href="../pages/kontakt.html">Контакти</a></li>
            <li><a href="logout.php">Изход</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="profile-container">
        <div class="profile-left">
            <img src="<?php echo !empty($user['profile_image']) ? '../uploads/'.$user['profile_image'] : '../img/default-user.png'; ?>" class="profile-image" alt="Профилна снимка">
        </div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p>Име: <?php echo htmlspecialchars($user['ime']); ?></p>
            <p>Фамилия: <?php echo htmlspecialchars($user['familiq']); ?></p>
            <a href="edit_profile.php" class="edit-profile-button">✏️ Редактирай профила</a>

        </div>
    </div>

    <div class="job-filter-buttons">
        <button class="filter-btn active" data-filter="all">Всички обяви</button>
        <button class="filter-btn" data-filter="offer">Предлагам работа</button>
        <button class="filter-btn" data-filter="seek">Търся работа</button>
        <button class="filter-btn" data-filter="add">Добави обява</button>
    </div>

    <div id="all-jobs" class="content-section active"><div id="jobList"></div></div>
    <div id="offer-jobs" class="content-section"><div id="offerJobList"></div></div>
    <div id="seek-jobs" class="content-section"><div id="seekJobList"></div></div>

    <div id="add-job-section" class="content-section">
        <div id="add-job-options">
            <button id="btn-add-offer">Добави работа</button>
            <button id="btn-add-seek">Добави екип</button>
        </div>
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
</body>
</html>
