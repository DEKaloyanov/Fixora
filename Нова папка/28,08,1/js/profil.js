// ======================= ЗАРЕЖДАНЕ НА ОБЯВИ =======================
function loadJobs(type = '') {
  let url = 'fetch_jobs.php';
  if (type) url += '?type=' + encodeURIComponent(type);

  fetch(url)
    .then(res => res.text())
    .then(html => {
      document.getElementById('jobList').innerHTML = html;
      document.getElementById('jobFormContainer').innerHTML = '';
    })
    .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

/* ================================================================
   ЗАПЛАЩАНЕ – UI блок (разширени методи без "Добави ред")
================================================================ */
function paymentsBlockHTML(){
  return `
  <fieldset class="payments-block">
    <legend>Заплащане</legend>

    <!-- Основни -->
    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="day"     name="pay_types[]" value="day"> Надник</label></div>
    <div class="pay-input" data-key="day" style="display:none"><input type="number" step="0.01" min="0" name="pay_day" placeholder="лв/ден"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="hour"    name="pay_types[]" value="hour"> Цена на час</label></div>
    <div class="pay-input" data-key="hour" style="display:none"><input type="number" step="0.01" min="0" name="pay_hour" placeholder="лв/час"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="square"  name="pay_types[]" value="square"> Цена/кв.м</label></div>
    <div class="pay-input" data-key="square" style="display:none"><input type="number" step="0.01" min="0" name="pay_square" placeholder="лв/кв.м"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="linear"  name="pay_types[]" value="linear"> Цена/л.м</label></div>
    <div class="pay-input" data-key="linear" style="display:none"><input type="number" step="0.01" min="0" name="pay_linear" placeholder="лв/л.м"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="piece"   name="pay_types[]" value="piece"> Цена/бр.</label></div>
    <div class="pay-input" data-key="piece" style="display:none"><input type="number" step="0.01" min="0" name="pay_piece" placeholder="лв/бр."></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="project" name="pay_types[]" value="project"> Цена за проект</label></div>
    <div class="pay-input" data-key="project" style="display:none"><input type="number" step="0.01" min="0" name="pay_project" placeholder="лв/проект"></div>

    <!-- Специфични за отделни дейности -->
    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_point"   name="pay_types[]" value="per_point"> Електр. точка (бр.)</label></div>
    <div class="pay-input" data-key="per_point" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_point" placeholder="лв/бр."></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_fixture" name="pay_types[]" value="per_fixture"> ВиК арматура/санитария (бр.)</label></div>
    <div class="pay-input" data-key="per_fixture" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_fixture" placeholder="лв/бр."></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_window"  name="pay_types[]" value="per_window"> Прозорец (бр.)</label></div>
    <div class="pay-input" data-key="per_window" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_window" placeholder="лв/бр."></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_door"    name="pay_types[]" value="per_door"> Врата (бр.)</label></div>
    <div class="pay-input" data-key="per_door" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_door" placeholder="лв/бр."></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_m3"      name="pay_types[]" value="per_m3"> Обем (лв/м³)</label></div>
    <div class="pay-input" data-key="per_m3" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_m3" placeholder="лв/м³"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="per_ton"     name="pay_types[]" value="per_ton"> Тонаж (лв/тон)</label></div>
    <div class="pay-input" data-key="per_ton" style="display:none"><input type="number" step="0.01" min="0" name="pay_per_ton" placeholder="лв/тон"></div>

    <!-- Довършителни примери -->
    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="tile_m2"     name="pay_types[]" value="tile_m2"> Плочки (лв/м²)</label></div>
    <div class="pay-input" data-key="tile_m2" style="display:none"><input type="number" step="0.01" min="0" name="pay_tile_m2" placeholder="лв/м²"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="plaster_m2"  name="pay_types[]" value="plaster_m2"> Шпакловка/мазилка (лв/м²)</label></div>
    <div class="pay-input" data-key="plaster_m2" style="display:none"><input type="number" step="0.01" min="0" name="pay_plaster_m2" placeholder="лв/м²"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="paint_m2"    name="pay_types[]" value="paint_m2"> Боядисване (лв/м²)</label></div>
    <div class="pay-input" data-key="paint_m2" style="display:none"><input type="number" step="0.01" min="0" name="pay_paint_m2" placeholder="лв/м²"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="insulation_m2" name="pay_types[]" value="insulation_m2"> Изолация (лв/м²)</label></div>
    <div class="pay-input" data-key="insulation_m2" style="display:none"><input type="number" step="0.01" min="0" name="pay_insulation_m2" placeholder="лв/м²"></div>

    <!-- Такси -->
    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="callout_fee" name="pay_types[]" value="callout_fee"> Такса посещение</label></div>
    <div class="pay-input" data-key="callout_fee" style="display:none"><input type="number" step="0.01" min="0" name="pay_callout_fee" placeholder="лв"></div>

    <div class="pay-row"><label><input type="checkbox" class="pm-type" data-key="min_charge"  name="pay_types[]" value="min_charge"> Минимална такса</label></div>
    <div class="pay-input" data-key="min_charge" style="display:none"><input type="number" step="0.01" min="0" name="pay_min_charge" placeholder="лв"></div>
  </fieldset>
  `;
}

function initPaymentsUI(form){
  const block = form.querySelector('.payments-block');
  if (!block) return;

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
  const jobList = document.getElementById('jobList');
  const formBox = document.getElementById('jobFormContainer');
  jobList.innerHTML = '';
  formBox.style.display = 'block';

  // общи полета за фирма (показват се при чекбокс)
  const companyFields = `
    <fieldset id="companyFields" class="company-fields" style="display:none">
      <legend>Фирма</legend>
      <div class="grid-2">
        <div>
          <label>Име на фирмата:</label>
          <input type="text" name="company_name" placeholder="напр. СтройФикс ООД">
        </div>
        <div>
          <label>ЕИК / Булстат (непублично):</label>
          <input type="text" name="company_eik" placeholder="123456789">
        </div>
      </div>
      <div class="grid-2">
        <div>
          <label>Телефон:</label>
          <input type="text" name="company_phone" placeholder="+359...">
        </div>
        <div>
          <label>Имейл:</label>
          <input type="email" name="company_email" placeholder="office@firma.bg">
        </div>
      </div>
      <div class="grid-2">
        <div>
          <label>Уебсайт:</label>
          <input type="text" name="company_website" placeholder="https://...">
        </div>
        <div class="inline">
          <label style="margin-right:8px">Регистрирана по ДДС:</label>
          <input type="checkbox" name="company_vat" value="1">
        </div>
      </div>
      <div class="grid-2">
        <div>
          <label>Facebook:</label>
          <input type="text" name="company_facebook" placeholder="https://facebook.com/...">
        </div>
        <div>
          <label>Instagram:</label>
          <input type="text" name="company_instagram" placeholder="https://instagram.com/...">
        </div>
      </div>
      <div class="grid-2">
        <div>
          <label>Лого (1 файл):</label>
          <input type="file" name="company_logo" accept="image/*">
        </div>
        <div></div>
      </div>
    </fieldset>
  `;

  // каскадна професия: главна -> под-професия
  const professionCascade = `
    <div id="single-profession-block">
      <label>Професионална област:</label>
      <select id="prof_main" required>
        <option value="">Избери област</option>
      </select>

      <label style="margin-top:10px">Подпрофесия:</label>
      <select name="profession" id="profession_single" required>
        <option value="">(първо избери област)</option>
      </select>
    </div>
  `;

  const multiProfessionBlock = `
    <div id="multi-profession-block" style="display:none">
      <label>Професии (до 10):</label>
      <input type="text" id="prof-search" placeholder="Търси професия...">
      <div id="prof-suggestions" class="prof-suggestions"></div>
      <div id="prof-chips" class="chips"></div>
      <input type="hidden" name="professions_json" id="professions_json">
    </div>
  `;

  const locationCascade = `
    <div class="grid-2">
      <div>
        <label>Област:</label>
        <select name="region" id="region_select" required>
          <option value="">Избери област</option>
          <option>Благоевград</option><option>Бургас</option><option>Варна</option><option>Велико Търново</option>
          <option>Видин</option><option>Враца</option><option>Габрово</option><option>Добрич</option>
          <option>Кърджали</option><option>Кюстендил</option><option>Ловеч</option><option>Монтана</option>
          <option>Пазарджик</option><option>Перник</option><option>Плевен</option><option>Пловдив</option>
          <option>Разград</option><option>Русе</option><option>Силистра</option><option>Сливен</option>
          <option>Смолян</option><option>София-град</option><option>София област</option><option>Стара Загора</option>
          <option>Търговище</option><option>Хасково</option><option>Шумен</option><option>Ямбол</option>
        </select>
      </div>
      <div>
        <label>Населено място:</label>
        <input type="text" name="city" id="settlement_input" required placeholder="град/село...">
        <datalist id="settlement_list"></datalist>
      </div>
    </div>

    <label>Адрес (по желание):</label>
    <input type="text" name="location" placeholder="ул., квартал…">
  `;

  let formHTML;
  if (type === 'offer') {
    formHTML = `
      <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="job_type" value="offer">

        <label>Кратко описание (Заглавие):</label>
        <input type="text" name="title" maxlength="120" placeholder="напр. Майстор плочки — баня 4 м²" required>

        <div class="company-switch">
          <label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма</label>
        </div>

        ${companyFields}
        ${professionCascade}
        ${multiProfessionBlock}

        <h4 class="section-h">Локация</h4>
        ${locationCascade}

        <h4 class="section-h">Заплащане</h4>
        ${paymentsBlockHTML()}
        <div class="hidden">
          <input type="number" name="price_per_day" step="0.01">
          <input type="number" name="price_per_square" step="0.01">
        </div>

        <div class="images-field">
          <label>Снимки:</label>
          <input type="file" id="jobImagesInput" name="images[]" accept="image/*" multiple style="display:none">
          <div class="images-toolbar">
            <button type="button" id="btnPickImages" class="btn-small">Добави снимки</button>
            <span class="images-hint">Плъзни, за да подредиш. ★ задава корица (първата е автоматично корица).</span>
          </div>
          <div id="imagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>
          <input type="hidden" name="cover_index" id="cover_index" value="0">
        </div>

        <label>Описание:</label>
        <textarea name="description" placeholder="Описание (незадължително)"></textarea>

        <button type="submit">Запази обявата</button>
      </form>`;
  } else {
    formHTML = `
      <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="job_type" value="seek">

        <label>Кратко описание (Заглавие):</label>
        <input type="text" name="title" maxlength="120" placeholder="напр. Търся работа — гипсокартон" required>

        <div class="company-switch">
          <label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма</label>
        </div>

        ${companyFields}
        ${professionCascade}
        ${multiProfessionBlock}

        <h4 class="section-h">Локация</h4>
        ${locationCascade}

        <label>Брой работници:</label>
        <input type="number" name="team_size" id="teamSize" min="1" max="20" value="1" required>
        <div id="teamMemberFields"></div>

        <h4 class="section-h">Заплащане</h4>
        ${paymentsBlockHTML()}
        <div class="hidden">
          <input type="number" name="price_per_day" step="0.01">
          <input type="number" name="price_per_square" step="0.01">
        </div>

        <label>Описание:</label>
        <textarea name="description" placeholder="Описание (незадължително)"></textarea>

        <button type="submit">Запази обявата</button>
      </form>`;
  }

  formBox.innerHTML = formHTML;

  const form = document.getElementById('jobForm');

  // 1) Каскадна професия main -> sub
  const mainSel = document.getElementById('prof_main');
  const subSel  = document.getElementById('profession_single');
  fetch('professions_tree_json.php')
    .then(r=>r.json())
    .then(tree=>{
      Object.keys(tree).forEach(k=>{
        const opt = document.createElement('option');
        opt.value = k; opt.textContent = tree[k].label;
        mainSel.appendChild(opt);
      });
      mainSel.addEventListener('change', ()=>{
        subSel.innerHTML = '<option value="">Избери подпрофесия</option>';
        const k = mainSel.value;
        if (!k || !tree[k]) return;
        tree[k].children.forEach(subKey=>{
          const o = document.createElement('option');
          o.value = subKey; o.textContent = tree.__labels[subKey] || subKey;
          subSel.appendChild(o);
        });
      });
    })
    .catch(()=>{ /* тихо */ });

  // 2) Multi-select (фирма)
  initCompanyMultiUI();

  // 3) Плащания
  initPaymentsUI(form);

  // 4) Company fields toggle
  const isCompany = document.getElementById('is_company');
  const companyFs = document.getElementById('companyFields');
  isCompany.addEventListener('change', ()=>{
    const on = isCompany.checked;
    companyFs.style.display = on ? '' : 'none';
    // превключване single vs multi
    document.getElementById('single-profession-block').style.display = on ? 'none' : 'block';
    document.getElementById('multi-profession-block').style.display  = on ? 'block' : 'none';
    if (on) subSel.removeAttribute('required'); else subSel.setAttribute('required','required');
  });

  // 5) „Seek“ – членове на екип
  if (type === 'seek') {
    const teamSize = document.getElementById('teamSize');
    const container = document.getElementById('teamMemberFields');
    const renderMembers = ()=>{
      container.innerHTML = '';
      for (let i=1;i<=parseInt(teamSize.value||'1',10);i++){
        const inp = document.createElement('input');
        inp.type = 'text'; inp.name = 'team_member_' + i;
        inp.placeholder = 'Име на работник ' + i; inp.required = true;
        container.appendChild(inp);
      }
    };
    teamSize.addEventListener('input', renderMembers);
    renderMembers();
  }

  // 6) Населено място – леки подсказки (общ DB fallback)
  const regionSel = document.getElementById('region_select');
  const settInp   = document.getElementById('settlement_input');
  const dl        = document.getElementById('settlement_list');
  let lastQ = '';
  function suggest(q){
    if (!q || q.length < 2) { dl.innerHTML=''; return; }
    fetch('places_json.php?q='+encodeURIComponent(q))
      .then(r=>r.json())
      .then(arr=>{
        dl.innerHTML = '';
        arr.slice(0,50).forEach(p=>{
          const o = document.createElement('option');
          o.value = p; dl.appendChild(o);
        });
      })
      .catch(()=>{ dl.innerHTML=''; });
  }
  settInp.setAttribute('list','settlement_list');
  settInp.addEventListener('input', ()=>{
    const q = settInp.value.trim();
    if (q===lastQ) return; lastQ=q; suggest(q);
  });

  // 7) Image manager (само при offer)
  if (type === 'offer') initImagesManager(form);
}

function initImagesManager(form){
  const input = document.getElementById('jobImagesInput');
  const grid  = document.getElementById('imagesGrid');
  const btn   = document.getElementById('btnPickImages');
  const coverHidden = document.getElementById('cover_index');

  let files = [];
  let coverIndex = 0;
  let dragSrc = null;

  btn.addEventListener('click', ()=> input.click());
  input.addEventListener('change', (e)=>{
    const list = Array.from(e.target.files || []);
    list.forEach(f=>{ if (f.type && f.type.indexOf('image/')===0) files.push(f); });
    renderGrid(); input.value='';
  });

  ['dragenter','dragover'].forEach(ev=>{
    grid.addEventListener(ev, e=>{ e.preventDefault(); grid.classList.add('dragging'); });
  });
  ['dragleave','drop'].forEach(ev=>{
    grid.addEventListener(ev, e=>{ e.preventDefault(); grid.classList.remove('dragging'); });
  });
  grid.addEventListener('drop', e=>{
    e.preventDefault();
    if (dragSrc!==null) { dragSrc=null; return; }
    const dt=e.dataTransfer; if (!dt || !dt.files) return;
    Array.from(dt.files).forEach(f=>{ if (f.type && f.type.indexOf('image/')===0) files.push(f); });
    renderGrid();
  });

  function renderGrid(){
    grid.innerHTML=''; grid.classList.toggle('empty', files.length===0);
    files.forEach((file, idx)=>{
      const url = URL.createObjectURL(file);
      const tile = document.createElement('div');
      tile.className = 'img-tile' + (idx===coverIndex?' is-cover':'');
      tile.draggable = true; tile.dataset.idx = String(idx);
      tile.innerHTML =
        '<img src="'+url+'" alt="Снимка">'+
        '<button type="button" class="remove-btn" title="Премахни">×</button>'+
        '<button type="button" class="cover-btn" title="Корица">★</button>'+
        '<span class="badge">Корица</span>';
      tile.querySelector('img').draggable=false;

      tile.querySelector('.remove-btn').addEventListener('click', ()=>{
        try{ URL.revokeObjectURL(url); }catch(e){}
        files.splice(idx,1);
        if (!files.length) coverIndex=0;
        else if (idx===coverIndex) coverIndex=Math.min(coverIndex, files.length-1);
        else if (idx<coverIndex) coverIndex--;
        renderGrid();
      });
      tile.querySelector('.cover-btn').addEventListener('click', ()=>{ coverIndex=idx; renderGrid(); });

      tile.addEventListener('dragstart', e=>{
        dragSrc=idx; e.dataTransfer.effectAllowed='move'; e.dataTransfer.setData('text/plain','');
      });
      tile.addEventListener('dragover', e=>{ e.preventDefault(); e.dataTransfer.dropEffect='move'; });
      tile.addEventListener('drop', e=>{
        e.preventDefault();
        const target = idx;
        if (dragSrc===null || dragSrc===target) { dragSrc=null; return; }
        const moved = files.splice(dragSrc,1)[0]; files.splice(target,0,moved);
        if (coverIndex===dragSrc) coverIndex=target;
        else if (dragSrc<coverIndex && target>=coverIndex) coverIndex--;
        else if (dragSrc>coverIndex && target<=coverIndex) coverIndex++;
        dragSrc=null; renderGrid();
      });

      grid.appendChild(tile);
    });
    coverHidden.value = String(coverIndex);
  }

  form.addEventListener('submit', ()=>{
    if (files.length>0){
      const dt = new DataTransfer();
      files.forEach(f=>dt.items.add(f));
      input.files = dt.files;
    }
    coverHidden.value = String(coverIndex>=0?coverIndex:0);
  });
}

// ===== Multi-select UI (Фирма) =====
function initCompanyMultiUI() {
  const isCompany   = document.getElementById('is_company');
  const singleBlock = document.getElementById('single-profession-block');
  const multiBlock  = document.getElementById('multi-profession-block');
  const singleSelect= document.getElementById('profession_single');

  if (!isCompany || !singleBlock || !multiBlock || !singleSelect) return;

  let ALL_PROFS = [];
  let picked = []; // ключове
  const profSearch = document.getElementById('prof-search');
  const profSuggest= document.getElementById('prof-suggestions');
  const profChips  = document.getElementById('prof-chips');
  const profHidden = document.getElementById('professions_json');

  fetch('professions_json.php')
    .then(r => r.json())
    .then(items => { ALL_PROFS = items; })
    .catch(console.error);

  isCompany.addEventListener('change', function () {
    const on = isCompany.checked;
    singleBlock.style.display = on ? 'none' : 'block';
    multiBlock .style.display = on ? 'block': 'none';
    if (on) singleSelect.removeAttribute('required'); else singleSelect.setAttribute('required', 'required');
  });

  if (profSearch) {
    profSearch.addEventListener('input', function () {
      const q = String(profSearch.value || '').toLowerCase().trim();
      if (!q) { profSuggest.style.display = 'none'; profSuggest.innerHTML = ''; return; }
      const matches = ALL_PROFS.filter(x =>
        x.key.toLowerCase().includes(q) || x.label.toLowerCase().includes(q)
      ).slice(0, 20);

      profSuggest.innerHTML = '';
      matches.forEach(m => {
        const row = document.createElement('div');
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
      const it = ALL_PROFS.find(x => x.key === k);
      const lbl = it ? it.label : k;
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = lbl + ' <button type="button" aria-label="remove">&times;</button>';
      chip.querySelector('button').addEventListener('click', function () {
        picked = picked.filter(x => x !== k); renderChips();
      });
      profChips.appendChild(chip);
    });
    profHidden.value = JSON.stringify(picked);

    if (isCompany.checked && picked.length) {
      const first = picked[0];
      if (!singleSelect.querySelector('option[value="' + first + '"]')) {
        const opt = document.createElement('option');
        opt.value = first; opt.text = first;
        singleSelect.appendChild(opt);
      }
      singleSelect.value = first;
    }
  }

  const form = document.getElementById('jobForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      if (isCompany.checked) {
        if (picked.length === 0) {
          e.preventDefault();
          alert('Моля, изберете поне една професия за фирмата.');
          return;
        }
        const first = picked[0];
        if (!singleSelect.querySelector('option[value="' + first + '"]')) {
          const opt = document.createElement('option');
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
        form.reset(); grid.innerHTML = ''; files = []; coverIndex = 0;
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

  let addMode = 'jobs';

  allJobsBtn.classList.add('active');
  leftGroup.classList.add('show');
  loadJobs();
  loadHistory();

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

  addJobBtn.addEventListener('click', (e) => {
    e.preventDefault();

    if (!addJobBtn.classList.contains('active')) {
      addJobBtn.classList.add('active');
      allJobsBtn.classList.remove('active');
    }

    leftGroup.classList.remove('show');
    rightGroup.classList.add('show');

    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobList').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'block';

    if (addMode === 'jobs') {
      loadJobForm('offer');
      addJobBtn.textContent = 'Добави история';
      addMode = 'history';
    } else {
      loadHistoryForm();
    }
  });

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

  document.getElementById('btn-offer').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'none';
    document.getElementById('jobList').style.display = 'block';
    loadJobs('offer');
  });

  document.getElementById('btn-seek').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('active-projects-section').style.display = 'none';
    document.getElementById('jobFormContainer').style.display = 'none';
    document.getElementById('jobList').style.display = 'block';
    loadJobs('seek');
  });

  document.getElementById('btn-add-offer').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('offer'); });
  document.getElementById('btn-add-seek').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('seek'); });

  document.getElementById('active-projects-section').style.display = 'none';

  document.getElementById('jobList').addEventListener('click', (e) => {
    const stop = e.target.closest('.favorite-icon, .edit-btn, .delete-btn, .delete-form, .job-side');
    if (stop) return;
    const card = e.target.closest('.job-card');
    if (!card) return;
    const id = card.getAttribute('data-job-id');
    if (id) window.location.href = 'job_details.php?id=' + encodeURIComponent(id);
  });
});
