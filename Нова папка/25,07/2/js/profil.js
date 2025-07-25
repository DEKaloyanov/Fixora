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
        el.classList.remove('show');
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const allJobsBtn = document.querySelector('[data-filter="all"]');
    const addJobBtn = document.querySelector('[data-filter="add"]');
    const allSubMenu = document.querySelector('.job-sub-buttons.all');
    const addSubMenu = document.querySelector('.job-sub-buttons.add');

    allJobsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (allSubMenu.classList.contains('show')) {
            hideAllSubMenus();
        } else {
            hideAllSubMenus();
            allSubMenu.classList.add('show');
        }
    });

    addJobBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (addSubMenu.classList.contains('show')) {
            hideAllSubMenus();
        } else {
            hideAllSubMenus();
            addSubMenu.classList.add('show');
        }
    });

    document.addEventListener('click', function () {
        hideAllSubMenus();
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

    document.getElementById('btn-add-offer').addEventListener('click', function (e) {
        e.preventDefault();
        hideAllSubMenus();
        loadJobForm('offer');
    });

    document.getElementById('btn-add-seek').addEventListener('click', function (e) {
        e.preventDefault();
        hideAllSubMenus();
        loadJobForm('seek');
    });

    // Зарежда всички по подразбиране
    loadJobs();
});

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
