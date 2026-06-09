/* Zahra Restaurant JS v2.0 */
(function () {
  'use strict';

  // Sticky Header
  const hdr = document.getElementById('zahra-header');
  if (hdr) window.addEventListener('scroll', () => hdr.classList.toggle('scrolled', scrollY > 50), {passive:true});

  // Hamburger
  const ham = document.getElementById('zahraHam');
  const mob = document.getElementById('zahraMobNav');
  if (ham && mob) {
    ham.addEventListener('click', () => {
      const a = ham.classList.toggle('active');
      mob.classList.toggle('open');
      document.body.classList.toggle('no-scroll', a);
      ham.setAttribute('aria-expanded', a);
    });
    mob.querySelectorAll('a').forEach(l => l.addEventListener('click', () => {
      ham.classList.remove('active'); mob.classList.remove('open');
      document.body.classList.remove('no-scroll');
    }));
  }

  // Scroll to Top
  const stb = document.querySelector('.scroll-top-btn');
  if (stb) {
    window.addEventListener('scroll', () => stb.classList.toggle('show', scrollY > 400), {passive:true});
    stb.addEventListener('click', () => window.scrollTo({top:0, behavior:'smooth'}));
  }

  // Animate on Scroll
  if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver(entries => {
      entries.forEach((e, i) => { if (e.isIntersecting) { setTimeout(() => e.target.classList.add('show'), i * 100); obs.unobserve(e.target); }});
    }, {threshold: 0.15});
    document.querySelectorAll('.anim').forEach(el => obs.observe(el));
  }

  // Testimonials / Review Slider
  const track = document.getElementById('reviewTrack');
  if (track) {
    const cards = track.querySelectorAll('.review-card');
    const dotsWrap = document.getElementById('rDots');
    let cur = 0;
    let pv = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1;
    const total = Math.ceil(cards.length / pv);
    let auto;

    if (dotsWrap) {
      for (let i = 0; i < total; i++) {
        const d = document.createElement('button');
        d.className = 'sdot' + (i === 0 ? ' active' : '');
        d.setAttribute('aria-label', 'Slide ' + (i+1));
        d.addEventListener('click', () => go(i));
        dotsWrap.appendChild(d);
      }
    }

    function go(idx) {
      cur = ((idx % total) + total) % total;
      const w = (cards[0].offsetWidth || 300) + 20;
      track.style.transform = `translateX(-${cur * pv * w}px)`;
      dotsWrap && dotsWrap.querySelectorAll('.sdot').forEach((d, i) => d.classList.toggle('active', i === cur));
    }

    const startA = () => { auto = setInterval(() => go(cur + 1), 4500); };
    const stopA  = () => clearInterval(auto);

    const prevBtn = document.getElementById('rPrev');
    const nextBtn = document.getElementById('rNext');
    if (prevBtn) prevBtn.addEventListener('click', () => { stopA(); go(cur - 1); startA(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { stopA(); go(cur + 1); startA(); });

    window.addEventListener('resize', () => { pv = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1; go(0); });
    startA();
  }

  // Menu Tabs
  document.querySelectorAll('.mtab').forEach(tab => {
    tab.addEventListener('click', () => {
      const t = tab.dataset.tab;
      document.querySelectorAll('.mtab').forEach(x => x.classList.remove('active'));
      document.querySelectorAll('.menu-section').forEach(x => x.classList.remove('active'));
      tab.classList.add('active');
      const sec = document.getElementById('tab-' + t);
      if (sec) sec.classList.add('active');
    });
  });

  // Gallery Lightbox
  const lb     = document.getElementById('lightbox');
  const lbImg  = document.getElementById('lbImg');
  const lbClose= document.getElementById('lbClose');
  const lbPrev = document.getElementById('lbPrev');
  const lbNext = document.getElementById('lbNext');

  if (lb) {
    let items = [], idx = 0;

    function collectItems() {
      items = Array.from(document.querySelectorAll('.gitem[data-src]'));
      items.forEach((item, i) => {
        item.addEventListener('click', () => {
          idx = i;
          lbImg.src = item.dataset.src;
          lbImg.alt = item.dataset.caption || '';
          lb.classList.add('open');
          document.body.classList.add('no-scroll');
        });
      });
    }
    collectItems();

    const closeLb = () => { lb.classList.remove('open'); document.body.classList.remove('no-scroll'); };
    if (lbClose) lbClose.addEventListener('click', closeLb);
    lb.addEventListener('click', e => { if (e.target === lb) closeLb(); });
    if (lbPrev) lbPrev.addEventListener('click', () => { idx = (idx - 1 + items.length) % items.length; lbImg.src = items[idx].dataset.src; });
    if (lbNext) lbNext.addEventListener('click', () => { idx = (idx + 1) % items.length; lbImg.src = items[idx].dataset.src; });
    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft' && lbPrev) lbPrev.click();
      if (e.key === 'ArrowRight' && lbNext) lbNext.click();
    });
  }

  // Contact Form (AJAX)
  const form = document.getElementById('contactForm');
  if (form) {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const msg  = document.getElementById('formMsg');
      const btn  = form.querySelector('[type=submit]');
      const orig = btn.textContent;
      btn.textContent = 'Sending...';
      btn.disabled = true;

      const fd = new FormData(form);
      fd.append('action', 'zahra_contact_form');

      try {
        if (typeof zahraData !== 'undefined') {
          fd.set('zahra_nonce', zahraData.nonce);
          const res  = await fetch(zahraData.ajax, {method:'POST', body: fd});
          const data = await res.json();
          if (msg) {
            msg.className = 'form-msg ' + (data.success ? 'ok' : 'err');
            msg.textContent = data.data?.msg || (data.success ? 'Sent!' : 'Error.');
            msg.style.display = 'block';
          }
          if (data.success) form.reset();
        } else {
          // Static demo fallback
          if (msg) { msg.className='form-msg ok'; msg.textContent='Thank you! We will contact you shortly. 🙏'; msg.style.display='block'; }
          form.reset();
        }
      } catch (err) {
        if (msg) { msg.className='form-msg err'; msg.textContent='Network error. Please call us: +91 93556 43665'; msg.style.display='block'; }
      } finally {
        btn.textContent = orig;
        btn.disabled = false;
      }
    });
  }

  // Active nav on scroll
  const navLinks = document.querySelectorAll('#zahra-nav a, .mob-nav a');
  const sections  = document.querySelectorAll('section[id]');
  if (navLinks.length && sections.length) {
    window.addEventListener('scroll', () => {
      const pos = scrollY + 100;
      sections.forEach(s => {
        if (s.offsetTop <= pos && s.offsetTop + s.offsetHeight > pos) {
          navLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') && l.getAttribute('href').endsWith('#' + s.id)));
        }
      });
    }, {passive:true});
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({behavior:'smooth', block:'start'}); }
    });
  });

})();
