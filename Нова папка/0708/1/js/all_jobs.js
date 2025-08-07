
function handleCardClick(event, jobId) {
    const target = event.target;

    // Ако е сърце или бутон – не отваряй страницата
    if (
        target.closest('.favorite-icon') ||
        target.classList.contains('edit-btn') ||
        target.closest('form')
    ) {
        return;
    }

    // Пренасочване към страницата с детайли
    window.location.href = `job_details.php?id=${jobId}`;
}

function loadAllJobs() {
    const type = document.getElementById('typeFilter').value;
    const profession = document.getElementById('professionFilter').value;

    fetch(`fetch_all_jobs.php?type=${type}&profession=${profession}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('all-jobs-container').innerHTML = data;
            attachFavoriteListeners();
        });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('typeFilter').addEventListener('change', loadAllJobs);
    document.getElementById('professionFilter').addEventListener('change', loadAllJobs);
    loadAllJobs();
});
