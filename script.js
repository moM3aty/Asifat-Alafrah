// script.js
const WA_NUMBER = '966564710254';

document.addEventListener('DOMContentLoaded', () => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const rand = (a, b) => a + Math.random() * (b - a);
  const small = window.innerWidth < 700;

  /* ---------- Header ---------- */
  const header = $('#siteHeader');
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  const nav = $('#mainNav');
  $('#burger').addEventListener('click', () => nav.classList.toggle('open'));
  $$('a', nav).forEach(a => a.addEventListener('click', () => nav.classList.remove('open')));

  /* ---------- Night sky: stars, wind gusts, lanterns, string lights ---------- */
  const stars = $('#stars');
  for (let i = 0; i < (small ? 40 : 80); i++) {
    const s = document.createElement('span');
    const size = rand(2, 4);
    s.style.cssText = `left:${rand(0, 100)}%;top:${rand(0, 70)}%;width:${size}px;height:${size}px;animation-duration:${rand(2, 5)}s;animation-delay:${rand(0, 5)}s`;
    stars.appendChild(s);
  }

  const gusts = $('#gusts');
  for (let i = 0; i < (small ? 4 : 8); i++) {
    const g = document.createElement('span');
    g.style.cssText = `top:${rand(8, 92)}%;width:${rand(120, 320)}px;opacity:${rand(.25, .6).toFixed(2)};animation-duration:${rand(4, 9)}s;animation-delay:${rand(0, 8)}s`;
    gusts.appendChild(g);
  }

  const lanterns = $('#lanterns');
  for (let i = 0; i < (small ? 6 : 12); i++) {
    const l = document.createElement('span');
    l.style.cssText = `left:${rand(2, 98)}%;animation-duration:${rand(14, 26)}s;animation-delay:${rand(0, 18)}s;transform:scale(${rand(.6, 1.2).toFixed(2)})`;
    lanterns.appendChild(l);
  }

  // bulbs hanging on the wire: path M0,10 Q360,90 720,10 Q1080,90 1440,10 (viewBox height 120 = 120px)
  const lights = $('#lights');
  const colors = ['#FFB347', '#E04F7B', '#FFF4E0', '#FFD36E'];
  const N = small ? 14 : 30;
  for (let i = 0; i <= N; i++) {
    const f = i / N;
    const seg = f < 0.5 ? 0 : 1;
    const t = (f - seg * 0.5) / 0.5;
    const y = Math.pow(1 - t, 2) * 10 + 2 * (1 - t) * t * 90 + t * t * 10;
    const b = document.createElement('span');
    b.className = 'bulb';
    const c = colors[i % colors.length];
    b.style.cssText = `left:${f * 100}%;top:${y + 2}px;background:${c};box-shadow:0 0 14px 4px ${c};animation-duration:${rand(1.6, 3.2)}s;animation-delay:${rand(0, 3)}s`;
    lights.appendChild(b);
  }

  /* ---------- Counters ---------- */
  const countObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, target = +el.dataset.count, t0 = performance.now();
      const step = (now) => {
        const p = Math.min((now - t0) / 1200, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
      countObs.unobserve(el);
    });
  }, { threshold: .6 });
  $$('[data-count]').forEach(el => countObs.observe(el));

  /* ---------- Reveal on scroll ---------- */
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('in-view'); revealObs.unobserve(e.target); }
    });
  }, { threshold: .15 });
  const watch = (el) => revealObs.observe(el);
  $$('[data-reveal], .tl-item').forEach(watch);

  /* ---------- Bento spotlight ---------- */
  $$('.tile').forEach(tile => {
    tile.addEventListener('mousemove', (e) => {
      const r = tile.getBoundingClientRect();
      tile.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      tile.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  /* ---------- Lightbox ---------- */
  const lb = $('#lightbox'), lbImg = $('#lbImg'), lbCount = $('#lbCount');
  let current = 0, list = [];
  const show = () => {
    lbImg.src = list[current].src;
    lbCount.textContent = `${current + 1} / ${list.length}`;
  };
  const openLb = (slot) => {
    list = slots.filter(s => s.src);
    current = list.indexOf(slot);
    show();
    lb.classList.add('open');
  };
  slots.forEach(slot => slot.btn.addEventListener('click', () => { if (slot.src) openLb(slot); }));
  const closeLb = () => lb.classList.remove('open');
  const next = () => { current = (current + 1) % list.length; show(); };
  const prev = () => { current = (current - 1 + list.length) % list.length; show(); };
  $('#lbClose').addEventListener('click', closeLb);
  $('#lbNext').addEventListener('click', next);
  $('#lbPrev').addEventListener('click', prev);
  lb.addEventListener('click', (e) => { if (e.target === lb) closeLb(); });
  document.addEventListener('keydown', (e) => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape') closeLb();
    if (e.key === 'ArrowLeft') next();   // RTL: left = next
    if (e.key === 'ArrowRight') prev();
  });

  /* ---------- Guide tabs ---------- */
  const tabs = $$('.gtab'), panels = $$('.gpanel');
  tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.toggle('active', t === tab));
    panels.forEach(p => p.classList.toggle('active', p.id === tab.dataset.target));
  }));

  /* ---------- Planner ---------- */
  const guests = $('#guests'), perTable = $('#perTable');
  const calc = () => {
    const g = +guests.value, per = +perTable.value;
    $('#guestsOut').textContent = g;
    $('#outChairs').textContent = g;
    $('#outTables').textContent = Math.ceil(g / per);
  };
  guests.addEventListener('input', calc);
  perTable.addEventListener('change', calc);
  calc();
  $('#sendPlan').addEventListener('click', () => {
    $('#fGuests').value = guests.value;
    $$('#fItems input').forEach(c => { if (['كراسي', 'طاولات'].includes(c.value)) c.checked = true; });
    $('#fNotes').value = `تقدير تقريبي: ${$('#outChairs').textContent} كرسي، ${$('#outTables').textContent} طاولة (${perTable.value} أشخاص على الطاولة)`;
  });

  /* ---------- Booking form -> WhatsApp ---------- */
  $('#bookingForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const items = $$('#fItems input:checked').map(c => c.value).join('، ') || 'غير محدد';
    const msg =
`السلام عليكم، أرغب بحجز تجهيزات لمناسبة:
الاسم: ${$('#fName').value || '-'}
نوع المناسبة: ${$('#fType').value}
تاريخ المناسبة: ${$('#fDate').value || 'غير محدد'}
عدد الضيوف: ${$('#fGuests').value || 'غير محدد'}
المطلوب: ${items}
ملاحظات: ${$('#fNotes').value || '-'}`;
    window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
  });

  /* ---------- Cursor glow & year ---------- */
  const glow = $('#cursorGlow');
  if (window.matchMedia('(hover: hover)').matches) {
    window.addEventListener('mousemove', (e) => {
      glow.classList.add('active');
      glow.style.transform = `translate(${e.clientX}px, ${e.clientY}px) translate(-50%,-50%)`;
    });
    window.addEventListener('mouseleave', () => glow.classList.remove('active'));
  }
  $('#year').textContent = new Date().getFullYear();
});