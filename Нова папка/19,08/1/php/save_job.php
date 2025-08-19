<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

/* -------------------------
   Входни данни от формата
--------------------------*/
$job_type = (isset($_POST['job_type']) && $_POST['job_type'] === 'seek') ? 'seek' : 'offer';

/* Фирма / Частно лице */
$is_company = 0;
if (isset($_POST['is_company'])) {
    $v = $_POST['is_company'];
    $is_company = ($v === '1' || $v === 1 || $v === 'on') ? 1 : 0;
}

/* Професии */
$profession = isset($_POST['profession']) ? trim((string)$_POST['profession']) : null; // „главна“ професия

// Може да дойде като масив (name="professions_json[]") или като JSON низ (name="professions_json")
$professions_raw = $_POST['professions_json'] ?? null;
$professions_arr = null;

if ($is_company) {
    if (is_array($professions_raw)) {
        // от <select multiple name="professions_json[]">
        $professions_arr = array_values(array_filter(array_map('trim', $professions_raw), fn($v) => $v !== ''));
    } elseif (is_string($professions_raw) && $professions_raw !== '') {
        // от скрито поле / друг фронтенд: JSON низ
        $decoded = json_decode($professions_raw, true);
        if (is_array($decoded)) {
            $professions_arr = array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
        }
    }

    // Ако имаме списък и не е подадена главна професия – вземаме първата
    if ($professions_arr && !$profession) {
        $profession = $professions_arr[0];
    }
}

// това е стойността за колоната `jobs.professions`
$professions_json_db = ($is_company && $professions_arr) ? json_encode($professions_arr, JSON_UNESCAPED_UNICODE) : null;

/* Останали полета */
$price_per_day     = (isset($_POST['price_per_day']) && $_POST['price_per_day'] !== '') ? $_POST['price_per_day'] : null;
$price_per_square  = (isset($_POST['price_per_square']) && $_POST['price_per_square'] !== '') ? $_POST['price_per_square'] : null;
$description       = $_POST['description'] ?? null;
$location          = $_POST['location'] ?? null; // при offer
$city              = $_POST['city'] ?? null;     // при seek

/* Екип (само при job_type = 'seek') */
$work_status  = null;
$team_size    = null;
$team_members = null;

if ($job_type === 'seek') {
    $team_size   = isset($_POST['team_size']) ? (int)$_POST['team_size'] : 1;
    $work_status = $team_size > 1 ? 'team' : 'solo';

    $team = [];
    for ($i = 1; $i <= $team_size; $i++) {
        $member = $_POST["team_member_$i"] ?? null;
        if ($member) $team[] = $member;
    }
    $team_members = json_encode($team, JSON_UNESCAPED_UNICODE);
}

/* -------------------------
   Снимки (преди INSERT)
--------------------------*/
$uploadedImages = [];

if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../uploads/jobs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['images']['name'] as $index => $name) {
        $tmp = $_FILES['images']['tmp_name'][$index] ?? null;
        if (!$tmp) continue;

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg','jpeg','png','webp','gif'])) {
            $extension = 'jpg';
        }

        $newName = uniqid('job_') . "." . $extension;
        $targetFile = $uploadDir . $newName;

        if (move_uploaded_file($tmp, $targetFile)) {
            $uploadedImages[] = 'uploads/jobs/' . $newName; // относителен път
        }
    }
}

/* Корица: премества избраната снимка най-отпред */
$cover_index = isset($_POST['cover_index']) ? (int)$_POST['cover_index'] : 0;
if (!empty($uploadedImages) && $cover_index >= 0 && $cover_index < count($uploadedImages)) {
    $cover = $uploadedImages[$cover_index];
    array_splice($uploadedImages, $cover_index, 1);
    array_unshift($uploadedImages, $cover);
}

$imageJSON = json_encode($uploadedImages, JSON_UNESCAPED_UNICODE);

/* -------------------------
   INSERT в jobs
--------------------------*/
$stmt = $conn->prepare("INSERT INTO jobs 
(user_id, job_type, profession, professions, is_company, location, city, price_per_day, price_per_square, work_status, team_size, team_members, description, images)
VALUES
(:user_id, :job_type, :profession, :professions, :is_company, :location, :city, :price_per_day, :price_per_square, :work_status, :team_size, :team_members, :description, :images)");

$stmt->execute([
    'user_id'          => $user_id,
    'job_type'         => $job_type,

    // винаги попълваме 'profession' (единичната). При фирма това е първата от множествените
    'profession'       => $profession ?: null,

    // при фирма -> JSON списък; иначе NULL
    'professions'      => $professions_json_db,
    'is_company'       => $is_company,

    'location'         => $location,
    'city'             => $city,

    'price_per_day'    => $price_per_day,
    'price_per_square' => $price_per_square,

    'work_status'      => $work_status,
    'team_size'        => $team_size,
    'team_members'     => $team_members,

    'description'      => $description,
    'images'           => $imageJSON
]);

header("Location: profil.php");
exit;
