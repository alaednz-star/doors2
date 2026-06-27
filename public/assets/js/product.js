(function () {
  'use strict';

  /* ── Gallery: thumbnail → main image ── */
  var main   = document.getElementById('pdMain');
  var thumbs = Array.prototype.slice.call(document.querySelectorAll('#pdThumbs .pd-thumb'));

  if (main && thumbs.length) {
    thumbs.forEach(function (t) {
      t.addEventListener('click', function () {
        var src = t.getAttribute('data-src');
        if (!src) return;
        main.src = src;
        thumbs.forEach(function (x) { x.classList.remove('is-active'); });
        t.classList.add('is-active');
      });
    });
  }

  /* ── Hero color swatches (circular) → label ── */
  var swatches  = Array.prototype.slice.call(document.querySelectorAll('#pdSwatches .pd-swatch'));
  var finishName = document.getElementById('pdColorName') || document.getElementById('pdFinishName');

  function selectSwatch(list, name) {
    list.forEach(function (s) {
      var on = (s.getAttribute('data-name') === name);
      s.classList.toggle('is-active', on);
      if (s.hasAttribute('aria-selected')) s.setAttribute('aria-selected', String(on));
    });
  }

  /* ── Colors section ring ── */
  var ring = Array.prototype.slice.call(document.querySelectorAll('#pdColorRing .pd-finish, #pdFinishRing .pd-finish'));

  function pick(name) {
    if (finishName) finishName.textContent = name;
    if (swatches.length) selectSwatch(swatches, name);
    if (ring.length)     selectSwatch(ring, name);
  }

  swatches.forEach(function (s) {
    s.addEventListener('click', function () { pick(s.getAttribute('data-name') || ''); });
  });
  ring.forEach(function (b) {
    var name = b.getAttribute('data-name') || '';
    b.addEventListener('click', function () { pick(name); });
    b.addEventListener('mouseenter', function () { selectSwatch(ring, name); });
  });

  /* ── Pointer-tracked zoom origin on the stage ── */
  var stage = document.getElementById('pdStage');
  if (stage && main && !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
    stage.addEventListener('mousemove', function (e) {
      var r = stage.getBoundingClientRect();
      var x = ((e.clientX - r.left) / r.width) * 100;
      var y = ((e.clientY - r.top) / r.height) * 100;
      main.style.transformOrigin = x + '% ' + y + '%';
    });
    stage.addEventListener('mouseleave', function () {
      main.style.transformOrigin = 'center';
    });
  }
}());
