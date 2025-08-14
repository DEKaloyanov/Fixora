<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

if (!isset($_GET['id'])) {
    echo "Грешка: липсва ID на обявата.";
    exit;
}
$job_id = (int)$_GET['id'];

/* Вземи обявата и провери правата */
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $job_id, 'user_id' => $user_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Обявата не е намерена или нямате права за редакция.";
    exit;
}

/* Старите снимки (за сравнение/изтриване) */
$oldImages = json_decode($job['images'] ?? '[]', true);
if (!is_array($oldImages)) $oldImages = [];

/* Обработка на POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Флаг за фирма
    $is_company = 0;
    if (isset($_POST['is_company'])) {
        $v = $_POST['is_company'];
        $is_company = ($v === '1' || $v === 'on' || (int)$v === 1) ? 1 : 0;
    }

    // Единична и множествени професии
    $profession_single = isset($_POST['profession']) ? trim($_POST['profession']) : '';
    $professions_json  = isset($_POST['professions_json']) && trim($_POST['professions_json']) !== '' ? $_POST['professions_json'] : null;

    $profession  = $profession_single; // по подразбиране – единичната
    $professions = null;               // по подразбиране – няма множ. професии

    if ($is_company && $professions_json) {
        $arr = json_decode($professions_json, true);
        if (is_array($arr) && count($arr) > 0) {
            $profession  = $arr[0]; // първата става "главна"
            $professions = json_encode($arr, JSON_UNESCAPED_UNICODE);
        }
    }

    // Други полета
    $location         = $_POST['location']        ?? '';
    $city             = $_POST['city']            ?? '';
    $price_per_day    = ($_POST['price_per_day']    !== '') ? $_POST['price_per_day']    : null;
    $price_per_square = ($_POST['price_per_square'] !== '') ? $_POST['price_per_square'] : null;
    $description      = $_POST['description']     ?? null;

    /* ---------- UPLOAD на нови снимки (в реда, в който ще ги използваме) ---------- */
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/jobs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        foreach ($_FILES['images']['name'] as $i => $name) {
            $tmp = $_FILES['images']['tmp_name'][$i] ?? '';
            if (!$tmp) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';

            $newName = uniqid('job_').'.'.$ext;
            $target  = $uploadDir.$newName;

            if (move_uploaded_file($tmp, $target)) {
                $uploadedImages[] = 'uploads/jobs/'.$newName; // относителен път за фронта
            }
        }
    }

    /* ---------- Финален ред на снимките (микс от стари и нови) ---------- 
       Фронтът подава hidden поле images_final_order (JSON от токени):
       [{t:'e', p:'uploads/jobs/old1.jpg'}, {t:'n'}, {t:'e', p:'uploads/jobs/old2.jpg'}, {t:'n'}, ...]
       Където:
         t:'e' => "existing" (стара) със свойство p (път)
         t:'n' => "new"     (нова)   – взимаме следващия файл от $uploadedImages по ред
    */
    $finalOrder = [];
    if (!empty($_POST['images_final_order'])) {
        $tmp = json_decode($_POST['images_final_order'], true);
        if (is_array($tmp)) $finalOrder = $tmp;
    }

    $resultImages = [];
    $nextNew = 0;

    if ($finalOrder) {
        foreach ($finalOrder as $tok) {
            if (isset($tok['t']) && $tok['t'] === 'e' && !empty($tok['p'])) {
                // добавяме САМО ако съществуваше преди (предпазване от подправени стойности)
                if (in_array($tok['p'], $oldImages, true)) {
                    $resultImages[] = $tok['p'];
                }
            } elseif (isset($tok['t']) && $tok['t'] === 'n') {
                if (isset($uploadedImages[$nextNew])) {
                    $resultImages[] = $uploadedImages[$nextNew];
                    $nextNew++;
                }
            }
        }
    } else {
        // Фолбек: няма подаден order → първо старите (в текущия им ред), после новите
        $resultImages = array_values($oldImages);
        foreach ($uploadedImages as $p) $resultImages[] = $p;
    }

    // Корица: премести избраната снимка най-отпред (за всеки случай)
    $cover_index = isset($_POST['cover_index']) ? (int)$_POST['cover_index'] : 0;
    if (!empty($resultImages) && $cover_index >= 0 && $cover_index < count($resultImages)) {
        $cover = $resultImages[$cover_index];
        array_splice($resultImages, $cover_index, 1);
        array_unshift($resultImages, $cover);
    }

    // Изтриване от диска: старите, които вече не присъстват в $resultImages
    $toDelete = array_diff($oldImages, $resultImages);
    foreach ($toDelete as $relPath) {
        $abs = dirname(__DIR__) . '/' . ltrim($relPath, '/');
        if (is_file($abs)) @unlink($abs);
    }

    $imagesJSON = json_encode($resultImages, JSON_UNESCAPED_UNICODE);

    // UPDATE
    $upd = $conn->prepare("
        UPDATE jobs
           SET profession       = :profession,
               professions      = :professions,
               is_company       = :is_company,
               location         = :location,
               city             = :city,
               price_per_day    = :price_per_day,
               price_per_square = :price_per_square,
               description      = :description,
               images           = :images
         WHERE id = :id AND user_id = :user_id
    ");
    $upd->execute([
        'profession'       => $profession,
        'professions'      => $professions,
        'is_company'       => $is_company,
        'location'         => $location,
        'city'             => $city,
        'price_per_day'    => $price_per_day,
        'price_per_square' => $price_per_square,
        'description'      => $description,
        'images'           => $imagesJSON,
        'id'               => $job_id,
        'user_id'          => $user_id
    ]);

    header("Location: profil.php");
    exit;
}

