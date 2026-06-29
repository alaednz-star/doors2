<?php
declare(strict_types=1);
/** @var array $settings */
/** @var string $token */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$img = static fn ($f) => '/door-showroom/assets/images/' . $f;
$ver = static fn ($p) => @filemtime(APP_ROOT . '/public' . $p) ?: '1';

$ci      = contact_info();
$email   = $ci['email'];
$phone   = $ci['phone'];
$address = $ci['address'];
$wa      = $ci['whatsapp_url'];
$telHref = $ci['tel_href'];
$L   = \App\Core\I18n::lang();
$DIR = \App\Core\I18n::dir();

$faqs = \App\Core\I18n::group('contact.faqs');
?>
<!DOCTYPE html>
<html lang="<?= $e($L) ?>" dir="<?= $e($DIR) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $e(t('contact.meta')) ?>" />
  <title><?= $e(t('contact.title')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/contact.css?v=<?= $ver('/assets/css/contact.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/i18n.css?v=<?= $ver('/assets/css/i18n.css') ?>" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav is-scrolled" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="nav-logo-img" />
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="/door-showroom/collections"><?= $e(t('nav.collections')) ?></a>
    <a href="/door-showroom/configure"><?= $e(t('nav.configurator')) ?></a>
    <a href="/door-showroom#colours"><?= $e(t('colours.eyebrow')) ?></a>
    <a href="/door-showroom#featured"><?= $e(t('nav.doors')) ?></a>
    <a href="/door-showroom/contact" class="is-current"><?= $e(t('contact.crumb')) ?></a>
    <?php $variant = 'mobile'; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  </nav>
  <?php $variant = ''; include APP_ROOT . '/src/Views/partials/lang-switch.php'; ?>
  <a href="/door-showroom/configure" class="nav-cta"><?= $e(t('nav.request_quote')) ?></a>
  <button class="nav-burger" id="navBurger" aria-label="<?= $e(t('nav.menu_open')) ?>" aria-expanded="false"><span></span><span></span><span></span></button>
</header>

<!-- ░ 1 · HERO ░ -->
<section class="ct-hero">
  <div class="ct-hero-bg"><img src="<?= $img('bghero.png') ?>" alt="" role="presentation" class="ct-hero-img" /><div class="ct-hero-overlay"></div></div>
  <div class="ct-hero-inner">
    <nav class="ct-crumb" aria-label="Breadcrumb"><a href="/door-showroom"><?= $e(t('common.home')) ?></a><span>/</span><span><?= $e(t('contact.crumb')) ?></span></nav>
    <p class="eyebrow"><?= $e(t('contact.hero_eyebrow')) ?></p>
    <h1 class="ct-hero-title"><?= $e(t('contact.hero_h_1')) ?><br /><em><?= $e(t('contact.hero_h_2')) ?></em></h1>
    <p class="ct-hero-sub"><?= $e(t('contact.hero_sub')) ?></p>
  </div>
</section>

<!-- ░ 2 · CONTACT INFORMATION ░ -->
<section class="ct-info">
  <div class="ct-info-grid reveal">
    <a class="ct-info-card" href="<?= $e($telHref) ?>">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M5 4h4l2 5-3 2a14 14 0 006 6l2-3 5 2v4a2 2 0 01-2 2A18 18 0 013 6a2 2 0 012-2z"/></svg></span>
      <span class="ct-info-label"><?= $e(t('contact.tel')) ?></span>
      <span class="ct-info-value"><?= $e($phone) ?></span>
    </a>
    <a class="ct-info-card" href="mailto:<?= $e($email) ?>">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
      <span class="ct-info-label"><?= $e(t('contact.email')) ?></span>
      <span class="ct-info-value"><?= $e($email) ?></span>
    </a>
    <div class="ct-info-card">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 21s-7-6.2-7-11a7 7 0 0114 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
      <span class="ct-info-label"><?= $e(t('contact.showroom')) ?></span>
      <span class="ct-info-value"><?= $e($address) ?></span>
    </div>
  </div>
</section>

<!-- ░ 3 · SHOWROOM INFORMATION ░ -->
<section class="ct-showroom">
  <div class="ct-showroom-media reveal">
    <img src="<?= $img('marron-prestige.jpg') ?>" alt="PORTES showroom" loading="lazy" />
  </div>
  <div class="ct-showroom-body reveal">
    <p class="eyebrow"><?= $e(t('contact.sr_eyebrow')) ?></p>
    <h2 class="ct-h2"><?= $e(t('contact.sr_h_1')) ?><br /><em><?= $e(t('contact.sr_h_2')) ?></em></h2>
    <p class="ct-lead"><?= $e(t('contact.sr_lead')) ?></p>
    <dl class="ct-hours">
      <div><dt><?= $e(t('contact.hours_1')) ?></dt><dd>9:00 — 18:00</dd></div>
      <div><dt><?= $e(t('contact.hours_2')) ?></dt><dd>10:00 — 17:00</dd></div>
      <div><dt><?= $e(t('contact.hours_3')) ?></dt><dd><?= $e(t('contact.hours_appt')) ?></dd></div>
    </dl>
    <a href="#ct-map" class="btn btn--outline"><?= $e(t('contact.view_map')) ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ░ 4 · CONTACT FORM ░ -->
<section class="ct-form-section" id="ct-form">
  <div class="ct-form-wrap reveal">
    <div class="ct-form-intro">
      <p class="eyebrow"><?= $e(t('contact.form_eyebrow')) ?></p>
      <h2 class="ct-h2"><?= $e(t('contact.form_h_1')) ?><br /><em><?= $e(t('contact.form_h_2')) ?></em></h2>
      <p class="ct-lead"><?= $e(t('contact.form_lead')) ?></p>
    </div>

    <form class="ct-form" id="ctForm" novalidate>
      <div class="ct-field-grid">
        <div class="ct-field">
          <label for="ctName"><?= $e(t('contact.f_name')) ?></label>
          <input type="text" id="ctName" name="name" autocomplete="name" maxlength="120" required />
          <span class="ct-err" data-err="name"></span>
        </div>
        <div class="ct-field">
          <label for="ctEmail"><?= $e(t('contact.f_email')) ?></label>
          <input type="email" id="ctEmail" name="email" autocomplete="email" maxlength="180" required />
          <span class="ct-err" data-err="email"></span>
        </div>
        <div class="ct-field">
          <label for="ctPhone"><?= $e(t('contact.f_phone')) ?> <span class="ct-opt"><?= $e(t('common.optional')) ?></span></label>
          <input type="tel" id="ctPhone" name="phone" autocomplete="tel" maxlength="30" />
          <span class="ct-err" data-err="phone"></span>
        </div>
        <div class="ct-field">
          <label for="ctSubject"><?= $e(t('contact.f_subject')) ?> <span class="ct-opt"><?= $e(t('common.optional')) ?></span></label>
          <input type="text" id="ctSubject" name="subject" maxlength="160" />
          <span class="ct-err" data-err="subject"></span>
        </div>
      </div>
      <div class="ct-field">
        <label for="ctMessage"><?= $e(t('contact.f_message')) ?></label>
        <textarea id="ctMessage" name="message" rows="6" maxlength="4000" required placeholder="<?= $e(t('contact.f_message_ph')) ?>"></textarea>
        <span class="ct-err" data-err="message"></span>
      </div>
      <!-- honeypot: hidden from humans, catches bots -->
      <div class="ct-hp" aria-hidden="true">
        <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off" /></label>
      </div>
      <div class="ct-form-foot">
        <button type="submit" class="btn btn--gold btn--lg" id="ctSubmit"><?= $e(t('contact.send')) ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
        <p class="ct-form-msg" id="ctFormMsg" hidden></p>
      </div>
    </form>

    <!-- success state -->
    <div class="ct-success" id="ctSuccess" hidden>
      <span class="ct-success-mark"><svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="32" cy="32" r="29"/><path d="M20 33l8 8 16-18" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
      <h3><?= $e(t('contact.success_t')) ?></h3>
      <p><?= $e(t('contact.success_d')) ?></p>
    </div>
  </div>
</section>

<!-- ░ 5 · WHATSAPP CTA ░ -->
<?php if ($wa): ?>
<section class="ct-whatsapp">
  <div class="ct-whatsapp-inner reveal">
    <span class="ct-whatsapp-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></span>
    <div class="ct-whatsapp-text">
      <h2 class="ct-h2"><?= $e(t('contact.wa_h')) ?></h2>
      <p class="ct-lead"><?= $e(t('contact.wa_lead')) ?></p>
    </div>
    <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--gold btn--lg"><?= $e(t('contact.wa_btn')) ?></a>
  </div>
</section>
<?php endif; ?>

<!-- ░ 6 · GOOGLE MAPS AREA ░ -->
<section class="ct-map" id="ct-map">
  <div class="ct-map-overlay">
    <div class="ct-map-card reveal">
      <p class="eyebrow"><?= $e(t('contact.find_eyebrow')) ?></p>
      <h3 class="ct-map-title"><?= $e(t('contact.find_title')) ?></h3>
      <p class="ct-map-addr"><?= $e($address) ?></p>
      <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($address) ?>" target="_blank" rel="noopener" class="btn btn--outline-light"><?= $e(t('contact.open_maps')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
  <iframe
    class="ct-map-frame"
    title="PORTES showroom location"
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade"
    src="https://www.google.com/maps?q=<?= rawurlencode($address) ?>&output=embed"></iframe>
</section>

<!-- ░ 7 · FAQ ░ -->
<section class="ct-faq">
  <div class="sec-intro sec-intro--center reveal">
    <p class="eyebrow"><?= $e(t('contact.faq_eyebrow')) ?></p>
    <h2 class="ct-h2"><?= $e(t('contact.faq_h_1')) ?><br /><em><?= $e(t('contact.faq_h_2')) ?></em></h2>
  </div>
  <div class="ct-faq-list reveal" id="ctFaq">
    <?php foreach ($faqs as $i => $f): ?>
      <div class="ct-faq-item">
        <button class="ct-faq-q" type="button" aria-expanded="false">
          <span><?= $e($f['q']) ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 5v14M5 12h14"/></svg>
        </button>
        <div class="ct-faq-a"><p><?= $e($f['a']) ?></p></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ░ 8 · REQUEST QUOTE CTA ░ -->
<section class="quote" id="quote">
  <div class="quote-bg"><img src="<?= $img('porte-scuro.jpg') ?>" alt="" role="presentation" loading="lazy" /><div class="quote-overlay"></div></div>
  <div class="quote-inner reveal">
    <h2 class="quote-title"><?= $e(t('contact.cta_title_1')) ?><br /><em><?= $e(t('contact.cta_title_2')) ?></em></h2>
    <p class="quote-sub"><?= $e(t('contact.cta_sub')) ?></p>
    <div class="quote-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg"><?= $e(t('contact.cta_btn')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="/door-showroom/collections" class="btn btn--outline-light btn--lg"><?= $e(t('contact.browse')) ?></a>
    </div>
  </div>
</section>

<!-- ░ FOOTER ░ -->
<footer class="footer" id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo"><img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="footer-logo-img" /></div>
      <p class="footer-tag"><?= $e(t('footer.tag')) ?></p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v7h3v-7H16l.5-3h-3V9.5c0-.3.2-.5.5-.5z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg></a>
        <?php if ($wa): ?><a href="<?= $e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></a><?php endif; ?>
      </div>
    </div>
    <div class="footer-col"><h4><?= $e(t('footer.collections')) ?></h4><ul>
      <li><a href="/door-showroom/collections/heritage">Heritage</a></li>
      <li><a href="/door-showroom/collections/moderne">Moderne</a></li>
      <li><a href="/door-showroom/collections/prestige">Prestige</a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.services')) ?></h4><ul>
      <li><a href="/door-showroom/configure"><?= $e(t('footer.configurator')) ?></a></li>
      <li><a href="/door-showroom#quote"><?= $e(t('footer.made')) ?></a></li>
      <li><a href="/door-showroom#quote"><?= $e(t('footer.install')) ?></a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.company')) ?></h4><ul>
      <li><a href="/door-showroom#why"><?= $e(t('footer.about')) ?></a></li>
      <li><a href="/door-showroom/contact"><?= $e(t('footer.contact')) ?></a></li>
      <li><a href="/door-showroom/collections"><?= $e(t('footer.projects')) ?></a></li>
    </ul></div>
    <div class="footer-col"><h4><?= $e(t('footer.contact')) ?></h4><ul>
      <?php if ($phone): ?><li><a href="<?= $e($telHref) ?>"><?= $e($phone) ?></a></li><?php endif; ?>
      <li><a href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a></li>
      <li><span><?= $e(t('footer.tagline')) ?></span></li>
      <?php if ($wa): ?><li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
    </ul></div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> PORTES. <?= $e(t('footer.rights')) ?></span>
    <span><?= $e(t('footer.tagline')) ?></span>
  </div>
</footer>

<a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="wa-float" aria-label="<?= $e(t('wa.chat')) ?>">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
</a>

<script type="application/json" id="ctData"><?= json_encode(['csrf' => $token, 'url' => '/door-showroom/contact/submit'], JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/home.js?v=<?= $ver('/assets/js/home.js') ?>"></script>
<script src="/door-showroom/assets/js/contact.js?v=<?= $ver('/assets/js/contact.js') ?>"></script>
</body>
</html>
