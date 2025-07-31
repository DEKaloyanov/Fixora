<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Всички обяви | Fixora</title>
  <link rel="stylesheet" href="../css/style.css">
  <?php include 'navbar.php'; ?>
  <style>
    body {
  background-image: url("../img/FON=FIXORA.png"); /* пътят до изображението */
  background-size:cover;        /* покрива цялата страница */
  background-repeat: repeat;  /* да не се повтаря */
  background-position: center;   /* центрира изображението */
}
    .jobs-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
    }
    .filters select {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .job-card {
      display: flex;
      gap: 20px;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 20px;
      padding: 15px;
      align-items: center;
    }
    .job-card img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 8px;
    }
    .job-info {
      flex: 1;
    }
    .job-info h3 {
      margin: 0 0 10px;
    }
    .job-info p {
      margin: 3px 0;
    }
    .job-info a {
      margin-top: 10px;
      display: inline-block;
      background: #002147;
      color: white;
      padding: 6px 12px;
      text-decoration: none;
      border-radius: 4px;
    }
  </style>
</head>
<body>
<main>
  <div class="jobs-wrapper">
    <h1>Всички обяви</h1>
    <div class="filters">
      <select id="filterType">
        <option value="">Всички типове</option>
        <option value="offer">Предлагам работа</option>
        <option value="seek">Търся работа</option>
      </select>
      <select id="filterProfession">
        <option value="">Всички професии</option>
        <option value="boqjdiq">Бояджия</option>
        <option value="zidar">Зидар</option>
        <option value="kofraj">Кофражист</option>
        <option value="elektrikar">Електротехник</option>
      </select>
    </div>
    <div id="allJobsContainer"></div>
  </div>
</main>

<footer class="footer-contacts">
  <p>Контакти: support@fixora.bg | Телефон: 0888 123 456</p>
</footer>

<script>
function loadAllJobs() {
  const type = document.getElementById('filterType').value;
  const profession = document.getElementById('filterProfession').value;
  const url = `fetch_all_jobs.php?type=${type}&profession=${profession}`;

  fetch(url)
    .then(res => res.text())
    .then(html => {
      document.getElementById('allJobsContainer').innerHTML = html;
    });
}

document.getElementById('filterType').addEventListener('change', loadAllJobs);
document.getElementById('filterProfession').addEventListener('change', loadAllJobs);
document.addEventListener('DOMContentLoaded', loadAllJobs);
</script>
</body>
</html>
