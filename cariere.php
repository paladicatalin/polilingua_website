<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();
$lang = getCurrentLang();
loadLang($lang);

$jobs = getActiveJobs($lang);
$careersTitle = trim((string)getContent('careers_title', $lang));
$careersSubtitle = trim((string)getContent('careers_subtitle', $lang));
$jobsTitle = trim((string)getContent('jobs_title', $lang));
$jobsSubtitle = trim((string)getContent('jobs_subtitle', $lang));
$jobsEmpty = trim((string)getContent('jobs_empty', $lang));
$whyJoinTitle = trim((string)getContent('why_join_team', $lang));

if ($careersTitle === '') $careersTitle = t('careers_page_title');
if ($jobsTitle === '') $jobsTitle = t('nav_jobs');
if ($jobsEmpty === '') $jobsEmpty = 'Nu există posturi vacante momentan.';
if ($whyJoinTitle === '') $whyJoinTitle = t('why_join_team');

$whyJoinItems = [
    [
        'title' => trim((string)getContent('why_innovate', $lang)) ?: t('why_innovate'),
        'text' => trim((string)getContent('why_innovate_text', $lang)) ?: t('why_innovate_text'),
    ],
    [
        'title' => trim((string)getContent('why_empower', $lang)) ?: t('why_empower'),
        'text' => trim((string)getContent('why_empower_text', $lang)) ?: t('why_empower_text'),
    ],
    [
        'title' => trim((string)getContent('why_unite', $lang)) ?: t('why_unite'),
        'text' => trim((string)getContent('why_unite_text', $lang)) ?: t('why_unite_text'),
    ],
    [
        'title' => trim((string)getContent('why_sustain', $lang)) ?: t('why_sustain'),
        'text' => trim((string)getContent('why_sustain_text', $lang)) ?: t('why_sustain_text'),
    ],
    [
        'title' => trim((string)getContent('why_inspire', $lang)) ?: t('why_inspire'),
        'text' => trim((string)getContent('why_inspire_text', $lang)) ?: t('why_inspire_text'),
    ],
    [
        'title' => trim((string)getContent('why_grow', $lang)) ?: t('why_grow'),
        'text' => trim((string)getContent('why_grow_text', $lang)) ?: t('why_grow_text'),
    ],
];

$pageTitle = 'PoliLingua - ' . $careersTitle;
$pageDesc = $careersSubtitle;
$page = 'careers';

$resolveCareersImage = static function (string $baseName): ?string {
    $relativeBases = [
        'assets/images/' . $baseName,
        'assets/images/careers/' . $baseName,
        'assets/' . $baseName,
    ];

    foreach ($relativeBases as $relativeBase) {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'avif'] as $ext) {
            $relativePath = $relativeBase . '.' . $ext;
            $absolutePath = __DIR__ . '/' . $relativePath;
            if (is_file($absolutePath) && filesize($absolutePath) > 0) {
                return SITE_URL . '/' . $relativePath;
            }
        }
    }

    return null;
};

$careersImages = [];
for ($i = 1; $i <= 6; $i++) {
    $careersImages[$i] = $resolveCareersImage('imaginea' . $i);
}

$employeeImages = [];
for ($i = 1; $i <= 12; $i++) {
    $employeeImages[$i] = $resolveCareersImage('angajat' . $i);
}

include __DIR__ . '/includes/header.php';
?>

