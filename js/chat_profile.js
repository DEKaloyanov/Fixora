// js/chat_profile.js (страницата е в /php)
(function(){
  const grid = document.getElementById('mediaGrid');
  const btnMore = document.getElementById('loadMoreMedia');
  const dlg = document.getElementById('reportDialog');
  const frm = document.getElementById('reportForm');
  const btnCancel = document.getElementById('reportCancel');
  const muteToggle = document.getElementById('muteToggle');
  const blockToggle = document.getElementById('blockToggle');

  let offset = 0, limit = 24;

  function loadMedia(initial=false){
    const params = new URLSearchParams({
      with: grid.dataset.with, job: grid.dataset.job,
      offset, limit
    });
    fetch('fetch_shared_media.php?'+params.toString(), {credentials:'same-origin'})
      .then(r=>r.text())
      .then(html=>{
        if(initial) grid.innerHTML = '';
        grid.insertAdjacentHTML('beforeend', html);
        offset += limit;
      });
  }

  loadMedia(true);
  btnMore.addEventListener('click', ()=>loadMedia(false));

  // Report
  document.getElementById('reportBtn').addEventListener('click', ()=> dlg.showModal());
  btnCancel.addEventListener('click', ()=> dlg.close());
  frm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const fd = new FormData(frm);
    fd.append('with', CHAT_PROFILE.with);
    fd.append('job', CHAT_PROFILE.job);
    fetch('report_user.php', {method:'POST', body:fd, credentials:'same-origin'})
      .then(r=>r.text()).then(txt=>{
        alert(txt || 'Сигналът е подаден.');
        dlg.close(); frm.reset();
      });
  });

  // Mute
  muteToggle.addEventListener('change', ()=>{
    const fd = new FormData();
    fd.append('with', CHAT_PROFILE.with);
    fd.append('job', CHAT_PROFILE.job);
    fd.append('mute', muteToggle.checked ? '1' : '0');
    fetch('mute_conversation.php', {method:'POST', body:fd, credentials:'same-origin'})
      .then(r=>r.text()).then(txt=> { if (txt && txt!=='ok') alert(txt); });
  });

  // Block / Unblock
  blockToggle.addEventListener('click', ()=>{
    const fd = new FormData();
    fd.append('with', CHAT_PROFILE.with);
    fd.append('block', blockToggle.classList.contains('danger') ? '0' : '1');
    fetch('toggle_block_user.php', {method:'POST', body:fd, credentials:'same-origin'})
      .then(r=>r.text()).then(txt=>{
        if (txt==='ok') {
          const nowBlocked = !(blockToggle.classList.contains('danger'));
          blockToggle.classList.toggle('danger', nowBlocked);
          blockToggle.textContent = nowBlocked ? 'Деблокирай' : 'Блокирай';
          alert(nowBlocked ? 'Потребителят е блокиран.' : 'Потребителят е деблокиран.');
        } else if (txt) alert(txt);
      });
  });
})();
