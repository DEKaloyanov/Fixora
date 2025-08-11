// Зареждане на обяви с филтър (всички, offer, seek)
function loadJobs(type = '') {
    let url = 'fetch_jobs.php';
    if (type) url += '?type=' + type;

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = html;
            document.getElementById('jobFormContainer').innerHTML = '';




            // Добавяне на икони към мета данните
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

// Зареждане на формата за добавяне
function loadJobForm(type) {
    const formHTML = type === 'offer' ? `
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
            <label>Снимки:</label>
            <input type="file" name="images[]" multiple accept="image/*">
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

    document.getElementById('jobList').innerHTML = '';
    document.getElementById('jobFormContainer').innerHTML = formHTML;

    // Напълни падащото меню с професии за новата форма
    const selectProfession = document.querySelector('#jobFormContainer select[name="profession"]');
    if (selectProfession) {
        // Запази първата опция (placeholder), изчисти останалото
        selectProfession.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());

        fetch('professions_json.php') // относителен път от php/profil.php
            .then(r => r.json())
            .then(items => {
                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.key;      // напр. 'zidar'
                    opt.textContent = it.label; // напр. 'Зидар'
                    selectProfession.appendChild(opt);
                });
            })
            .catch(console.error);
    }


    if (type === 'seek') {
        document.getElementById('teamSize').addEventListener('input', function () {
            const container = document.getElementById('teamMemberFields');
            container.innerHTML = '';
            for (let i = 1; i <= this.value; i++) {
                container.innerHTML += `<input type="text" name="team_member_${i}" placeholder="Име на работник ${i}" required>`;
            }
        });
    }

    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const previewContainer = document.createElement('div');
            previewContainer.className = 'image-preview';
            this.parentNode.appendChild(previewContainer);
            previewContainer.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    previewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    }

}

document.addEventListener("DOMContentLoaded", () => {
    const allJobsBtn = document.getElementById('btn-all-jobs');
    const addJobBtn = document.getElementById('btn-add-job');
    const activeProjectsBtn = document.getElementById('active-projects-btn');
    const leftGroup = document.querySelector('.job-sub-buttons.all');
    const rightGroup = document.querySelector('.job-sub-buttons.add');

    // Първоначално състояние
    allJobsBtn.classList.add('active');
    leftGroup.classList.add('show');
    loadJobs();

    // Бутон за всички обяви
    allJobsBtn.addEventListener('click', function (e) {
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

    // Бутон за добавяне на обява
    addJobBtn.addEventListener('click', function (e) {
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

    // Бутон за активни обяви
    activeProjectsBtn.addEventListener('click', function (e) {
        e.preventDefault();
        allJobsBtn.classList.remove('active');
        addJobBtn.classList.remove('active');
        leftGroup.classList.remove('show');
        rightGroup.classList.remove('show');

        document.getElementById('jobList').style.display = 'none';
        document.getElementById('jobFormContainer').style.display = 'none';
        document.getElementById('active-projects-section').style.display = 'block';
    });

    // Бутони за зареждане на обяви
    document.getElementById('btn-offer').addEventListener('click', function (e) {
        e.preventDefault();
        loadJobs('offer');
    });

    document.getElementById('btn-seek').addEventListener('click', function (e) {
        e.preventDefault();
        loadJobs('seek');
    });

    // Бутони за добавяне на обяви
    document.getElementById('btn-add-offer').addEventListener('click', function (e) {
        e.preventDefault();
        loadJobForm('offer');
    });

    document.getElementById('btn-add-seek').addEventListener('click', function (e) {
        e.preventDefault();
        loadJobForm('seek');
    });

    // Скрий активните обяви по начало
    document.getElementById('active-projects-section').style.display = 'none';
});
