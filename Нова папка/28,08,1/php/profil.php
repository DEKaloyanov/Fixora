<?php
session_start();
require('db.php');
$role = $_SESSION['user']['role'] ?? 'user';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

$user = $_SESSION['user'];
$current_user_id = (int)$user['id'];
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Моят Профил - Fixora</title>
    <link rel="stylesheet" href="../css/profil.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
<?php include 'navbar.php'; ?>
<main>

    <!-- ===== Профилен хедър (нова подредба) ===== -->
    <div class="profile-container">
        <!-- Лява колона: профилна снимка -->
        <div class="profile-left">
            <?php
            $profileImage = !empty($user['profile_image']) && file_exists('../uploads/' . $user['profile_image'])
                ? '../uploads/' . $user['profile_image']
                : '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
            ?>
            <img src="<?= htmlspecialchars($profileImage) ?>" class="profile-image" alt="Профилна снимка">
        </div>

        <!-- Дясна зона: три колони -->
        <div class="profile-right">
            <!-- Колона 1: Основни -->
            <div class="info-col basic">
                <p class="info-row"><span class="label">Потребителско име:</span><span class="value"><?= htmlspecialchars($user['username']) ?></span></p>
                <p class="info-row"><span class="label">Име:</span><span class="value"><?= htmlspecialchars($user['ime']) ?></span></p>
                <p class="info-row"><span class="label">Фамилия:</span><span class="value"><?= htmlspecialchars($user['familiq']) ?></span></p>
            </div>

            <!-- Колона 2: Контакти (ако са видими) -->
            <div class="info-col contact">
                <?php if (!empty($user['show_email'])): ?>
                    <p class="info-row"><span class="label">Имейл:</span><span class="value"><?= htmlspecialchars($user['email']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($user['show_phone'])): ?>
                    <p class="info-row"><span class="label">Телефон:</span><span class="value"><?= htmlspecialchars($user['telefon']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($user['show_city'])): ?>
                    <p class="info-row"><span class="label">Град:</span><span class="value"><?= htmlspecialchars($user['city']) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($user['show_age'])): ?>
                    <p class="info-row"><span class="label">Години:</span><span class="value"><?= htmlspecialchars($user['age']) ?></span></p>
                <?php endif; ?>
            </div>

            <!-- Колона 3: Средна оценка + бутони -->
            <div class="info-col actions">
                <div class="avg-rating">
                    <?php
                    include_once 'rating_utils.php';
                    echo displayUserAverageRating($conn, $user['id']);
                    ?>
                </div>
                <a href="#history-section" class="btn-action secondary">Предишни дейности</a>
                <a href="edit_profile.php" class="btn-action primary">Редактирай профила</a>

                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="admin/admin.php" class="btn-action admin">⚙️ Админ панел</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $current_user_id = (int)$user['id']; ?>

    <!-- ===== Заявки за свързване ===== -->
    <?php
    $stmt = $conn->prepare("
        SELECT cr.*, ime, familiq, profile_image
        FROM connection_requests cr
        JOIN users u ON cr.sender_id = u.id
        WHERE cr.receiver_id = ? AND cr.status = 'pending'
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($requests): ?>
        <div class="connection-requests">
            <h3>Заявки за свързване:</h3>
            <?php foreach ($requests as $req): ?>
                <?php
                $requestImage = !empty($req['profile_image']) && file_exists('../uploads/' . $req['profile_image'])
                    ? '../uploads/' . $req['profile_image']
                    : '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
                ?>
                <div class="request-card">
                    <img src="<?= htmlspecialchars($requestImage) ?>" alt="Профил" class="avatar">
                    <span><?= htmlspecialchars($req['ime'] . ' ' . $req['familiq']) ?></span>
                    <form action="approve_request.php" method="post" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                        <button name="action" value="accept">✅ Приеми</button>
                        <button name="action" value="decline">❌ Откажи</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ===== Контроли за обяви ===== -->
    <div class="job-controls">
        <div class="job-sub-buttons all">
            <button id="btn-offer">Моите проекти</button>
            <button id="btn-seek">Моят екип</button>
        </div>
        <div class="job-sub-buttons add">
            <button id="btn-add-offer">Добави проект</button>
            <button id="btn-add-seek">Добави екип</button>
        </div>
        <div class="main-buttons">
            <button id="btn-all-jobs">Всички обяви</button>
            <button id="active-projects-btn">Активни обяви</button>
            <button id="btn-add-job">Добави обява</button>
        </div>
    </div>

    <!-- форми / списъци -->
    <div id="jobFormContainer"></div>
    <div id="jobList"></div>

    <!-- ===== Активни проекти ===== -->
    <div id="active-projects-section" style="display:none;">
        <h2>Активни обяви</h2>
        <?php
        $stmt = $conn->prepare("
            SELECT ps.*, j.profession, j.description, j.user_id AS owner_id,
                   u1.ime AS user1_ime, u1.familiq AS user1_familiq, u1.profile_image AS user1_img,
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
            $other_user_id = ($project['user1_id'] == $current_user_id && $project['user2_id'] != $current_user_id)
                ? $project['user2_id']
                : (($project['user2_id'] == $current_user_id && $project['user1_id'] != $current_user_id)
                    ? $project['user1_id'] : null);
            if (is_null($other_user_id)) continue;

            $other_user_name = ($project['user1_id'] == $current_user_id)
                ? $project['user2_ime'] . ' ' . $project['user2_familiq']
                : $project['user1_ime'] . ' ' . $project['user1_familiq'];

            $you_started = ($project['user1_id'] == $current_user_id) ? $project['user1_started'] : $project['user2_started'];
            $they_started = ($project['user1_id'] == $current_user_id) ? $project['user2_started'] : $project['user1_started'];

            $you_rated = ($project['user1_id'] == $current_user_id) ? $project['user1_rated'] : $project['user2_rated'];
        ?>
            <div class="active-project-card">
                <h3><?= htmlspecialchars($project['profession']) ?></h3>
                <p><?= htmlspecialchars($project['description']) ?></p>

                <div class="timeline">
                    <div class="step <?= $you_started ? 'done' : '' ?>">Вие започнахте</div>
                    <div class="step <?= $they_started ? 'done' : '' ?>"><?= htmlspecialchars($other_user_name) ?> започна</div>
                </div>

                <?php if (!$you_started): ?>
                    <form method="POST" action="start_project.php">
                        <input type="hidden" name="job_id" value="<?= (int)$project['job_id'] ?>">
                        <button type="submit">Започни проекта</button>
                    </form>
                <?php endif; ?>

                <?php if ($you_started && $they_started && !$you_rated && $current_user_id !== (int)$project['owner_id']): ?>
                    <form method="POST" action="submit_rating.php">
                        <input type="hidden" name="to_user_id" value="<?= (int)$other_user_id ?>">
                        <input type="hidden" name="job_id" value="<?= (int)$project['job_id'] ?>">
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

    <!-- ===== История (стари проекти) ===== -->
    <section id="history-section" class="history-section">
        <!-- пълни се от fetch_history.php чрез JS -->
    </section>

</main>

<footer>
    <p>Свържи се с нас: support@fixora.bg | +359 888 123 456</p>
</footer>

<script src="../js/profil.js?v=<?php echo time(); ?>"></script>
<script src="/Fixora/js/favorites.js"></script>
</body>
</html>
