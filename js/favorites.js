document.addEventListener("DOMContentLoaded", () => {
  document.body.addEventListener("click", function (e) {
    const heart = e.target.closest("img.favorite-heart");

    if (heart && !heart.dataset.locked) {
      e.stopPropagation();

      const jobId = heart.dataset.jobId;
      const isCurrentlyFavorite = heart.classList.contains("favorited");

      // Заключване на бутона за кратко
      heart.dataset.locked = "true";

      // Смяна на изображението веднага (визуален ефект)
      if (isCurrentlyFavorite) {
        heart.src = "../img/heart-outline.png";
        heart.alt = "Добави в любими";
        heart.title = "Добави в любими";
        heart.classList.remove("favorited");
      } else {
        heart.src = "../img/heart-filled.png";
        heart.alt = "Премахни от любими";
        heart.title = "Премахни от любими";
        heart.classList.add("favorited");
      }

      // AJAX към toggle_favorite.php
      fetch("toggle_favorite.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "job_id=" + encodeURIComponent(jobId)
      })
        .finally(() => {
          // Отключваме сърцето след 300ms
          setTimeout(() => {
            delete heart.dataset.locked;
          }, 300);
        });
    }
  });

  // Глобална логика за отваряне на job_details.php
  document.body.addEventListener("click", function (e) {
    const card = e.target.closest(".job-card");

    // Игнорирай ако сме кликнали на сърце или бутон за редакция
    if (
      card &&
      !e.target.closest(".favorite-icon") &&
      !e.target.closest(".edit-btn")
    ) {
      const jobId = card.dataset.jobId;
      if (jobId) {
        window.location.href = "job_details.php?id=" + jobId;
      }
    }
  });
});