// За визуализация
require_once __DIR__ . '/professions.php'; // $professions масив за етикети
$isCompany = (int)$job['is_company'] === 1;
$prefilledMulti = [];
if (!empty($job['professions'])) {
    $tmp = json_decode($job['professions'], true);
    if (is_array($tmp)) $prefilledMulti = $tmp;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Редакция на обява</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Минимални стилове за множествените професии + изображения (премести в CSS при желание) */
        .inline { display:inline-flex; align-items:center; gap:8px; margin:8px 0; }
        .multi-profession { border:1px solid #ccc; border-radius:8px; padding:10px; background:#fff; }
        .chips { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
        .chip { background:#eef3ff; border:1px solid #bcd; border-radius:16px; padding:4px 10px; font-size:13px; }
        .chip button { margin-left:6px; border:none; background:transparent; cursor:pointer; font-size:14px; }
        .suggestions { display:none; margin-top:6px; border:1px solid #ddd; border-radius:6px; max-height:160px; overflow:auto; background:#fff; }
        .suggestions div { padding:6px 10px; cursor:pointer; }
        .suggestions div:hover { background:#f0f6ff; }

        .badge-company { display:inline-block; background:#1f4365; color:#fff; border-radius:6px; padding:2px 6px; font-size:12px; margin-bottom:8px; }
        .job-form-container { max-width: 920px; margin: 20px auto; background:#fff; border:1px solid #eee; border-radius:12px; padding:20px; }
        .job-form-container h2 { margin-top:0; }
        .job-form-container label { display:block; margin-top:12px; font-weight:600; }
        .job-form-container input[type="text"],
        .job-form-container input[type="number"],
        .job-form-container textarea,
        .job-form-container select  { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; }
        .job-form-container button.button { margin-top:16px; background:#002147; color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; }
        .job-form-container button.button:hover { background:#004080; }

        /* Image manager */
        .images-field { margin-top: 10px; }
        .images-toolbar { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
        .btn-small{ padding:8px 12px; border:1px solid #cfd8e3; background:#fff; color:#1f4365; border-radius:10px; cursor:pointer; font-weight:600; }
        .btn-small:hover{ border-color:#1f4365; box-shadow:0 2px 8px rgba(31,67,101,.12); }
        .images-grid{
            position:relative; display:grid; grid-template-columns: repeat(auto-fill, minmax(120px,1fr));
            gap:10px; border:2px dashed #cfd8e3; border-radius:12px; padding:10px; min-height:120px; background:#f8fafc;
        }
        .images-grid.dragging{ border-color:#1f4365; }
        .images-grid.empty::before{
            content: attr(data-empty); position:absolute; left:50%; top:50%;
            transform:translate(-50%,-50%); color:#94a3b8; font-size:14px;
        }
        .img-tile{ position:relative; border-radius:12px; overflow:hidden; background:#fff; box-shadow:0 2px 10px rgba(31,67,101,.08); }
        .img-tile img{ width:100%; height:120px; object-fit:cover; display:block; }
        .img-tile .remove-btn, .img-tile .cover-btn{
            position:absolute; top:6px; width:28px; height:28px; border-radius:50%;
            border:1px solid #cfd8e3; background:#fff; cursor:pointer; line-height:26px; text-align:center;
            box-shadow:0 2px 6px rgba(31,67,101,.12); font-weight:700;
        }
        .img-tile .remove-btn{ right:6px; }
        .img-tile .cover-btn{ left:6px; color:#1f4365; }
        .img-tile .badge{
            position:absolute; left:6px; bottom:6px; background:#1f4365; color:#fff; font-size:12px; padding:4px 8px; border-radius:10px; display:none;
        }
        .img-tile.is-cover .badge{ display:inline-block; }
    </style>
</head>
<body>
    <main class="job-form-container">
        <h2>Редактирай обявата</h2>

        <?php if ($isCompany): ?>
            <div class="badge-company">Фирмена обява</div>
        <?php endif; ?>

        <form method="POST" id="editJobForm" enctype="multipart/form-data">
            <!-- Флаг: Аз съм фирма -->
            <label class="inline">
                <input type="checkbox" id="is_company" name="is_company" <?= $isCompany ? 'checked' : '' ?>>
                Аз съм фирма
            </label>

            <!-- Блок: единична професия (за частно лице) -->
            <div id="single-profession-block" style="<?= $isCompany ? 'display:none' : '' ?>">
                <label>Тип работа:</label>
                <select name="profession" id="profession_single" <?= $isCompany ? '' : 'required' ?>>
                    <option value="">Избери</option>
                    <?php foreach ($professions as $k => $label): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= ($job['profession'] === $k ? 'selected' : '') ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Блок: множество професии (за фирма) -->
            <div id="multi-profession-block" style="<?= $isCompany ? '' : 'display:none' ?>">
                <label>Професии (до 10):</label>
                <div class="multi-profession">
                    <input type="text" id="prof-search" placeholder="Търси професия...">
                    <div id="prof-suggestions" class="suggestions"></div>
                    <div id="prof-chips" class="chips"></div>
                    <input type="hidden" name="professions_json" id="professions_json" value="[]">
                </div>
            </div>

            <label>Локация:</label>
            <input type="text" name="location" value="<?= htmlspecialchars((string)$job['location']) ?>">

            <label>Град:</label>
            <input type="text" name="city" value="<?= htmlspecialchars((string)$job['city']) ?>">

            <label>Цена на ден:</label>
            <input type="number" step="0.01" name="price_per_day" value="<?= htmlspecialchars((string)$job['price_per_day']) ?>">

            <label>Цена за квадрат:</label>
            <input type="number" step="0.01" name="price_per_square" value="<?= htmlspecialchars((string)$job['price_per_square']) ?>">

            <label>Описание:</label>
            <textarea name="description"><?= htmlspecialchars((string)$job['description']) ?></textarea>

            <!-- =========== Image Manager =========== -->
            <div class="images-field">
                <label>Снимки:</label>
                <input type="file" id="jobImagesInput" name="images[]" accept="image/*" multiple style="display:none">
                <div class="images-toolbar">
                    <button type="button" id="btnPickImages" class="btn-small">Добави снимки</button>
                    <span class="images-hint">Плъзни, за да подредиш. Кликни ★ за корица.</span>
                </div>
                <div id="imagesGrid" class="images-grid" data-empty="Няма снимки. Пусни тук или 'Добави снимки'."></div>
                <input type="hidden" name="cover_index" id="cover_index" value="0">
                <input type="hidden" name="images_final_order" id="images_final_order" value="">
            </div>
            <!-- =========== /Image Manager =========== -->

            <button type="submit" class="button">Запази промените</button>
        </form>
    </main>

<script>
// ---------------- МНОЖЕСТВЕНИ ПРОФЕСИИ ----------------
var ALL_PROFS = [];
fetch('professions_json.php')
  .then(function(r){ return r.json(); })
  .then(function(items){ ALL_PROFS = items; initMultiUI(); })
  .catch(function(err){ console.error(err); });

function initMultiUI() {
  var isCompany    = document.getElementById('is_company');
  var singleBlock  = document.getElementById('single-profession-block');
  var multiBlock   = document.getElementById('multi-profession-block');
  var singleSelect = document.getElementById('profession_single');

  var preselected  = <?php echo json_encode($prefilledMulti, JSON_UNESCAPED_UNICODE); ?>;
  var picked       = (Array.isArray(preselected) ? preselected.slice(0,10) : []);

  var profSearch   = document.getElementById('prof-search');
  var profSuggest  = document.getElementById('prof-suggestions');
  var profChips    = document.getElementById('prof-chips');
  var profHidden   = document.getElementById('professions_json');

  renderChips();

  isCompany.addEventListener('change', function () {
    var on = isCompany.checked;
    singleBlock.style.display = on ? 'none' : 'block';
    multiBlock.style.display  = on ? 'block' : 'none';
    if (on) { singleSelect.removeAttribute('required'); }
    else    { singleSelect.setAttribute('required','required'); }
  });

  profSearch.addEventListener('input', function () {
    showSuggestions(profSearch.value);
  });

  document.addEventListener('click', function (e) {
    if (!profSuggest.contains(e.target) && e.target !== profSearch) {
      profSuggest.style.display = 'none';
    }
  });

  function showSuggestions(term) {
    var q = String(term || '').trim().toLowerCase();
    if (!q) {
      profSuggest.style.display = 'none';
      profSuggest.innerHTML = '';
      return;
    }
    var matches = ALL_PROFS.filter(function(x){
      return x.key.toLowerCase().indexOf(q) !== -1 || x.label.toLowerCase().indexOf(q) !== -1;
    }).slice(0,20);

    profSuggest.innerHTML = '';
    matches.forEach(function(m){
      var row = document.createElement('div');
      row.textContent = m.label;
      row.addEventListener('click', function(){
        if (picked.indexOf(m.key) === -1) {
          if (picked.length >= 10) { alert('Може да изберете до 10 професии.'); return; }
          picked.push(m.key);
          renderChips();
        }
        profSuggest.style.display = 'none';
        profSearch.value = '';
      });
      profSuggest.appendChild(row);
    });
    profSuggest.style.display = matches.length ? 'block' : 'none';
  }

  function renderChips() {
    profChips.innerHTML = '';
    picked.forEach(function(k){
      var it  = ALL_PROFS.find(function(x){ return x.key === k; });
      var lbl = it ? it.label : k;
      var chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = lbl + ' <button type="button" aria-label="remove">&times;</button>';
      chip.querySelector('button').addEventListener('click', function(){
        picked = picked.filter(function(x){ return x !== k; });
        renderChips();
      });
      profChips.appendChild(chip);
    });
    profHidden.value = JSON.stringify(picked);

    // синхронизирай първата към селекта (ако е фирма)
    if (document.getElementById('is_company').checked && picked.length) {
      var first = picked[0];
      if (!singleSelect.querySelector('option[value="' + first + '"]')) {
        var opt = document.createElement('option');
        opt.value = first;
        opt.text  = first;
        singleSelect.appendChild(opt);
      }
      singleSelect.value = first;
    }
  }

  var form = document.getElementById('editJobForm');
  form.addEventListener('submit', function (e) {
    if (isCompany.checked) {
      if (picked.length === 0) {
        e.preventDefault();
        alert('Моля, изберете поне една професия за фирмата.');
        return;
      }
      var first = picked[0];
      if (!singleSelect.querySelector('option[value="' + first + '"]')) {
        var opt = document.createElement('option');
        opt.value = first;
        opt.text  = first;
        singleSelect.appendChild(opt);
      }
      singleSelect.value = first;
    }
  });
}

// ---------------- IMAGE MANAGER ----------------
(function(){
  var input       = document.getElementById('jobImagesInput');
  var grid        = document.getElementById('imagesGrid');
  var btnPick     = document.getElementById('btnPickImages');
  var coverHidden = document.getElementById('cover_index');
  var orderHidden = document.getElementById('images_final_order');
  var form        = document.getElementById('editJobForm');

  var EXISTING = <?php echo json_encode($oldImages, JSON_UNESCAPED_UNICODE); ?>;

  // items: {kind:'existing', path, url} | {kind:'new', file, url}
  var items = [];
  EXISTING.forEach(function(p){
    items.push({ kind:'existing', path:p, url:'../' + p });
  });

  var coverIndex = 0;

  input.style.display = 'none';
  grid.classList.toggle('empty', items.length === 0);

  btnPick.addEventListener('click', function(){ input.click(); });

  input.addEventListener('change', function (e) {
    var list = Array.from(e.target.files || []);
    list.forEach(function(f){
      if (f.type && f.type.indexOf('image/') === 0) {
        var url = URL.createObjectURL(f);
        items.push({ kind:'new', file:f, url:url });
      }
    });
    renderGrid();
    input.value = '';
  });

  ['dragenter','dragover'].forEach(function(ev){
    grid.addEventListener(ev, function(e){ e.preventDefault(); grid.classList.add('dragging'); });
  });
  ['dragleave','drop'].forEach(function(ev){
    grid.addEventListener(ev, function(e){ e.preventDefault(); grid.classList.remove('dragging'); });
  });

  grid.addEventListener('drop', function(e){
    e.preventDefault();
    var dt = e.dataTransfer;
    if (!dt || !dt.files || dt.files.length === 0) return;
    Array.from(dt.files).forEach(function(f){
      if (f.type && f.type.indexOf('image/') === 0) {
        var url = URL.createObjectURL(f);
        items.push({ kind:'new', file:f, url:url });
      }
    });
    renderGrid();
  });

  function renderGrid() {
    grid.innerHTML = '';
    grid.classList.toggle('empty', items.length === 0);

    items.forEach(function(it, idx){
      var tile = document.createElement('div');
      tile.className = 'img-tile' + (idx === coverIndex ? ' is-cover' : '');
      tile.draggable = true;
      tile.dataset.idx = String(idx);

      tile.innerHTML =
        '<img src="' + it.url + '" alt="Снимка">' +
        '<button type="button" class="remove-btn" title="Премахни">×</button>' +
        '<button type="button" class="cover-btn" title="Задай като корица">★</button>' +
        '<span class="badge">Корица</span>';

      // забрана за drag на самото img
      tile.querySelector('img').draggable = false;

      // Премахване
      tile.querySelector('.remove-btn').addEventListener('click', function(){
        if (it.kind === 'new') { try { URL.revokeObjectURL(it.url); } catch(e){} }
        items.splice(idx, 1);
        if (items.length === 0) { coverIndex = 0; }
        else if (idx === coverIndex) { coverIndex = Math.min(coverIndex, items.length - 1); }
        else if (idx < coverIndex) { coverIndex -= 1; }
        renderGrid();
      });

      // Корица
      tile.querySelector('.cover-btn').addEventListener('click', function(){
        coverIndex = idx;
        renderGrid();
      });

      // Drag & drop пренареждане
      tile.addEventListener('dragstart', function(e){
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(idx));
      });
      tile.addEventListener('dragover', function(e){ e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
      tile.addEventListener('drop', function(e){
        e.preventDefault();
        var src = parseInt(e.dataTransfer.getData('text/plain'), 10);
        var target = parseInt(tile.dataset.idx, 10);
        if (isNaN(src) || src === target) return;
        var moved = items.splice(src, 1)[0];
        items.splice(target, 0, moved);

        if (coverIndex === src) coverIndex = target;
        else if (src < coverIndex && target >= coverIndex) coverIndex -= 1;
        else if (src > coverIndex && target <= coverIndex) coverIndex += 1;

        renderGrid();
      });

      grid.appendChild(tile);
    });

    coverHidden.value = String(coverIndex);
  }

  // първоначален рендер
  renderGrid();

  // submit: описваме реда и подаваме само новите файлове
  form.addEventListener('submit', function(){
    var finalOrder = [];
    var dt = new DataTransfer();
    items.forEach(function(it){
      if (it.kind === 'existing') {
        finalOrder.push({ t: 'e', p: it.path });
      } else {
        finalOrder.push({ t: 'n' });
        dt.items.add(it.file);
      }
    });
    document.getElementById('images_final_order').value = JSON.stringify(finalOrder);
    input.files = dt.files;
  });
})();
</script>

</body>
</html>
