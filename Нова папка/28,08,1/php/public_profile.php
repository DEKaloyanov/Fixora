<?php
session_start();
require 'db.php';

// ID на профила за гледане
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($profile_id <= 0) {
    http_response_code(400);
    echo "Липсва или невалиден потребител.";
    exit;
}

// Зареждаме потребителя от БД
$stmt = $conn->prepare("SELECT id, username, ime, familiq, email, telefon, city, age, profile_image, show_email, show_phone, show_city, show_age FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $profile_id]);
$viewUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$viewUser) {
    http_response_code(404);
    echo "Потребителят не е намерен.";
    exit;
}

// Профилна снимка
$profileImage = (!empty($viewUser['profile_image']) && file_exists('../uploads/' . $viewUser['profile_image']))
    ? '../uploads/' . $viewUser['profile_image']
    : '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Профил на <?= htmlspecialchars($viewUser['username']) ?> - Fixora</title>
    <link rel="stylesheet" href="../css/profil.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Малки override-и за публичния изглед -->
    <style>
      /* Лентата с бутони вляво */
      .job-controls.public { align-items: flex-start; }
      .job-controls.public .main-buttons { justify-content: flex-start; }
      /* Равномерен отстъп между бутоните */
      .main-buttons.public-left { gap: 10px; flex-wrap: wrap; }
      /* Премахваме всичко свързано с добавяне/активни секции при публичния профил */
      .job-controls.public .job-sub-buttons, 
      .job-controls.public #active-projects-btn, 
      .job-controls.public #btn-add-job { display: none !important; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main>

    <!-- ===== Хедър: еднакъв стил, но без "Редактирай профила" ===== -->
    <div class="profile-container">
        <div class="profile-left">
            <img src="<?= htmlspecialchars($profileImage) ?>" class="profile-image" alt="Профилна снимка">
        </div>

        <div class="profile-right">
            <!-- Колона 1 -->
            <div class="info-col basic">
                <p class="info-row"><span class="label">Потребителско име:</span><span class="value"><?= htmlspecialchars($viewUser['username']) ?></span></p>
                <p class="info-row"><span class="label">Име:</span><span class="value"><?= htmlspecialchars($viewUser['ime']) ?></span></p>
                <p class="info-row"><span class="label">Фамилия:</span><span class="value"><?= htmlspecialchars($viewUser['familiq']) ?></span></p>
            </div>

            <!-- Колона 2: видимите контакти на ТОЗИ потребител -->
            <div class="info-col contact">
                <?php if (!empty($viewUser['show_email'])): ?>
                    <p class="info-row"><span class="label">Имейл:</span><span class="value"><?= htmlspecialchars($viewUser['email']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($viewUser['show_phone'])): ?>
                    <p class="info-row"><span class="label">Телефон:</span><span class="value"><?= htmlspecialchars($viewUser['telefon']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($viewUser['show_city'])): ?>
                    <p class="info-row"><span class="label">Град:</span><span class="value"><?= htmlspecialchars($viewUser['city']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($viewUser['show_age'])): ?>
                    <p class="info-row"><span class="label">Години:</span><span class="value"><?= htmlspecialchars($viewUser['age']) ?></span></p>
                <?php endif; ?>
            </div>

            <!-- Колона 3: само средна оценка (без бутони) -->
            <div class="info-col actions">
                <div class="avg-rating">
                    <?php
                    include_once 'rating_utils.php';
                    echo displayUserAverageRating($conn, (int)$viewUser['id']);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Контроли (вляво): Всички / Предлагам / Търся / Предишни дейности ===== -->
    <div class="job-controls public">
        <div class="main-buttons public-left">
            <button id="btn-all-jobs" class="active">Всички обяви</button>
            <button id="btn-offer">Моите проекти</button>
            <button id="btn-seek">Моят екип</button>
            <button id="btn-history">Предишни дейности</button>
        </div>
    </div>

    <!-- Списък с обяви -->
    <div id="jobList" style="margin-top:30px;"></div>

    <!-- ===== История (стари проекти) ===== -->
    <section id="history-section" class="history-section">
        <!-- пълни се от fetch_user_history.php чрез JS -->
    </section>

</main>

<footer>
    <p>Свържи се с нас: support@fixora.bg | +359 888 123 456</p>
</footer>

<script>const VIEW_USER_ID = <?= (int)$viewUser['id'] ?>;</script>
<script src="../js/public_profile.js?v=<?php echo time(); ?>"></script>
<script src="/Fixora/js/favorites.js"></script>
</body>
</html>
