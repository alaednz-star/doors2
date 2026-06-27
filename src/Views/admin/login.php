<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Sign In — Luxury Door Showroom</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/auth.css" />
</head>
<body>

<div class="auth-wrap">

  <div class="auth-visual" aria-hidden="true">
    <div class="visual-gradient"></div>
    <div class="visual-content">
      <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="visual-brand-img" />
      <p class="visual-tagline">Architectural doors &amp; kitchens crafted<br>for exceptional spaces.</p>
    </div>
  </div>

  <div class="auth-form-panel">
    <div class="form-card">

      <div class="card-header">
        <div class="card-logo">
          <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK" class="card-logo-img" />
          <div>
            <span>Admin Console</span>
          </div>
        </div>
        <h1>Welcome back</h1>
        <p>Sign in to manage your showroom.</p>
      </div>

      <div class="alert alert-error" id="alertBox" hidden>
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
        </svg>
        <span id="alertText"></span>
      </div>

      <form id="loginForm" novalidate autocomplete="on">
        <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />

        <div class="field" id="fieldEmail">
          <label for="email">Email address</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
              <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
            </svg>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="admin@showroom.dz"
              autocomplete="email"
              autocapitalize="none"
              spellcheck="false"
              required
            />
          </div>
          <span class="field-error" id="emailError" role="alert" aria-live="polite"></span>
        </div>

        <div class="field" id="fieldPassword">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
            </svg>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Your password"
              autocomplete="current-password"
              required
            />
            <button type="button" class="pw-toggle" id="pwToggle" tabindex="-1" aria-label="Toggle password visibility">
              <svg id="eyeOpen" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
              <svg id="eyeClosed" viewBox="0 0 20 20" fill="currentColor" hidden>
                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/>
                <path d="M10.748 13.93l2.523 2.524a10.065 10.065 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a2.5 2.5 0 002.678 2.678l2 2z"/>
              </svg>
            </button>
          </div>
          <span class="field-error" id="passwordError" role="alert" aria-live="polite"></span>
        </div>

        <div class="field-row">
          <label class="checkbox-wrap">
            <input type="checkbox" id="remember" name="remember" />
            <span>Keep me signed in for 30 days</span>
          </label>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <span id="btnLabel">Sign In</span>
          <span id="btnSpinner" class="spinner" hidden aria-hidden="true"></span>
        </button>
      </form>

      <p class="card-note">Protected area &mdash; authorised personnel only.</p>

    </div>
  </div>

</div>

<script src="/door-showroom/assets/js/auth.js"></script>
</body>
</html>
