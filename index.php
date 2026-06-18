<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();
$lang = getCurrentLang();
loadLang($lang);

$jobs = getActiveJobs($lang);

$heroTitleText = trim((string)getContent('hero_title', $lang));
$heroSubtitleText = trim((string)getContent('hero_subtitle', $lang));
$jobsTitleText = trim((string)getContent('jobs_title', $lang));
$jobsSubtitleText = trim((string)getContent('jobs_subtitle', $lang));
$jobsEmptyText = trim((string)getContent('jobs_empty', $lang));

$heroTitleLines = preg_split('/\R+/', $heroTitleText !== '' ? $heroTitleText : 'Descoperim noi oportunități de lucru') ?: ['Descoperim noi oportunități de lucru'];
$heroTitleLines = array_values(array_filter(array_map('trim', $heroTitleLines), static fn($line) => $line !== ''));
if (empty($heroTitleLines)) {
  $heroTitleLines = ['Descoperim noi oportunități de lucru'];
}

$heroSubtitleLines = preg_split('/\R+/', $heroSubtitleText !== '' ? $heroSubtitleText : "Bun venit la PoliLingua,\no companie globală de recrutare profesională care împlinește experiența") ?: [];
$heroSubtitleLines = array_values(array_filter(array_map('trim', $heroSubtitleLines), static fn($line) => $line !== ''));
if (empty($heroSubtitleLines)) {
  $heroSubtitleLines = ['Bun venit la PoliLingua, o companie globală de recrutare profesională care împlinește experiența'];
}

$jobsTitleDisplay = $jobsTitleText !== '' ? $jobsTitleText : 'Posturi vacante';
$jobsSubtitleDisplay = $jobsSubtitleText !== '' ? $jobsSubtitleText : 'Suntem în căutare continuă de noi profesioniști, atât angajați înalt specializați, cât și noi tinere talente.';
$jobsEmptyDisplay = $jobsEmptyText !== '' ? $jobsEmptyText : 'Nu există posturi vacante momentan.';
$jobsCtaDisplay = t('btn_all_jobs');

$careersTitleDisplay = trim((string)getContent('careers_title', $lang));
$careersSubtitleDisplay = trim((string)getContent('careers_subtitle', $lang));
if ($careersTitleDisplay === '') $careersTitleDisplay = t('careers_page_title');

$pageTitle = 'PoliLingua - ' . ($heroTitleLines[0] ?? 'Descoperim noi oportunități de lucru');
$pageDesc = $heroSubtitleLines[0] ?? '';
$page = 'home';

$servicesTitleText = trim((string)getContent('services_title', $lang));
$servicesTitleDisplay = $servicesTitleText !== '' ? $servicesTitleText : t('services_title');
$services = getActiveServices($lang);

if (empty($services)) {
  $legacyServiceKeys = [
    'service1_title', 'service2_title', 'service3_title', 'service4_title',
    'service5_title', 'service6_title', 'service7_title', 'service8_title'
  ];
  foreach ($legacyServiceKeys as $legacyKey) {
    $legacyTitle = trim((string)t($legacyKey));
    if ($legacyTitle !== '' && $legacyTitle !== $legacyKey) {
      $services[] = [
        'title' => $legacyTitle,
        'description' => '',
        'icon_key' => 'clipboard-check',
      ];
    }
  }
}

$contactTitleLine1 = trim((string)getContent('contact_title_line1', $lang));
$contactTitleLine2 = trim((string)getContent('contact_title_line2', $lang));
$contactQuote = trim((string)getContent('contact_quote', $lang));
$contactQuoteAuthor = trim((string)getContent('contact_quote_author', $lang));
$contactCallLabel = trim((string)getContent('contact_call_label', $lang));
$contactMessageLabel = trim((string)getContent('contact_message_label', $lang));
$contactMapAddress = trim((string)getContent('contact_address', $lang));
$contactPhone = trim((string)getContent('contact_phone', $lang));
$contactEmail = trim((string)getContent('contact_email', $lang));

