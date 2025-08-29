<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']['id'])) {
    exit('Грешка: не сте влезли в системата.');
}

$current_user_id = (int)$_SESSION['user']['id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($receiver_id === 0 || $job_id === 0) {
    exit('Невалидни данни.');
}

/* Блокиране – ако някой е блокирал другия, не позволяваме изпращане */
$blk = $conn->prepare("SELECT 1 FROM blocks WHERE (blocker_id=:me AND blocked_id=:rid) OR (blocker_id=:rid AND blocked_id=:me) LIMIT 1");
try { $blk->execute([':me'=>$current_user_id, ':rid'=>$receiver_id]); if ($blk->fetchColumn()) exit('Разговорът е блокиран.'); } catch(Throwable $e){ /* ако таблицата още я няма */ }

/* Проверка за валидна връзка */
$stmt = $conn->prepare("
    SELECT id FROM connections 
    WHERE job_id = :job_id AND (
        (user1_id = :uid AND user2_id = :rid) OR 
        (user1_id = :rid AND user2_id = :uid)
    ) LIMIT 1
");
$stmt->execute([
    'job_id' => $job_id,
    'uid' => $current_user_id,
    'rid' => $receiver_id
]);
if (!$stmt->fetchColumn()) {
    exit('Нямате право да изпратите съобщение.');
}

/* Прикачено изображение? */
$hasImage = isset($_FILES['image']) && is_array($_FILES['image']) && ($_FILES['image']['error'] === UPLOAD_ERR_OK);

$image_path = $thumb_path = $mime = null;
$iw = $ih = $isz = null;

if ($hasImage) {
    $f = $_FILES['image'];
    $tmp = $f['tmp_name'];
    $isz = (int)$f['size'];

    if ($isz > 10*1024*1024) exit('Файлът е твърде голям (макс 10MB).');

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($mime, $allowed, true)) exit('Невалиден тип файл.');

    $ext = match($mime){
        'image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp', default=>'bin'
    };

    $dir = __DIR__ . '/../uploads/chat/' . date('Y') . '/' . date('m');
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $nameBase = 'msg_' . $current_user_id . '_' . time() . '_' . bin2hex(random_bytes(4));
    $dest = $dir . '/' . $nameBase . '.' . $ext;
    if (!move_uploaded_file($tmp, $dest)) exit('Неуспешно качване.');

    $rel = 'uploads/chat/' . date('Y') . '/' . date('m') . '/' . basename($dest);
    $image_path = $rel;

    /* Генерираме thumbnail (max 800px) + взимаме размери */
    [$iw, $ih] = @getimagesize($dest) ?: [null, null];

    $thumb_dir = dirname($dest);
    $thumb_dest = $thumb_dir . '/' . $nameBase . '_thumb.' . $ext;
    if (create_thumb($dest, $thumb_dest, 800)) {
        $thumb_path = 'uploads/chat/' . date('Y') . '/' . date('m') . '/' . basename($thumb_dest);
    } else {
        $thumb_path = $image_path;
    }
}

/* Ако няма нито текст, нито снимка -> стоп (UI не би изпратил) */
if (!$hasImage && $message === '') exit('ok');

/* Запис на съобщението */
if ($hasImage) {
    $st = $conn->prepare("
      INSERT INTO messages (sender_id, receiver_id, job_id, message, message_type, image_path, thumb_path, mime_type, image_w, image_h, image_size, created_at, is_read)
      VALUES (:sid,:rid,:job,:msg,1,:ip,:tp,:mime,:iw,:ih,:isz,NOW(),0)
    ");
    $st->execute([
        ':sid'=>$current_user_id, ':rid'=>$receiver_id, ':job'=>$job_id,
        ':msg'=>$message, ':ip'=>$image_path, ':tp'=>$thumb_path, ':mime'=>$mime,
        ':iw'=>$iw, ':ih'=>$ih, ':isz'=>$isz
    ]);
} else {
    $st = $conn->prepare("
      INSERT INTO messages (sender_id, receiver_id, job_id, message, message_type, created_at, is_read)
      VALUES (:sid,:rid,:job,:msg,0,NOW(),0)
    ");
    $st->execute([
        ':sid'=>$current_user_id, ':rid'=>$receiver_id, ':job'=>$job_id, ':msg'=>$message
    ]);
}

echo 'ok';

/* ---------- Helpers ---------- */
function create_thumb(string $src, string $dst, int $maxW): bool {
    try{
        $info = getimagesize($src);
        if (!$info) return false;
        [$w,$h] = $info;
        if ($w <= $maxW) { return copy($src, $dst); }
        $ratio = $h/$w;
        $nw = $maxW; $nh = (int)round($maxW*$ratio);

        $from = null;
        switch($info['mime']){
            case 'image/jpeg': $from = imagecreatefromjpeg($src); break;
            case 'image/png':  $from = imagecreatefrompng($src); break;
            case 'image/gif':  $from = imagecreatefromgif($src); break;
            case 'image/webp': $from = imagecreatefromwebp($src); break;
            default: return false;
        }
        if (!$from) return false;
        $to = imagecreatetruecolor($nw,$nh);
        imagealphablending($to, false); imagesavealpha($to, true);
        imagecopyresampled($to,$from,0,0,0,0,$nw,$nh,$w,$h);

        $ok = false;
        switch($info['mime']){
            case 'image/jpeg': $ok = imagejpeg($to, $dst, 82); break;
            case 'image/png':  $ok = imagepng($to, $dst, 6); break;
            case 'image/gif':  $ok = imagegif($to, $dst); break;
            case 'image/webp': $ok = imagewebp($to, $dst, 80); break;
        }
        imagedestroy($from); imagedestroy($to);
        return (bool)$ok;
    }catch(Throwable $e){ return false; }
}
