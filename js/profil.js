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
   ЗАПЛАЩАНЕ – всички методи видими; активиране с клик на плочката;
   полето за цена се показва вдясно от етикета (inline).
================================================================ */
function paymentsBlockHTML(){
  // Всички методи в една мрежа – 3 колони
  const items = [
    ['day','Надник','лв/ден'],
    ['hour','Цена на час','лв/час'],
    ['square','Цена/кв.м','лв/кв.м'],
    ['linear','Цена/л.м','лв/л.м'],
    ['piece','Цена/бр.','лв/бр.'],
    ['project','Цена за проект','лв/проект'],
    ['per_m3','Обем','лв/м³'],
    ['per_ton','Тонаж','лв/тон'],
    ['callout_fee','Такса посещение','лв']
  ];

  return `
    <fieldset class="payments-block">
      <legend>Заплащане</legend>
      <div class="payments-grid">
        ${items.map(([k,l,p]) => payItemHTML(k,l,p)).join('')}
      </div>
    </fieldset>
  `;
}

function payItemHTML(key, label, placeholder){
  // Няма checkbox — плочката е кликаема; input-ът е вдясно и е скрит докато не е активна плочката
  return `
    <div class="pm-item" data-key="${key}" tabindex="0" role="button" aria-pressed="false">
      <span class="pm-label">${label}</span>
      <input type="number" step="0.01" min="0" name="pay_${key}" class="pm-price" placeholder="${placeholder}" inputmode="decimal" />
    </div>
  `;
}

function initPaymentsUI(form){
  const block = form.querySelector('.payments-block');
  if (!block) return;

  const items = block.querySelectorAll('.pm-item');

  items.forEach(item=>{
    const price = item.querySelector('.pm-price');
    // Стартово: неактивно
    item.classList.remove('on');
    item.setAttribute('aria-pressed','false');
    price.value = '';
    price.blur();

    // Клик/фокус активира
    const toggle = ()=>{
      const nowOn = !item.classList.contains('on');
      if (nowOn){
        item.classList.add('on');
        item.setAttribute('aria-pressed','true');
        // Фокус към полето
        price.focus();
      } else {
        item.classList.remove('on');
        item.setAttribute('aria-pressed','false');
        price.value = '';
      }
    };

    item.addEventListener('click', (e)=>{
      // Ако клик е върху самия input – само активирай, не изключвай
      if (e.target === price){
        if (!item.classList.contains('on')){
          item.classList.add('on');
          item.setAttribute('aria-pressed','true');
        }
        return;
      }
      toggle();
    });

    item.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter' || e.key === ' '){
        e.preventDefault();
        // Enter/Space → toggle
        if (e.key === ' ' && e.target === price) return;
        toggle();
      }
    });

    // Ако потребителят изтрие стойността и напусне полето → изключваме
    price.addEventListener('blur', ()=>{
      if (price.value === ''){
        item.classList.remove('on');
        item.setAttribute('aria-pressed','false');
      }
    });

    // Въвеждане на стойност автоматично активира
    price.addEventListener('input', ()=>{
      if (price.value !== ''){
        item.classList.add('on');
        item.setAttribute('aria-pressed','true');
      }
    });
  });

  // при submit — за съвместимост попълваме старите колони
  form.addEventListener('submit', ()=>{
    const getVal = n => (form.querySelector(`input[name="${n}"]`)?.value || '').trim();
    const legacyDay = form.querySelector('input[name="price_per_day"]');
    const legacySq  = form.querySelector('input[name="price_per_square"]');
    if (legacyDay) legacyDay.value = getVal('pay_day');
    if (legacySq ) legacySq.value  = getVal('pay_square');
  });
}

/* ======================= ЕДИНЕН СЕЛЕКТ ЗА ПРОФЕСИИ ======================= */
function professionsUnifiedSelect(){
  return `
    <div id="profession-block">
      <label>Професии / подпрофесии:</label>
      <select id="profession_select" name="profession" required>
        <option value="">Зареждане…</option>
      </select>
      <input type="hidden" name="professions_json" id="professions_json" value="">
      <small class="hint">При „Фирма“ можеш да избереш няколко с Ctrl/⌘ + клик.</small>
    </div>
  `;
}

