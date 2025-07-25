// Функция за зареждане на обяви
function loadJobs(type = '') {
    let target = 'jobList';
    if (type === 'offer') target = 'offerJobList';
    if (type === 'seek') target = 'seekJobList';
    
    fetch(`fetch_jobs.php${type ? '?type=' + type : ''}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById(target).innerHTML = html;
        })
        .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

// Функция за скриване на всички подменюта
function hideAllSubMenus() {
    document.querySelectorAll('.job-sub-buttons').forEach(el => {
        el.classList.remove('show');
    });
}

// Инициализация при зареждане на страницата
document.addEventListener("DOMContentLoaded", () => {
    const allJobsBtn = document.querySelector('[data-filter="all"]');
    const addJobBtn = document.querySelector('[data-filter="add"]');
    const allSubMenu = document.querySelector('.job-sub-buttons.all');
    const addSubMenu = document.querySelector('.job-sub-buttons.add');
    
    // Скриване на всички подменюта
    function hideAllSubMenus() {
        allSubMenu.classList.remove('show');
        addSubMenu.classList.remove('show');
    }
    
    // Клик върху Всички обяви
    allJobsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (allSubMenu.classList.contains('show')) {
            hideAllSubMenus();
        } else {
            hideAllSubMenus();
            allSubMenu.classList.add('show');
        }
    });
    
    // Клик върху Добави обява
    addJobBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (addSubMenu.classList.contains('show')) {
            hideAllSubMenus();
        } else {
            hideAllSubMenus();
            addSubMenu.classList.add('show');
        }
    });
    
    // Клик извън менюто
    document.addEventListener('click', function() {
        hideAllSubMenus();
    });
    
    // Зареждане на формите
    document.getElementById('btn-add-offer').addEventListener('click', function(e) {
        e.preventDefault();
        // Вашия код за формата
    });
    
    document.getElementById('btn-add-seek').addEventListener('click', function(e) {
        e.preventDefault();
        // Вашия код за формата
    });
});