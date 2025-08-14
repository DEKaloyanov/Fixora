<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Всички обяви | Fixora</title>
    <link rel="stylesheet" href="../css/all_jobs.css">
    <script src="../js/favorites.js" defer></script>
    <?php include 'navbar.php'; ?>
    <style>
        .hidden { display: none; }
        .filters-row { display:flex; flex-wrap:wrap; gap:15px; align-items:center; justify-content:center; }
        .filters-row input[type="number"] { width: 110px; }
        .filters-row input[type="date"] { width: 160px; }
        .toggle-adv { border: 1px solid #ccc; border-radius:8px; padding:8px 12px; background:#fff; cursor:pointer; }
        .badge-company {
            display:inline-block; margin-right:8px; padding:2px 8px; border-radius:999px;
            background:#eef4ff; color:#002147; font-size:12px; vertical-align:middle;
        }
        .profession-chips .chip {
            display:inline-block; margin:4px 6px 0 0; padding:2px 8px; border-radius:999px;
            background:#f3f3f3; font-size:12px;
        }
    </style>
</head>
<body>

<div class="jobs-wrapper">
    <div class="filters">
        <!-- Основни филтри -->
        <div class="filters-row">
            <select id="typeFilter" title="Тип">
                <option value="">Всички типове</option>
                <option value="offer">Предлагам работа</option>
                <option value="seek">Търся работа</option>
            </select>

            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="companyOnly"> Само фирми
            </label>

            <input id="placeFilter" list="placesList" placeholder="Град / населено място" style="min-width:220px;">
            <datalist id="placesList"></datalist>

            <select id="professionFilter" multiple size="8" style="min-width:260px;" title="Професии">
                <!-- пълни се динамично -->
            </select>

            <!-- НОВО: сортиране -->
            <select id="sortBy" title="Сортиране">
                <option value="newest">Най-нови</option>
                <option value="oldest">Най-стари</option>
                <option value="price_day_asc">Цена/ден ↑</option>
                <option value="price_day_desc">Цена/ден ↓</option>
                <option value="price_sq_asc">Цена/кв.м ↑</option>
                <option value="price_sq_desc">Цена/кв.м ↓</option>
                <option value="rating_desc">Рейтинг ↓</option>
                <option value="rating_asc">Рейтинг ↑</option>
            </select>

            <button id="toggleAdv" type="button" class="toggle-adv">Още филтри</button>
            <button id="clearFilters" type="button">Изчисти</button>
        </div>

        <!-- Разширени филтри (скрити по начало) -->
        <div id="advancedFilters" class="filters-row hidden" style="margin-top:12px;">
            <div class="price-group" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <span><strong>Надник:</strong></span>
                <input id="minDay" type="number" step="0.01" placeholder="мин">
                <input id="maxDay" type="number" step="0.01" placeholder="макс">

                <span style="margin-left:10px;"><strong>Цена/кв.м:</strong></span>
                <input id="minSq" type="number" step="0.01" placeholder="мин">
                <input id="maxSq" type="number" step="0.01" placeholder="макс">
            </div>

            <div class="rating-date-group" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <span><strong>Рейтинг (мин.):</strong></span>
                <select id="minRating" title="Минимален рейтинг">
                    <option value="">Всички</option>
                    <option value="1">1+ ⭐</option>
                    <option value="2">2+ ⭐</option>
                    <option value="3">3+ ⭐</option>
                    <option value="4">4+ ⭐</option>
                    <option value="5">5 ⭐</option>
                </select>

                <span style="margin-left:10px;"><strong>Дата:</strong></span>
                <input id="dateFrom" type="date" title="От дата">
                <input id="dateTo"   type="date" title="До дата">
            </div>
        </div>
    </div>

    <div id="all-jobs-container"></div>
</div>

<script>
(function () {
    const els = {
        type:        document.getElementById('typeFilter'),
        companyOnly: document.getElementById('companyOnly'),
        place:       document.getElementById('placeFilter'),
        prof:        document.getElementById('professionFilter'),
        sort:        document.getElementById('sortBy'),

        minDay:      document.getElementById('minDay'),
        maxDay:      document.getElementById('maxDay'),
        minSq:       document.getElementById('minSq'),
        maxSq:       document.getElementById('maxSq'),

        minRating:   document.getElementById('minRating'),
        dateFrom:    document.getElementById('dateFrom'),
        dateTo:      document.getElementById('dateTo'),

        advWrap:     document.getElementById('advancedFilters'),
        toggleAdv:   document.getElementById('toggleAdv'),
        clearBtn:    document.getElementById('clearFilters'),

        container:   document.getElementById('all-jobs-container'),
        placesList:  document.getElementById('placesList'),
    };

    function showAdv(open) {
        els.advWrap.classList.toggle('hidden', !open);
        els.toggleAdv.textContent = open ? 'Скрий филтрите' : 'Още филтри';
    }
    function anyAdvancedSet() {
        return !!(
            els.minDay.value || els.maxDay.value ||
            els.minSq.value  || els.maxSq.value  ||
            els.minRating.value || els.dateFrom.value || els.dateTo.value
        );
    }

    function populateProfessions() {
        return fetch('professions_json.php')
            .then(r => r.json())
            .then(items => {
                els.prof.innerHTML = '';
                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.key;
                    opt.textContent = it.label;
                    els.prof.appendChild(opt);
                });
            });
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
            });
    }

    function paramsFromUI() {
        const selectedProfs = Array.from(els.prof.selectedOptions).map(o => o.value);
        const p = new URLSearchParams();

        if (els.type.value)             p.set('type', els.type.value);
        if (els.companyOnly.checked)    p.set('companyOnly', '1');
        if (els.place.value.trim())     p.set('place', els.place.value.trim());
        if (selectedProfs.length)       p.set('profession', selectedProfs.join(','));

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

    function applyStateFromURL() {
        const q = new URLSearchParams(location.search);

        els.type.value = q.get('type') || '';
        els.companyOnly.checked = (q.get('companyOnly') === '1');
        els.place.value = q.get('place') || '';

        const profStr = q.get('profession') || '';
        const profArr = profStr ? profStr.split(',').map(s => s.trim()).filter(Boolean) : [];
        if (profArr.length) {
            Array.from(els.prof.options).forEach(opt => {
                opt.selected = profArr.includes(opt.value);
            });
        } else {
            els.prof.selectedIndex = -1;
        }

        els.minDay.value = q.get('minDay') || '';
        els.maxDay.value = q.get('maxDay') || '';
        els.minSq.value  = q.get('minSq')  || '';
        els.maxSq.value  = q.get('maxSq')  || '';

        els.minRating.value = q.get('minRating') || '';
        els.dateFrom.value  = q.get('dateFrom')  || '';
        els.dateTo.value    = q.get('dateTo')    || '';

        els.sort.value      = q.get('sort') || 'newest';

        showAdv(anyAdvancedSet());
    }

    function updateURLFromUI(replace = true) {
        const params = paramsFromUI();
        const qs = params.toString();
        const newURL = qs ? ('?' + qs) : location.pathname;
        if (replace) history.replaceState(null, '', newURL);
        else         history.pushState(null, '', newURL);
    }

    function loadAllJobs() {
        const params = paramsFromUI();
        const url = 'fetch_all_jobs.php' + (params.toString() ? ('?' + params.toString()) : '');
        fetch(url)
            .then(res => res.text())
            .then(html => {
                els.container.innerHTML = html;
                if (typeof attachFavoriteListeners === 'function') attachFavoriteListeners();
            })
            .catch(err => {
                console.error(err);
                els.container.innerHTML = '<p>Възникна грешка при зареждане.</p>';
            });
    }

    [els.type, els.companyOnly, els.prof, els.minDay, els.maxDay, els.minSq, els.maxSq, els.minRating, els.dateFrom, els.dateTo, els.sort]
        .forEach(el => {
            el.addEventListener('change', () => {
                updateURLFromUI(true);
                loadAllJobs();
                if ([els.minDay, els.maxDay, els.minSq, els.maxSq, els.minRating, els.dateFrom, els.dateTo].includes(el)) {
                    showAdv(anyAdvancedSet());
                }
            });
        });

    els.place.addEventListener('input', () => {
        updateURLFromUI(true);
        loadAllJobs();
    });

    els.toggleAdv.addEventListener('click', () => {
        const willOpen = els.advWrap.classList.contains('hidden');
        showAdv(willOpen);
    });

    els.clearBtn.addEventListener('click', () => {
        els.type.value = '';
        els.companyOnly.checked = false;
        els.place.value = '';
        els.prof.selectedIndex = -1;

        els.minDay.value = '';
        els.maxDay.value = '';
        els.minSq.value = '';
        els.maxSq.value = '';

        els.minRating.value = '';
        els.dateFrom.value = '';
        els.dateTo.value = '';

        els.sort.value = 'newest';

        showAdv(false);
        history.replaceState(null, '', location.pathname);
        loadAllJobs();
    });

    window.addEventListener('popstate', () => {
        applyStateFromURL();
        loadAllJobs();
    });

    document.addEventListener('DOMContentLoaded', () => {
        Promise.all([populateProfessions(), populatePlaces()])
            .then(() => {
                applyStateFromURL();
                loadAllJobs();
            });
    });
})();
</script>

</body>
</html>