function initProfessionsUnifiedUI(form){
  const select    = form.querySelector('#profession_select');
  const isCompany = form.querySelector('#is_company');
  const hiddenArr = form.querySelector('#professions_json');

  fetch('professions_tree_json.php')
    .then(r=>r.json())
    .then(tree=>{
      select.innerHTML = '';
      const placeholder = document.createElement('option');
      placeholder.value = ''; placeholder.textContent = 'Избери…';
      select.appendChild(placeholder);

      Object.keys(tree).forEach(k=>{
        if (k === '__labels') return;
        const main = tree[k];
        const og = document.createElement('optgroup');
        og.label = main.label || k;

        const mainOpt = document.createElement('option');
        mainOpt.value = k; mainOpt.textContent = (main.label || k) + ' (област)';
        og.appendChild(mainOpt);

        (main.children || []).forEach(subKey=>{
          const subOpt = document.createElement('option');
          subOpt.value = subKey;
          subOpt.textContent = tree.__labels?.[subKey] || subKey;
          og.appendChild(subOpt);
        });
        select.appendChild(og);
      });

      updateSelectMode();
    }).catch(()=>{ /* тихо */ });

  function updateSelectMode(){
    const on = isCompany.checked;
    if (on) {
      select.setAttribute('multiple','multiple');
      select.setAttribute('size','8');
      const first = select.querySelector('option[value=""]');
      if (first) first.disabled = true;
      hiddenArr.value = '[]';
      select.removeAttribute('required');
    } else {
      select.removeAttribute('multiple');
      select.removeAttribute('size');
      const first = select.querySelector('option[value=""]');
      if (first) { first.disabled = false; first.selected = true; }
      hiddenArr.value = '';
      select.setAttribute('required','required');
    }
  }

  isCompany.addEventListener('change', ()=>{
    updateSelectMode();
    Array.from(select.options).forEach(o=>o.selected=false);
  });

  form.addEventListener('submit', (e)=>{
    const company = isCompany.checked;
    const picked  = Array.from(select.selectedOptions).map(o=>o.value).filter(Boolean);

    if (!company) {
      if (picked.length !== 1) {
        e.preventDefault();
        select.focus();
        showInlineError(select, 'Моля, избери една професия.');
        return;
      }
    } else {
      if (picked.length === 0) {
        e.preventDefault();
        select.focus();
        showInlineError(select, 'Моля, избери поне една професия за фирмата.');
        return;
      }
    }

    if (company) {
      hiddenArr.value = JSON.stringify(picked);
    } else {
      hiddenArr.value = '';
    }
  });
}

/* Малък помощник за inline грешки */
function showInlineError(el, msg){
  clearInlineError(el);
  el.classList.add('invalid');
  const small = document.createElement('div');
  small.className = 'field-error';
  small.textContent = msg;
  el.insertAdjacentElement('afterend', small);
  el.scrollIntoView({behavior:'smooth', block:'center'});
}
function clearInlineError(el){
  el.classList.remove('invalid');
  const n = el.parentElement?.querySelector('.field-error');
  if (n) n.remove();
}

/* ======================= ФОРМА ЗА ДОБАВЯНЕ (обяви) ======================= */
function loadJobForm(type) {
  const jobList = document.getElementById('jobList');
  const formBox = document.getElementById('jobFormContainer');
  jobList.innerHTML = '';
  formBox.style.display = 'block';

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

  const unifiedProfSelect = professionsUnifiedSelect();

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
      <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="job_type" value="offer">

        <label>Кратко описание (Заглавие):</label>
        <input type="text" name="title" maxlength="120" placeholder="напр. Майстор плочки — баня 4 м²" required>

        <div class="company-switch">
          <label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма</label>
        </div>

        ${companyFields}
        ${unifiedProfSelect}

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
            <span class="images-hint">Плъзни, за да подредиш. Корица е първата снимка.</span>
          </div>
          <div id="imagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>
        </div>

        <label>Описание:</label>
        <textarea name="description" placeholder="Описание (незадължително)"></textarea>

        <button type="submit">Запази обявата</button>
      </form>`;
  } else {
    formHTML = `
      <form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="job_type" value="seek">

        <label>Кратко описание (Заглавие):</label>
        <input type="text" name="title" maxlength="120" placeholder="напр. Търся работа — гипсокартон" required>

        <div class="company-switch">
          <label><input type="checkbox" id="is_company" name="is_company" value="1"> Фирма</label>
        </div>

        ${companyFields}
        ${unifiedProfSelect}

        <h4 class="section-х">Локация</h4>
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

  // Професии – единен селект
  initProfessionsUnifiedUI(form);

  // Плащания
  initPaymentsUI(form);

  // Company fields toggle
  const isCompany = document.getElementById('is_company');
  const companyFs = document.getElementById('companyFields');
  isCompany.addEventListener('change', ()=>{
    const on = isCompany.checked;
    companyFs.style.display = on ? '' : 'none';
  });

  // „Seek“ – членове на екип
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

  // Населено място – подсказки
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

  // Image manager (offer)
  if (type === 'offer') initImagesManager(form);

  // Клиентска валидация при submit
  form.addEventListener('submit', (e)=>{
    form.querySelectorAll('.field-error').forEach(n=>n.remove());
    form.querySelectorAll('.invalid').forEach(n=>n.classList.remove('invalid'));

    const title = form.querySelector('input[name="title"]');
    if (!title.value.trim()) {
      e.preventDefault(); showInlineError(title, 'Въведи заглавие.'); return;
    }

    const region = form.querySelector('#region_select');
    if (!region.value) {
      e.preventDefault(); showInlineError(region, 'Избери област.'); return;
    }

    const city = form.querySelector('#settlement_input');
    if (!city.value.trim()) {
      e.preventDefault(); showInlineError(city, 'Въведи населено място.'); return;
    }
  });
}

