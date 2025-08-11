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

            // Тригер анимация на "Любими"
            const favoritesLink = document.getElementById("favorites-link");
            if (favoritesLink) {
                favoritesLink.classList.add("animate-favorite");
                setTimeout(() => {
                    favoritesLink.classList.remove("animate-favorite");
                }, 700); // Продължителност на анимацията
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

// Вътре в блока: if (!isCurrentlyFavorite) { ... }
if (!isCurrentlyFavorite) {
    heart.src = "../img/heart-filled.png";
    heart.alt = "Премахни от любими";
    heart.title = "Премахни от любими";
    heart.classList.add("favorited");

    // 🔔 Добавяне на анимация към линка "Любими"
    const favLink = document.querySelector('a[href$="favorites.php"]');
    if (favLink) {
        favLink.classList.add("animate-favorite");
        setTimeout(() => {
            favLink.classList.remove("animate-favorite");
        }, 600);
    }
}


document.addEventListener("DOMContentLoaded", () => {
  document.body.addEventListener("click", function (e) {
    const removeBtn = e.target.closest(".remove-favorite");

    if (removeBtn) {
      e.stopPropagation();
      const jobCard = removeBtn.closest(".job-card");
      const jobId = jobCard.dataset.jobId;

      // AJAX заявка за премахване
      fetch("remove_favorite.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "job_id=" + encodeURIComponent(jobId)
      })
        .then(res => res.text())
        .then(result => {
          if (result === "removed") {
            // Премахване на елемента визуално
            jobCard.remove();
          }
        });
    }
  });
});


