<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Fixora Навигация</title>
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    
    <nav>
      <ul class="navbar">
        <a href="index.php"><img src="../img/logo.png" alt="Fixora Logo" class="logo-small"></a>
        <li><a href="all_jobs.php">Обяви</a></li>
        <li><a href="../pages/obqvi.html">Работодатели</a></li>
        <li><a href="../pages/chat.html">Чат</a></li>
        <li><a href="../pages/kalkulator.html">Калкулатор</a></li>
        <li><a href="../pages/za-nas.html">За нас</a></li>
        <li><a href="../pages/kontakt.html">Контакти</a></li>
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="profil.php" class="button">Профил</a></li>
          <li><a href="logout.php" class="button">Изход</a></li>
        <?php else: ?>
          <li><a href="#" id="loginBtn" class="button">Вход</a></li>
        <?php endif; ?>
      </ul>
    </nav>
</body>
</html>