function initImagesManager(form){
  const input = document.getElementById('jobImagesInput');
  const grid  = document.getElementById('imagesGrid');
  const btn   = document.getElementById('btnPickImages');

  let files = [];
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
      tile.className = 'img-tile' + (idx===0 ? ' is-cover' : '');
      tile.draggable = true; tile.dataset.idx = String(idx);
      tile.innerHTML =
        '<img src="'+url+'" alt="Снимка">'+
        '<button type="button" class="remove-btn" title="Премахни">×</button>'+
        '<span class="badge">Корица</span>';
      tile.querySelector('img').draggable=false;

      tile.querySelector('.remove-btn').addEventListener('click', ()=>{
        try{ URL.revokeObjectURL(url); }catch(e){}
        files.splice(idx,1);
        renderGrid();
      });

      tile.addEventListener('dragstart', e=>{
        dragSrc=idx; e.dataTransfer.effectAllowed='move'; e.dataTransfer.setData('text/plain','');
      });
      tile.addEventListener('dragover', e=>{ e.preventDefault(); e.dataTransfer.dropEffect='move'; });
      tile.addEventListener('drop', e=>{
        e.preventDefault();
        const target = idx;
        if (dragSrc===null || dragSrc===target) { dragSrc=null; return; }
        const moved = files.splice(dragSrc,1)[0]; files.splice(target,0,moved);
        dragSrc=null; renderGrid();
      });

      grid.appendChild(tile);
    });
  }

  form.addEventListener('submit', ()=>{
    if (files.length>0){
      const dt = new DataTransfer();
      files.forEach(f=>dt.items.add(f));
      input.files = dt.files;
    }
  });
}