if ($contactTitleLine1 === '') $contactTitleLine1 = t('future_starts');
if ($contactTitleLine2 === '') $contactTitleLine2 = t('be_part');
if ($contactQuote === '') $contactQuote = t('about_quote');
if ($contactQuoteAuthor === '') $contactQuoteAuthor = 'Phil Jackson';
if ($contactCallLabel === '') $contactCallLabel = t('contact_call');
if ($contactMessageLabel === '') $contactMessageLabel = t('contact_message');
if ($contactMapAddress === '') $contactMapAddress = 'BD. DECEBAL 6, CHIȘINĂU, MOLDOVA';
if ($contactPhone === '') $contactPhone = '+37360933888';
if ($contactEmail === '') $contactEmail = 'hr@polilingua.co.uk';

$contactMapQuery = rawurlencode($contactMapAddress);

include __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════ -->
<!-- HERO SECTION -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="hero hero-home" id="hero">
  <div class="hero-rings" aria-hidden="true">
    <div class="hero-ring hero-ring-1"></div>
    <div class="hero-ring hero-ring-2"></div>
    <div class="hero-ring hero-ring-3"></div>
  </div>

  <div class="hero-avatar-layer" id="hero-orbit" aria-hidden="true">
    <div class="hero-avatar" data-ring="0" data-angle="25"><img src="<?= SITE_URL ?>/assets/images/avatars/pic1.png" alt=""></div>
    <div class="hero-avatar" data-ring="0" data-angle="205"><img src="<?= SITE_URL ?>/assets/images/avatars/pic2.png" alt=""></div>

    <div class="hero-avatar" data-ring="1" data-angle="35"><img src="<?= SITE_URL ?>/assets/images/avatars/pic3.png" alt=""></div>
    <div class="hero-avatar" data-ring="1" data-angle="155"><img src="<?= SITE_URL ?>/assets/images/avatars/pic4.png" alt=""></div>
    <div class="hero-avatar" data-ring="1" data-angle="275"><img src="<?= SITE_URL ?>/assets/images/avatars/pic5.png" alt=""></div>

    <div class="hero-avatar" data-ring="2" data-angle="80"><img src="<?= SITE_URL ?>/assets/images/avatars/pic6.png" alt=""></div>
    <div class="hero-avatar" data-ring="2" data-angle="200"><img src="<?= SITE_URL ?>/assets/images/avatars/pic7.png" alt=""></div>
    <div class="hero-avatar" data-ring="2" data-angle="320"><img src="<?= SITE_URL ?>/assets/images/avatars/pic8.png" alt=""></div>
  </div>

  <div class="hero-content hero-content-home reveal">
    <h1>
      <?php foreach ($heroTitleLines as $line): ?>
        <span class="hero-line"><?= e($line) ?></span>
      <?php endforeach; ?>
    </h1>
    <p>
      <?php foreach ($heroSubtitleLines as $line): ?>
        <span class="hero-line"><?= e($line) ?></span>
      <?php endforeach; ?>
    </p>
    <a href="cariere.php?lang=<?= $lang ?>" class="hero-cta"><?= t('btn_join') ?></a>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- POSTURI VACANTE -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="jobs-section" id="jobs">
  <div class="section-header reveal">
    <h2><?= e($jobsTitleDisplay) ?></h2>
    <p><?= e($jobsSubtitleDisplay) ?></p>
  </div>

  <div class="sticky-grid">
    <?php if (empty($jobs)): ?>
      <p style="text-align:center;color:#777;grid-column:1/-1"><?= e($jobsEmptyDisplay) ?></p>
    <?php else: ?>
      <?php
      $homeStickyFallback = ['#c5d4e8', '#d2deee', '#46668f'];
      foreach ($jobs as $idx => $job):
        $rawColor = trim((string)($job['sticky_color'] ?? ''));
        $isHexColor = preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $rawColor) === 1;
        $isTooLight = false;
        if ($isHexColor) {
          $hex = ltrim($rawColor, '#');
          if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
          }
          if (strlen($hex) === 8) {
            $hex = substr($hex, 0, 6);
          }
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
          $isTooLight = (($r + $g + $b) / 3) >= 238;
        }
        $stickyColor = ($isHexColor && !$isTooLight) ? $rawColor : $homeStickyFallback[$idx % count($homeStickyFallback)];
        $rawTilt = str_replace(',', '.', trim((string)($job['sticky_rotation'] ?? '')));
        $stickyTilt = is_numeric($rawTilt) ? (float)$rawTilt : (($idx % 2 === 0) ? -4.5 : 4.5);
      ?>
        <div class="sticky-note reveal" style="--sticky-bg: <?= e($stickyColor) ?>; --sticky-tilt: <?= $stickyTilt ?>deg;">
          <div>
            <h3><?= e((string)($job['title'] ?? '')) ?></h3>
            <p><?= e((string)($job['short_desc'] ?? '')) ?></p>
          </div>
          <div class="job-meta">
            <?php if ($job['location']): ?>
              <span><?= e((string)$job['location']) ?></span>
            <?php endif; ?>
            <?php if ($job['schedule']): ?>
              <span><?= e((string)$job['schedule']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="jobs-cta reveal">
    <a href="cariere.php?lang=<?= $lang ?>" class="btn btn-outline"><?= e($jobsCtaDisplay) ?></a>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- DESPRE POLILINGUA -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="about-section" id="about">
  <div class="about-grid">
    <div class="about-text reveal">
      <h2><?= e((string)getContent('about_title', $lang)) ?></h2>
      <p><?= e((string)getContent('about_text', $lang)) ?></p>
    </div>
    <div class="about-visual reveal" aria-hidden="true">
      <div class="about-avatar-cluster">
        <div class="about-brand-mark">
          <span class="about-brand-ring about-brand-ring-outer"></span>
          <span class="about-brand-ring about-brand-ring-inner"></span>
          <span class="about-brand-dot"></span>
        </div>

        <figure class="about-avatar about-avatar-main about-avatar-circle">
          <img src="<?= SITE_URL ?>/assets/images/avatars/pic4.png" alt="">
        </figure>

        <figure class="about-avatar about-avatar-one about-avatar-cut">
          <img src="<?= SITE_URL ?>/assets/images/avatars/pic2.png" alt="">
        </figure>

        <div class="about-talent-badge">
          <span><?= t('new_talent') ?></span>
        </div>

        <figure class="about-avatar about-avatar-two about-avatar-cut">
          <img src="<?= SITE_URL ?>/assets/images/avatars/pic5.png" alt="">
        </figure>

        <figure class="about-avatar about-avatar-three about-avatar-circle">
          <img src="<?= SITE_URL ?>/assets/images/avatars/pic8.png" alt="">
        </figure>

      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- SERVICII -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="services-section" id="why">
  <div class="services-wrap">
    <div class="services-head reveal">
      <h2><?= e($servicesTitleDisplay) ?></h2>
    </div>

    <div class="services-grid">
      <?php
      $serviceIcons = getServiceIconSvgs();
      $iconOrder = array_keys($serviceIcons);
      foreach ($services as $idx => $service):
        $fallbackIcon = $iconOrder[$idx % count($iconOrder)] ?? 'clipboard-check';
        $iconKey = normalizeServiceIconKey((string)($service['icon_key'] ?? $fallbackIcon));
        $iconSvg = $serviceIcons[$iconKey] ?? ($serviceIcons['clipboard-check'] ?? '');
        $serviceDescription = trim((string)($service['description'] ?? ''));
      ?>
        <article class="service-card reveal">
          <div class="service-card-glow" aria-hidden="true"></div>
          <div class="service-card-icon" aria-hidden="true"><?= $iconSvg ?></div>
          <h3><?= e((string)($service['title'] ?? '')) ?></h3>
          <?php if ($serviceDescription !== ''): ?>
            <p><?= e($serviceDescription) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="services-more reveal">
      <a href="https://www.polilingua.com/ro/servicii.htm" class="btn btn-green" target="_blank" rel="noopener noreferrer"><?= t('btn_learn_more') ?></a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- CONTACT BANNER -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="contact-banner" id="contact">
  <div class="contact-banner-inner">
    <div class="contact-col contact-col-right reveal">
      <h3 class="contact-focus-title">
        <span><?= e($contactTitleLine1) ?></span>
        <span><?= e($contactTitleLine2) ?></span>
      </h3>

      <div class="contact-map-shell">
        <iframe
          class="contact-map"
          title="<?= t('contact_find') ?>"
          src="https://www.google.com/maps?q=<?= $contactMapQuery ?>&output=embed"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          allowfullscreen></iframe>
        <p class="contact-map-address"><?= e($contactMapAddress) ?></p>
      </div>

    </div>

    <div class="contact-col contact-col-left reveal">
      <p class="contact-quote"><?= e($contactQuote) ?></p>
      <p class="contact-quote-author">- <?= e($contactQuoteAuthor) ?></p>

      <div class="contact-cards">
        <article class="contact-card contact-card--call reveal">
          <div class="contact-card-logo-wrap" aria-hidden="true">
            <img class="contact-card-logo" src="<?= SITE_URL ?>/assets/images/logo/contact-call-placeholder.png" alt="" loading="lazy">
          </div>
          <div class="contact-card-content">
            <h4><?= e($contactCallLabel) ?></h4>
            <p><a href="tel:<?= e(preg_replace('/\s+/', '', $contactPhone)) ?>"><?= e($contactPhone) ?></a></p>
          </div>
        </article>

        <article class="contact-card contact-card--message reveal">
          <div class="contact-card-logo-wrap" aria-hidden="true">
            <img class="contact-card-logo" src="<?= SITE_URL ?>/assets/images/logo/contact-message-placeholder.png" alt="" loading="lazy">
          </div>
          <div class="contact-card-content">
            <h4><?= e($contactMessageLabel) ?></h4>
            <p><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></p>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- CARIERE PROMO -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="careers-promo" id="careers">
  <div class="careers-promo-decor careers-promo-decor-left" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M4 5.3252H6M5 4.3252V6.3252M11.5 4.3252L11 6.3252M18 5.3252H20M19 4.3252V6.3252M15 9.3252L14 10.3252M18 13.3252L20 12.8252M18 19.3252H20M19 18.3252V20.3252M14 16.8432L7.48205 10.3252L3.09205 19.9052C3.00528 20.0912 2.97785 20.2994 3.01347 20.5015C3.0491 20.7037 3.14605 20.8899 3.29118 21.0351C3.43632 21.1802 3.62259 21.2771 3.82472 21.3128C4.02685 21.3484 4.23505 21.321 4.42105 21.2342L14 16.8432Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    </svg>
  </div>
  <div class="careers-promo-decor careers-promo-decor-right" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M4 5.3252H6M5 4.3252V6.3252M11.5 4.3252L11 6.3252M18 5.3252H20M19 4.3252V6.3252M15 9.3252L14 10.3252M18 13.3252L20 12.8252M18 19.3252H20M19 18.3252V20.3252M14 16.8432L7.48205 10.3252L3.09205 19.9052C3.00528 20.0912 2.97785 20.2994 3.01347 20.5015C3.0491 20.7037 3.14605 20.8899 3.29118 21.0351C3.43632 21.1802 3.62259 21.2771 3.82472 21.3128C4.02685 21.3484 4.23505 21.321 4.42105 21.2342L14 16.8432Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    </svg>
  </div>

  <div class="careers-promo-inner">
    <h2 class="reveal"><?= e($careersTitleDisplay) ?></h2>
    <p class="reveal"><?= e($careersSubtitleDisplay) ?></p>
    <a href="cariere.php?lang=<?= $lang ?>" class="hero-cta reveal"><?= t('btn_join') ?></a>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- APPLY FORM -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="apply-section" id="apply">
  <div class="apply-card reveal">
    <div class="apply-left">
      <h2>
        <?php foreach (preg_split('/\s+/', trim((string) t('talent_diff'))) as $word): ?>
          <span><?= e($word) ?></span>
        <?php endforeach; ?>
      </h2>
    </div>
    <div class="apply-right">
      <form id="apply-form" action="<?= SITE_URL ?>/apply.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label><?= t('form_job') ?></label>
          <select name="job_id">
            <option value=""><?= t('form_select_job') ?></option>
            <?php foreach ($jobs as $job): ?>
              <option value="<?= $job['id'] ?>"><?= e($job['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label><?= t('form_name') ?></label>
          <input type="text" name="name" required placeholder="<?= t('form_name') ?>">
        </div>
        <div class="form-group">
          <label><?= t('form_email') ?></label>
          <input type="email" name="email" required placeholder="<?= t('form_email') ?>">
        </div>
        <div class="form-group">
          <label><?= t('form_phone') ?></label>
          <input type="tel" name="phone" required placeholder="<?= t('form_phone') ?>">
        </div>
        <div class="form-group">
          <label><?= t('form_cv') ?></label>
          <input type="file" name="cv_file" accept=".pdf,.doc,.docx">
        </div>
        <button type="submit" class="btn btn-green" data-label="<?= t('btn_apply') ?>"><?= t('btn_apply') ?></button>
        <div class="form-success" id="form-success"><?= t('form_success') ?></div>
        <div class="form-error-msg" id="form-error"><?= t('form_error') ?></div>
      </form>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
