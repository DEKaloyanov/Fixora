// ===== ЗАРЕЖДАНЕ НА ОБЯВИ ЗА КОНКРЕТЕН ПОТРЕБИТЕЛ =====
function loadUserJobs(userId, type = '') {
    let url = 'fetch_user_jobs.php?user_id=' + encodeURIComponent(String(userId));
    if (type) url += '&type=' + encodeURIComponent(type);

    fetch(url, { credentials: 'same-origin' })
        .then(res => res.text())
        .then(html => {
            document.getElementById('jobList').innerHTML = html;
        })
        .catch(err => console.error('Грешка при зареждане на обяви:', err));
}

// ===== ЗАРЕЖДАНЕ НА ИСТОРИЯ =====
function loadUserHistory(userId) {
    const url = 'fetch_user_history.php?user_id=' + encodeURIComponent(String(userId));
    fetch(url, { credentials: 'same-origin' })
        .then(r => r.text())
        .then(html => { document.getElementById('history-section').innerHTML = html; })
        .catch(() => { /* тихо */ });
}

document.addEventListener('DOMContentLoaded', () => {
    const uid = typeof VIEW_USER_ID !== 'undefined' ? VIEW_USER_ID : 0;
    if (!uid) return;

    const allJobsBtn = document.getElementById('btn-all-jobs');
    const offerBtn = document.getElementById('btn-offer');
    const seekBtn = document.getElementById('btn-seek');
    const histBtn = document.getElementById('btn-history');

    // Начално състояние
    allJobsBtn.classList.add('active');
    loadUserJobs(uid);
    loadUserHistory(uid);

    // Активна визия на избрания филтър
    function setActive(btn) {
        [allJobsBtn, offerBtn, seekBtn].forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
    }

    allJobsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        setActive(allJobsBtn);
        loadUserJobs(uid);
        // показваме списъка с обяви, не скриваме историята
        document.getElementById('jobList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // „Предлагам работа“ -> показвай SEEK (тип "seek")
    offerBtn.addEventListener('click', (e) => {
        e.preventDefault();
        setActive(offerBtn);
        loadUserJobs(uid, 'seek');
        document.getElementById('jobList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // „Търся работа“ -> показвай OFFER (тип "offer")
    seekBtn.addEventListener('click', (e) => {
        e.preventDefault();
        setActive(seekBtn);
        loadUserJobs(uid, 'offer');
        document.getElementById('jobList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Предишни дейности – скрол до секцията
    histBtn.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('history-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Клик по карта -> job_details (игнорирай heart)
    document.getElementById('jobList').addEventListener('click', (e) => {
        // Ако е кликнато върху иконата със сърце – не навигираме,
        // оставяме favorites.js да обработи добавяне/махане от любими.
        if (e.target.closest('.favorite-icon')) return;

        const card = e.target.closest('.job-card');
        if (!card) return;

        const id = card.getAttribute('data-job-id');
        if (id) window.location.href = 'job_details.php?id=' + encodeURIComponent(id);
    });
});