/* ======================= ФОРМА ЗА ИСТОРИЯ ======================= */
function loadHistoryForm() {
  const jobList = document.getElementById('jobList');
  const formBox = document.getElementById('jobFormContainer');
  jobList.innerHTML = '';
  formBox.style.display = 'block';

  formBox.innerHTML = `
    <form id="historyForm" class="history-form" action="save_history.php" method="POST" enctype="multipart/form-data" novalidate>
      <div class="hf-card">
        <div class="hf-header">
          <div class="hf-title">
            <i class="fas fa-history"></i>
            <div>
              <h3>Добави история (завършен проект)</h3>
              <p class="hf-sub">Опиши накратко проекта и добави снимки (първата е корица).</p>
            </div>
          </div>
          <button type="submit" class="hf-submit">Запази историята</button>
        </div>

        <div class="hf-grid">
          <div class="hf-col">
            <div class="hf-field">
              <label>Заглавие на проекта</label>
              <input type="text" name="title" placeholder="напр. Ремонт на баня в Център" required>
            </div>

            <div class="hf-field">
              <label>Професия / дейност</label>
              <select name="profession" id="history_profession" required>
                <option value="">Избери…</option>
              </select>
            </div>

            <div class="hf-field">
              <label>Кратко описание</label>
              <textarea name="description" placeholder="Какво беше извършено, особености, материали…"></textarea>
            </div>
          </div>

          <div class="hf-col">
            <div class="hf-field">
              <label>Град</label>
              <input type="text" name="city" placeholder="напр. София" required>
            </div>

            <div class="hf-field">
              <label>Локация / адрес (по желание)</label>
              <input type="text" name="location" placeholder="ул., квартал…">
            </div>

            <div class="hf-field hf-dates">
              <div>
                <label>Начало</label>
                <input type="date" name="start_date">
              </div>
              <div>
                <label>Край</label>
                <input type="date" name="end_date">
              </div>
            </div>
          </div>
        </div>

        <div class="hf-field images-field">
          <label>Снимки от проекта</label>
          <div class="images-toolbar">
            <button type="button" id="btnPickHistoryImages" class="btn-small">
              <i class="fas fa-upload"></i> Добави снимки
            </button>
            <span class="images-hint">Плъзни за подредба. <strong>Корица е първата снимка</strong>.</span>
          </div>
          <input type="file" id="historyImagesInput" name="images[]" accept="image/*" multiple style="display:none">
          <div id="historyImagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>
        </div>

        <div class="hf-footer">
          <button type="submit" class="hf-submit bottom">Запази историята</button>
        </div>
      </div>
    </form>
  `;

  /* ==== Професии: същото дърво и групиране както при обявата ==== */
  fetch('professions_tree_json.php')
    .then(r => r.json())
    .then(tree => {
      const sel = document.getElementById('history_profession');
      // изчистваме и добавяме placeholder само веднъж
      sel.innerHTML = '';
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Избери…';
      sel.appendChild(placeholder);

      Object.keys(tree).forEach(k => {
        if (k === '__labels') return;
        const main = tree[k];

        const og = document.createElement('optgroup');
        og.label = main.label || k;

        // по избор: позволяваме избор и на областта
        const mainOpt = document.createElement('option');
        mainOpt.value = k;
        mainOpt.textContent = (main.label || k) + ' (област)';
        og.appendChild(mainOpt);

        (main.children || []).forEach(subKey => {
          const subOpt = document.createElement('option');
          subOpt.value = subKey;
          subOpt.textContent = tree.__labels?.[subKey] || subKey;
          og.appendChild(subOpt);
        });

        sel.appendChild(og);
      });
    })
    .catch(()=>{ /* тихо */ });

  // ===== Image manager (първата е корица) =====
  const input = document.getElementById('historyImagesInput');
  const grid  = document.getElementById('historyImagesGrid');
  const btn   = document.getElementById('btnPickHistoryImages');
  const form  = document.getElementById('historyForm');

  let files = [];
  let dragSrc = null;

  btn.addEventListener('click', () => input.click());
  input.addEventListener('change', (e) => {
    Array.from(e.target.files || []).forEach(f => {
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid(); input.value = '';
  });

  ['dragenter','dragover'].forEach(ev=>{
    grid.addEventListener(ev, e=>{ e.preventDefault(); grid.classList.add('dragging'); });
  });
  ['dragleave','drop'].forEach(ev=>{
    grid.addEventListener(ev, e=>{ e.preventDefault(); grid.classList.remove('dragging'); });
  });
  grid.addEventListener('drop', e=>{
    e.preventDefault();
    const dt = e.dataTransfer; if (!dt || !dt.files) return;
    Array.from(dt.files).forEach(f=>{
      if (f.type && f.type.indexOf('image/') === 0) files.push(f);
    });
    renderGrid();
  });

  function renderGrid() {
    grid.innerHTML = ''; grid.classList.toggle('empty', files.length === 0);
    files.forEach((file, idx) => {
      const url = URL.createObjectURL(file);
      const tile = document.createElement('div');
      tile.className = 'img-tile' + (idx === 0 ? ' is-cover' : '');
      tile.draggable = true; tile.dataset.idx = String(idx);
      tile.innerHTML =
        '<img src="'+url+'" alt="img">' +
        '<button type="button" class="remove-btn" title="Премахни">×</button>' +
        '<span class="badge">Корица</span>';
      tile.querySelector('img').draggable = false;

      tile.querySelector('.remove-btn').addEventListener('click', () => {
        try { URL.revokeObjectURL(url); } catch(e){}
        files.splice(idx, 1);
        renderGrid();
      });

      tile.addEventListener('dragstart', e => {
        dragSrc = idx; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain','');
      });
      tile.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect='move'; });
      tile.addEventListener('drop', e => {
        e.preventDefault();
        const target = idx; if (dragSrc === null || dragSrc === target) { dragSrc=null; return; }
        const moved = files.splice(dragSrc,1)[0]; files.splice(target,0,moved);
        dragSrc = null; renderGrid();
      });

      grid.appendChild(tile);
    });
  }

  // ===== Валидация и запис =====
  form.addEventListener('submit', function (e) {
    // базова валидация
    const reqEls = [
      form.querySelector('input[name="title"]'),
      form.querySelector('select[name="profession"]'),
      form.querySelector('input[name="city"]')
    ];
    let hasErr = false;
    reqEls.forEach(el => {
      el.classList.remove('invalid');
      const err = el.parentElement.querySelector('.field-error');
      if (err) err.remove();
      if (!String(el.value || '').trim()) {
        hasErr = true; el.classList.add('invalid');
        const msg = document.createElement('div');
        msg.className = 'field-error';
        msg.textContent = 'Задължително поле.';
        el.parentElement.appendChild(msg);
      }
    });
    if (hasErr) { e.preventDefault(); return; }

    if (files.length > 0) {
      const dt = new DataTransfer();
      files.forEach(f => dt.items.add(f));
      input.files = dt.files;
    }

    e.preventDefault();
    const fd = new FormData(form);
    fetch('save_history.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.text())
      .then(txt => {
        if (txt.trim() !== 'ok') { alert(txt || 'Грешка при запис.'); return; }
        alert('Историята е добавена успешно.');
        form.reset(); grid.innerHTML = ''; files = [];
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
