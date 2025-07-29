<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
  require_once 'php/db.php';
$unread_count = 0;
if (isset($_SESSION['user'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user']['id']]);
    $unread_count = $stmt->fetchColumn();
}

}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <link rel="stylesheet" href="../css/navbar.css?v=<?php echo time(); ?>">
</head>

<header>
  <a href="/Fixora/index.php"><img src="/Fixora/img/Untitled-2.png" alt="Fixora Logo" class="logo-small"></a>
  <nav>
    <ul class="navbar">
      <li><a href="/Fixora/php/all_jobs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'all_jobs.php' ? 'active' : '' ?>">Обяви</a></li>
      <li><a href="/Fixora/pages/kalkulator.php" class="<?= basename($_SERVER['PHP_SELF']) == 'kalkulator.php' ? 'active' : '' ?>">Калкулатор</a></li>
      <li><a href="/Fixora/pages/za-nas.php" class="<?= basename($_SERVER['PHP_SELF']) == 'za-nas.php' ? 'active' : '' ?>">За нас</a></li>
      <li><a href="/Fixora/pages/kontakt.php" class="<?= basename($_SERVER['PHP_SELF']) == 'kontakt.php' ? 'active' : '' ?>">Запитвания</a></li>
      <li><a href="/Fixora/php/chat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>">Чат</a></li>
      <li>
        <a href="notifications.php" class="notification-link">
            🔔
            <?php if (isset($unread_count) && $unread_count > 0): ?>
                <span class="badge"><?= $unread_count ?></span>
            <?php endif; ?>

        </a>
      </li>

      <!--<li><a href="search_jobs.php">Търси обяви</a></li>-->

      <?php if (isset($_SESSION['user'])): ?>
        <li class="dropdown">
          <a href="/Fixora/php/profil.php" class="button dropdown-toggle"><?= htmlspecialchars($_SESSION['user']['username']) ?></a>
          <ul class="dropdown-menu">
            <li><a href="/Fixora/php/profil.php">Обяви</a></li>
            <li><a href="/Fixora/php/chat.php">Чат</a></li>
            <li><a href="/Fixora/php/edit_profile.php">Редактиране</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li><a href="#" onclick="openLoginModal()">Вход</a></li>
        <li><a href="#" onclick="openRegisterModal()">Регистрация</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>


<!-- Overlay -->
<div id="overlay"></div>

<!-- Login Modal -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user']) && $currentPage !== 'profil.php'):
?>
  <!-- Login Modal -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeLoginModal()">&times;</span>
      <h2>Вход</h2>
      <form action="/Fixora/php/login.php" method="post">
        <input type="text" name="username" placeholder="Потребителско име" required>
        <input type="password" name="password" placeholder="Парола" required>
        <button type="submit">Вход</button>
      </form>
    </div>
  </div>

  <!-- Register Modal -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeRegisterModal()">&times;</span>
      <h2>Регистрация</h2>
      <form action="php/register.php" method="POST" class="register-form">
          <input type="text" name="username" placeholder="Потребителско име" required>
          <input type="text" name="ime" placeholder="Име" required>
          <input type="text" name="familiq" placeholder="Фамилия" required>
          <input type="text" name="telefon" placeholder="Телефон" required>
          <input type="email" name="email" placeholder="Имейл" required>
          <input type="password" name="password" placeholder="Парола" required>
          <input type="password" name="confirm_password" placeholder="Потвърди парола" required>
          <button type="submit">Регистрация</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<script src="/Fixora/js/modal.js"></script>
</html>