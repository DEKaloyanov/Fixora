<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if (!isset($_GET['id'])) {
    echo "Грешка: липсва ID на обявата.";
    exit;
}

$job_id = $_GET['id'];

// Провери дали обявата съществува и принадлежи на потребителя
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $job_id, 'user_id' => $user_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Обявата не е намерена или нямате права за редакция.";
    exit;
}

// Ако формата е изпратена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profession = $_POST['profession'] ?? '';
    $location = $_POST['location'] ?? '';
    $city = $_POST['city'] ?? '';
    $price_per_day = $_POST['price_per_day'] ?? null;
    $price_per_square = $_POST['price_per_square'] ?? null;
    $description = $_POST['description'] ?? null;

    $stmt = $conn->prepare("UPDATE jobs SET profession = :profession, location = :location, city = :city,
        price_per_day = :price_per_day, price_per_square = :price_per_square, description = :description
        WHERE id = :id AND user_id = :user_id");

    $stmt->execute([
        'profession' => $profession,
        'location' => $location,
        'city' => $city,
        'price_per_day' => $price_per_day,
        'price_per_square' => $price_per_square,
        'description' => $description,
        'id' => $job_id,
        'user_id' => $user_id
    ]);

    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Редакция на обява</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="job-form">
        <h2>Редактирай обявата</h2>
        <form method="POST">
            <label>Тип работа:</label>
            <select name="profession" required>
                <option value="">Избери</option>
                <option value="boqjdiq" <?= $job['profession'] == 'boqjdiq' ? 'selected' : '' ?>>Бояджия</option>
                <option value="zidar" <?= $job['profession'] == 'zidar' ? 'selected' : '' ?>>Зидар</option>
                <option value="kofraj" <?= $job['profession'] == 'kofraj' ? 'selected' : '' ?>>Кофражист</option>
                <option value="elektrikar" <?= $job['profession'] == 'elektrikar' ? 'selected' : '' ?>>Електротехник</option>
            </select>

            <label>Локация:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>">

            <label>Град:</label>
            <input type="text" name="city" value="<?= htmlspecialchars($job['city']) ?>">

            <label>Цена на ден:</label>
            <input type="number" name="price_per_day" value="<?= htmlspecialchars($job['price_per_day']) ?>">

            <label>Цена за квадрат:</label>
            <input type="number" name="price_per_square" value="<?= htmlspecialchars($job['price_per_square']) ?>">

            <label>Описание:</label>
            <textarea name="description"><?= htmlspecialchars($job['description']) ?></textarea>

            <button type="submit" class="button">Запази промените</button>
        </form>
    </main>
</body>
</html>
