// js/all_jobs.js

function fetchProfessionsAndFill(selectEl) {
  fetch('professions_json.php')
    .then(r => r.json())
    .then(items => {
      // изчисти
      selectEl.innerHTML = '';
      // добави placeholder като "всички" (при multiple няма selected)
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Всички професии';
      placeholder.disabled = true;
      placeholder.hidden = true;
      selectEl.appendChild(placeholder);

      items.forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.key;
        opt.textContent = it.label;
        selectEl.appendChild(opt);
      });
    })
    .catch(console.error);
}

function getSelectedValues(selectEl) {
  return Array.from(selectEl.selectedOptions).map(o => o.value).filter(Boolean);
}

function loadAllJobs() {
  const typeSel   = document.getElementById('typeFilter');
  const companyCb = document.getElementById('companyOnly');
  const profSel   = document.getElementById('professionFilter');

  const type = typeSel.value;
  const company = companyCb.checked ? '1' : '';
  // ако е фирма → позволяваме много, иначе ако случайно са избрани повече — взимаме само първата
  let professions = getSelectedValues(profSel);
  if (!company) {
    professions = professions.slice(0, 1);
  }

  const params = new URLSearchParams();
  if (type) params.set('type', type);
  if (company) params.set('company', '1');
  if (professions.length > 0) params.set('professions', professions.join(','));

  fetch('fetch_all_jobs.php?' + params.toString())
    .then(res => res.text())
    .then(html => {
      document.getElementById('all-jobs-container').innerHTML = html;
      if (typeof attachFavoriteListeners === 'function') attachFavoriteListeners();
    })
    .catch(console.error);
}

document.addEventListener('DOMContentLoaded', () => {
  const companyCb = document.getElementById('companyOnly');
  const profSel   = document.getElementById('professionFilter');

  // Първоначално напълване
  fetchProfessionsAndFill(profSel);

  // По умолчание: multiple изключен (само ако е фирма ще е включен)
  profSel.multiple = false;

  // Слушатели
  document.getElementById('typeFilter').addEventListener('change', loadAllJobs);
  document.getElementById('professionFilter').addEventListener('change', loadAllJobs);

  companyCb.addEventListener('change', () => {
    // превключваме multiple
    profSel.multiple = companyCb.checked;
    // ако вече има много избрани, а махнем чекбокса → оставяме само първата избрана
    if (!companyCb.checked) {
      const selected = getSelectedValues(profSel);
      const keep = selected[0] || '';
      Array.from(profSel.options).forEach(opt => opt.selected = false);
      if (keep) {
        const toKeep = Array.from(profSel.options).find(o => o.value === keep);
        if (toKeep) toKeep.selected = true;
      }
    }
    loadAllJobs();
  });

  // първо зареждане
  loadAllJobs();
});
