<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('Неоторизиран достъп');
}

$me   = (int)$_SESSION['user']['id'];
$with = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$job  = isset($_GET['job'])  ? (int)$_GET['job']  : 0;

if ($with <= 0 || $job <= 0) {
    echo '<p class="no-chat">Изберете разговор.</p>';
    exit;
}

/* ВАЖНО: този път е относителен спрямо chat.php (в корена) */
$DEFAULT_AVATAR = 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';

$sql = "
    SELECT m.id, m.sender_id, m.receiver_id, m.job_id, m.message, m.created_at,
           s.profile_image AS sender_img
    FROM messages m
    JOIN users s ON s.id = m.sender_id
    WHERE m.job_id = :job
      AND (
            (m.sender_id = :me AND m.receiver_id = :with)
         OR (m.sender_id = :with AND m.receiver_id = :me)
      )
    ORDER BY m.created_at ASC, m.id ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute([':job' => $job, ':me' => $me, ':with' => $with]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo '<div class="no-messages">Няма съобщения. Започнете разговора!</div>';
    exit;
}

/* Помощници */
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function time_label($ts){ return date('H:i', strtotime($ts)); }
function day_key($ts){ return date('Y-m-d', strtotime($ts)); }
function day_label($ts){ return date('d.m.Y', strtotime($ts)); }

/* Намира коректен URL за аватар спрямо chat.php; fallback → $DEFAULT_AVATAR */
function resolve_avatar_url(?string $sender_img, string $default): string {
    $imgRel = ltrim((string)$sender_img, '/');

    // кандидати по файлова система (get_messages.php е в /php, качванията са в /uploads)
    $candidatesFS = [
        __DIR__ . '/../uploads/' . $imgRel,  // ако в БД е само име:  my.jpg
        __DIR__ . '/../' . $imgRel,          // ако в БД има 'uploads/my.jpg'
    ];
    foreach ($candidatesFS as $fsPath) {
        if ($imgRel && file_exists($fsPath)) {
            // HTML път спрямо chat.php
            if (strpos($imgRel, 'uploads/') === 0) return $imgRel;
            return 'uploads/' . $imgRel;
        }
    }
    return $default;
}

$prevSender = null;
$prevTime   = null;
$prevDay    = null;

foreach ($rows as $m) {
    $sent    = ((int)$m['sender_id'] === $me);
    $klass   = $sent ? 'sent' : 'received';
    $created = $m['created_at'];
    $dKey    = day_key($created);

    // Разделител по дни
    if ($dKey !== $prevDay) {
        echo '<div class="day-separator">'.esc(day_label($created)).'</div>';
        $prevDay = $dKey;
        $prevSender = null;
        $prevTime   = null;
    }

    // Аватар (на подателя)
    $avatarUrl = resolve_avatar_url($m['sender_img'] ?? null, $DEFAULT_AVATAR);

    // Първо ли е в блок от същия подател (≤ 5 мин)?
    $isFirstInBlock = true;
    if ($prevSender !== null && $prevSender == $m['sender_id'] && $prevTime) {
        $delta = abs(strtotime($created) - strtotime($prevTime));
        if ($delta <= 300) $isFirstInBlock = false;
    }

    echo '<div class="msg-row '.esc($klass).'">';

    // Аватар: показваме само за първото в блок; иначе „скрит“ за да пазим подравняване
    if ($isFirstInBlock) {
        echo '<img class="avatar" src="'.esc($avatarUrl).'" alt="avatar" onerror="this.onerror=null;this.src=\''.esc($DEFAULT_AVATAR).'\';">';
    } else {
        echo '<img class="avatar" src="'.esc($avatarUrl).'" alt="" style="visibility:hidden;" onerror="this.onerror=null;this.src=\''.esc($DEFAULT_AVATAR).'\';">';
    }

    echo '  <div class="msg-col">';
    echo '      <div class="msg-time">'.esc(time_label($created)).'</div>';
    echo '      <div class="message '.esc($klass).'">';
    echo '          <div class="msg-text">'.nl2br(esc($m['message'])).'</div>';
    echo '      </div>';
    echo '  </div>'; // .msg-col

    echo '</div>'; // .msg-row

    $prevSender = $m['sender_id'];
    $prevTime   = $created;
}
