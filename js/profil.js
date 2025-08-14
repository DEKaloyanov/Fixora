// ======================= ЗАРЕЖДАНЕ НА ОБЯВИ =======================
function loadJobs(type = '') {
    let url = 'fetch_jobs.php';
    if (type) url += '?type=' + encodeURIComponent(type);

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = html;
            document.getElementById('jobFormContainer').innerHTML = '';

            // Иконки към мета
            document.querySelectorAll('.job-meta-item.location').forEach(el => {
                el.innerHTML = `<i class="fas fa-map-marker-alt"></i>${el.textContent}`;
            });
            document.querySelectorAll('.job-meta-item.price-day').forEach(el => {
                el.innerHTML = `<i class="fas fa-coins"></i>${el.textContent}`;
            });
            document.querySelectorAll('.job-meta-item.price-square').forEach(el => {
                el.innerHTML = `<i class="fas fa-ruler-combined"></i>${el.textContent}`;
            });
        })
        .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

// ======================= ФОРМА ЗА ДОБАВЯНЕ =======================
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

          '<label>Надник:</label>' +
          '<input type="number" name="price_per_day" placeholder="Въведете надник">' +

          '<label>Цена на квадрат:</label>' +
          '<input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">' +

          '<div class="images-field">' +
            '<label>Снимки:</label>' +
            '<input type="file" id="jobImagesInput" name="images[]" accept="image/*" multiple style="display:none">' +
            '<div class="images-toolbar">' +
              '<button type="button" id="btnPickImages" class="btn-small">Добави снимки</button>' +
              '<span class="images-hint">Плъзни, за да подредиш. Кликни ★ за корица.</span>' +
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

          '<label>Надник:</label>' +
          '<input type="number" name="price_per_day" placeholder="Въведете надник">' +

          '<label>Цена на квадрат:</label>' +
          '<input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">' +

          '<label>Описание:</label>' +
          '<textarea name="description" placeholder="Описание (незадължително)"></textarea>' +

          '<button type="submit">Запази обявата</button>' +
        '</form>';
    }

    formBox.innerHTML = formHTML;

    // 1) Пълним единичния select от централизиран JSON
    var singleSelect = document.getElementById('profession_single');
    if (singleSelect) {
        fetch('professions_json.php')
          .then(function(r){ return r.json(); })
          .then(function(items){
              singleSelect.querySelectorAll('option:not(:first-child)').forEach(function(o){ o.remove(); });
              items.forEach(function(it){
                  var opt = document.createElement('option');
                  opt.value = it.key;
                  opt.text  = it.label;
                  singleSelect.appendChild(opt);
              });
          })
          .catch(console.error);
    }

    // 2) Multi-select UI за фирми
    initCompanyMultiUI();

    // 3) Доп. полета при "seek"
    if (type === 'seek') {
        var teamSize  = document.getElementById('teamSize');
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
        // няма image manager в тази форма
        return;
    }

    // 4) Image manager (само при "offer", както до момента)
    var input = document.getElementById('jobImagesInput');
    var grid  = document.getElementById('imagesGrid');
    var btn   = document.getElementById('btnPickImages');
    var coverHidden = document.getElementById('cover_index');
    var form  = document.getElementById('jobForm');

    var files = [];
    var coverIndex = 0;
    var dragSrc = null;

    input.style.display = 'none';
    grid.classList.add('empty');

    btn.addEventListener('click', function(){ input.click(); });

    input.addEventListener('change', function (e) {
        var list = Array.from(e.target.files || []);
        list.forEach(function(f){
            if (f.type && f.type.indexOf('image/') === 0) files.push(f);
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
        if (dragSrc !== null) { dragSrc = null; return; }
        var dt = e.dataTransfer;
        if (!dt || !dt.files) return;
        Array.from(dt.files).forEach(function(f){
            if (f.type && f.type.indexOf('image/') === 0) files.push(f);
        });
        renderGrid();
    });

    function renderGrid() {
        grid.innerHTML = '';
        grid.classList.toggle('empty', files.length === 0);

        files.forEach(function(file, idx){
            var url  = URL.createObjectURL(file);
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

            tile.querySelector('.remove-btn').addEventListener('click', function(){
                try { URL.revokeObjectURL(url); } catch(e){}
                files.splice(idx, 1);
                if (files.length === 0) { coverIndex = 0; }
                else if (idx === coverIndex) { coverIndex = Math.min(coverIndex, files.length - 1); }
                else if (idx < coverIndex) { coverIndex -= 1; }
                renderGrid();
            });

            tile.querySelector('.cover-btn').addEventListener('click', function(){
                coverIndex = idx;
                renderGrid();
            });

            tile.addEventListener('dragstart', function(e){
                dragSrc = idx;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain','');
            });
            tile.addEventListener('dragover', function(e){ e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
            tile.addEventListener('drop', function(e){
                e.preventDefault();
                var target = idx;
                if (dragSrc === null || dragSrc === target) { dragSrc = null; return; }
                var moved = files.splice(dragSrc, 1)[0];
                files.splice(target, 0, moved);

                if (coverIndex === dragSrc) coverIndex = target;
                else if (dragSrc < coverIndex && target >= coverIndex) coverIndex -= 1;
                else if (dragSrc > coverIndex && target <= coverIndex) coverIndex += 1;

                dragSrc = null;
                renderGrid();
            });

            grid.appendChild(tile);
        });

        coverHidden.value = String(coverIndex);
    }

    form.addEventListener('submit', function(){
        if (files.length > 0) {
            var dt = new DataTransfer();
            files.forEach(function(f){ dt.items.add(f); });
            input.files = dt.files;
        }
        coverHidden.value = String(coverIndex >= 0 ? coverIndex : 0);
    });
}

// ===== Multi-select UI (Фирма) – общо за двете форми =====
function initCompanyMultiUI() {
    var isCompany   = document.getElementById('is_company');
    var singleBlock = document.getElementById('single-profession-block');
    var multiBlock  = document.getElementById('multi-profession-block');
    var singleSelect= document.getElementById('profession_single');

    if (!isCompany || !singleBlock || !multiBlock || !singleSelect) return;

    var ALL_PROFS   = [];
    var picked      = []; // ключове
    var profSearch  = document.getElementById('prof-search');
    var profSuggest = document.getElementById('prof-suggestions');
    var profChips   = document.getElementById('prof-chips');
    var profHidden  = document.getElementById('professions_json');

    fetch('professions_json.php')
      .then(function(r){ return r.json(); })
      .then(function(items){ ALL_PROFS = items; })
      .catch(console.error);

    isCompany.addEventListener('change', function(){
        var on = isCompany.checked;
        singleBlock.style.display = on ? 'none' : 'block';
        multiBlock.style.display  = on ? 'block' : 'none';
        if (on) singleSelect.removeAttribute('required'); else singleSelect.setAttribute('required','required');
    });

    if (profSearch) {
        profSearch.addEventListener('input', function(){
            var q = String(profSearch.value || '').toLowerCase().trim();
            if (!q) { profSuggest.style.display = 'none'; profSuggest.innerHTML=''; return; }
            var matches = ALL_PROFS.filter(function(x){
                return x.key.toLowerCase().indexOf(q) !== -1 || x.label.toLowerCase().indexOf(q) !== -1;
            }).slice(0, 20);

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
        });

        document.addEventListener('click', function(e){
            if (!profSuggest.contains(e.target) && e.target !== profSearch) {
                profSuggest.style.display = 'none';
            }
        });
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

        if (isCompany.checked && picked.length) {
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

    // подсигуряване при submit
    var form = document.getElementById('jobForm');
    form.addEventListener('submit', function(e){
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




// ======================= ИНИЦИАЛИЗАЦИЯ НА БУТОНИ =======================
document.addEventListener('DOMContentLoaded', () => {
    const allJobsBtn = document.getElementById('btn-all-jobs');
    const addJobBtn = document.getElementById('btn-add-job');
    const activeProjectsBtn = document.getElementById('active-projects-btn');
    const leftGroup = document.querySelector('.job-sub-buttons.all');
    const rightGroup = document.querySelector('.job-sub-buttons.add');

    allJobsBtn.classList.add('active');
    leftGroup.classList.add('show');
    loadJobs();

    allJobsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!allJobsBtn.classList.contains('active')) {
            allJobsBtn.classList.add('active');
            addJobBtn.classList.remove('active');
            leftGroup.classList.add('show');
            rightGroup.classList.remove('show');
        }
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
            leftGroup.classList.remove('show');
            rightGroup.classList.add('show');
        }
        document.getElementById('active-projects-section').style.display = 'none';
        document.getElementById('jobList').style.display = 'none';
        document.getElementById('jobFormContainer').style.display = 'block';
    });

    activeProjectsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        allJobsBtn.classList.remove('active');
        addJobBtn.classList.remove('active');
        leftGroup.classList.remove('show');
        rightGroup.classList.remove('show');

        document.getElementById('jobList').style.display = 'none';
        document.getElementById('jobFormContainer').style.display = 'none';
        document.getElementById('active-projects-section').style.display = 'block';
    });

    document.getElementById('btn-offer').addEventListener('click', (e) => { e.preventDefault(); loadJobs('offer'); });
    document.getElementById('btn-seek').addEventListener('click', (e) => { e.preventDefault(); loadJobs('seek'); });
    document.getElementById('btn-add-offer').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('offer'); });
    document.getElementById('btn-add-seek').addEventListener('click', (e) => { e.preventDefault(); loadJobForm('seek'); });

    document.getElementById('active-projects-section').style.display = 'none';
});
