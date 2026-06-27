(function () {
  'use strict';

  var form        = document.getElementById('loginForm');
  var emailInput  = document.getElementById('email');
  var passInput   = document.getElementById('password');
  var csrfInput   = document.getElementById('csrfToken');
  var rememberChk = document.getElementById('remember');
  var submitBtn   = document.getElementById('submitBtn');
  var btnLabel    = document.getElementById('btnLabel');
  var btnSpinner  = document.getElementById('btnSpinner');
  var alertBox    = document.getElementById('alertBox');
  var alertText   = document.getElementById('alertText');
  var pwToggle    = document.getElementById('pwToggle');
  var eyeOpen     = document.getElementById('eyeOpen');
  var eyeClosed   = document.getElementById('eyeClosed');

  var fieldEmail    = document.getElementById('fieldEmail');
  var emailError    = document.getElementById('emailError');
  var fieldPassword = document.getElementById('fieldPassword');
  var passwordError = document.getElementById('passwordError');

  function showAlert(msg) {
    alertText.textContent = msg;
    alertBox.hidden = false;
  }

  function hideAlert() {
    alertBox.hidden = true;
    alertText.textContent = '';
  }

  function fieldError(inputEl, fieldEl, errorEl, msg) {
    inputEl.classList.add('is-invalid');
    fieldEl.classList.add('has-error');
    errorEl.textContent = msg;
  }

  function clearError(inputEl, fieldEl, errorEl) {
    inputEl.classList.remove('is-invalid');
    fieldEl.classList.remove('has-error');
    errorEl.textContent = '';
  }

  function setLoading(on) {
    submitBtn.disabled = on;
    btnSpinner.hidden  = !on;
    btnLabel.textContent = on ? 'Signing in…' : 'Sign In';
  }

  function refreshCsrf() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/door-showroom/admin/csrf', true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4 || xhr.status !== 200) return;
      try {
        var data = JSON.parse(xhr.responseText);
        if (data.token) csrfInput.value = data.token;
      } catch (e) {}
    };
    xhr.send();
  }

  pwToggle.addEventListener('click', function () {
    var hidden = passInput.type === 'password';
    passInput.type  = hidden ? 'text' : 'password';
    eyeOpen.hidden  = hidden;
    eyeClosed.hidden = !hidden;
  });

  emailInput.addEventListener('input', function () {
    hideAlert();
    clearError(emailInput, fieldEmail, emailError);
  });

  passInput.addEventListener('input', function () {
    hideAlert();
    clearError(passInput, fieldPassword, passwordError);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    hideAlert();
    clearError(emailInput, fieldEmail, emailError);
    clearError(passInput, fieldPassword, passwordError);

    var emailVal = emailInput.value.trim();
    var passVal  = passInput.value;
    var valid    = true;

    if (!emailVal) {
      fieldError(emailInput, fieldEmail, emailError, 'Email address is required.');
      valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
      fieldError(emailInput, fieldEmail, emailError, 'Enter a valid email address.');
      valid = false;
    }

    if (!passVal) {
      fieldError(passInput, fieldPassword, passwordError, 'Password is required.');
      valid = false;
    } else if (passVal.length < 8) {
      fieldError(passInput, fieldPassword, passwordError, 'Password must be at least 8 characters.');
      valid = false;
    }

    if (!valid) return;

    setLoading(true);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/door-showroom/admin/login', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-Token', csrfInput.value);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;
      setLoading(false);

      if (xhr.status === 0) {
        showAlert('Connection error. Check your network and try again.');
        return;
      }

      var data;
      try {
        data = JSON.parse(xhr.responseText);
      } catch (e) {
        showAlert('Unexpected server response. Please try again.');
        return;
      }

      if (data.success) {
        if (data.csrf) csrfInput.value = data.csrf;
        btnLabel.textContent   = 'Redirecting…';
        submitBtn.disabled     = true;
        window.location.href   = data.redirect || '/door-showroom/admin';
        return;
      }

      if (data.errors) {
        if (data.errors.email) {
          fieldError(emailInput, fieldEmail, emailError, data.errors.email);
        }
        if (data.errors.password) {
          fieldError(passInput, fieldPassword, passwordError, data.errors.password);
        }
      } else {
        showAlert(data.message || 'Invalid email or password.');
        passInput.value = '';
        passInput.focus();
      }

      refreshCsrf();
    };

    xhr.onerror = function () {
      setLoading(false);
      showAlert('Connection error. Check your network and try again.');
    };

    xhr.send(JSON.stringify({
      email:    emailVal,
      password: passVal,
      remember: rememberChk.checked,
      _csrf:    csrfInput.value
    }));
  });

  emailInput.focus();
}());
