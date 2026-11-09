/* Conzmo motion layer */
(function () {
  if (window.__cmMotion) return; window.__cmMotion = true;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var GROUPS = [
    '.section-heading', '.cgrid__header', '.cmp__head', '.ctas__copy', '.ctas__actions', '.ctas__stats > *',
    '.metrics__grid > li', '.why-card', '.pencil-card', '.mediaf__media',
    '.cgrid__item', '.ans__card', '.psol__card', '.bfor__card', '.cmp__row', '.faq__item',
    '.sgrid__card', '.srflow__col', '.srflow__reach', '.factors-diagram-container',
    '.ovx__panel', '.bzs', '.bzg', '.bzx__card','.bzp__item', '.bzt__card', '.bzf__step', '.bzf__loop',
    '.bzd', '.dx', '.dk', '.hb-sector', '.hb-card', '.hb-big__card', '.hb-legal > li',
    '.cp-mv__card', '.cp-who__card', '.cp-nova', '.cp-flow > li', '.cp-docs', '.cp-faq details',
    '.lg__toc', '.lg__sec', '.footer__col', '.footer__band > *'
  ];
  var EXCLUDE = '.nav, .mnav, .mega, .sshow, .nova, [data-megamenu-panel]';
  var BARS_H = '.dkh__track i, .bzd__bar i, .dktbl .bar i, .indm__nm span i, .bzt__val i, .ovmap__bar i, .dxb__pill, .dkship__steps i.on';
  var BARS_V = '.dkbars i, .dxg__bars i, .dxf__sizes i, .ovcap__stack i, .ovpro__w i, .ovsig__bars i, .ovgan__block';
  var COUNT = '.metric-card__number, .metric-card__value, .bzd__kpi b, .dkk b, .bzt__val b, .dxf__kpi b, .dxb__pill b, .dxg__tile b, .ctas__stat-value';

  function within(el, sel) { return el.closest && el.closest(sel); }

  function animateCount(el) {
    if (el.__cmCounted || el.children.length) return;
    var text = el.textContent; var re = /\d[\d,]*(?:\.\d+)?/g; var nums = text.match(re);
    if (!nums) return; el.__cmCounted = true;
    var targets = nums.map(function (n) { return { raw: n, val: parseFloat(n.replace(/,/g, '')), dec: (n.split('.')[1] || '').length, comma: n.indexOf(',') > -1 }; });
    var start = performance.now(), dur = 1400;
    setTimeout(function () { el.textContent = text; }, dur + 400);
    function fmt(v, t) { var s = v.toFixed(t.dec); if (t.comma) { var p = s.split('.'); p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); s = p.join('.'); } return s; }
    function step(ts) {
      var k = Math.max(0, Math.min(1, (performance.now() - start) / dur)); var e = 1 - Math.pow(1 - k, 3); var i = 0;
      el.textContent = text.replace(re, function () { var t = targets[i++]; return fmt(t.val * e, t); });
      if (k < 1) requestAnimationFrame(step); else el.textContent = text;
    }
    requestAnimationFrame(step);
  }

  function init() {
    var root = document.documentElement;
    if (reduce) { root.classList.remove('cm-ready'); setupChrome(); return; }
    root.classList.add('cm-ready');

    // Hero entrance
    document.querySelectorAll('main .hero__copy').forEach(function (c) { c.classList.add('cm-hero'); });
    document.querySelectorAll('main .hero__media').forEach(function (m) { m.classList.add('cm-hero-media'); });
    setTimeout(function () { document.querySelectorAll('.cm-hero, .cm-hero-media').forEach(function (h) { h.classList.add('cm-done'); }); }, 1600);

    // Reveal targets with stagger by sibling order
    var seen = new Set();
    GROUPS.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        if (seen.has(el) || within(el, EXCLUDE) || within(el, '.hero')) return;
        seen.add(el); el.classList.add('cm-r');
      });
    });
    seen.forEach(function (el) {
      var p = el.parentElement; if (!p) return;
      var sibs = Array.prototype.filter.call(p.children, function (c) { return c.classList.contains('cm-r'); });
      var i = sibs.indexOf(el); el.style.setProperty('--cm-d', Math.min(i, 7) * 90 + 'ms');
      if (el.matches('.psol__card') && i === 0) el.classList.add('cm-left');
      if (el.matches('.psol__card') && i === 1) el.classList.add('cm-right');
      if (el.matches('.bzd, .dx, .dk, .bzs, .factors-diagram-container, .ovx__panel, .cp-nova, .cp-docs')) el.classList.add('cm-zoom');
    });
    document.querySelectorAll(BARS_H).forEach(function (b) { if (!within(b, EXCLUDE)) b.classList.add('cm-bar'); });
    document.querySelectorAll(BARS_V).forEach(function (b, i) { if (!within(b, EXCLUDE)) { b.classList.add('cm-barv'); var p = b.parentElement, idx = p ? Array.prototype.indexOf.call(p.parentElement ? p.parentElement.children : [], p) : 0; b.style.setProperty('--cm-d', Math.max(0, idx) * 60 + 'ms'); } });

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target; el.classList.add('is-in'); io.unobserve(el);
        if (el.classList.contains('cm-r')) { var d = parseInt(el.style.getPropertyValue('--cm-d')) || 0; setTimeout(function () { el.classList.remove('cm-r', 'cm-left', 'cm-right', 'cm-zoom', 'is-in'); el.style.removeProperty('--cm-d'); }, 900 + d); }
        if (el.matches(COUNT)) animateCount(el);
        el.querySelectorAll && el.querySelectorAll(COUNT).forEach(animateCount);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    document.querySelectorAll('.cm-r, .cm-bar, .cm-barv').forEach(function (el) { io.observe(el); });
    document.querySelectorAll(COUNT).forEach(function (el) { if (!within(el, '.cm-r')) io.observe(el); });

    // Parallax on hero art
    var par = Array.prototype.slice.call(document.querySelectorAll('main .hero .indm, main .hero .hero__card, main .hero .hero__glow'));
    par.forEach(function (p) { p.classList.add('cm-par'); });
    window.__cmPar = par;
    setupChrome();
  }

  function setupChrome() {
    if (!document.querySelector('.cm-progress')) {
      var pr = document.createElement('div'); pr.className = 'cm-progress'; pr.setAttribute('aria-hidden', 'true'); pr.innerHTML = '<i></i>'; document.body.appendChild(pr);
    }
    if (!document.querySelector('.cm-top')) {
      var b = document.createElement('button'); b.type = 'button'; b.className = 'cm-top'; b.setAttribute('aria-label', 'Back to top');
      b.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>';
      b.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });
      document.body.appendChild(b);
    }
    onScroll();
  }

  var ticking = false;
  function onScroll() {
    if (ticking) return; ticking = true;
    requestAnimationFrame(function () {
      ticking = false;
      var y = window.scrollY || 0, h = document.documentElement.scrollHeight - window.innerHeight;
      var bar = document.querySelector('.cm-progress i'); if (bar) bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
      var top = document.querySelector('.cm-top'); if (top) top.classList.toggle('is-on', y > 600);
      if (!reduce && window.__cmPar && y < 1200) window.__cmPar.forEach(function (p, i) { p.style.transform = 'translate3d(0,' + (y * (i === 0 ? 0.06 : 0.1)).toFixed(1) + 'px,0)'; });
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
  document.addEventListener('astro:page-load', function () { if (document.body && !document.body.__cmInit) { document.body.__cmInit = true; init(); } });
  document.addEventListener('astro:after-swap', function () { document.body.__cmInit = false; });
})();
