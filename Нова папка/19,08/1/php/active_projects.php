<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT j.*, 
           u.id AS other_user_id,
           u.ime, u.familiq, u.profile_image,
           ps.user1_started, ps.user2_started, ps.user1_rated, ps.user2_rated
    FROM project_status ps
    INNER JOIN jobs j ON j.id = ps.job_id
    INNER JOIN users u ON u.id = IF(ps.user1_id = :uid, ps.user2_id, ps.user1_id)
    WHERE ps.user1_id = :uid OR ps.user2_id = :uid
    ORDER BY ps.created_at DESC
");
$stmt->execute(['uid' => $current_user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="active-projects" class="active-section">
    <h2>Активни обяви</h2>
    <?php if (empty($projects)): ?>
        <p>Нямате активни обяви.</p>
    <?php else: ?>
        <?php foreach ($projects as $proj): ?>
            <div class="project-card">
                <div class="project-info">
                    <strong><?= htmlspecialchars($proj['profession']) ?></strong><br>
                    <span>С потребител: <?= htmlspecialchars($proj['ime'] . ' ' . $proj['familiq']) ?></span>
                </div>
                <div class="project-status">
                    <?php
                    $started_by_me = ($proj['user1_id'] == $current_user_id) ? $proj['user1_started'] : $proj['user2_started'];
                    $started_by_other = ($proj['user1_id'] == $current_user_id) ? $proj['user2_started'] : $proj['user1_started'];
                    $rated_by_me = ($proj['user1_id'] == $current_user_id) ? $proj['user1_rated'] : $proj['user2_rated'];
                    ?>
                    <?php if (!$started_by_me): ?>
                        <form method="post" action="php/start_project.php">
                            <input type="hidden" name="job_id" value="<?= $proj['id'] ?>">
                            <button class="start-btn" type="submit">🚀 Започни проекта</button>
                        </form>
                        <small>След натискане на бутона ще може да оценявате отсрещния потребител.</small>
                    <?php elseif ($started_by_me && !$started_by_other): ?>
                        <span class="waiting">Изчаква се отсрещната страна...</span>
                    <?php elseif ($started_by_me && $started_by_other && !$rated_by_me): ?>
                        <form method="post" action="php/submit_rating.php">
                            <input type="hidden" name="job_id" value="<?= $proj['id'] ?>">
                            <input type="hidden" name="to_user_id" value="<?= $proj['other_user_id'] ?>">
                            <label>Оцени този потребител (1–5):</label>
                            <input type="number" name="rating" min="1" max="5" required>
                            <textarea name="comment" placeholder="Коментар (незадължителен)"></textarea>
                            <button type="submit">Изпрати</button>
                        </form>
                    <?php else: ?>
                        <span class="completed">✅ Проектът е завършен и оценен.</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
