<?php session_start(); ?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8" />
  <title>Fixora – Начало</title>
  <?php include 'php/navbar.php'; ?>
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
  
</body>
</html>
