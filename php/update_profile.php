<?php
session_start();
require 'db.php';

// Проверка дали потребителят е влязъл
if (!isset($_SESSION['user']['id'])) {
    header("Location: ../index.php");
    exit();
}

// В началото на файла, след проверката за вход
$user_id = $_SESSION['user']['id'];

// Проверка за съществуване на полетата
$username = $_POST['username'] ?? '';
$ime = $_POST['ime'] ?? '';
$familiq = $_POST['familiq'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$email = $_POST['email'] ?? '';
$city = $_POST['city'] ?? '';
$age = $_POST['age'] ?? '';

$show_phone = isset($_POST['show_phone']) ? 1 : 0;
$show_email = isset($_POST['show_email']) ? 1 : 0;
$show_city = isset($_POST['show_city']) ? 1 : 0;
$show_age = isset($_POST['show_age']) ? 1 : 0;

// Промяна на паролата (ако е попълнена)
$change_password = false;
if (!empty($_POST['old_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        // Вземи текущата парола от базата
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($_POST['old_password'], $user['password'])) {
            $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $change_password = true;
        } else {
            $_SESSION['error'] = "Невалидна стара парола!";
            header("Location: ../php/profil.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Новите пароли не съвпадат!";
        header("Location: ../php/profil.php");
        exit();
    }
}

// Промяна на снимка (ако е качена)
if (!empty($_FILES['profile_image']['name'])) {
    $upload_dir = '../uploads/';
    $image_name = basename($_FILES['profile_image']['name']);
    $target_path = $upload_dir . $image_name;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
        $image_field = ", profile_image = ?";
    } else {
        $_SESSION['error'] = "Неуспешно качване на снимка!";
        header("Location: ../php/profil.php");
        exit();
    }
} else {
    $image_field = "";
}

// Съставяне на заявката
$query = "UPDATE users SET 
            username = ?, ime = ?, familiq = ?, telefon = ?, email = ?, 
            city = ?, age = ?, 
            show_phone = ?, show_email = ?, show_city = ?, show_age = ? 
            $image_field";

if ($change_password) {
    $query .= ", password = ?";
}

$query .= " WHERE id = ?";

// Подготовка на параметрите
$params = [$username, $ime, $familiq, $telefon, $email, $city, $age, $show_phone, $show_email, $show_city, $show_age];

if (!empty($image_field)) {
    $params[] = $image_name;
}

if ($change_password) {
    $params[] = $password;
}

$params[] = $user_id;

// Изпълнение
$stmt = $conn->prepare($query);
$stmt->execute($params);

// Обнови сесията
$_SESSION['user']['username'] = $username;
$_SESSION['user']['ime'] = $ime;
$_SESSION['user']['familiq'] = $familiq;
$_SESSION['user']['telefon'] = $telefon;
$_SESSION['user']['email'] = $email;
$_SESSION['user']['city'] = $city;
$_SESSION['user']['age'] = $age;
$_SESSION['user']['profile_image'] = !empty($image_name) ? $image_name : $_SESSION['user']['profile_image'];

$_SESSION['success_message'] = "Профилът е успешно обновен!";
header("Location: profil.php");
exit();
?>
