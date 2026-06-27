(function () {
  'use strict';

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── Hero entrance (opt-in; content visible by default) ── */
  if (!reduce) {
    var hero = document.querySelector('.hero');
    if (hero) hero.classList.add('hero-anim');
  }

  /* ── Nav scroll state ── */
  var nav = document.getElementById('nav');
  function onScroll() { if (nav) nav.classList.toggle('is-scrolled', window.scrollY > 40); }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ── Mobile menu ── */
  var burger = document.getElementById('navBurger');
  var links  = document.getElementById('navLinks');
  if (burger && links) {
    burger.addEventListener('click', function () {
      var open = links.classList.toggle('is-open');
      burger.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', String(open));
      document.body.style.overflow = open ? 'hidden' : '';
    });
    links.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        links.classList.remove('is-open');
        burger.classList.remove('is-open');
        burger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      });
    });
  }

  /* ── Ken-burns on load ── */
  ['.hero-img', '.quote-bg img'].forEach(function (sel) {
    var img = document.querySelector(sel);
    if (!img) return;
    if (img.complete) img.classList.add('is-loaded');
    else img.addEventListener('load', function () { img.classList.add('is-loaded'); });
  });

  /* ── Smooth anchor scroll ── */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href').slice(1);
      if (!id) return;
      var t = document.getElementById(id);
      if (!t) return;
      e.preventDefault();
      var top = t.getBoundingClientRect().top + window.scrollY - (nav ? nav.offsetHeight : 80);
      window.scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' });
    });
  });

  /* ── Scroll reveal ── */
  if (!reduce && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('js-reveal');
    var ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-visible'); ro.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { ro.observe(el); });
  }

  /* ── Configurator preview: colour swatch → tinted door + labels ── */
  var cfgSwatches = document.querySelectorAll('.cfg-swatch');
  var cfgTag      = document.getElementById('cfgTag');
  var cfgName     = document.getElementById('cfgFinishName');
  var cfgDoorFig  = document.getElementById('cfgDoorFigure');

  function setActive(list, idx) {
    list.forEach(function (el) {
      var on = +el.getAttribute('data-index') === idx;
      el.classList.toggle('is-active', on);
      if (el.hasAttribute('aria-selected')) el.setAttribute('aria-selected', String(on));
    });
  }

  cfgSwatches.forEach(function (s) {
    s.addEventListener('click', function () {
      var idx  = +s.getAttribute('data-index');
      var name = s.getAttribute('data-name') || '';
      var coll = s.getAttribute('data-collection') || '';
      var hex  = s.getAttribute('data-hex') || '';
      var img  = s.getAttribute('data-img') || '';

      setActive(cfgSwatches, idx);

      // Tint the door placeholder with the colour's hex, or show its photo.
      if (cfgDoorFig) {
        if (hex) cfgDoorFig.style.setProperty('--door-color', hex);
        if (img) cfgDoorFig.style.setProperty('--door-img', "url('" + img + "')");
        else     cfgDoorFig.style.removeProperty('--door-img');
      }

      var label = coll ? (name + '  ·  ' + coll) : name;
      if (cfgTag)  cfgTag.innerHTML = name + (coll ? ' <em>· ' + coll + '</em>' : '');
      if (cfgName) cfgName.textContent = name;
    });
  });

  /* ── Finishes section: hover/focus/click → background door preview ── */
  var finishBtns  = document.querySelectorAll('.finish');
  var finishDoors = document.querySelectorAll('.finishes-door');
  finishBtns.forEach(function (b) {
    var idx = +b.getAttribute('data-index');
    function show() { setActive(finishDoors, idx); setActive(finishBtns, idx); }
    b.addEventListener('mouseenter', show);
    b.addEventListener('focus', show);
    b.addEventListener('click', show);
  });

}());
