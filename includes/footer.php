<?php
// includes/footer.php
$currentLang = $lang ?? 'ro';
$currentPage = $page ?? 'home';
$homePrefix = $currentPage === 'home' ? '' : SITE_URL . '/index.php?lang=' . $currentLang;
$jobsUrl = SITE_URL . '/cariere.php?lang=' . $currentLang . '#jobs';
$contactUrl = $homePrefix . '#contact';
$aboutUrl = $homePrefix . '#about';
$careersUrl = $homePrefix . '#careers';
$faqUrl = 'https://www.polilingua.com/ro/întrebări-frecvente.htm';

$footerByLang = [
  'ro' => '© ' . date('Y') . ' PoliLingua. Toate drepturile rezervate.',
  'ru' => '© ' . date('Y') . ' PoliLingua. Все права защищены.',
  'en' => '© ' . date('Y') . ' PoliLingua. All rights reserved.',
];
$footerText = $footerByLang[$currentLang] ?? $footerByLang['ro'];
$footerAbout = trim(getContent('footer_about', $currentLang));
if ($footerAbout === '') {
  $footerAbout = 'Colaborăm cu lingviști atent selectați pentru a livra servicii de calitate, adaptate fiecărui proiect.';
}
$menuLinks = [
  ['href' => $homePrefix . '#hero', 'label' => t('nav_home')],
  ['href' => $jobsUrl, 'label' => t('nav_jobs')],
  ['href' => $aboutUrl, 'label' => t('nav_about')],
  ['href' => $homePrefix . '#why', 'label' => t('nav_why')],
  ['href' => $contactUrl, 'label' => t('nav_contact')],
  ['href' => $careersUrl, 'label' => t('nav_careers')],
];
$partners = [
  ['file' => 'elia.png', 'name' => 'ELIA'],
  ['file' => 'gala.png', 'name' => 'GALA'],
  ['file' => 'atc.png', 'name' => 'EUATC'],
  ['file' => 'cyber.png', 'name' => 'CYBER'],
  ['file' => 'atc_or.png', 'name' => 'ISO'],
];

$footerUiByLang = [
  'ro' => [
    'services_title' => 'Meniu',
    'info_title' => 'Informații',
    'faq_label' => 'FAQs',
    'partners_title' => 'Parteneri',
  ],
  'ru' => [
    'services_title' => 'Меню',
    'info_title' => 'Информация',
    'faq_label' => 'Частые вопросы',
    'partners_title' => 'Партнеры',
  ],
  'en' => [
    'services_title' => 'Menu',
    'info_title' => 'Information',
    'faq_label' => 'FAQs',
    'partners_title' => 'Partners',
  ],
];

$footerUi = $footerUiByLang[$currentLang] ?? $footerUiByLang['ro'];
?>
<footer class="footer">
  <div class="footer-grid">
    <div class="footer-col footer-col-brand">
      <a href="<?= SITE_URL ?>/index.php?lang=<?= e($currentLang) ?>" class="footer-brand-logo" aria-label="PoliLingua">
        <img src="<?= SITE_URL ?>/assets/images/logo/logo.svg" alt="PoliLingua" loading="lazy">
      </a>
      <p class="footer-description"><?= e($footerAbout) ?></p>
      <div class="footer-mini-actions">
        <a href="<?= e($contactUrl) ?>" class="footer-mini-btn"><?= t('nav_contact') ?></a>
        <a href="<?= $jobsUrl ?>" class="footer-jobs-link"><?= t('nav_jobs') ?></a>
      </div>
    </div>

    <div class="footer-col">
      <h3 class="footer-title"><?= e($footerUi['services_title']) ?></h3>
      <ul class="footer-list">
        <?php foreach ($menuLinks as $menuItem): ?>
          <li><a href="<?= e($menuItem['href']) ?>"><?= e($menuItem['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h3 class="footer-title"><?= e($footerUi['info_title']) ?></h3>
      <ul class="footer-list">
        <li><a href="<?= e($aboutUrl) ?>"><?= t('nav_about') ?></a></li>
        <li><a href="<?= e($faqUrl) ?>"><?= e($footerUi['faq_label']) ?></a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3 class="footer-title"><?= e($footerUi['partners_title']) ?></h3>
      <div class="footer-partners-grid">
        <?php foreach ($partners as $partner): ?>
          <?php
            $partnerFile = __DIR__ . '/../assets/images/logo/' . $partner['file'];
            $hasPartnerImage = is_file($partnerFile) && filesize($partnerFile) > 0;
          ?>
          <div class="footer-partner-badge" role="img" aria-label="<?= e($partner['name']) ?>">
            <?php if ($hasPartnerImage): ?>
              <img src="<?= SITE_URL ?>/assets/images/logo/<?= e($partner['file']) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
            <?php else: ?>
              <span class="footer-partner-fallback"><?= e($partner['name']) ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="footer-divider" aria-hidden="true"></div>
  <p class="footer-copy"><?= e($footerText) ?></p>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
</body>
</html>
