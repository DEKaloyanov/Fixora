<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']['id'])) { header('Location: ../index.php'); exit; }

$me   = (int)$_SESSION['user']['id'];
$with = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$job  = isset($_GET['job'])  ? (int)$_GET['job']  : 0;
if ($with<=0 || $job<=0) { header('Location: ../chat.php'); exit; }

/* Валидираме, че има такава връзка */
$ck = $conn->prepare("
  SELECT 1 FROM connections
  WHERE job_id=:job AND (
    (user1_id=:me AND user2_id=:with) OR
    (user1_id=:with AND user2_id=:me)
  ) LIMIT 1
");
$ck->execute([':job'=>$job, ':me'=>$me, ':with'=>$with]);
if (!$ck->fetchColumn()) { header('Location: ../chat.php'); exit; }

/* Другият потребител */
$u = $conn->prepare("SELECT id, username, ime, familiq, profile_image FROM users WHERE id=:id");
$u->execute([':id'=>$with]);
$other = $u->fetch(PDO::FETCH_ASSOC);
if (!$other){ header('Location: ../chat.php'); exit; }

/* Пътища (страницата е в /php) */
$DEFAULT_AVATAR = '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
$avatar = $DEFAULT_AVATAR;
if (!empty($other['profile_image'])) {
  $fs = __DIR__ . '/../uploads/' . ltrim($other['profile_image'],'/');
  if (file_exists($fs)) {
    $avatar = '../uploads/' . ltrim($other['profile_image'],'/');
  }
}

/* mute / block статус */
$is_muted = (bool)$conn->prepare("SELECT 1 FROM muted_conversations WHERE user_id=:me AND other_user_id=:with AND job_id=:job")
                      ->execute([':me'=>$me, ':with'=>$with, ':job'=>$job]) && $conn->query('SELECT 1')->fetchColumn();
$muteQ = $conn->prepare("SELECT 1 FROM muted_conversations WHERE user_id=:me AND other_user_id=:with AND job_id=:job");
$muteQ->execute([':me'=>$me, ':with'=>$with, ':job'=>$job]);
$is_muted = (bool)$muteQ->fetchColumn();

$blockQ = $conn->prepare("SELECT 1 FROM blocks WHERE blocker_id=:me AND blocked_id=:with");
$blockQ->execute([':me'=>$me, ':with'=>$with]);
$is_blocked = (bool)$blockQ->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8" />
  <title>Чат профил | Fixora</title>
  <link rel="stylesheet" href="../css/chat_profile.css?v=<?php echo time(); ?>">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="cp-wrap">
  <header class="cp-header">
    <div class="cp-user">
      <img class="cp-avatar" src="<?= htmlspecialchars($avatar) ?>" alt="Аватар"
           onerror="this.onerror=null;this.src='<?= htmlspecialchars($DEFAULT_AVATAR) ?>';">
      <div>
        <div class="cp-name"><?= htmlspecialchars(trim(($other['ime']??'').' '.($other['familiq']??''))) ?></div>
        <div class="cp-username">@<?= htmlspecialchars($other['username'] ?? ('user'.$other['id'])) ?></div>
      </div>
    </div>
    <div class="cp-actions">
      <!-- ВАЖНО: public_profile.php очаква ?id= -->
      <a class="cp-btn" href="public_profile.php?id=<?= (int)$with ?>">Към профила</a>
      <label class="cp-switch" title="Заглуши разговора">
        <input type="checkbox" id="muteToggle" <?= $is_muted?'checked':'' ?>>
        <span>Заглуши</span>
      </label>
      <button id="blockToggle" class="cp-btn <?= $is_blocked?'danger':'' ?>">
        <?= $is_blocked ? 'Деблокирай' : 'Блокирай' ?>
      </button>
      <button id="reportBtn" class="cp-btn outline">Репорт</button>
    </div>
  </header>

  <section class="cp-media">
    <h3>Споделени изображения</h3>
    <div id="mediaGrid" class="cp-grid" data-with="<?= (int)$with ?>" data-job="<?= (int)$job ?>"></div>
    <button id="loadMoreMedia" class="cp-btn ghost">Зареди още</button>
  </section>

  <dialog id="reportDialog" class="cp-dialog">
    <form id="reportForm" method="dialog">
      <h4>Подай сигнал</h4>
      <label>Причина:
        <select name="reason" required>
          <option value="">Избери…</option>
          <option value="abuse">Злоупотреба/обиди</option>
          <option value="spam">Спам/реклама</option>
          <option value="fraud">Измама</option>
          <option value="other">Друго</option>
        </select>
      </label>
      <label>Детайли:
        <textarea name="details" rows="4" placeholder="Опиши накратко…"></textarea>
      </label>
      <div class="cp-dialog-actions">
        <button type="button" id="reportCancel" class="cp-btn ghost">Откажи</button>
        <button type="submit" class="cp-btn">Изпрати</button>
      </div>
    </form>
  </dialog>
</div>

<script>
  window.CHAT_PROFILE = {
    with: <?= (int)$with ?>,
    job: <?= (int)$job ?>,
    isBlocked: <?= $is_blocked ? 'true':'false' ?>,
    isMuted: <?= $is_muted ? 'true':'false' ?>
  };
</script>
<script src="../js/chat_profile.js?v=<?php echo time(); ?>"></script>
</body>
</html>
