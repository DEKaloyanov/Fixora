
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
<div class="form-container">
    <h2>Редакция на профил</h2>
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Потребителско име</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Име</label>
        <input type="text" name="ime" value="<?php echo htmlspecialchars($user['ime']); ?>" required>

        <label>Фамилия</label>
        <input type="text" name="familiq" value="<?php echo htmlspecialchars($user['familiq']); ?>" required>

        <label>Имейл:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        <!--<label><input type="checkbox" name="show_email" <?php if ($user['show_email']) echo 'checked'; ?>> Показвай имейла</label>-->

        <label>Телефон:</label>
        <input type="text" name="telefon" value="<?php echo htmlspecialchars($user['telefon']); ?>">
        <!--<label><input type="checkbox" name="show_phone" <?php if ($user['show_phone']) echo 'checked'; ?>> Показвай телефона</label>-->
        
        <label>Град:</label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
        <!--<label><input type="checkbox" name="show_city" <?php if ($user['show_city']) echo 'checked'; ?>> Показвай града</label>-->

        <label>Години:</label>
        <input type="number" name="godini" value="<?php echo htmlspecialchars($user['godini']); ?>">
        <!--<label><input type="checkbox" name="show_age" <?php if ($user['show_age']) echo 'checked'; ?>> Показвай възрастта</label>-->

        <label>Снимка</label>
        <input type="file" name="profile_image" accept="image/*">

        <label>Стара парола</label>
        <input type="password" name="old_password">

        <label>Нова парола</label>
        <input type="password" name="new_password">

        <label>Потвърди нова парола</label>
        <input type="password" name="confirm_password">

        

        <button class="submit-btn" type="submit">Запази промените</button>
    </form>
</div>
</body>
</html>
