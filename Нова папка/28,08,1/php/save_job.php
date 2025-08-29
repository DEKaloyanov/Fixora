<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

/* Малка помощна: има ли такава колона в таблицата */
function jobs_has_col(PDO $conn, string $col): bool {
    static $cols = null;
    if ($cols === null) {
        $cols = [];
        try {
            $stmt = $conn->query("SHOW COLUMNS FROM jobs");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $cols[strtolower($r['Field'])] = true;
            }
        } catch (Throwable $e) {}
    }
    return isset($cols[strtolower($col)]);
}

/* -------------------------
   Входни данни от формата
--------------------------*/
$job_type = (isset($_POST['job_type']) && $_POST['job_type'] === 'seek') ? 'seek' : 'offer';
$title    = trim($_POST['title'] ?? ''); // ново заглавие

/* Фирма / Частно лице */
$is_company = 0;
if (isset($_POST['is_company'])) {
    $v = $_POST['is_company'];
    $is_company = ($v === '1' || $v === 1 || $v === 'on') ? 1 : 0;
}

/* Професии */
$profession = isset($_POST['profession']) ? trim((string)$_POST['profession']) : null; // под-професия от каскадата

// Може да дойде като масив (name="professions_json[]") или като JSON низ (name="professions_json")
$professions_raw = $_POST['professions_json'] ?? null;
$professions_arr = null;

if ($is_company) {
    if (is_array($professions_raw)) {
        $professions_arr = array_values(array_filter(array_map('trim', $professions_raw), fn($v) => $v !== ''));
    } elseif (is_string($professions_raw) && $professions_raw !== '') {
        $decoded = json_decode($professions_raw, true);
        if (is_array($decoded)) {
            $professions_arr = array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
        }
    }
    if ($professions_arr && !$profession) {
        $profession = $professions_arr[0]; // първата става „главна“
    }
}
$professions_json_db = ($is_company && $professions_arr) ? json_encode($professions_arr, JSON_UNESCAPED_UNICODE) : null;

/* Локация */
$region     = trim($_POST['region'] ?? '');
$city       = trim($_POST['city'] ?? '');
$address    = trim($_POST['location'] ?? ''); // адрес (свободен текст)

/* Описание */
$description = $_POST['description'] ?? null;

/* Екип (само при job_type = 'seek') */
$work_status  = null;
$team_size    = null;
$team_members = null;

if ($job_type === 'seek') {
    $team_size   = isset($_POST['team_size']) ? (int)$_POST['team_size'] : 1;
    $work_status = $team_size > 1 ? 'team' : 'solo';

    $team = [];
    for ($i = 1; $i <= max(1,$team_size); $i++) {
        $member = $_POST["team_member_$i"] ?? null;
        if ($member) $team[] = $member;
    }
    $team_members = json_encode($team, JSON_UNESCAPED_UNICODE);
}

/* -------------------------
   Фирмени данни (JSON)
--------------------------*/
$company_json = null;
if ($is_company) {
    $c = [
        'name'      => trim($_POST['company_name'] ?? ''),
        'eik'       => trim($_POST['company_eik'] ?? ''), // непублично
        'vat'       => !empty($_POST['company_vat']),
        'contacts'  => [
            'phone'   => trim($_POST['company_phone'] ?? ''),
            'email'   => trim($_POST['company_email'] ?? ''),
            'website' => trim($_POST['company_website'] ?? ''),
        ],
        'socials'   => [
            'facebook'  => trim($_POST['company_facebook'] ?? ''),
            'instagram' => trim($_POST['company_instagram'] ?? ''),
        ],
        'verified'  => false,
        'logo'      => null
    ];

    // Лого (по желание)
    if (!empty($_FILES['company_logo']['name'])) {
        $upDir = '../uploads/logos/';
        if (!is_dir($upDir)) @mkdir($upDir, 0755, true);
        $name = $_FILES['company_logo']['name'];
        $tmp  = $_FILES['company_logo']['tmp_name'];
        if ($tmp) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';
            $new = uniqid('logo_').'.'.$ext;
            if (move_uploaded_file($tmp, $upDir.$new)) {
                $c['logo'] = 'uploads/logos/'.$new; // относителен
            }
        }
    }
    $company_json = json_encode($c, JSON_UNESCAPED_UNICODE);
}

/* -------------------------
   Начини на заплащане (разширени)
--------------------------*/
$pay_types = isset($_POST['pay_types']) ? (array)$_POST['pay_types'] : [];
$pay_types = array_unique(array_map('strval', $pay_types));

$map = [
  'day','hour','square','linear','piece','project',
  'per_point','per_fixture','per_window','per_door','per_m3','per_ton',
  'tile_m2','plaster_m2','paint_m2','insulation_m2',
  'callout_fee','min_charge'
];

$payments = [];
foreach ($map as $k) {
    $field = 'pay_'.$k;
    if (in_array($k,$pay_types,true) && isset($_POST[$field]) && $_POST[$field] !== '') {
        $payments[$k] = (float)$_POST[$field];
    }
}
$payment_methods_json = !empty($payments) ? json_encode(['types'=>$payments], JSON_UNESCAPED_UNICODE) : null;

/* За съвместимост – попълваме старите колони ако има данни */
$price_per_day    = $payments['day']    ?? (isset($_POST['price_per_day']) && $_POST['price_per_day'] !== '' ? (float)$_POST['price_per_day'] : null);
$price_per_square = $payments['square'] ?? (isset($_POST['price_per_square']) && $_POST['price_per_square'] !== '' ? (float)$_POST['price_per_square'] : null);

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
            $uploadedImages[] = 'uploads/jobs/' . $newName;
        }
    }
}

/* Корица -> най-отпред */
$cover_index = isset($_POST['cover_index']) ? (int)$_POST['cover_index'] : 0;
if (!empty($uploadedImages) && $cover_index >= 0 && $cover_index < count($uploadedImages)) {
    $cover = $uploadedImages[$cover_index];
    array_splice($uploadedImages, $cover_index, 1);
    array_unshift($uploadedImages, $cover);
}
$imageJSON = json_encode($uploadedImages, JSON_UNESCAPED_UNICODE);

/* -------------------------
   INSERT в jobs (динамично по налични колони)
--------------------------*/
$cols = [
  'user_id'         => $user_id,
  'job_type'        => $job_type,
  'profession'      => $profession ?: null,
  'professions'     => $professions_json_db,
  'is_company'      => $is_company,
  'location'        => $address,
  'city'            => $city,
  'price_per_day'   => $price_per_day,
  'price_per_square'=> $price_per_square,
  'payment_methods' => $payment_methods_json,
  'work_status'     => $work_status,
  'team_size'       => $team_size,
  'team_members'    => $team_members,
  'description'     => $description,
  'images'          => $imageJSON
];

if (jobs_has_col($conn,'title'))        $cols['title']        = ($title !== '' ? $title : null);
if (jobs_has_col($conn,'company_json')) $cols['company_json'] = $company_json;
if (jobs_has_col($conn,'region'))       $cols['region']       = ($region !== '' ? $region : null);

$names = array_keys($cols);
$ph    = array_map(fn($k)=>':'.$k, $names);

$sql = "INSERT INTO jobs (".implode(',', $names).") VALUES (".implode(',', $ph).")";
$stmt = $conn->prepare($sql);
$stmt->execute(array_combine($ph, array_values($cols)));

header("Location: profil.php");
exit;
