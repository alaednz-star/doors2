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

  /* ── Signature colours: hover shows a real interior in the background ── */
  var coloursSec = document.getElementById('colours');
  var coloursRow = document.getElementById('coloursRow');
  if (coloursSec && coloursRow) {
    var bgImgs = coloursSec.querySelectorAll('.colours-bg-img');
    function showBg(idx) {
      bgImgs.forEach(function (b) {
        b.classList.toggle('is-active', b.getAttribute('data-bg') === String(idx));
      });
    }
    coloursRow.querySelectorAll('.colour-chip').forEach(function (chip) {
      chip.addEventListener('mouseenter', function () {
        coloursSec.classList.add('is-hovering');
        showBg(chip.getAttribute('data-bg'));
      });
    });
    coloursRow.addEventListener('mouseleave', function () {
      coloursSec.classList.remove('is-hovering');
    });
  }

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

}());
