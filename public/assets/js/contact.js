(function () {
  'use strict';

  var DATA = {};
  try { DATA = JSON.parse(document.getElementById('ctData').textContent || '{}'); } catch (e) {}

  /* ── FAQ accordion ── */
  var faq = document.getElementById('ctFaq');
  if (faq) {
    faq.querySelectorAll('.ct-faq-q').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') === 'true';
        // close others (single-open accordion)
        faq.querySelectorAll('.ct-faq-q').forEach(function (b) {
          b.setAttribute('aria-expanded', 'false');
          var a = b.nextElementSibling; if (a) a.style.maxHeight = null;
        });
        if (!open) {
          btn.setAttribute('aria-expanded', 'true');
          var ans = btn.nextElementSibling;
          if (ans) ans.style.maxHeight = ans.scrollHeight + 'px';
        }
      });
    });
  }

  /* ── contact form ── */
  var form    = document.getElementById('ctForm');
  var submit  = document.getElementById('ctSubmit');
  var success = document.getElementById('ctSuccess');
  var formMsg = document.getElementById('ctFormMsg');
  if (!form) return;

  function clearErrors() {
    form.querySelectorAll('.ct-err').forEach(function (e) { e.textContent = ''; });
    form.querySelectorAll('.ct-field.has-error').forEach(function (f) { f.classList.remove('has-error'); });
    if (formMsg) { formMsg.hidden = true; formMsg.classList.remove('is-error'); }
  }
  function showErrors(errors) {
    Object.keys(errors).forEach(function (k) {
      var span = form.querySelector('[data-err="' + k + '"]');
      if (span) { span.textContent = errors[k]; var f = span.closest('.ct-field'); if (f) f.classList.add('has-error'); }
    });
    var first = form.querySelector('.ct-field.has-error');
    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var payload = {
      name:    document.getElementById('ctName').value.trim(),
      email:   document.getElementById('ctEmail').value.trim(),
      phone:   document.getElementById('ctPhone').value.trim(),
      subject: document.getElementById('ctSubject').value.trim(),
      message: document.getElementById('ctMessage').value.trim(),
      company_website: (form.querySelector('[name=company_website]') || {}).value || '',
    };

    submit.disabled = true;
    submit.textContent = 'Sending…';

    fetch(DATA.url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': DATA.csrf },
      body: JSON.stringify(payload),
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        submit.disabled = false;
        submit.innerHTML = 'Send Message';
        if (res.d && res.d.success) {
          form.hidden = true;
          if (success) { success.hidden = false; success.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        } else if (res.d && res.d.errors) {
          showErrors(res.d.errors);
        } else if (formMsg) {
          formMsg.hidden = false; formMsg.classList.add('is-error');
          formMsg.textContent = (res.d && res.d.message) || 'Something went wrong. Please try again.';
        }
      })
      .catch(function () {
        submit.disabled = false;
        submit.innerHTML = 'Send Message';
        if (formMsg) { formMsg.hidden = false; formMsg.classList.add('is-error'); formMsg.textContent = 'Network error. Please try again or reach us on WhatsApp.'; }
      });
  });
}());