<style>
  .page-careers .navbar {
    background: transparent;
    backdrop-filter: none;
    border-bottom: none;
    box-shadow: none;
    position: fixed;
    height: 76px;
    padding-top: 4px;
  }

  .page-careers .navbar.scrolled {
    position: fixed;
    background: rgba(245, 247, 250, 0.92);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(17, 31, 53, 0.1);
    box-shadow: 0 8px 24px rgba(12, 24, 43, 0.08);
    height: 70px;
    padding-top: 0;
  }

  .page-careers .navbar-logo img {
    width: 188px;
    height: 44px;
    max-width: none;
  }

  .page-careers .navbar.scrolled .navbar-logo img {
    width: 180px;
    height: 40px;
  }

  .page-careers .navbar-nav {
    gap: 0px;
  }

  .page-careers .navbar-nav a {
    display: block;
    width: 100%;
    box-sizing: border-box;
    font-size: 0.94rem;
    font-weight: 700;
    color: #141e32;
    padding: 8px 16px;
    border-radius: 0;
    line-height: 1.2;
    text-align: center;
  }

  .page-careers .navbar.scrolled .navbar-nav a {
    font-size: 0.88rem;
  }

  @media (min-width: 769px) {
    .page-careers .navbar-nav a:hover,
    .page-careers .navbar-nav a:focus-visible,
    .page-careers .navbar-nav a:active,
    .page-careers .navbar-nav li:hover > a,
    .page-careers .navbar-nav li:focus-within > a {
      color: var(--blue-mid);
      background: transparent;
      box-shadow: none;
      transform: none;
      margin: 0;
    }
  }

  .page-careers .lang-toggle {
    border: none;
    background: transparent;
    font-size: 1rem;
    font-weight: 700;
    color: #141e32;
    padding: 5px 4px;
  }

  .page-careers .navbar.scrolled .lang-toggle {
    font-size: 0.99rem;
  }

  .page-careers .lang-toggle:hover {
    background: transparent;
    color: var(--blue-mid);
  }

  .page-careers .lang-menu {
    border: 1px solid rgba(20, 30, 50, 0.1);
  }

  .page-careers #jobs .section-label {
    background: #e91e8c;
    color: #fff;
  }

  .page-careers .careers-promo-decor {
    width: clamp(170px, 17vw, 250px);
    height: clamp(170px, 17vw, 250px);
  }

  .page-careers .careers-promo-decor-left {
    left: clamp(-54px, 0.3vw, 6px);
  }

  .page-careers .careers-promo-decor-right {
    right: clamp(-54px, 0.3vw, 6px);
  }

  .careers-gallery-section {
    padding: 34px var(--layout-inline-padding) 68px;
    background: var(--bg-light);
  }

  .careers-gallery-grid {
    max-width: 1260px;
    margin: 0 auto;
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(180px, 1.05fr) minmax(180px, 0.95fr) minmax(260px, 2.1fr) minmax(180px, 0.95fr);
    grid-template-areas:
      "left mid-top main right-top"
      "left mid-bottom main right-bottom";
  }

  .careers-gallery-card {
    background: #d6d9de;
    overflow: hidden;
    position: relative;
  }

  .careers-gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .careers-gallery-left { grid-area: left; height: 420px !important; min-height: 450px !important; }
  .careers-gallery-mid-top { grid-area: mid-top; height: 250px !important; min-height: 250px !important; }
  .careers-gallery-mid-bottom { grid-area: mid-bottom; height: 155px !important; min-height: 185px !important; }
  .careers-gallery-main { grid-area: main; height: 420px !important; min-height: 450px !important; }
  .careers-gallery-right-top { grid-area: right-top; height: 155px !important; min-height: 165px !important; }
  .careers-gallery-right-bottom { grid-area: right-bottom; height: 270px !important; min-height:200px !important;margin-top: -85px; }

  .careers-gallery-placeholder {
    width: 100%;
    height: 100%;
    min-height: 72px;
    border: 2px dashed rgba(30, 58, 138, 0.3);
    background: repeating-linear-gradient(
      -45deg,
      rgba(147, 167, 198, 0.16),
      rgba(147, 167, 198, 0.16) 12px,
      rgba(147, 167, 198, 0.05) 12px,
      rgba(147, 167, 198, 0.05) 24px
    );
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #3b4f6e;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 12px;
  }

  .employees-gallery-section {
    padding: 0 var(--layout-inline-padding) 72px;
    background: var(--bg-light);
  }

  .employees-gallery-head {
    max-width: 1260px;
    margin: 0 auto 18px;
    text-align: center;
  }

  .employees-gallery-head h2 {
    margin: 0;
    font-family: var(--font-display);
    font-size: clamp(1.7rem, 2.8vw, 2.55rem);
    font-weight: 700;
    line-height: 1.18;
    color: #182438;
  }

  .employees-gallery-grid {
    max-width: 1260px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }

  .employees-gallery-card {
    background: #d6d9de;
    overflow: hidden;
    border-radius: 8px;
    aspect-ratio: 4 / 5;
  }

  .employees-gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .employees-gallery-placeholder {
    width: 100%;
    height: 100%;
    border: 2px dashed rgba(30, 58, 138, 0.3);
    background: repeating-linear-gradient(
      -45deg,
      rgba(147, 167, 198, 0.16),
      rgba(147, 167, 198, 0.16) 12px,
      rgba(147, 167, 198, 0.05) 12px,
      rgba(147, 167, 198, 0.05) 24px
    );
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #3b4f6e;
    font-weight: 600;
    font-size: 0.84rem;
    padding: 10px;
  }

  @media (max-width: 1080px) {
    .page-careers .careers-promo-decor {
      width: clamp(132px, 16vw, 190px);
      height: clamp(132px, 16vw, 190px);
    }

    .careers-gallery-grid {
      grid-template-columns: 1fr 1fr;
      grid-template-areas:
        "left main"
        "mid-top right-top"
        "mid-bottom right-bottom";
    }

    .careers-gallery-left,
    .careers-gallery-main,
    .careers-gallery-mid-top,
    .careers-gallery-mid-bottom,
    .careers-gallery-right-top,
    .careers-gallery-right-bottom {
      height: 140px !important;
      min-height: 140px !important;
    }

    .employees-gallery-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }

  @media (max-width: 768px) {
    .page-careers .navbar,
    .page-careers .navbar.scrolled {
      left: 0;
      right: 0;
      width: auto;
      max-width: none;
      box-sizing: border-box;
      height: 60px;
      padding-left: max(10px, env(safe-area-inset-left));
      padding-right: max(10px, env(safe-area-inset-right));
    }

    .page-careers .navbar {
      position: fixed;
      height: 60px;
      padding-top: 0;
      background: transparent;
      backdrop-filter: none;
      border-bottom: none;
      box-shadow: none;
    }

    .page-careers .navbar.scrolled {
      height: 60px;
      background: rgba(245, 247, 250, 0.94);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid rgba(19, 31, 52, 0.08);
      padding-top: 0;
    }

    .page-careers .navbar-logo img,
    .page-careers .navbar.scrolled .navbar-logo img {
      width: clamp(118px, 36vw, 144px);
      height: auto;
    }

    .page-careers .navbar-nav {
      gap: 0px;
    }

    .page-careers .navbar-nav li {
      margin: 0;
      list-style: none;
    }

    .page-careers .navbar-nav a:hover,
    .page-careers .navbar-nav a:focus-visible,
    .page-careers .navbar-nav a:active {
      color: #ffffff;
      background: linear-gradient(135deg, #4F77A8 0%, #2D4E75 100%);
      box-shadow: 0 2px 8px rgba(79, 119, 168, 0.3);
      transform: translateX(2px);
    }

    .page-careers .lang-toggle,
    .page-careers .navbar.scrolled .lang-toggle {
      padding: 4px 5px;
      font-size: 0.90rem;
    }
  }

  @media (max-width: 700px) {
    .careers-gallery-section {
      padding-top: 24px;
      padding-bottom: 44px;
    }

    .careers-gallery-grid {
      grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
      grid-template-areas:
        "main right-top"
        "main right-bottom";
      gap: 10px;
    }

    .careers-gallery-card {
      height: 132px !important;
      min-height: 132px !important;
    }

    .careers-gallery-main {
      height: 274px !important;
      min-height: 274px !important;
    }

    .careers-gallery-right-top,
    .careers-gallery-right-bottom {
      display: block !important;
      margin-top: 0 !important;
    }

    .careers-gallery-left,
    .careers-gallery-mid-top,
    .careers-gallery-mid-bottom {
      display: none !important;
    }

    .employees-gallery-section {
      padding-bottom: 44px;
    }

    .employees-gallery-head {
      margin-bottom: 14px;
    }

    .employees-gallery-head h2 {
      font-size: clamp(1.35rem, 5.8vw, 1.9rem);
    }

    .employees-gallery-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .employees-gallery-grid .employees-gallery-card:nth-child(n + 7) {
      display: none;
    }
  }

  @media (max-width: 480px) {
    .page-careers .navbar,
    .page-careers .navbar.scrolled {
      height: 56px;
      padding-left: max(8px, env(safe-area-inset-left));
      padding-right: max(8px, env(safe-area-inset-right));
    }

    .page-careers .navbar-logo img,
    .page-careers .navbar.scrolled .navbar-logo img {
      width: clamp(108px, 34vw, 130px);
    }

    .page-careers .lang-toggle,
    .page-careers .navbar.scrolled .lang-toggle {
      padding: 3px 4px;
      font-size: 0.74rem;
    }
  }

  .page-careers .why-join-section {
    padding: 86px var(--layout-inline-padding) 96px;
    background: var(--bg-light);
  }

  .page-careers .why-join-head {
    max-width: 1320px;
    margin: 0 auto 62px;
    text-align: center;
  }

  .page-careers .why-join-head h2 {
    margin: 0;
    font-family: var(--font-main);
    font-size: clamp(1.68rem, 3.2vw, 3.45rem);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.02em;
    color: #030712;
  }

  .page-careers .why-join-grid {
    max-width: 1540px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    column-gap: 56px;
    row-gap: 54px;
  }

  .page-careers .why-join-point {
    grid-column: span 2;
  }

  .page-careers .why-join-point-1 { grid-column: 1 / span 2; }
  .page-careers .why-join-point-2 { grid-column: 3 / span 2; }
  .page-careers .why-join-point-3 { grid-column: 5 / span 2; }
  .page-careers .why-join-point-4 { grid-column: 1 / span 2; }
  .page-careers .why-join-point-5 { grid-column: 3 / span 2; }
  .page-careers .why-join-point-6 { grid-column: 5 / span 2; }

  .page-careers .why-join-title {
    margin: 0 0 12px 40px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-family: var(--font-main);
    font-size: clamp(1.25rem, 1.5vw, 1.85rem);
    font-weight: 800;
    line-height: 1.1;
    color: #0b0f19;
  }

  .page-careers .why-join-check {
    width: 28px;
    height: 28px;
    color: #22c55e;
    flex: 0 0 auto;
  }

  .page-careers .why-join-check svg {
    width: 100%;
    height: 100%;
    display: block;
  }

  .page-careers .why-join-point p {
    margin-left: 40px;
    color: #161b25;
    font-size: clamp(0.92rem, 0.95vw, 1.02rem);
    line-height: 1.45;
    max-width: 34ch;
  }

  @media (max-width: 1080px) {
    .page-careers .why-join-section {
      padding-top: 74px;
      padding-bottom: 82px;
    }

    .page-careers .why-join-head {
      margin-bottom: 42px;
    }

    .page-careers .why-join-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      column-gap: 34px;
      row-gap: 34px;
    }

    .page-careers .why-join-point,
    .page-careers .why-join-point-1,
    .page-careers .why-join-point-2,
    .page-careers .why-join-point-3,
    .page-careers .why-join-point-4,
    .page-careers .why-join-point-5,
    .page-careers .why-join-point-6 {
      grid-column: auto / span 1;
    }
  }

  @media (max-width: 700px) {
    .page-careers .why-join-section {
      padding-top: 56px;
      padding-bottom: 60px;
    }

    .page-careers .why-join-head {
      margin-bottom: 30px;
    }

    .page-careers .why-join-grid {
      grid-template-columns: 1fr;
      row-gap: 26px;
    }

    .page-careers .why-join-title {
      font-size: 1.2rem;
      margin-bottom: 10px;
    }

    .page-careers .why-join-check {
      width: 24px;
      height: 24px;
    }

    .page-careers .why-join-point p {
      max-width: none;
      font-size: 0.94rem;
    }
  }
