// ===== Тъмбове -> главна снимка =====
document.addEventListener('click', (e) => {
  const t = e.target;
  if (!t.classList.contains('jd-thumb')) return;
  const idx = Number(t.getAttribute('data-index') || '0') || 0;
  setMainIndex(idx);
});

document.getElementById('jdCopyLink')?.addEventListener('click', async (e) => {
  const btn = e.currentTarget;
  const url = btn.getAttribute('data-url') || window.location.href;
  try {
    await navigator.clipboard.writeText(url);
    btn.innerHTML = '<i class="fas fa-check"></i> Копирано!';
    setTimeout(() => btn.innerHTML = '<i class="fas fa-link"></i> Копирай линк', 1500);
  } catch {
    const ta = document.createElement('textarea');
    ta.value = url; document.body.appendChild(ta);
    ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
    btn.innerHTML = '<i class="fas fa-check"></i> Копирано!';
    setTimeout(() => btn.innerHTML = '<i class="fas fa-link"></i> Копирай линк', 1500);
  }
});

// ===== Карусел (пред лайтбокса) =====
const gallery = document.getElementById('jdGallery');
let G_IMAGES = [];
if (gallery) {
  try { G_IMAGES = JSON.parse(gallery.dataset.images || '[]'); } catch {}
  if (!Array.isArray(G_IMAGES) || !G_IMAGES.length) {
    const cur = document.getElementById('jdMainImage')?.getAttribute('src');
    if (cur) G_IMAGES = [cur];
  }

  const btnPrev = gallery.querySelector('.jd-g-prev');
  const btnNext = gallery.querySelector('.jd-g-next');
  btnPrev?.addEventListener('click', () => setMainIndex(getMainIndex() - 1));
  btnNext?.addEventListener('click', () => setMainIndex(getMainIndex() + 1));

  // Отваряне на лайтбокс при клик върху голямото изображение
  document.getElementById('jdMainImage')?.addEventListener('click', () => {
    openLightbox(getMainIndex());
  });

  // Swipe на мобилно
  let startX = null;
  document.querySelector('.jd-main-img')?.addEventListener('touchstart', e => {
    startX = e.touches[0].clientX;
  });
  document.querySelector('.jd-main-img')?.addEventListener('touchend', e => {
    if (startX == null) return;
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 40) setMainIndex(getMainIndex() + (dx < 0 ? 1 : -1));
    startX = null;
  });
}

function getMainIndex(){
  const main = document.getElementById('jdMainImage');
  return Number(main?.dataset.index || '0') || 0;
}
function setMainIndex(i){
  if (!G_IMAGES.length) return;
  const main = document.getElementById('jdMainImage');
  const thumbs = Array.from(document.querySelectorAll('.jd-thumb'));
  const n = G_IMAGES.length;
  const idx = (i % n + n) % n;
  if (main) {
    main.src = G_IMAGES[idx];
    main.dataset.index = String(idx);
  }
  thumbs.forEach(t => t.classList.toggle('active', Number(t.dataset.index) === idx));
}

// ===== Лайтбокс със Fit по подразбиране, Zoom и Pan (ръчичка) =====
const lb = document.getElementById('jdLightbox');
const lbImg = document.getElementById('jdLbImage');
const lbStage = document.getElementById('jdLbStage');
const lbPrev = document.querySelector('.jd-lb-prev');
const lbNext = document.querySelector('.jd-lb-next');
const lbClose= document.querySelector('.jd-lb-close');
const lbIn   = document.querySelector('.jd-lb-zoom-in');
const lbOut  = document.querySelector('.jd-lb-zoom-out');
const lb100  = document.querySelector('.jd-lb-zoom-reset');
const lbDl   = document.getElementById('jdLbDownload');

let LB_INDEX = 0;
let scale = 1, tx = 0, ty = 0;
let baseScale = 1; // „побери“ в прозореца (началният вид)
let dragging = false, lastX = 0, lastY = 0;

