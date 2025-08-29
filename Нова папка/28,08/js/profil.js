// ======================= ЗАРЕЖДАНЕ НА ОБЯВИ =======================
function loadJobs(type = '') {
  let url = 'fetch_jobs.php';
  if (type) url += '?type=' + encodeURIComponent(type);

  fetch(url)
    .then(res => res.text())
    .then(html => {
      document.getElementById('jobList').innerHTML = html;
      document.getElementById('jobFormContainer').innerHTML = '';
      // Премахнахме JS-инжектирането на иконки – вече идват от PHP.
    })
    .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

/* ================================================================
   ЗАПЛАЩАНЕ – UI блок (работи със save_job.php)
================================================================ */
function paymentsBlockHTML(){
  return `
  <fieldset class="payments-block">
    <legend>Заплащане</legend>

    <div class="pay-row">
      <label><input type="checkbox" name="pay_types[]" value="day" class="pm-type" data-key="day"> Надник</label>
    </div>
    <div class="pay-input" data-key="day" style="display:none">
      <input type="number" step="0.01" min="0" name="pay_day" placeholder="лв/ден">
    </div>

    <div class="pay-row">
      <label><input type="checkbox" name="pay_types[]" value="square" class="pm-type" data-key="square"> Цена/кв.м</label>
    </div>
    <div class="pay-input" data-key="square" style="display:none">
      <input type="number" step="0.01" min="0" name="pay_square" placeholder="лв/кв.м">
    </div>

    <div class="pay-row">
      <label><input type="checkbox" name="pay_types[]" value="hour" class="pm-type" data-key="hour"> Цена на час</label>
    </div>
    <div class="pay-input" data-key="hour" style="display:none">
      <input type="number" step="0.01" min="0" name="pay_hour" placeholder="лв/час">
    </div>

    <div class="pay-row">
      <label><input type="checkbox" name="pay_types[]" value="project" class="pm-type" data-key="project"> Цена за проект</label>
    </div>
    <div class="pay-input" data-key="project" style="display:none">
      <input type="number" step="0.01" min="0" name="pay_project" placeholder="лв/проект">
    </div>

    <div class="pay-custom">
      <div class="pay-custom-head">
        <span>Допълнително</span>
        <button type="button" class="btn-small pm-add-custom">+ Добави ред</button>
      </div>
      <div class="pay-custom-body"></div>
    </div>
  </fieldset>
  `;
}

function initPaymentsUI(form){
  const block = form.querySelector('.payments-block');
  if (!block) return;

  // показване/скриване на полетата според избраните типове
  block.querySelectorAll('.pm-type').forEach(cb=>{
    cb.addEventListener('change', ()=>{
      const key = cb.dataset.key;
      const row = block.querySelector('.pay-input[data-key="'+key+'"]');
      if (!row) return;
      row.style.display = cb.checked ? '' : 'none';
      if (!cb.checked) {
        const inp = row.querySelector('input[type="number"]');
        if (inp) inp.value = '';
      }
    });
  });

  // добавяне/премахване на custom редове
  const addBtn = block.querySelector('.pm-add-custom');
  const customBody = block.querySelector('.pay-custom-body');
  addBtn?.addEventListener('click', ()=>{
    const wrap = document.createElement('div');
    wrap.className = 'pay-custom-row';
    wrap.innerHTML = `
      <input type="text"   name="custom_label[]"  placeholder="Етикет (напр. Транспорт)">
      <input type="number" name="custom_price[]"  step="0.01" min="0" placeholder="Цена">
      <button type="button" class="btn-small pm-custom-remove">✕</button>
    `;
    wrap.querySelector('.pm-custom-remove').addEventListener('click', ()=> wrap.remove());
    customBody.appendChild(wrap);
  });

  // при submit копираме day/square в legacy скритите полета за съвместимост
  form.addEventListener('submit', ()=>{
    const dayInp    = form.querySelector('input[name="pay_day"]');
    const sqInp     = form.querySelector('input[name="pay_square"]');
    const legacyDay = form.querySelector('input[name="price_per_day"]');
    const legacySq  = form.querySelector('input[name="price_per_square"]');
    if (legacyDay && dayInp) legacyDay.value = dayInp.value || '';
    if (legacySq  && sqInp ) legacySq.value  = sqInp.value  || '';
  });
}

/* ======================= ФОРМА ЗА ДОБАВЯНЕ (обяви) ======================= */
function loadJobForm(type) {
  var jobList = document.getElementById('jobList');
  var formBox = document.getElementById('jobFormContainer');
  jobList.innerHTML = '';
  formBox.style.display = 'block';

  var formHTML;
  if (type === 'offer') {
    formHTML =
      '<form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">' +
      '<input type="hidden" name="job_type" value="offer">' +

      '<div class="company-switch">' +
      '<label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма (работите по няколко професии)</label>' +
      '</div>' +

      '<div id="single-profession-block">' +
      '<label>Тип работа:</label>' +
      '<select name="profession" id="profession_single" required>' +
      '<option value="">Избери тип работа</option>' +
      '</select>' +
      '</div>' +

      '<div id="multi-profession-block" style="display:none">' +
      '<label>Професии (до 10):</label>' +
      '<input type="text" id="prof-search" placeholder="Търси професия...">' +
      '<div id="prof-suggestions" class="prof-suggestions"></div>' +
      '<div id="prof-chips" class="chips"></div>' +
      '<input type="hidden" name="professions_json" id="professions_json">' +
      '</div>' +

      '<label>Населено място:</label>' +
      '<input type="text" name="location" required placeholder="Изберете град">' +

      // === Нов блок за заплащане ===
      paymentsBlockHTML() +
      // legacy скрити (за филтри/сорти)
      '<div class="hidden">' +
      '  <input type="number" name="price_per_day" step="0.01">' +
      '  <input type="number" name="price_per_square" step="0.01">' +
      '</div>' +

      '<div class="images-field">' +
      '<label>Снимки:</label>' +
      '<input type="file" id="jobImagesInput" name="images[]" accept="image/*" multiple style="display:none">' +
      '<div class="images-toolbar">' +
      '<button type="button" id="btnPickImages" class="btn-small">Добави снимки</button>' +
      '<span class="images-hинт">Плъзни, за да подредиш. Кликни ★ за корица.</span>' +
      '</div>' +
      '<div id="imagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>' +
      '<input type="hidden" name="cover_index" id="cover_index" value="0">' +
      '</div>' +

      '<label>Описание:</label>' +
      '<textarea name="description" placeholder="Описание (незадължително)"></textarea>' +

      '<button type="submit">Запази обявата</button>' +
      '</form>';
  } else {
    formHTML =
      '<form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">' +
      '<input type="hidden" name="job_type" value="seek">' +

      '<div class="company-switch">' +
      '<label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма (работите по няколко професии)</label>' +
      '</div>' +

      '<div id="single-profession-block">' +
      '<label>Тип работа:</label>' +
      '<select name="profession" id="profession_single" required>' +
      '<option value="">Избери тип работа</option>' +
      '</select>' +
      '</div>' +

      '<div id="multi-profession-block" style="display:none">' +
      '<label>Професии (до 10):</label>' +
      '<input type="text" id="prof-search" placeholder="Търси професия...">' +
      '<div id="prof-suggestions" class="prof-suggestions"></div>' +
      '<div id="prof-chips" class="chips"></div>' +
      '<input type="hidden" name="professions_json" id="professions_json">' +
      '</div>' +

      '<label>Населено място:</label>' +
      '<input type="text" name="city" required placeholder="Изберете град">' +

      '<label>Брой работници:</label>' +
      '<input type="number" name="team_size" id="teamSize" min="1" max="20" value="1" required>' +
      '<div id="teamMemberFields"></div>' +

      // === Нов блок за заплащане ===
      paymentsBlockHTML() +
      // legacy скрити (за филтри/сорти)
      '<div class="hidden">' +
      '  <input type="number" name="price_per_day" step="0.01">' +
      '  <input type="number" name="price_per_square" step="0.01">' +
      '</div>' +

      '<label>Описание:</label>' +
      '<textarea name="description" placeholder="Описание (незадължително)"></textarea>' +

      '<button type="submit">Запази обявата</button>' +
      '</form>';
  }

  formBox.innerHTML = formHTML;

  // референция към формата
  const form = document.getElementById('jobForm');

  // 1) Пълним единичния select
  var singleSelect = document.getElementById('profession_single');
  if (singleSelect) {
    fetch('professions_json.php')
      .then(r => r.json())
      .then(items => {
        singleSelect.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
        items.forEach(it => {
          var opt = document.createElement('option');
          opt.value = it.key; opt.text = it.label;
          singleSelect.appendChild(opt);
        });
      })
      .catch(console.error);
  }

  // 2) Multi-select (фирма)
  initCompanyMultiUI();

  // 3) Доп. полета за "seek"
  if (type === 'seek') {
    var teamSize = document.getElementById('teamSize');
    var container = document.getElementById('teamMemberFields');
    teamSize.addEventListener('input', function () {
      container.innerHTML = '';
      for (var i = 1; i <= parseInt(this.value || '1', 10); i++) {
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.name = 'team_member_' + i;
        inp.placeholder = 'Име на работник ' + i;
        inp.required = true;
        container.appendChild(inp);
      }
    });

    // инициализирай заплащане за SEEK преди return
    initPaymentsUI(form);
    return; // няма image manager тук
  }

  // 4) Image manager (offer)
  var input = document.getElementById('jobImagesInput');
  var grid = document.getElementById('imagesGrid');
  var btn = document.getElementById('btnPickImages');
  var coverHidden = document.getElementById('cover_index');

  // инициализирай заплащането за OFFER
  initPaymentsUI(form);

  var files = [];
  var coverIndex = 0;
  var dragSrc = null;

  btn.addEventListener('click', function () { input.click(); });

  input.addEventListener('change', function (e) {
    var list = Array.from(e.target.files || []);
    list.forEach(function (f) {
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid(); input.value = '';
  });

  ['dragenter', 'dragover'].forEach(function (ev) {
    grid.addEventListener(ev, function (e) { e.preventDefault(); grid.classList.add('dragging'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    grid.addEventListener(ev, function (e) { e.preventDefault(); grid.classList.remove('dragging'); });
  });
  grid.addEventListener('drop', function (e) {
    e.preventDefault();
    if (dragSrc !== null) { dragSrc = null; return; }
    var dt = e.dataTransfer;
    if (!dt || !dt.files) return;
    Array.from(dt.files).forEach(function (f) {
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid();
  });

  function renderGrid() {
    grid.innerHTML = '';
    grid.classList.toggle('empty', files.length === 0);

    files.forEach(function (file, idx) {
      var url = URL.createObjectURL(file);
      var tile = document.createElement('div');
      tile.className = 'img-tile' + (idx === coverIndex ? ' is-cover' : '');
      tile.draggable = true;
      tile.dataset.idx = String(idx);

      tile.innerHTML =
        '<img src="' + url + '" alt="Снимка">' +
        '<button type="button" class="remove-btn" title="Премахни">×</button>' +
        '<button type="button" class="cover-btn" title="Задай като корица">★</button>' +
        '<span class="badge">Корица</span>';

      tile.querySelector('img').draggable = false;

      tile.querySelector('.remove-btn').addEventListener('click', function () {
        try { URL.revokeObjectURL(url); } catch (e) { }
        files.splice(idx, 1);
        if (files.length === 0) { coverIndex = 0; }
        else if (idx === coverIndex) { coverIndex = Math.min(coverIndex, files.length - 1); }
        else if (idx < coverIndex) { coverIndex -= 1; }
        renderGrid();
      });

      tile.querySelector('.cover-btn').addEventListener('click', function () {
        coverIndex = idx; renderGrid();
      });

      tile.addEventListener('dragstart', function (e) {
        dragSrc = idx;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
      });
      tile.addEventListener('dragover', function (e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
      tile.addEventListener('drop', function (e) {
        e.preventDefault();
        var target = idx;
        if (dragSrc === null || dragSrc === target) { dragSrc = null; return; }
        var moved = files.splice(dragSrc, 1)[0];
        files.splice(target, 0, moved);

        if (coverIndex === dragSrc) coverIndex = target;
        else if (dragSrc < coverIndex && target >= coverIndex) coverIndex -= 1;
        else if (dragSrc > coverIndex && target <= coverIndex) coverIndex += 1;

        dragSrc = null; renderGrid();
      });

      grid.appendChild(tile);
    });

    coverHidden.value = String(coverIndex);
  }

  form.addEventListener('submit', function () {
    if (files.length > 0) {
      var dt = new DataTransfer();
      files.forEach(function (f) { dt.items.add(f); });
      input.files = dt.files;
    }
    coverHidden.value = String(coverIndex >= 0 ? coverIndex : 0);
  });
}

// ===== Multi-select UI (Фирма) – общо за двете форми =====
function initCompanyMultiUI() {
  var isCompany = document.getElementById('is_company');
  var singleBlock = document.getElementById('single-profession-block');
  var multiBlock = document.getElementById('multi-profession-block');
  var singleSelect = document.getElementById('profession_single');

  if (!isCompany || !singleBlock || !multiBlock || !singleSelect) return;

  var ALL_PROFS = [];
  var picked = []; // ключове
  var profSearch = document.getElementById('prof-search');
  var profSuggest = document.getElementById('prof-suggestions');
  var profChips = document.getElementById('prof-chips');
  var profHidden = document.getElementById('professions_json');

  fetch('professions_json.php')
    .then(r => r.json())
    .then(items => { ALL_PROFS = items; })
    .catch(console.error);

  isCompany.addEventListener('change', function () {
    var on = isCompany.checked;
    singleBlock.style.display = on ? 'none' : 'block';
    multiBlock.style.display = on ? 'block' : 'none';
    if (on) singleSelect.removeAttribute('required'); else singleSelect.setAttribute('required', 'required');
  });

  if (profSearch) {
    profSearch.addEventListener('input', function () {
      var q = String(profSearch.value || '').toLowerCase().trim();
      if (!q) { profSuggest.style.display = 'none'; profSuggest.innerHTML = ''; return; }
      var matches = ALL_PROFS.filter(x =>
        x.key.toLowerCase().includes(q) || x.label.toLowerCase().includes(q)
      ).slice(0, 20);

      profSuggest.innerHTML = '';
      matches.forEach(m => {
        var row = document.createElement('div');
        row.textContent = m.label;
        row.addEventListener('click', function () {
          if (!picked.includes(m.key)) {
            if (picked.length >= 10) { alert('Може да изберете до 10 професии.'); return; }
            picked.push(m.key); renderChips();
          }
          profSuggest.style.display = 'none'; profSearch.value = '';
        });
        profSuggest.appendChild(row);
      });
      profSuggest.style.display = matches.length ? 'block' : 'none';
    });

    document.addEventListener('click', function (e) {
      if (!profSuggest.contains(e.target) && e.target !== profSearch) {
        profSuggest.style.display = 'none';
      }
    });
  }

  function renderChips() {
    profChips.innerHTML = '';
    picked.forEach(k => {
      var it = ALL_PROFS.find(x => x.key === k);
      var lbl = it ? it.label : k;
      var chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = lbl + ' <button type="button" aria-label="remove">&times;</button>';
      chip.querySelector('button').addEventListener('click', function () {
        picked = picked.filter(x => x !== k); renderChips();
      });
      profChips.appendChild(chip);
    });
    profHidden.value = JSON.stringify(picked);

    if (isCompany.checked && picked.length) {
      var first = picked[0];
      if (!singleSelect.querySelector('option[value="' + first + '"]')) {
        var opt = document.createElement('option');
        opt.value = first; opt.text = first;
        singleSelect.appendChild(opt);
      }
      singleSelect.value = first;
    }
  }

  var form = document.getElementById('jobForm');
  if (form) {
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
          opt.value = first; opt.text = first;
          singleSelect.appendChild(opt);
        }
        singleSelect.value = first;
      }
    });
  }
}

/* ======================= ФОРМА ЗА ИСТОРИЯ ======================= */
function loadHistoryForm() {
  const jobList = document.getElementById('jobList');
  const formBox = document.getElementById('jobFormContainer');
  jobList.innerHTML = '';
  formBox.style.display = 'block';

  formBox.innerHTML = `
    <form id="historyForm" class="job-form" action="save_history.php" method="POST" enctype="multipart/form-data">
      <h3>Добави история (завършен проект)</h3>

      <label>Заглавие на проекта:</label>
      <input type="text" name="title" placeholder="напр. Ремонт на баня в Център" required>

      <label>Професия:</label>
      <select name="profession" id="history_profession" required>
        <option value="">Избери професия</option>
      </select>

      <label>Град:</label>
      <input type="text" name="city" placeholder="напр. София" required>

      <label>Локация / адрес (незадължително):</label>
      <input type="text" name="location" placeholder="ул., квартал…">

      <div class="grid-2">
        <div>
          <label>Начало:</label>
          <input type="date" name="start_date">
        </div>
        <div>
          <label>Край:</label>
          <input type="date" name="end_date">
        </div>
      </div>

      <label>Кратко описание:</label>
      <textarea name="description" placeholder="Какво беше извършено, особености, материали…"></textarea>

      <div class="images-field">
        <label>Снимки от проекта:</label>
        <input type="file" id="historyImagesInput" name="images[]" accept="image/*" multiple style="display:none">
        <div class="images-toolbar">
          <button type="button" id="btnPickHistoryImages" class="btn-small">Добави снимки</button>
          <span class="images-hint">Плъзни, за да подредиш. Кликни ★ за корица.</span>
        </div>
        <div id="historyImagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>
        <input type="hidden" name="cover_index" id="history_cover_index" value="0">
      </div>

      <button type="submit">Запази историята</button>
    </form>
  `;

  // професии
  fetch('professions_json.php')
    .then(r => r.json())
    .then(items => {
      const sel = document.getElementById('history_profession');
      items.forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.key; opt.textContent = it.label;
        sel.appendChild(opt);
      });
    });

  // image manager (опростен)
  const input = document.getElementById('historyImagesInput');
  const grid = document.getElementById('historyImagesGrid');
  const btn = document.getElementById('btnPickHistoryImages');
  const coverHidden = document.getElementById('history_cover_index');
  const form = document.getElementById('historyForm');

  let files = [];
  let coverIndex = 0;
  let dragSrc = null;

  btn.addEventListener('click', () => input.click());
  input.addEventListener('change', (e) => {
    Array.from(e.target.files || []).forEach(f => {
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid(); input.value = '';
  });

  ['dragenter', 'dragover'].forEach(ev => {
    grid.addEventListener(ev, e => { e.preventDefault(); grid.classList.add('dragging'); });
  });
  ['dragleave', 'drop'].forEach(ev => {
    grid.addEventListener(ev, e => { e.preventDefault(); grid.classList.remove('dragging'); });
  });
  grid.addEventListener('drop', e => {
    e.preventDefault();
    if (dragSrc !== null) { dragSrc = null; return; }
    const dt = e.dataTransfer; if (!dt || !dt.files) return;
    Array.from(dt.files).forEach(f => {
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid();
  });

  function renderGrid() {
    grid.innerHTML = ''; grid.classList.toggle('empty', files.length === 0);
    files.forEach((file, idx) => {
      const url = URL.createObjectURL(file);
      const tile = document.createElement('div');
      tile.className = 'img-tile' + (idx === coverIndex ? ' is-cover' : '');
      tile.draggable = true; tile.dataset.idx = String(idx);
      tile.innerHTML =
        '<img src="' + url + '" alt="img">' +
        '<button type="button" class="remove-btn" title="Премахни">×</button>' +
        '<button type="button" class="cover-btn" title="Корица">★</button>' +
        '<span class="badge">Корица</span>';
      tile.querySelector('img').draggable = false;

      tile.querySelector('.remove-btn').addEventListener('click', () => {
        try { URL.revokeObjectURL(url); } catch (e) { }
        files.splice(idx, 1);
        if (!files.length) { coverIndex = 0; }
        else if (idx === coverIndex) { coverIndex = Math.min(coverIndex, files.length - 1); }
        else if (idx < coverIndex) { coverIndex--; }
        renderGrid();
      });
      tile.querySelector('.cover-btn').addEventListener('click', () => { coverIndex = idx; renderGrid(); });
      tile.addEventListener('dragstart', e => { dragSrc = idx; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', ''); });
      tile.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
      tile.addEventListener('drop', e => {
        e.preventDefault();
        const target = idx; if (dragSrc === null || dragSrc === target) { dragSrc = null; return; }
        const moved = files.splice(dragSrc, 1)[0]; files.splice(target, 0, moved);
        if (coverIndex === dragSrc) coverIndex = target;
        else if (dragSrc < coverIndex && target >= coverIndex) coverIndex--;
        else if (dragSrc > coverIndex && target <= coverIndex) coverIndex++;
        dragSrc = null; renderGrid();
      });
      grid.appendChild(tile);
    });
    coverHidden.value = String(coverIndex);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (files.length > 0) {
      const dt = new DataTransfer();
      files.forEach(f => dt.items.add(f));
      input.files = dt.files;
    }
    coverHidden.value = String(coverIndex >= 0 ? coverIndex : 0);

    const fd = new FormData(form);
    fetch('save_history.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.text())
      .then(txt => {
        if (txt.trim() !== 'ok') { alert(txt || 'Грешка при запис.'); return; }
        alert('Историята е добавена успешно.');
        form.reset(); grid.innerHTML = ''; files = []; coverIndex = 0; renderGrid();
        loadHistory();
      })
      .catch(err => alert('Грешка: ' + err));
  });
}

/* ======================= ЗАРЕЖДАНЕ НА ИСТОРИЯ ======================= */
function loadHistory() {
  fetch('fetch_history.php', { credentials: 'same-origin' })
    .then(r => r.text())
    .then(html => {
      document.getElementById('history-section').innerHTML = html;
    })
    .catch(() => { /* тихо */ });
}

// ======================= ИНИЦИАЛИЗАЦИЯ НА БУТОНИ =======================
document.addEventListener('DOMContentLoaded', () => {
  const allJobsBtn = document.getElementById('btn-all-jobs');
  const addJobBtn = document.getElementById('btn-add-job');
  const activeProjectsBtn = document.getElementById('active-projects-btn');
  const leftGroup = document.querySelector('.job-sub-buttons.all');
  const rightGroup = document.querySelector('.job-sub-buttons.add');

  // режим на десния основен бутон
  let addMode = 'jobs';

  allJobsBtn.classList.add('active');
  leftGroup.classList.add('show');
  loadJobs();
  loadHistory();

  // Всички обяви
  allJobsBtn.addEventListener('click', (e) => {
    e.preventDefault();
    if (!allJobsBtn.classList.contains('active')) {
      allJobsBtn.classList.add('active');
      addJobBtn.classList.remove('active');
    }
    leftGroup.classList.add('show');
    rightGroup.classList.remove('show');

    addJobBtn.textContent = 'Добави обява';
    addMode = 'jobs';

    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobList').style.display = 'block';
    document.getElementById('jobFormContainer').style.display = 'none';
    loadJobs();
  });

  // Тогъл „Добави обява“ -> „Добави история“
  addJobBtn.addEventListener('click', (e) => {
    e.preventDefault();

    if (!addJobBtn.classList.contains('active')) {
      addJobBtn.classList.add('active');
      allJobsBtn.classList.remove('active');
    }

    // показваме десното подменю, крием лявото
    leftGroup.classList.remove('show');
    rightGroup.classList.add('show');

    // скриваме списъка и показваме контейнера за форми
    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobList').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'block';

    if (addMode === 'jobs') {
      // Първо натискане: показва форма за обява и сменя текста на бутона
      loadJobForm('offer');
      addJobBtn.textContent = 'Добави история';
      addMode = 'history';
    } else {
      // Следващи натискания: остава в режим "history" и показва формата за история
      loadHistoryForm();
    }
  });

  // Активни обяви
  activeProjectsBtn.addEventListener('click', (e) => {
    e.preventDefault();
    allJobsBtn.classList.remove('active');
    addJobBtn.classList.remove('active');
    leftGroup.classList.remove('show');
    rightGroup.classList.remove('show');

    addJobBtn.textContent = 'Добави обява';
    addMode = 'jobs';

    document.getElementById('jobList').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'none';
    document.getElementById('active-projects-section').style.display = 'block';
  });

  // Подменюта – филтри под "Всички обяви"
  document.getElementById('btn-offer').addEventListener('click', (e) => {
    e.preventDefault();
    // „Предлагам работа“ -> показвай SEEK (по текущата логика)
    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'none';
    document.getElementById('jobList').style.display = 'block';
    loadJobs('offer');
  });

  document.getElementById('btn-seek').addEventListener('click', (e) => {
    e.preventDefault();
    // „Търся работа“ -> показвай OFFER (по текущата логика)
    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'none';
    document.getElementById('jobList').style.display = 'block';
    loadJobs('seek');
  });

  document.getElementById('btn-add-offer').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('offer'); });
  document.getElementById('btn-add-seek').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('seek'); });

  document.getElementById('active-projects-section').style.display = 'none';

  // Клик по карта -> job_details (игнорирай heart, Редактирай и Изтрий)
  document.getElementById('jobList').addEventListener('click', (e) => {
    const stop = e.target.closest('.favorite-icon, .edit-btn, .delete-btn, .delete-form, .job-side');
    if (stop) return; // спираме навигацията при действията вдясно
    const card = e.target.closest('.job-card');
    if (!card) return;
    const id = card.getAttribute('data-job-id');
    if (id) window.location.href = 'job_details.php?id=' + encodeURIComponent(id);
  });
});