</style>

<!-- ══════════════════════════════════════════════════════ -->
<!-- CARIERE HERO HEADER -->
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
    <h2 class="reveal"><?= e($careersTitle) ?></h2>
    <?php if ($careersSubtitle !== ''): ?>
      <p class="reveal"><?= e($careersSubtitle) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- IMAGE GALLERY -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="careers-gallery-section">
  <div class="careers-gallery-grid reveal">
    <figure class="careers-gallery-card careers-gallery-left">
      <?php if ($careersImages[1]): ?>
        <img src="<?= e($careersImages[4]) ?>" alt="Imagine cariere 1" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea1.jpg`</div>
      <?php endif; ?>
    </figure>

    <figure class="careers-gallery-card careers-gallery-mid-top">
      <?php if ($careersImages[2]): ?>
        <img src="<?= e($careersImages[3]) ?>" alt="Imagine cariere 2" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea2.jpg`</div>
      <?php endif; ?>
    </figure>

    <figure class="careers-gallery-card careers-gallery-main">
      <?php if ($careersImages[3]): ?>
        <img src="<?= e($careersImages[5]) ?>" alt="Imagine cariere 3" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea3.jpg`</div>
      <?php endif; ?>
    </figure>

    <figure class="careers-gallery-card careers-gallery-right-top">
      <?php if ($careersImages[4]): ?>
        <img src="<?= e($careersImages[2]) ?>" alt="Imagine cariere 4" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea4.jpg`</div>
      <?php endif; ?>
    </figure>

    <figure class="careers-gallery-card careers-gallery-mid-bottom">
      <?php if ($careersImages[5]): ?>
        <img src="<?= e($careersImages[6]) ?>" alt="Imagine cariere 5" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea5.jpg`</div>
      <?php endif; ?>
    </figure>

    <figure class="careers-gallery-card careers-gallery-right-bottom">
      <?php if ($careersImages[6]): ?>
        <img src="<?= e($careersImages[1]) ?>" alt="Imagine cariere 6" loading="lazy">
      <?php else: ?>
        <div class="careers-gallery-placeholder">Adaugă `assets/images/imaginea6.jpg`</div>
      <?php endif; ?>
    </figure>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- DE CE AR TREBUI SA TE ALATURE -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="why-join-section">
  <div class="why-join-head reveal">
    <h2><?= e($whyJoinTitle) ?></h2>
  </div>

  <div class="why-join-grid reveal">
    <?php foreach ($whyJoinItems as $idx => $item): ?>
      <article class="why-join-point why-join-point-<?= $idx + 1 ?>">
        <h3 class="why-join-title">
          <span><?= e($item['title']) ?></span>
          <span class="why-join-check" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.2"></circle>
              <path d="m7.5 12 3 3 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </span>
        </h3>
        <p><?= e($item['text']) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- POSTURI VACANTE - ACCORDION -->
<!-- ══════════════════════════════════════════════════════ -->
<section style="padding:70px var(--layout-inline-padding);background:var(--bg-light);" id="jobs">
  <div class="section-header reveal">
    <span class="section-label"><?= t('btn_join') ?></span>
    <h2><?= e($jobsTitle) ?></h2>
    <?php if ($jobsSubtitle !== ''): ?>
      <p><?= e($jobsSubtitle) ?></p>
    <?php endif; ?>
  </div>

  <div class="jobs-accordion">
    <?php if (empty($jobs)): ?>
      <p style="text-align:center;color:#777;"><?= e($jobsEmpty) ?></p>
    <?php else: ?>
      <?php foreach ($jobs as $job): ?>
        <div class="accordion-item reveal">
          <div class="accordion-header">
            <span><?= e($job['title']) ?></span>
            <div class="accordion-icon">&gt;</div>
          </div>
          <div class="accordion-body">
            <div class="accordion-body-inner">
              <?php if ($job['full_desc']): ?>
                <?= $job['full_desc'] ?>
              <?php else: ?>
                <p><?= e($job['short_desc']) ?></p>
              <?php endif; ?>
              <div style="margin-top:16px;">
                <?php if ($job['location']): ?>
                  <span style="display:inline-block;background:#e8eef6;color:#3d608a;padding:4px 12px;border-radius:50px;font-size:0.8rem;margin-right:8px;">📍 <?= e($job['location']) ?></span>
                <?php endif; ?>
                <?php if ($job['schedule']): ?>
                  <span style="display:inline-block;background:#dfe8f5;color:#325379;padding:4px 12px;border-radius:50px;font-size:0.8rem;">⏰ <?= e($job['schedule']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ -->
<!-- APPLY FORM -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="apply-section">
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

<!-- ══════════════════════════════════════════════════════ -->
<!-- GALERIE ANGAJAȚI -->
<!-- ══════════════════════════════════════════════════════ -->
<section class="employees-gallery-section">
  <div class="employees-gallery-head reveal">
    <h2><?= t('employees_gallery_title') ?></h2>
  </div>
  <div class="employees-gallery-grid reveal">
    <?php foreach ($employeeImages as $idx => $employeeImage): ?>
      <figure class="employees-gallery-card">
        <?php if ($employeeImage): ?>
          <img src="<?= e($employeeImage) ?>" alt="Angajat <?= (int)$idx ?>" loading="lazy">
        <?php else: ?>
          <div class="employees-gallery-placeholder">Adaugă `assets/images/angajat<?= (int)$idx ?>.jpg`</div>
        <?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
