<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Редакция на профил - Fixora</title>
    <link rel="stylesheet" href="../css/edit-profile.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f8f8f8;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
        }
        form input, form select {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        form label {
            margin-top: 15px;
            display: block;
        }
        .submit-btn {
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
    <a href="profil.php" style="
        display: inline-block;
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease;">
        ⬅️ Назад към профила
    </a>
</div>
<div class="form-container">
    <h2>Редакция на профил</h2>
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Потребителско име</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Име</label>
        <input type="text" name="ime" value="<?= htmlspecialchars($user['ime']) ?>" required>

        <label>Фамилия</label>
        <input type="text" name="familiq" value="<?= htmlspecialchars($user['familiq']) ?>" required>

        <label>Имейл</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        <label><input type="checkbox" name="show_email" <?= $user['show_email'] ? 'checked' : '' ?>> Показвай имейла</label>

        <label>Телефон</label>
        <input type="text" name="telefon" value="<?= htmlspecialchars($user['telefon']) ?>">
        <label><input type="checkbox" name="show_phone" <?= $user['show_phone'] ? 'checked' : '' ?>> Показвай телефона</label>

        <label>Град</label>
        <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>">
        <label><input type="checkbox" name="show_city" <?= $user['show_city'] ? 'checked' : '' ?>> Показвай града</label>

        <label>Години</label>
        <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>">
        <label><input type="checkbox" name="show_age" <?= $user['show_age'] ? 'checked' : '' ?>> Показвай възрастта</label>

        <label>Снимка</label>
        <input type="file" name="profile_image" accept="image/*">

        <label>Стара парола</label>
        <div style="position: relative;">
        <input type="password" name="old_password" id="old_password" autocomplete="new-password">
        <span class="toggle-password" onclick="togglePassword('old_password')">👁️</span>
        </div>

        <label>Нова парола</label>
        <div style="position: relative;">
        <input type="password" name="new_password" id="new_password" autocomplete="new-password">
        <span class="toggle-password" onclick="togglePassword('new_password')">👁️</span>
        </div>

        <label>Потвърди нова парола</label>
        <div style="position: relative;">
        <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password">
        <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
        </div>

        <button class="submit-btn" type="submit">Запази промените</button>
    </form>
</div>
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === "password" ? "text" : "password";
}
</script>

<style>
.toggle-password {
    position: absolute;
    right: 10px;
    top: 10px;
    cursor: pointer;
    user-select: none;
}
</style>

</body>
</html>