function applyTransform(){ lbImg.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`; }

function computeBaseScale(){
  const sw = lbStage.clientWidth;
  const sh = lbStage.clientHeight;
  const iw = lbImg.naturalWidth || lbImg.width;
  const ih = lbImg.naturalHeight || lbImg.height;
  if (!iw || !ih) { baseScale = 1; return; }
  baseScale = Math.min(sw / iw, sh / ih, 1);
}

function setLBIndex(i){
  if (!G_IMAGES.length) return;
  const n = G_IMAGES.length;
  LB_INDEX = (i % n + n) % n;
  lbImg.src = G_IMAGES[LB_INDEX];
  lbDl.href = G_IMAGES[LB_INDEX];

  // изчакай да се зареди, за да „поберем“
  lbImg.onload = () => {
    computeBaseScale();
    scale = baseScale; tx = 0; ty = 0; applyTransform();
  };
}

function openLightbox(start){
  if (!G_IMAGES.length) return;
  setLBIndex(typeof start==='number' ? start : getMainIndex());
  document.body.classList.add('modal-open');
  lb.classList.remove('hidden');
  lb.setAttribute('aria-hidden','false');
  lbImg.focus();
}
function closeLightbox(){
  lb.classList.add('hidden');
  lb.setAttribute('aria-hidden','true');
  document.body.classList.remove('modal-open');
}

lbPrev?.addEventListener('click', ()=> setLBIndex(LB_INDEX-1));
lbNext?.addEventListener('click', ()=> setLBIndex(LB_INDEX+1));
lbClose?.addEventListener('click', closeLightbox);
document.querySelector('.jd-lb-backdrop')?.addEventListener('click', closeLightbox);

// Zoom бутони
lbIn?.addEventListener('click', ()=>{ zoomAtCenter(1.2); });
lbOut?.addEventListener('click', ()=>{ zoomAtCenter(1/1.2); });
// 100% = реален размер
lb100?.addEventListener('click', ()=>{
  scale = 1;
  tx = 0; ty = 0;
  applyTransform();
});

// Двоен клик: тугъл между Fit и 2×
lbImg.addEventListener('dblclick', ()=>{
  if (scale <= baseScale + 0.001) {
    scale = Math.min(baseScale * 2, 8);
  } else {
    scale = baseScale; tx = 0; ty = 0;
  }
  applyTransform();
});

// Пан с „ръчичка“ — активен само при zoom над fit
lbImg.addEventListener('mousedown', (e)=>{
  if (scale <= baseScale + 0.001) return;
  dragging = true; lbStage.classList.add('grabbing');
  lastX = e.clientX; lastY = e.clientY;
});
window.addEventListener('mousemove', (e)=>{
  if (!dragging) return;
  const dx = e.clientX - lastX;
  const dy = e.clientY - lastY;
  lastX = e.clientX; lastY = e.clientY;
  tx += dx; ty += dy; applyTransform();
});
window.addEventListener('mouseup', ()=>{
  dragging=false; lbStage.classList.remove('grabbing');
});

// Zoom с колелцето към курсора
lbStage.addEventListener('wheel', (e)=>{
  e.preventDefault();
  const dir = e.deltaY > 0 ? -1 : 1;
  const factor = dir>0 ? 1.1 : 1/1.1;

  const rect = lbImg.getBoundingClientRect();
  const cx = e.clientX - rect.left - rect.width/2;
  const cy = e.clientY - rect.top  - rect.height/2;

  // трансформация около курсора
  tx = (tx - cx) * factor + cx;
  ty = (ty - cy) * factor + cy;
  scale = Math.min(Math.max(scale * factor, baseScale), 8);
  // ако стигнем обратно до fit, центрирай
  if (scale <= baseScale + 0.001) { scale = baseScale; tx = 0; ty = 0; }
  applyTransform();
}, { passive:false });

// Клавиатурни шорткъти
window.addEventListener('keydown', (e)=>{
  if (lb.classList.contains('hidden')) return;
  if (e.key === 'Escape')        { e.preventDefault(); closeLightbox(); }
  else if (e.key === 'ArrowLeft'){ e.preventDefault(); setLBIndex(LB_INDEX-1); }
  else if (e.key === 'ArrowRight'){ e.preventDefault(); setLBIndex(LB_INDEX+1); }
  else if (e.key === '+')        { e.preventDefault(); zoomAtCenter(1.2); }
  else if (e.key === '-')        { e.preventDefault(); zoomAtCenter(1/1.2); }
  else if (e.key === '0')        { e.preventDefault(); scale = 1; tx = 0; ty = 0; applyTransform(); }
});

// Помощна: zoom около центъра на екрана
function zoomAtCenter(factor){
  const rect = lbImg.getBoundingClientRect();
  const centerX = rect.width/2;
  const centerY = rect.height/2;

  tx = (tx - centerX) * factor + centerX;
  ty = (ty - centerY) * factor + centerY;
  scale = Math.min(Math.max(scale * factor, baseScale), 8);
  if (scale <= baseScale + 0.001) { scale = baseScale; tx = 0; ty = 0; }
  applyTransform();
}

// При resize преизчисляваме fit
window.addEventListener('resize', ()=>{
  if (lb.classList.contains('hidden')) return;
  const prevBase = baseScale;
  computeBaseScale();
  if (scale <= prevBase + 0.001) {
    // ако сме били на fit, остани на fit
    scale = baseScale; tx = 0; ty = 0; applyTransform();
  } else if (scale < baseScale) {
    // ако прозорецът е станал по-малък – не падай под fit
    scale = baseScale; tx = 0; ty = 0; applyTransform();
  }
});
