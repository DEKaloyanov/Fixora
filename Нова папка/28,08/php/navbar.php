<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$unread_count = 0;
try {
  // По-надежден include път независимо откъде се include-ва navbar.php
  require_once __DIR__ . '/db.php';
  if (isset($_SESSION['user'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([ (int)$_SESSION['user']['id'] ]);
    $unread_count = (int)$stmt->fetchColumn();
  }
} catch (Throwable $e) {
  // Мълчаливо продължаваме – навбарът трябва да се покаже дори без БД
}
?>
<link rel="stylesheet" href="/Fixora/css/navbar.css?v=<?php echo time(); ?>">

<header id="site-navbar" class="site-navbar">
  <div class="nav-inner">
    <a href="/Fixora/index.php" class="nav-logo">
      <img src="/Fixora/img/Untitled-2.png" alt="Fixora Logo" class="logo-small">
    </a>

    <nav class="nav-main">
      <ul class="navbar">
        <li><a href="/Fixora/php/all_jobs.php"
               class="<?= basename($_SERVER['PHP_SELF']) === 'all_jobs.php' ? 'active' : '' ?>">Обяви</a></li>

        <li><a href="/Fixora/pages/kalkulator.php"
               class="<?= basename($_SERVER['PHP_SELF']) === 'kalkulator.php' ? 'active' : '' ?>">Калкулатор</a></li>

        <li><a href="/Fixora/pages/za-nas.php"
               class="<?= basename($_SERVER['PHP_SELF']) === 'za-nas.php' ? 'active' : '' ?>">За нас</a></li>

        <li><a href="/Fixora/pages/kontakt.php"
               class="<?= basename($_SERVER['PHP_SELF']) === 'kontakt.php' ? 'active' : '' ?>">Запитвания</a></li>

        <li><a href="/Fixora/chat.php"
               class="<?= basename($_SERVER['PHP_SELF']) === 'chat.php' ? 'active' : '' ?>">Чат</a></li>

        <li>
          <a href="/Fixora/php/notifications.php" class="notification-link <?= basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : '' ?>">
            Известия
            <?php if ($unread_count > 0): ?>
              <span class="badge" aria-label="Непрочетени известия"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li>
          <a href="/Fixora/php/favorites.php"
             id="favorites-link"
             class="<?= basename($_SERVER['PHP_SELF']) === 'favorites.php' ? 'active' : '' ?>">
            Любими
          </a>
        </li>

        <?php if (isset($_SESSION['user'])): ?>
          <li class="dropdown">
            <a href="/Fixora/php/profil.php" class="button dropdown-toggle">
              <?= htmlspecialchars($_SESSION['user']['username']) ?>
            </a>
            <ul class="dropdown-menu">
              <li><a href="/Fixora/php/profil.php">Обяви</a></li>
              <li><a href="/Fixora/php/chat.php">Чат</a></li>
              <li><a href="/Fixora/php/edit_profile.php">Редактиране</a></li>
              <li><a href="/Fixora/php/logout.php">Изход</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li><a href="#" onclick="openLoginModal()">Вход</a></li>
          <li><a href="#" onclick="openRegisterModal()">Регистрация</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<!-- Overlay (за модалите) -->
<div id="overlay"></div>

<?php
// Показваме модалите, само ако не сме логнати и не сме на profil.php
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user']) && $currentPage !== 'profil.php'):
?>
  <div id="loginModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <span class="close" onclick="closeLoginModal()" aria-label="Затвори">&times;</span>
      <h2>Вход</h2>
      <form action="/Fixora/php/login.php" method="post">
        <input type="text" name="username" placeholder="Потребителско име" required>
        <input type="password" name="password" placeholder="Парола" required>
        <button type="submit">Вход</button>
      </form>
    </div>
  </div>

  <div id="registerModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <span class="close" onclick="closeRegisterModal()" aria-label="Затвори">&times;</span>
      <h2>Регистрация</h2>
      <form action="/Fixora/php/register.php" method="POST" class="register-form">
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
<script src="/Fixora/js/favorites.js"></script>

<!-- Автоматично отстояние под fixed нав бара -->
<style>
  :root { --nav-height: 64px; }
  body.has-fixed-nav { padding-top: var(--nav-height); }
</style>
<script>
(function () {
  if (window.__navSpacingApplied) return;
  window.__navSpacingApplied = true;

  var nav = document.getElementById('site-navbar');
  if (!nav) return;

  document.body.classList.add('has-fixed-nav');

  function apply() {
    var h = nav.offsetHeight || 0;
    if (h < 40) h = 40; // безопасен минимум
    document.documentElement.style.setProperty('--nav-height', h + 'px');
  }

  // следи динамично височината (responsive, двуредови менюта и т.н.)
  try {
    var ro = new ResizeObserver(apply);
    ro.observe(nav);
  } catch (e) {
    // fallback: поне на load/resize
    window.addEventListener('resize', apply, { passive: true });
  }

  window.addEventListener('load', apply, { passive: true });
  apply();
})();
</script>
