<?php
session_start();
require('db.php');
$role = $_SESSION['user']['role'] ?? 'user';


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
                
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <div style="text-align: right; margin: 10px;">
                        <a href="admin/admin.php" style="padding: 10px 15px; background: #002147; color: white; text-decoration: none; border-radius: 5px;">
                            ⚙️ Админ панел
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>


    <?php
    // Заявки за свързване
    $stmt = $conn->prepare("
        SELECT cr.*, ime, familiq, profile_image
        FROM connection_requests cr
        JOIN users u ON cr.sender_id = u.id
        WHERE cr.receiver_id = ? AND cr.status = 'pending'
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($requests):
    ?>
    <div class="connection-requests">
        <h3>Заявки за свързване:</h3>
        <?php foreach ($requests as $req): ?>
            <div class="request-card">
                <img src="<?php echo !empty($req['profile_image']) ? '../uploads/' . htmlspecialchars($req['profile_image']) : '../img/default-user.png'; ?>" alt="Профил" class="avatar">
                <span><?php echo htmlspecialchars($req['ime'] . ' ' . $req['familiq']); ?></span>
                <form action="approve_request.php" method="post" style="display: inline;">
                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                    <button name="action" value="accept">✅ Приеми</button>
                    <button name="action" value="decline">❌ Откажи</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>



    <!-- Начало на секцията с бутони и обяви -->
    <div class="job-controls">
        <div class="job-sub-buttons all">
            <button id="btn-offer">Предлагам работа</button>
            <button id="btn-seek">Търся работа</button>
        </div>
        <div class="job-sub-buttons add">
            <button id="btn-add-offer">Добави работа</button>
            <button id="btn-add-seek">Добави екип</button>
        </div>
        <div class="main-buttons">
            <button id="btn-all-jobs">Всички обяви</button>
            <button id="active-projects-btn" class="profil-btn">Активни обяви</button>
            <button id="btn-add-job">Добави обява</button>
        </div>
    </div>

    <!-- Място за показване на формите за добавяне -->
    <div id="jobFormContainer"></div>

    <!-- Място за зареждане на обявите -->
    <div id="jobList"></div>

<!-- Останалата част от HTML кода остава същата -->


</main>

  <footer>
    <p>Свържи се с нас: support@fixora.bg | +359 888 123 456</p>
  </footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const allJobsBtn = document.getElementById('btn-all-jobs');
    const addJobBtn = document.getElementById('btn-add-job');
    const leftGroup = document.querySelector('.job-sub-buttons.all');
    const rightGroup = document.querySelector('.job-sub-buttons.add');

    // Първоначално състояние
    allJobsBtn.classList.add('active');
    leftGroup.classList.add('show');
    loadJobs();

    // Бутон за всички обяви
    allJobsBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (!allJobsBtn.classList.contains('active')) {
            allJobsBtn.classList.add('active');
            addJobBtn.classList.remove('active');
            leftGroup.classList.add('show');
            rightGroup.classList.remove('show');
            loadJobs();
        }
    });

    // Бутон за добавяне на обява
    addJobBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (!addJobBtn.classList.contains('active')) {
            addJobBtn.classList.add('active');
            allJobsBtn.classList.remove('active');
            leftGroup.classList.remove('show');
            rightGroup.classList.add('show');
            document.getElementById('jobList').innerHTML = '';
            document.getElementById('jobFormContainer').style.display = 'block';
        }
    });

    // Бутони за зареждане на обяви
    document.getElementById('btn-offer').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobs('offer');
    });

    document.getElementById('btn-seek').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobs('seek');
    });

    // Бутони за добавяне на обяви
    document.getElementById('btn-add-offer').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobForm('offer');
    });

    document.getElementById('btn-add-seek').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobForm('seek');
    });

    // Функция за зареждане на обяви
