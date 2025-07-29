
// Зареждане на обяви с филтър (всички, offer, seek)
function loadJobs(type = '') {
    let url = 'fetch_jobs.php';
    if (type) url += '?type=' + type;

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = html;
            document.getElementById('jobFormContainer').innerHTML = ''; // Скрива формата
        })
        .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

// Скрива всички подменюта
function hideAllSubMenus() {
    document.querySelectorAll('.job-sub-buttons').forEach(el => {
        el.style.display = 'none';
    });
}

// Показва конкретно подменю
function showSubMenu(menuClass) {
    hideAllSubMenus();
    document.querySelector(`.job-sub-buttons.${menuClass}`).style.display = 'flex';
}

// Зареждане на формата за добавяне
function loadJobForm(type) {
    fetch('php/load_job_form.php?type=' + type)
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = ''; // Скрива обявите
            document.getElementById('jobFormContainer').innerHTML = html;
        })
        .catch(err => console.error('Грешка при зареждане на форма:', err));
}

document.addEventListener("DOMContentLoaded", () => {
    const allJobsBtn = document.getElementById('btn-all-jobs');
    const addJobBtn = document.getElementById('btn-add-job');

    const leftGroup = document.querySelector('.job-sub-buttons.all');
    const rightGroup = document.querySelector('.job-sub-buttons.add');

    // Запазваме текущо активната група (left, right или null)
    let activeGroup = 'left';

    // Първоначално състояние
    leftGroup.classList.add('show');
    rightGroup.classList.remove('show');

    // Зарежда всички обяви при първоначално зареждане
    loadJobs();

    // Бутон за всички обяви
    allJobsBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (activeGroup !== 'left') {
            activeGroup = 'left';
            allJobsBtn.classList.add('active');
            addJobBtn.classList.remove('active');
            loadJobs();
            leftGroup.classList.add('show');
            rightGroup.classList.remove('show');
        }
    });

    // Бутон за добавяне на обява
    addJobBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (activeGroup !== 'right') {
            activeGroup = 'right';
            addJobBtn.classList.add('active');
            allJobsBtn.classList.remove('active');
            document.getElementById('jobList').innerHTML = '';
            leftGroup.classList.remove('show');
            rightGroup.classList.add('show');
        }
    });

    // Бутони за зареждане на обяви
    document.getElementById('btn-offer').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobs('offer');
    });

    document.getElementById('btn-seek').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobs('seek');
    });

    document.getElementById('btn-add-offer').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobForm('offer');
        const form = document.getElementById('jobFormContainer');
        form.querySelector('input[type="file"]').addEventListener('change', function () {
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
    });

    document.getElementById('btn-add-seek').addEventListener('click', function(e) {
        e.preventDefault();
        loadJobForm('seek');
    });
});
