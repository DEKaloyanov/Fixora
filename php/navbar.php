<header>
  <link rel="stylesheet" href="../css/navbar.css?v=<?php echo time(); ?>">
    <a href="index.php"><img src="img/Untitled-2.png" alt="Fixora Logo" class="logo-small"></a>
    <nav>
      <ul class="navbar">
        <li><a href="php/all_jobs.php">Обяви</a></li>
        <li><a href="pages/chat.html">Чат</a></li>
        <li><a href="pages/za-nas.html">За нас</a></li>
        <li><a href="pages/kontakt.html">Контакти</a></li>
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="php/profil.php" class="button">Профил</a></li>
          <li><a href="php/logout.php" class="button">Изход</a></li>
        <?php else: ?>
          <li><a href="#" id="loginBtn" class="button">Вход</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
    <div class="login-toast">✅ Успешен вход!</div>
    <script>
      setTimeout(() => {
        document.querySelector('.login-toast')?.remove();
      }, 5000);
    </script>
  <?php endif; ?>
  <!-- Замъгляване -->
  <div id="overlay"></div>

  <!-- Модален Вход -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('loginModal')">&times;</span>
      <h2>Вход</h2>
      <form method="POST" action="php/login.php">
        <input type="text" name="login" placeholder="Имейл / потребителско име / телефон" required />
        <input type="password" name="password" placeholder="Парола" required />
        <button type="submit">Вход</button>
      </form>
      <p>Нямаш акаунт? <a href="#" onclick="switchModal('loginModal', 'registerModal')">Регистрирай се</a></p>
    </div>
  </div>

  <!-- Модална Регистрация -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('registerModal')">&times;</span>
      <h2>Регистрация</h2>
      <form method="POST" action="php/register.php">
        <input type="text" name="username" placeholder="Потребителско име" required />
        <input type="text" name="ime" placeholder="Име" required />
        <input type="text" name="familiq" placeholder="Фамилия" required />
        <input type="text" name="telefon" placeholder="Телефон" required />
        <input type="email" name="email" placeholder="Имейл" required />
        <input type="password" name="password" placeholder="Парола" required />
        <input type="password" name="confirmPassword" placeholder="Потвърди паролата" required />
        <button type="submit">Регистрация</button>
      </form>
      <p>Вече имаш акаунт? <a href="#" onclick="switchModal('registerModal', 'loginModal')">Вход</a></p>
    </div>
  </div>

  <!-- Скриптове -->
  <script>
    const loginBtn = document.getElementById("loginBtn");
    const overlay = document.getElementById("overlay");

    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
      overlay.style.display = 'block';
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
      overlay.style.display = 'none';
    }

    function switchModal(from, to) {
      closeModal(from);
      openModal(to);
    }

    if (loginBtn) {
      loginBtn.addEventListener("click", () => {
        openModal("loginModal");
      });
    }

    window.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeModal("loginModal");
        closeModal("registerModal");
      }
    });
  </script>
</body>
</html>