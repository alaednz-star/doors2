(function () {
  'use strict';

  var stories = document.getElementById('colStories');
  if (!stories) return;

  var cards     = Array.prototype.slice.call(stories.querySelectorAll('.col-story'));
  if (!cards.length) return;

  var filters   = Array.prototype.slice.call(document.querySelectorAll('.col-filter'));
  var searchEl  = document.getElementById('colSearch');
  var noResults = document.getElementById('colNoResults');
  var resetBtn  = document.getElementById('colReset');

  var state = { filter: 'all', q: searchEl ? (searchEl.value || '') : '' };

  function apply() {
    var q = state.q.trim().toLowerCase();
    var visible = 0;

    cards.forEach(function (card) {
      var slug = card.getAttribute('data-slug') || '';
      var name = card.getAttribute('data-name') || '';
      var okFilter = state.filter === 'all' || slug === state.filter;
      var okSearch = q === '' || name.indexOf(q) !== -1 || slug.indexOf(q) !== -1;
      var show = okFilter && okSearch;
      card.classList.toggle('is-hidden', !show);
      if (show) visible++;
    });

    if (noResults) noResults.hidden = visible !== 0;
    syncUrl();
  }

  function syncUrl() {
    var p = new URLSearchParams();
    if (state.q.trim() !== '') p.set('q', state.q.trim());
    if (state.filter !== 'all') p.set('filter', state.filter);
    var qs = p.toString();
    window.history.replaceState(null, '', '/door-showroom/collections' + (qs ? '?' + qs : ''));
  }

  function setFilter(val) {
    state.filter = val;
    filters.forEach(function (b) {
      var on = b.getAttribute('data-filter') === val;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', String(on));
    });
    apply();
  }

  filters.forEach(function (b) {
    b.addEventListener('click', function () { setFilter(b.getAttribute('data-filter') || 'all'); });
  });

  if (searchEl) {
    var t;
    searchEl.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(function () { state.q = searchEl.value; apply(); }, 150);
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (searchEl) searchEl.value = '';
      state.q = '';
      setFilter('all');
    });
  }

  /* hydrate filter from URL */
  var initial = new URLSearchParams(window.location.search).get('filter');
  if (initial && filters.some(function (b) { return b.getAttribute('data-filter') === initial; })) {
    setFilter(initial);
  } else {
    apply();
  }
}());
