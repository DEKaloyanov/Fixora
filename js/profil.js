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
    const jobList = document.getElementById('jobList');
    const formBox = document.getElementById('jobFormContainer');
    jobList.innerHTML = '';
    formBox.style.display = 'block';

    const formHTML = (type === 'offer') ? `
<form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="job_type" value="offer">

  <label>Тип работа:</label>
  <select name="profession" required>
    <option value="">Избери тип работа</option>
  </select>

  <label>Населено място:</label>
  <input type="text" name="location" required placeholder="Изберете град">

  <label>Надник:</label>
  <input type="number" name="price_per_day" placeholder="Въведете надник">

  <label>Цена на квадрат:</label>
  <input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">

  <!-- Image Manager -->
  <div class="images-field">
    <label>Снимки:</label>
    <input type="file" id="jobImagesInput" name="images[]" accept="image/*" multiple style="display:none">
    <div class="images-toolbar">
      <button type="button" id="btnPickImages" class="btn-small">Добави снимки</button>
      <span class="images-hint">Плъзни, за да подредиш. Кликни ★ за корица.</span>
    </div>
    <div id="imagesGrid" class="images-grid" data-empty="Пусни снимки тук"></div>
    <input type="hidden" name="cover_index" id="cover_index" value="0">
  </div>

  <label>Описание:</label>
  <textarea name="description" placeholder="Описание (незадължително)"></textarea>

  <button type="submit">Запази обявата</button>
</form>
` : `
<form class="job-form" id="jobForm" action="save_job.php" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="job_type" value="seek">

  <label>Тип работа:</label>
  <select name="profession" required>
    <option value="">Избери тип работа</option>
  </select>

  <label>Населено място:</label>
  <input type="text" name="city" required placeholder="Изберете град">

  <label>Брой работници:</label>
  <input type="number" name="team_size" id="teamSize" min="1" max="20" value="1" required>
  <div id="teamMemberFields"></div>

  <label>Надник:</label>
  <input type="number" name="price_per_day" placeholder="Въведете надник">

  <label>Цена на квадрат:</label>
  <input type="number" name="price_per_square" placeholder="Въведете цена за квадрат">

  <label>Описание:</label>
  <textarea name="description" placeholder="Описание (незадължително)"></textarea>

  <button type="submit">Запази обявата</button>
</form>
`;

    formBox.innerHTML = formHTML;

    // Напълни падащото меню с професии (централизиран файл)
    const selectProfession = document.querySelector('#jobFormContainer select[name="profession"]');
    if (selectProfession) {
        fetch('professions_json.php')   // файлът е в същата папка като profil.php
            .then(r => r.json())
            .then(items => {
                selectProfession.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.key;
                    opt.textContent = it.label;
                    selectProfession.appendChild(opt);
                });
            })
            .catch(console.error);
    }

    // Допълнителни полета за "seek"
    if (type === 'seek') {
        const teamSize = document.getElementById('teamSize');
        const container = document.getElementById('teamMemberFields');
        teamSize.addEventListener('input', function () {
            container.innerHTML = '';
            for (let i = 1; i <= this.value; i++) {
                container.innerHTML += `<input type="text" name="team_member_${i}" placeholder="Име на работник ${i}" required>`;
            }
        });
        return; // няма image manager в тази форма
    }

    // ======================= Image Manager (само за "offer") =======================
    const input = document.getElementById('jobImagesInput');
    const grid = document.getElementById('imagesGrid');
    const btnPick = document.getElementById('btnPickImages');
    const coverHidden = document.getElementById('cover_index');
    const form = document.getElementById('jobForm');

    let files = [];
    let coverIndex = 0;
    let dragSrc = null;

    // винаги скрит резервния input
    input.style.display = 'none';
    grid.classList.add('empty');

    btnPick.addEventListener('click', () => input.click());

    input.addEventListener('change', (e) => {
        const list = Array.from(e.target.files || []);
        for (const f of list) {
            if (f.type && f.type.startsWith('image/')) files.push(f);
        }
        renderGrid();
        input.value = ''; // позволи повторен избор на същите файлове
    });

    // Drop нови файлове в зоната
    ['dragenter', 'dragover'].forEach(ev =>
        grid.addEventListener(ev, (e) => { e.preventDefault(); grid.classList.add('dragging'); })
    );
    ['dragleave', 'drop'].forEach(ev =>
        grid.addEventListener(ev, (e) => { e.preventDefault(); grid.classList.remove('dragging'); })
    );
    grid.addEventListener('drop', (e) => {
        e.preventDefault();
        grid.classList.remove('dragging');

        // ако това е вътрешно пренареждане, не добавяме „файлове“
        if (dragSrc !== null) { dragSrc = null; return; }

        const dt = e.dataTransfer;
        if (!dt || !dt.files || dt.files.length === 0) return;

        for (const f of Array.from(dt.files)) {
            if (f.type && f.type.startsWith('image/')) files.push(f);
        }
        renderGrid();
    });


    function renderGrid() {
        grid.innerHTML = '';
        grid.classList.toggle('empty', files.length === 0);

        files.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            const tile = document.createElement('div');
            tile.className = 'img-tile' + (idx === coverIndex ? ' is-cover' : '');
            tile.draggable = true;
            tile.dataset.idx = idx;

            tile.innerHTML = `
        <img src="${url}" alt="Снимка">
        <button type="button" class="remove-btn" title="Премахни">×</button>
        <button type="button" class="cover-btn" title="Задай като корица">★</button>
        <span class="badge">Корица</span>
      `;

            // забраняваме drag на самото <img>, за да влачим само плочката
            tile.querySelector('img').draggable = false;
            tile.querySelector('img').addEventListener('dragstart', (e) => e.preventDefault());


            // Премахване
            tile.querySelector('.remove-btn').onclick = () => {
                URL.revokeObjectURL(url);
                files.splice(idx, 1);
                if (files.length === 0) { coverIndex = 0; }
                else if (idx === coverIndex) { coverIndex = Math.min(coverIndex, files.length - 1); }
                else if (idx < coverIndex) { coverIndex -= 1; }
                renderGrid();
            };

            // Корица
            tile.querySelector('.cover-btn').onclick = () => {
                coverIndex = idx;
                renderGrid();
            };

            // Пренареждане
            tile.addEventListener('dragstart', (e) => {
                dragSrc = idx;
                e.dataTransfer.effectAllowed = 'move';
                // някои браузъри изискват setData, за да не го третират като линк/картинка
                e.dataTransfer.setData('text/plain', '');
            });

            tile.addEventListener('dragover', (e) => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
            tile.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation(); // важно: да не стига до грида
                const target = parseInt(e.currentTarget.dataset.idx, 10);
                if (dragSrc === null || dragSrc === target) { dragSrc = null; return; }

                const moved = files.splice(dragSrc, 1)[0];
                files.splice(target, 0, moved);

                // Коригиране на корицата според новия ред
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

    // Подготвяме файловете в избрания от нас ред + корица
    form.addEventListener('submit', () => {
        if (files.length > 0) {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            input.files = dt.files;
        }
        coverHidden.value = String(coverIndex >= 0 ? coverIndex : 0);
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
