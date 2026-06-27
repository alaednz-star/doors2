<?php
declare(strict_types=1);
/** @var array $settings */
/** @var string $token */
$e   = static fn ($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$img = static fn ($f) => '/door-showroom/assets/images/' . $f;
$ver = static fn ($p) => @filemtime(APP_ROOT . '/public' . $p) ?: '1';

$email   = $settings['contact_email'];
$phone   = $settings['contact_phone'];
$address = $settings['contact_address'];
$wa      = 'https://wa.me/213512345678';
$telHref = 'tel:' . preg_replace('/[^0-9+]/', '', $phone);

$faqs = [
    ['q' => 'Do you ship and install outside Algiers?',
     'a' => 'Yes. We deliver across Algeria and to select international projects. Installation is carried out by our own certified teams; for projects abroad we coordinate with vetted local partners under our supervision.'],
    ['q' => 'How long does a bespoke door take?',
     'a' => 'From signed specification to delivery, the typical lead time is six weeks. Prestige commissions with rare veneers or fully custom dimensions may take longer — we confirm a precise timeline with your quotation.'],
    ['q' => 'Can I see and feel the colours before ordering?',
     'a' => 'Absolutely. We encourage a visit to our Algiers showroom, where every collection and colour sample is on display. Private consultations can be arranged by appointment.'],
    ['q' => 'What does the warranty cover?',
     'a' => 'Every PORTES door carries a 25-year structural guarantee, transferable to new owners. It covers craftsmanship and dimensional stability under normal use.'],
    ['q' => 'How do I get a price for my project?',
     'a' => 'Use our configurator to specify collection, colour, usage, construction and dimensions — you’ll see an indicative price instantly, then request a tailored quotation. Our specialists respond within 24–48 hours.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Contact PORTES — visit our Algiers showroom, speak with a specialist, or request a tailored quotation for your bespoke architectural door." />
  <title>Contact — PORTES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/home.css?v=<?= $ver('/assets/css/home.css') ?>" />
  <link rel="stylesheet" href="/door-showroom/assets/css/contact.css?v=<?= $ver('/assets/css/contact.css') ?>" />
</head>
<body>

<!-- ░ NAV ░ -->
<header class="nav is-scrolled" id="nav">
  <a href="/door-showroom" class="nav-logo">
    <img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="nav-logo-img" />
  </a>
  <nav class="nav-links" id="navLinks" aria-label="Primary">
    <a href="/door-showroom/collections">Collections</a>
    <a href="/door-showroom/configure">Configurator</a>
    <a href="/door-showroom#colors">Colours</a>
    <a href="/door-showroom#featured">Doors</a>
    <a href="/door-showroom/contact" class="is-current">Contact</a>
  </nav>
  <a href="/door-showroom/configure" class="nav-cta">Request Quote</a>
  <button class="nav-burger" id="navBurger" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
</header>

<!-- ░ 1 · HERO ░ -->
<section class="ct-hero">
  <div class="ct-hero-bg"><img src="<?= $img('bghero.png') ?>" alt="" role="presentation" class="ct-hero-img" /><div class="ct-hero-overlay"></div></div>
  <div class="ct-hero-inner">
    <nav class="ct-crumb" aria-label="Breadcrumb"><a href="/door-showroom">Home</a><span>/</span><span>Contact</span></nav>
    <p class="eyebrow">Get in Touch</p>
    <h1 class="ct-hero-title">Let’s open<br /><em>a conversation.</em></h1>
    <p class="ct-hero-sub">Whether you’re an architect, a developer or a homeowner, our specialists are here to guide your project from first sketch to final installation.</p>
  </div>
</section>

<!-- ░ 2 · CONTACT INFORMATION ░ -->
<section class="ct-info">
  <div class="ct-info-grid reveal">
    <a class="ct-info-card" href="<?= $e($telHref) ?>">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M5 4h4l2 5-3 2a14 14 0 006 6l2-3 5 2v4a2 2 0 01-2 2A18 18 0 013 6a2 2 0 012-2z"/></svg></span>
      <span class="ct-info-label">Telephone</span>
      <span class="ct-info-value"><?= $e($phone) ?></span>
    </a>
    <a class="ct-info-card" href="mailto:<?= $e($email) ?>">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
      <span class="ct-info-label">Email</span>
      <span class="ct-info-value"><?= $e($email) ?></span>
    </a>
    <div class="ct-info-card">
      <span class="ct-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 21s-7-6.2-7-11a7 7 0 0114 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
      <span class="ct-info-label">Showroom</span>
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
    <p class="eyebrow">The Showroom</p>
    <h2 class="ct-h2">Experience every<br /><em>door in person.</em></h2>
    <p class="ct-lead">Our Algiers showroom presents the full PORTES catalogue — each collection, finish and material sample, displayed as it deserves. Touch the timber, feel the hardware, and see how light falls across each surface.</p>
    <dl class="ct-hours">
      <div><dt>Monday — Thursday</dt><dd>9:00 — 18:00</dd></div>
      <div><dt>Saturday</dt><dd>10:00 — 17:00</dd></div>
      <div><dt>Friday — Sunday</dt><dd>By appointment</dd></div>
    </dl>
    <a href="#ct-map" class="btn btn--outline">View on Map
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ░ 4 · CONTACT FORM ░ -->
<section class="ct-form-section" id="ct-form">
  <div class="ct-form-wrap reveal">
    <div class="ct-form-intro">
      <p class="eyebrow">Send a Message</p>
      <h2 class="ct-h2">Tell us about<br /><em>your project.</em></h2>
      <p class="ct-lead">Share a few details and the right specialist will be in touch within 24 hours. For a priced configuration, use our configurator instead.</p>
    </div>

    <form class="ct-form" id="ctForm" novalidate>
      <div class="ct-field-grid">
        <div class="ct-field">
          <label for="ctName">Name *</label>
          <input type="text" id="ctName" name="name" autocomplete="name" maxlength="120" required />
          <span class="ct-err" data-err="name"></span>
        </div>
        <div class="ct-field">
          <label for="ctEmail">Email *</label>
          <input type="email" id="ctEmail" name="email" autocomplete="email" maxlength="180" required />
          <span class="ct-err" data-err="email"></span>
        </div>
        <div class="ct-field">
          <label for="ctPhone">Phone <span class="ct-opt">(optional)</span></label>
          <input type="tel" id="ctPhone" name="phone" autocomplete="tel" maxlength="30" />
          <span class="ct-err" data-err="phone"></span>
        </div>
        <div class="ct-field">
          <label for="ctSubject">Subject <span class="ct-opt">(optional)</span></label>
          <input type="text" id="ctSubject" name="subject" maxlength="160" />
          <span class="ct-err" data-err="subject"></span>
        </div>
      </div>
      <div class="ct-field">
        <label for="ctMessage">Message *</label>
        <textarea id="ctMessage" name="message" rows="6" maxlength="4000" required placeholder="Tell us about your project — type of space, timeline, any questions…"></textarea>
        <span class="ct-err" data-err="message"></span>
      </div>
      <!-- honeypot: hidden from humans, catches bots -->
      <div class="ct-hp" aria-hidden="true">
        <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off" /></label>
      </div>
      <div class="ct-form-foot">
        <button type="submit" class="btn btn--gold btn--lg" id="ctSubmit">Send Message
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
        <p class="ct-form-msg" id="ctFormMsg" hidden></p>
      </div>
    </form>

    <!-- success state -->
    <div class="ct-success" id="ctSuccess" hidden>
      <span class="ct-success-mark"><svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="32" cy="32" r="29"/><path d="M20 33l8 8 16-18" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
      <h3>Message received.</h3>
      <p>Thank you for reaching out. Our team will respond within 24 hours.</p>
    </div>
  </div>
</section>

<!-- ░ 5 · WHATSAPP CTA ░ -->
<section class="ct-whatsapp">
  <div class="ct-whatsapp-inner reveal">
    <span class="ct-whatsapp-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></span>
    <div class="ct-whatsapp-text">
      <h2 class="ct-h2">Prefer to chat?</h2>
      <p class="ct-lead">Message us directly on WhatsApp for a quick response during showroom hours.</p>
    </div>
    <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="btn btn--gold btn--lg">Chat on WhatsApp</a>
  </div>
</section>

<!-- ░ 6 · GOOGLE MAPS AREA ░ -->
<section class="ct-map" id="ct-map">
  <div class="ct-map-overlay">
    <div class="ct-map-card reveal">
      <p class="eyebrow">Find Us</p>
      <h3 class="ct-map-title">PORTES Showroom</h3>
      <p class="ct-map-addr"><?= $e($address) ?></p>
      <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($address) ?>" target="_blank" rel="noopener" class="btn btn--outline-light">Open in Google Maps
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
    <p class="eyebrow">Questions</p>
    <h2 class="ct-h2">Answers, before<br /><em>you ask.</em></h2>
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
    <h2 class="quote-title">Ready to design<br /><em>your door?</em></h2>
    <p class="quote-sub">Configure your door to exact specification and request a tailored quotation in minutes.</p>
    <div class="quote-actions">
      <a href="/door-showroom/configure" class="btn btn--gold btn--lg">Open the Configurator
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="/door-showroom/collections" class="btn btn--outline-light btn--lg">Browse Collections</a>
    </div>
  </div>
</section>

<!-- ░ FOOTER ░ -->
<footer class="footer" id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo"><img src="/door-showroom/assets/images/logo-adk.png" alt="ADK — Algerian Doors &amp; Kitchens" class="footer-logo-img" /></div>
      <p class="footer-tag">Luxury architectural doors,<br />designed by you. Engineered to last.</p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v7h3v-7H16l.5-3h-3V9.5c0-.3.2-.5.5-.5z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg></a>
        <a href="<?= $e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg></a>
      </div>
    </div>
    <div class="footer-col"><h4>Collections</h4><ul>
      <li><a href="/door-showroom/collections/heritage">Heritage</a></li>
      <li><a href="/door-showroom/collections/moderne">Moderne</a></li>
      <li><a href="/door-showroom/collections/prestige">Prestige</a></li>
    </ul></div>
    <div class="footer-col"><h4>Services</h4><ul>
      <li><a href="/door-showroom/configure">Configurator</a></li>
      <li><a href="/door-showroom#quote">Made to Measure</a></li>
      <li><a href="/door-showroom#quote">Installation</a></li>
    </ul></div>
    <div class="footer-col"><h4>Company</h4><ul>
      <li><a href="/door-showroom#why">About Us</a></li>
      <li><a href="/door-showroom/contact">Contact</a></li>
      <li><a href="/door-showroom/collections">Projects</a></li>
    </ul></div>
    <div class="footer-col"><h4>Contact</h4><ul>
      <li><a href="<?= $e($telHref) ?>"><?= $e($phone) ?></a></li>
      <li><a href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a></li>
      <li><span>Algiers, Algeria</span></li>
      <li><a href="<?= $e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
    </ul></div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> PORTES. All rights reserved.</span>
    <span>Luxury Architectural Doors — Algiers</span>
  </div>
</footer>

<a href="<?= $e($wa) ?>" target="_blank" rel="noopener" class="wa-float" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1012 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.2.6.2.4.8 1.3 1.6 2 .9.8 1.7 1.1 2.1 1.3.2.1.5.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.9.9c.2.1.4.2.4.3.1.1.1.6-.1 1.2z"/></svg>
</a>

<script type="application/json" id="ctData"><?= json_encode(['csrf' => $token, 'url' => '/door-showroom/contact/submit'], JSON_HEX_TAG) ?></script>
<script src="/door-showroom/assets/js/home.js?v=<?= $ver('/assets/js/home.js') ?>"></script>
<script src="/door-showroom/assets/js/contact.js?v=<?= $ver('/assets/js/contact.js') ?>"></script>
</body>
</html>
