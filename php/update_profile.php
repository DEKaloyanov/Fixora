<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = (int)$_SESSION['user']['id'];

$username = $_POST['username'] ?? '';
$ime = $_POST['ime'] ?? '';
$familiq = $_POST['familiq'] ?? '';
$email = $_POST['email'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$city = $_POST['city'] ?? '';
$age = $_POST['age'] ?? '';

$show_email = isset($_POST['show_email']) ? 1 : 0;
$show_phone = isset($_POST['show_phone']) ? 1 : 0;
$show_city  = isset($_POST['show_city'])  ? 1 : 0;
$show_age   = isset($_POST['show_age'])   ? 1 : 0;

// директории
$uploadsDir   = "../uploads/";
$originalsDir = $uploadsDir . "originals/";
$cropsDir     = $uploadsDir . "crops/";
if (!is_dir($uploadsDir))   mkdir($uploadsDir, 0755, true);
if (!is_dir($originalsDir)) mkdir($originalsDir, 0755, true);
if (!is_dir($cropsDir))     mkdir($cropsDir, 0755, true);

$profile_image = $_SESSION['user']['profile_image'] ?? '';

// 1) Ако имаме изрязано изображение (base64 от canvas) – това е визията за показване
if (!empty($_POST['cropped_image']) && strpos($_POST['cropped_image'], 'data:image/') === 0) {
    // Запазваме PNG/JPG/WebP
    $data = $_POST['cropped_image'];
    [$meta, $content] = explode(',', $data, 2);
    preg_match('/data:image\/(png|jpeg|jpg|webp)/i', $meta, $m);
    $ext = isset($m[1]) ? strtolower($m[1]) : 'png';
    if ($ext === 'jpeg') $ext = 'jpg';

    $binary = base64_decode($content);
    if ($binary !== false) {
        $new_profile_image_name = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $target_file = $uploadsDir . $new_profile_image_name;
        if (file_put_contents($target_file, $binary) !== false) {
            $profile_image = $new_profile_image_name;
        }
    }
}

// 2) Ако има качен ОРИГИНАЛЕН файл – пазим копие като original (за бъдещо „ънзуумване“)
if (!empty($_FILES['profile_image']['name'])) {
    $tmp  = $_FILES["profile_image"]["tmp_name"];
    $name = $_FILES["profile_image"]["name"];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $ext = 'png';
    }
    if (@getimagesize($tmp)) {
        $original_name = 'profile_' . $userId . '.' . $ext; // фиксирано име за да е лесно за намиране
        $original_path = $originalsDir . $original_name;
        // презаписваме оригинала – винаги държим последния
        @move_uploaded_file($tmp, $original_path);
        // ако move_uploaded_file не сработи (напр. при някои хостинги), опитай copy:
        if (!is_file($original_path)) {
            @copy($tmp, $original_path);
        }
    }
}

// 3) Запазваме СЪСТОЯНИЕТО на кропа (за да го възстановим следващия път)
$crop_scale = isset($_POST['crop_scale']) ? floatval($_POST['crop_scale']) : null;
$crop_pos_x = isset($_POST['crop_pos_x']) ? floatval($_POST['crop_pos_x']) : null;
$crop_pos_y = isset($_POST['crop_pos_y']) ? floatval($_POST['crop_pos_y']) : null;

$cropState = null;
if ($crop_scale !== null && $crop_pos_x !== null && $crop_pos_y !== null) {
    $cropState = [
        'scale' => $crop_scale,
        'posX'  => $crop_pos_x,
        'posY'  => $crop_pos_y,
        'updatedAt' => time()
    ];
    @file_put_contents($cropsDir . "profile_{$userId}.json", json_encode($cropState, JSON_UNESCAPED_UNICODE));
}

$sql = "UPDATE users SET
    username = :username,
    ime = :ime,
    familiq = :familiq,
    email = :email,
    telefon = :telefon,
    city = :city,
    age = :age,
    profile_image = :profile_image,
    show_email = :show_email,
    show_phone = :show_phone,
    show_city = :show_city,
    show_age = :show_age
WHERE id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'username' => $username,
    'ime' => $ime,
    'familiq' => $familiq,
    'email' => $email,
    'telefon' => $telefon,
    'city' => $city,
    'age' => $age,
    'profile_image' => $profile_image,
    'show_email' => $show_email,
    'show_phone' => $show_phone,
    'show_city' => $show_city,
    'show_age' => $show_age,
    'id' => $userId
]);

// Рефрешваме сесията
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

header("Location: profil.php");
exit();
