<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8" />
  <title>Всички обяви | Fixora</title>
  <link rel="stylesheet" href="../css/all_jobs.css?v=<?php echo time(); ?>">
  <!-- Font Awesome за иконките (еднакво с останалите страници) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="../js/favorites.js" defer></script>
  <script src="../js/all_jobs.js" defer></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="jobs-page">
  <!-- ФИЛТРИ (ляв панел с вътрешен скрол) -->
  <aside class="filters-panel">
    <form id="filtersForm" class="filters-panel__inner" onsubmit="return false;">
      <div class="filters-panel__header">
        <h2>Филтри</h2>
      </div>

      <!-- Основни -->
      <section class="filter-group">
        <h3>Основни</h3>

        <label class="form-row">
          <span class="form-label">Тип</span>
          <select id="typeFilter">
            <option value="">Всички типове</option>
            <option value="offer">Предлагам работа</option>
            <option value="seek">Търся работа</option>
          </select>
        </label>

        <label class="checkbox-chip">
          <input type="checkbox" id="companyOnly" />
          <span>Само фирми</span>
        </label>

        <label class="form-row">
          <span class="form-label">Град/населено място</span>
          <input id="placeFilter" list="placesList" placeholder="Започни да пишеш…">
          <datalist id="placesList"></datalist>
        </label>

        <label class="form-row">
          <span class="form-label">Професия</span>
          <select id="mainProfessionFilter">
            <option value="">Всички</option>
          </select>
        </label>

        <label class="form-row hidden" id="subProfWrap">
          <span class="form-label">Подпрофесия</span>
          <select id="subProfessionFilter">
            <option value="">Всички</option>
          </select>
        </label>
      </section>

      <!-- Цени -->
      <section class="filter-group">
        <h3>Цени</h3>
        <div class="grid-2">
          <label class="form-row">
            <span class="form-label">Надник (мин.)</span>
            <input id="minDay" type="number" step="0.01" placeholder="напр. 100">
          </label>
          <label class="form-row">
            <span class="form-label">Надник (макс.)</span>
            <input id="maxDay" type="number" step="0.01" placeholder="напр. 300">
          </label>

          <label class="form-row">
            <span class="form-label">Цена/кв.м (мин.)</span>
            <input id="minSq" type="number" step="0.01" placeholder="напр. 10">
          </label>
          <label class="form-row">
            <span class="form-label">Цена/кв.м (макс.)</span>
            <input id="maxSq" type="number" step="0.01" placeholder="напр. 50">
          </label>
        </div>
      </section>

      <!-- Рейтинг и дата -->
      <section class="filter-group">
        <h3>Рейтинг и дата</h3>
        <label class="form-row">
          <span class="form-label">Минимален рейтинг</span>
          <select id="minRating">
            <option value="">Всички</option>
            <option value="1">1+ ⭐</option>
            <option value="2">2+ ⭐</option>
            <option value="3">3+ ⭐</option>
            <option value="4">4+ ⭐</option>
            <option value="5">5 ⭐</option>
          </select>
        </label>

        <div class="grid-2">
          <label class="form-row">
            <span class="form-label">От дата</span>
            <input id="dateFrom" type="date">
          </label>
          <label class="form-row">
            <span class="form-label">До дата</span>
            <input id="dateTo" type="date">
          </label>
        </div>
      </section>

      <!-- Сортиране -->
      <section class="filter-group">
        <h3>Сортиране</h3>
        <label class="form-row">
          <span class="form-label">Подреди по</span>
          <select id="sortBy">
            <option value="newest">Най-нови</option>
            <option value="oldest">Най-стари</option>
            <option value="price_day_asc">Цена/ден ↑</option>
            <option value="price_day_desc">Цена/ден ↓</option>
            <option value="price_sq_asc">Цена/кв.м ↑</option>
            <option value="price_sq_desc">Цена/кв.м ↓</option>
            <option value="rating_desc">Рейтинг ↓</option>
            <option value="rating_asc">Рейтинг ↑</option>
          </select>
        </label>
      </section>

      <!-- Действия -->
      <div class="filters-actions">
        <button id="applyFilters" class="btn btn-apply" type="button">Приложи филтрите</button>
        <button id="clearFilters" class="btn btn-clear" type="button">Изчисти</button>
      </div>
    </form>
  </aside>

  <!-- РЕЗУЛТАТИ -->
  <section class="results-panel">
    <div id="all-jobs-container" class="results-list"></div>
  </section>
</div>

</body>
</html>