// Зареждане на обяви
function loadJobs(type = '') {
    fetch(`fetch_jobs.php${type ? '?type=' + type : ''}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = html;
        });
}
}

    // Функция за зареждане на форма
    function loadJobForm(type) {
        document.getElementById('jobList').innerHTML = '';
        document.getElementById('jobFormContainer').style.display = 'block';
        
        const formHTML = type === 'offer' ? `
            <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
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
            </form>
        ` : `
            <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
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
            </form>
        `;
        
        document.getElementById('jobFormContainer').innerHTML = formHTML;
        
        // Инициализиране на event listeners
        if (type === 'seek') {
            document.getElementById('teamSize').addEventListener('input', function() {
                const container = document.getElementById('teamMemberFields');
                container.innerHTML = '';
                for (let i = 1; i <= this.value; i++) {
                    container.innerHTML += `<input type="text" name="team_member_${i}" placeholder="Име на работник ${i}" required>`;
                }
            });
        }
    }
);

<div id="active-projects-section" style="display: none;">
    <h2>Активни обяви</h2>
    <?php
    $stmt = $conn->prepare("
        SELECT ps.*, j.profession, j.description, u1.ime AS user1_ime, u1.familiq AS user1_familiq, u1.profile_image AS user1_img,
                             u2.ime AS user2_ime, u2.familiq AS user2_familiq, u2.profile_image AS user2_img
        FROM project_status ps
        JOIN jobs j ON j.id = ps.job_id
        JOIN users u1 ON u1.id = ps.user1_id
        JOIN users u2 ON u2.id = ps.user2_id
        WHERE (ps.user1_id = ? OR ps.user2_id = ?)
    ");
    $stmt->execute([$current_user_id, $current_user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($projects as $project):
        $other_user_id = ($project['user1_id'] == $current_user_id) ? $project['user2_id'] : $project['user1_id'];
        $other_user_name = ($project['user1_id'] == $current_user_id)
            ? $project['user2_ime'] . ' ' . $project['user2_familiq']
            : $project['user1_ime'] . ' ' . $project['user1_familiq'];
        $other_user_img = ($project['user1_id'] == $current_user_id)
            ? $project['user2_img']
            : $project['user1_img'];

        $you_started = ($project['user1_id'] == $current_user_id) ? $project['user1_started'] : $project['user2_started'];
        $they_started = ($project['user1_id'] == $current_user_id) ? $project['user2_started'] : $project['user1_started'];

        $you_rated = ($project['user1_id'] == $current_user_id) ? $project['user1_rated'] : $project['user2_rated'];
        $they_rated = ($project['user1_id'] == $current_user_id) ? $project['user2_rated'] : $project['user1_rated'];
    ?>
        <div class="active-project-card">
            <h3><?= htmlspecialchars($project['profession']) ?></h3>
            <p><?= htmlspecialchars($project['description']) ?></p>

            <div class="timeline">
                <div class="step <?= $you_started ? 'done' : '' ?>">Вие започнахте</div>
                <div class="step <?= $they_started ? 'done' : '' ?>"><?= htmlspecialchars($other_user_name) ?> започна</div>
            </div>

            <?php if (!$you_started): ?>
                <form method="POST" action="php/start_project.php">
                    <input type="hidden" name="job_id" value="<?= $project['job_id'] ?>">
                    <button type="submit">Започни проекта</button>
                </form>
            <?php endif; ?>

            <?php if ($you_started && $they_started && !$you_rated): ?>
                <form method="POST" action="php/submit_rating.php">
                    <input type="hidden" name="to_user_id" value="<?= $other_user_id ?>">
                    <input type="hidden" name="job_id" value="<?= $project['job_id'] ?>">
                    <label>Оцени потребителя:</label>
                    <select name="rating" required>
                        <option value="">Избери</option>
                        <option value="5">⭐⭐⭐⭐⭐</option>
                        <option value="4">⭐⭐⭐⭐</option>
                        <option value="3">⭐⭐⭐</option>
                        <option value="2">⭐⭐</option>
                        <option value="1">⭐</option>
                    </select>
                    <textarea name="comment" placeholder="Коментар (незадължителен)"></textarea>
                    <button type="submit">Изпрати оценка</button>
                </form>
            <?php elseif ($you_rated): ?>
                <p>✅ Вече сте оценили този потребител.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div id="ratings-section">
    <h2>Оценки и обратна връзка</h2>

    <h3>⭐ Средна оценка:</h3>
    <?php
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE to_user_id = ?");
    $stmt->execute([$current_user_id]);
    $avg = $stmt->fetchColumn();
    echo $avg ? number_format($avg, 2) . ' / 5' : 'Няма оценки';
    ?>

    <h3>✅ Получени оценки:</h3>
    <?php
    $stmt = $conn->prepare("
        SELECT r.*, u.ime, u.familiq 
        FROM ratings r 
        JOIN users u ON u.id = r.from_user_id 
        WHERE r.to_user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$current_user_id]);
    $received = $stmt->fetchAll();

    if ($received):
        foreach ($received as $rate): ?>
            <div class="rating-card">
                <strong><?= htmlspecialchars($rate['ime'] . ' ' . $rate['familiq']) ?></strong>
                <span>Оценка: <?= str_repeat('⭐', (int)$rate['rating']) ?></span>
                <?php if (!empty($rate['comment'])): ?>
                    <p><?= htmlspecialchars($rate['comment']) ?></p>
                <?php endif; ?>
                <small><?= date('d.m.Y H:i', strtotime($rate['created_at'])) ?></small>
            </div>
        <?php endforeach;
    else:
        echo "<p>Няма получени оценки.</p>";
    endif;
    ?>

    <h3>📤 Дадени от вас оценки:</h3>
    <?php
    $stmt = $conn->prepare("
        SELECT r.*, u.ime, u.familiq 
        FROM ratings r 
        JOIN users u ON u.id = r.to_user_id 
        WHERE r.from_user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$current_user_id]);
    $given = $stmt->fetchAll();

    if ($given):
        foreach ($given as $rate): ?>
            <div class="rating-card given">
                <strong><?= htmlspecialchars($rate['ime'] . ' ' . $rate['familiq']) ?></strong>
                <span>Оценка: <?= str_repeat('⭐', (int)$rate['rating']) ?></span>
                <?php if (!empty($rate['comment'])): ?>
                    <p><?= htmlspecialchars($rate['comment']) ?></p>
                <?php endif; ?>
                <small><?= date('d.m.Y H:i', strtotime($rate['created_at'])) ?></small>
            </div>
        <?php endforeach;
    else:
        echo "<p>Няма дадени оценки.</p>";
    endif;
    ?>
</div>


</script>
<script src="../js/profil.js?v=<?php echo time(); ?>"></script>
</body>
</html>
