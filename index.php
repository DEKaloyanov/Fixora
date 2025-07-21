<?php session_start(); ?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8" />
  <title>Fixora – Начало</title>
  <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>">
  <style>
    .login-toast {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #d4edda;
      color: #155724;
      padding: 12px 24px;
      border: 1px solid #c3e6cb;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      font-weight: bold;
      z-index: 10000;
      animation: fadeInOut 5s ease-in-out;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; top: 0px; }
      10% { opacity: 1; top: 20px; }
      90% { opacity: 1; top: 20px; }
      100% { opacity: 0; top: 0px; }
    }

    
  </style>
</head>
<body>
  <header>
    <?php include('php/navbar.php'); ?>


    <!--<a href="index.php"><img src="img/Untitled-2.png" alt="Fixora Logo" class="logo-small"></a>
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
    </nav>-->
  </header>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
    <div class="login-toast">✅ Успешен вход!</div>
    <script>
      setTimeout(() => {
        document.querySelector('.login-toast')?.remove();
      }, 5000);
    </script>
  <?php endif; ?>

  <main class="centered">
    <img src="img/Untitled-2.png" alt="Fixora Large Logo" class="logoto-large">

  <div class="main-buttons-wrapper">
    <a href="pages/vhod-klient.html" class="main-button left-button">Предлагам работа</a>

    <div class="divider-vertical"></div>

    <a href="pages/vhod-maistor.html" class="main-button right-button">Търся работа</a>
  </div>

    <hr class="section-divider">

    <div class="trust-info">
      <div class="column">
        <h3>Защо да предлагате работа в Fixora?</h3>
        <!--<p>Fixora гарантира сигурност чрез нашата система – плащаш на нас, а ние плащаме на майстора само когато работата е свършена.</p>-->
      </div>
      <div class="column">
        <h3>Защо да търсите работа в Fixora?</h3>
        <!--<p>Сигурни плащания, повече клиенти и рейтингова система. Възможност за развитие и видимост.</p>-->
      </div>
    </div>
    
  </main>

  <footer>
    <p>Свържи се с нас: support@fixora.bg | +359 888 123 456</p>
  </footer>

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
