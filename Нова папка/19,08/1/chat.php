<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}

$current_user_id = (int)$_SESSION['user']['id'];

// Избран чат (по URL)
$selected_user_id = isset($_GET['with']) ? (int)$_GET['with'] : null;
$selected_job_id  = isset($_GET['job'])  ? (int)$_GET['job']  : null;

// Връзки (одобрени заявки) + насрещен потребител + професия/обява
$stmt = $conn->prepare("
    SELECT c.*,
           u.id AS other_user_id,
           u.ime,
           u.familiq,
           u.profile_image,
           j.profession,
           j.id AS job_id
    FROM connections c
    INNER JOIN users u ON (u.id = CASE WHEN c.user1_id = :uid THEN c.user2_id ELSE c.user1_id END)
    INNER JOIN jobs j   ON j.id = c.job_id
    WHERE c.user1_id = :uid OR c.user2_id = :uid
    ORDER BY c.id DESC
");
$stmt->execute(['uid' => $current_user_id]);
$connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ако няма избран чат – насочи към първия
if ((!$selected_user_id || !$selected_job_id) && !empty($connections)) {
    $selected_user_id = (int)$connections[0]['other_user_id'];
    $selected_job_id  = (int)$connections[0]['job_id'];
    header("Location: chat.php?with={$selected_user_id}&job={$selected_job_id}");
    exit;
}

// Път до дефолтния аватар (същия като на другите страници)
$DEFAULT_AVATAR = 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';

// Данни за активния контакт (за header-а)
$active = null;
$activeAvatar = $DEFAULT_AVATAR;
$activeName = '';
$activeProfession = '';
if ($selected_user_id && $selected_job_id) {
    foreach ($connections as $c) {
        if ((int)$c['other_user_id'] === $selected_user_id && (int)$c['job_id'] === $selected_job_id) {
            $active = $c;
            break;
        }
    }
    if ($active) {
        if (!empty($active['profile_image'])) {
            $fs = __DIR__ . '/uploads/' . $active['profile_image'];
            if (file_exists($fs)) {
                $activeAvatar = 'uploads/' . $active['profile_image'];
            }
        }
        $activeName = trim(($active['ime'] ?? '') . ' ' . ($active['familiq'] ?? ''));
        $activeProfession = $active['profession'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8" />
    <title>Чат | Fixora</title>
    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
<?php include 'php/navbar.php'; ?>

<div class="chat-container">
    <aside class="chat-sidebar">
        <h3>Съобщения</h3>
        <ul class="chat-contact-list">
            <?php if (!empty($connections)): ?>
                <?php foreach ($connections as $conn): ?>
                    <?php
                        // Сървърна проверка за аватар
                        $avatar_path = $DEFAULT_AVATAR;
                        if (!empty($conn['profile_image'])) {
                            $candidateFS = __DIR__ . '/uploads/' . $conn['profile_image'];
                            if (file_exists($candidateFS)) {
                                $avatar_path = 'uploads/' . $conn['profile_image'];
                            }
                        }
                        $isActive = ($conn['other_user_id'] == $selected_user_id && $conn['job_id'] == $selected_job_id);
                    ?>
                    <li class="chat-contact <?= $isActive ? 'active' : '' ?>">
                        <a href="chat.php?with=<?= (int)$conn['other_user_id'] ?>&job=<?= (int)$conn['job_id'] ?>">
                            <img
                                src="<?= htmlspecialchars($avatar_path) ?>"
                                alt="Профил"
                                class="contact-avatar"
                                onerror="this.onerror=null;this.src='<?= htmlspecialchars($DEFAULT_AVATAR) ?>';"
                            >
                            <div class="contact-info">
                                <strong><?= htmlspecialchars($conn['profession'] ?? '') ?></strong><br>
                                <small><?= htmlspecialchars(($conn['ime'] ?? '') . ' ' . ($conn['familiq'] ?? '')) ?></small>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="chat-contact"><div class="contact-info"><small>Нямате активни разговори.</small></div></li>
            <?php endif; ?>
        </ul>
    </aside>

    <main class="chat-main">
        <?php if ($selected_user_id && $selected_job_id): ?>
            <header class="chat-header">
                <div class="chat-peer">
                    <img
                        class="peer-avatar"
                        src="<?= htmlspecialchars($activeAvatar) ?>"
                        alt="Профил"
                        onerror="this.onerror=null;this.src='<?= htmlspecialchars($DEFAULT_AVATAR) ?>';"
                    >
                    <div class="peer-meta">
                        <div class="peer-name"><?= htmlspecialchars($activeName) ?></div>
                        <div class="peer-sub"><?= htmlspecialchars($activeProfession) ?></div>
                    </div>
                </div>
            </header>

            <section class="chat-messages" id="chat-messages"></section>

            <form id="message-form" class="chat-form">
                <input type="hidden" name="receiver_id" value="<?= (int)$selected_user_id ?>">
                <input type="hidden" name="job_id" value="<?= (int)$selected_job_id ?>">
                <input type="text" name="message" placeholder="Напишете съобщение…" required>
                <button type="submit" class="send-btn">Изпрати</button>
            </form>
        <?php else: ?>
            <p class="no-chat">Няма налични чатове.</p>
        <?php endif; ?>
    </main>
</div>

<script src="js/chat.js?v=<?php echo time(); ?>"></script>
</body>
</html>
