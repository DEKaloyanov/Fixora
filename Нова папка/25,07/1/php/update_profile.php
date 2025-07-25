<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user']['id'];

$username = $_POST['username'] ?? '';
$ime = $_POST['ime'] ?? '';
$familiq = $_POST['familiq'] ?? '';
$email = $_POST['email'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$city = $_POST['city'] ?? '';
$age = $_POST['age'] ?? '';

$show_email = isset($_POST['show_email']) ? 1 : 0;
$show_phone = isset($_POST['show_phone']) ? 1 : 0;
$show_city = isset($_POST['show_city']) ? 1 : 0;
$show_age = isset($_POST['show_age']) ? 1 : 0;

$profile_image = $_SESSION['user']['profile_image'] ?? '';
if (!empty($_FILES['profile_image']['name'])) {
    $target_dir = "../uploads/";
    $profile_image = basename($_FILES["profile_image"]["name"]);
    $target_file = $target_dir . $profile_image;
    move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
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

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

header("Location: profil.php");
exit();
?>