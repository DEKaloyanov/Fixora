// js/all_jobs.js
(function () {
  const els = {
    // основни
    type:        document.getElementById('typeFilter'),
    companyOnly: document.getElementById('companyOnly'),
    place:       document.getElementById('placeFilter'),

    // професии
    mainProf:    document.getElementById('mainProfessionFilter'),
    subProf:     document.getElementById('subProfessionFilter'),
    subProfWrap: document.getElementById('subProfWrap'),

    // цени/рейтинг/дата
    minDay:      document.getElementById('minDay'),
    maxDay:      document.getElementById('maxDay'),
    minSq:       document.getElementById('minSq'),
    maxSq:       document.getElementById('maxSq'),
    minRating:   document.getElementById('minRating'),
    dateFrom:    document.getElementById('dateFrom'),
    dateTo:      document.getElementById('dateTo'),

    // сортиране
    sort:        document.getElementById('sortBy'),

    // действия
    applyBtn:    document.getElementById('applyFilters'),
    clearBtn:    document.getElementById('clearFilters'),

    // контейнер
    container:   document.getElementById('all-jobs-container'),
    placesList:  document.getElementById('placesList'),

    form:        document.getElementById('filtersForm')
  };

  /* ---------------- помощни ---------------- */
  function paramsFromUI() {
    const p = new URLSearchParams();

    if (els.type.value)             p.set('type', els.type.value);
    if (els.companyOnly.checked)    p.set('company_only', '1');
    if (els.place.value.trim())     p.set('place', els.place.value.trim());

    // ако има избрана подпрофесия – тя води
    if (!els.subProf.classList.contains('hidden') && els.subProf.value) {
      p.set('sub', els.subProf.value);
    } else if (els.mainProf.value) {
      p.set('main', els.mainProf.value);
    }

    if (els.minDay.value)           p.set('minDay', els.minDay.value);
    if (els.maxDay.value)           p.set('maxDay', els.maxDay.value);
    if (els.minSq.value)            p.set('minSq',  els.minSq.value);
    if (els.maxSq.value)            p.set('maxSq',  els.maxSq.value);

    if (els.minRating.value)        p.set('minRating', els.minRating.value);
    if (els.dateFrom.value)         p.set('dateFrom', els.dateFrom.value);
    if (els.dateTo.value)           p.set('dateTo',   els.dateTo.value);

    if (els.sort.value)             p.set('sort', els.sort.value);

    return p;
  }

  function updateURLFromUI(replace = true) {
    const qs = paramsFromUI().toString();
    const url = qs ? ('?' + qs) : location.pathname;
    if (replace) history.replaceState(null, '', url);
    else         history.pushState(null, '', url);
  }

  function applyStateFromURL() {
    const q = new URLSearchParams(location.search);

    els.type.value           = q.get('type') || '';
    els.companyOnly.checked  = (q.get('company_only') === '1');
    els.place.value          = q.get('place') || '';

    els.minDay.value         = q.get('minDay') || '';
    els.maxDay.value         = q.get('maxDay') || '';
    els.minSq.value          = q.get('minSq')  || '';
    els.maxSq.value          = q.get('maxSq')  || '';
    els.minRating.value      = q.get('minRating') || '';
    els.dateFrom.value       = q.get('dateFrom')  || '';
    els.dateTo.value         = q.get('dateTo')    || '';

    els.sort.value           = q.get('sort') || 'newest';

    // избираме main/sub след като напълним списъците
    const main = q.get('main') || '';
    const sub  = q.get('sub')  || '';

    if (main) {
      els.mainProf.value = main;
      return loadSubFor(main).then(() => {
        if (sub) {
          els.subProf.value = sub;
          if (!els.subProf.value) {
            els.subProf.classList.add('hidden');
            els.subProfWrap.classList.add('hidden');
            els.subProf.value = '';
          }
        }
      });
    } else {
      els.mainProf.value = '';
      els.subProf.classList.add('hidden');
      els.subProfWrap.classList.add('hidden');
      els.subProf.value = '';
      return Promise.resolve();
    }
  }

  function loadAllJobs() {
    const qs = paramsFromUI().toString();
    const url = 'fetch_all_jobs.php' + (qs ? ('?' + qs) : '');
    fetch(url)
      .then(r => r.text())
      .then(html => {
        els.container.innerHTML = html;
        if (typeof attachFavoriteListeners === 'function') attachFavoriteListeners();
      })
      .catch(() => {
        els.container.innerHTML = '<p>Възникна грешка при зареждане.</p>';
      });
  }

  /* ---------------- данни за филтрите ---------------- */
  function loadMainCategories() {
    return fetch('professions_json.php')
      .then(r => r.json())
      .then(list => {
        [...els.mainProf.querySelectorAll('option:not(:first-child)')].forEach(o => o.remove());
        list.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item.key;
          opt.textContent = item.count ? `${item.label} (${item.count})` : item.label;
          els.mainProf.appendChild(opt);
        });
      })
      .catch(console.error);
  }

  function loadSubFor(mainKey) {
    if (!mainKey) {
      els.subProf.classList.add('hidden');
      els.subProfWrap.classList.add('hidden');
      [...els.subProf.querySelectorAll('option:not(:first-child)')].forEach(o => o.remove());
      els.subProf.value = '';
      return Promise.resolve();
    }
    return fetch('professions_json.php?children_of=' + encodeURIComponent(mainKey))
      .then(r => r.json())
      .then(list => {
        [...els.subProf.querySelectorAll('option:not(:first-child)')].forEach(o => o.remove());
        if (!Array.isArray(list) || list.length === 0) {
          els.subProf.classList.add('hidden');
          els.subProfWrap.classList.add('hidden');
          els.subProf.value = '';
          return;
        }
        list.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item.key;
          opt.textContent = item.label;
          els.subProf.appendChild(opt);
        });
        els.subProf.classList.remove('hidden');
        els.subProfWrap.classList.remove('hidden');
      })
      .catch(console.error);
  }

  function populatePlaces() {
    return fetch('places_json.php')
      .then(r => r.json())
      .then(list => {
        els.placesList.innerHTML = '';
        list.forEach(name => {
          const opt = document.createElement('option');
          opt.value = name;
          els.placesList.appendChild(opt);
        });
      })
      .catch(() => {/* не е задължително */});
  }

  /* ---------------- събития ---------------- */
  els.mainProf.addEventListener('change', () => {
    loadSubFor(els.mainProf.value);
  });

  // Enter в който и да е input в панела => Приложи
  els.form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      els.applyBtn.click();
    }
  });

  // Приложи
  els.applyBtn.addEventListener('click', () => {
    updateURLFromUI(true);
    loadAllJobs();
  });

  // Изчисти
  els.clearBtn.addEventListener('click', () => {
    els.type.value = '';
    els.companyOnly.checked = false;
    els.place.value = '';

    els.mainProf.value = '';
    els.subProf.value = '';
    els.subProf.classList.add('hidden');
    els.subProfWrap.classList.add('hidden');

    els.minDay.value = '';
    els.maxDay.value = '';
    els.minSq.value  = '';
    els.maxSq.value  = '';
    els.minRating.value = '';
    els.dateFrom.value  = '';
    els.dateTo.value    = '';

    els.sort.value = 'newest';

    history.replaceState(null, '', location.pathname);
    loadAllJobs();
  });

  // назад/напред в браузъра запазва филтрите
  window.addEventListener('popstate', () => {
    applyStateFromURL().then(loadAllJobs);
  });

  /* ---------------- boot ---------------- */
  document.addEventListener('DOMContentLoaded', () => {
    Promise.all([loadMainCategories(), populatePlaces()])
      .then(() => applyStateFromURL())
      .then(loadAllJobs);
  });
})();

/* ---------------- Навигация при клик по карта ----------------
   Важно: ignore при клик върху .favorite-icon / .favorite-heart,
   .edit-btn и формата ѝ, както и върху <a> линкове вътре.
---------------------------------------------------------------- */
window.handleCardClick = function (event, jobId) {
  const t = event.target;
  if (
    t.closest('.favorite-icon') ||
    t.closest('.favorite-heart') ||
    t.closest('.edit-btn') ||
    t.closest('form') ||
    t.closest('a')
  ) {
    return;
  }
  window.location.href = 'job_details.php?id=' + jobId;
};
