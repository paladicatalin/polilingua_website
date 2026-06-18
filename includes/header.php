<?php
// includes/header.php
// Variables expected: $lang, $translations, $page (optional)
$currentLang = $lang ?? 'ro';
$currentPage = $page ?? 'home';
$siteUrl = SITE_URL;

/**
 * Normalizează URL-urile pentru rețelele sociale.
 */
$normalizeSocialUrl = static function (string $url, string $fallback): string {
    $trimmed = trim($url);
    if ($trimmed === '') {
        return $fallback;
    }
    if (preg_match('/^https?:\/\//i', $trimmed) === 1) {
        return $trimmed;
    }
    return 'https://' . ltrim($trimmed, '/');
};

$instagramUrl = $normalizeSocialUrl((string)getContent('social_instagram_url', $currentLang), 'https://www.instagram.com/');
$facebookUrl = $normalizeSocialUrl((string)getContent('social_facebook_url', $currentLang), 'https://www.facebook.com/');
$linkedinUrl = $normalizeSocialUrl((string)getContent('social_linkedin_url', $currentLang), 'https://www.linkedin.com/');
$twitterUrl = $normalizeSocialUrl((string)getContent('social_twitter_url', $currentLang), 'https://x.com/');
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>PoliLingua</title>
    
    <meta name="description" content="<?= e($pageDesc ?? 'PoliLingua - Descoperim noi oportunități de lucru') ?>">

    <link rel="icon" type="image/svg+xml" href="<?= $siteUrl ?>/assets/images/logo/logo.svg?v=1.1">
    <link rel="alternate icon" type="image/png" href="<?= $siteUrl ?>/assets/images/logo/logo.svg?v=1.1">
    <link rel="apple-touch-icon" href="<?= $siteUrl ?>/assets/images/logo/logo.svg?v=1.1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/style.css?v=1.1">
    <link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/responsive.css?v=1.1">
</head>
<body class="page-<?= e($currentPage) ?>">

<nav class="navbar" id="navbar">
    <a href="<?= $siteUrl ?>/index.php?lang=<?= $currentLang ?>" class="navbar-logo">
        <img src="<?= $siteUrl ?>/assets/images/logo/logo.svg" alt="PoliLingua">
    </a>

    <ul class="navbar-nav" id="nav-menu">
        <?php $homePrefix = $currentPage === 'home' ? '' : $siteUrl . '/index.php?lang=' . $currentLang; ?>
        <li><a href="<?= $homePrefix ?>#hero"><?= t('nav_home') ?></a></li>
        <li><a href="<?= $homePrefix ?>#jobs"><?= t('nav_jobs') ?></a></li>
        <li><a href="<?= $homePrefix ?>#about"><?= t('nav_about') ?></a></li>
        <li><a href="<?= $homePrefix ?>#why"><?= t('nav_why') ?></a></li>
        <li><a href="<?= $homePrefix ?>#contact"><?= t('nav_contact') ?></a></li>
        <li><a href="<?= $homePrefix ?>#careers"><?= t('nav_careers') ?></a></li>
    </ul>

    <div class="navbar-right">
        <div class="lang-dropdown" id="lang-dropdown">
            <button class="lang-toggle" id="lang-toggle" type="button" aria-expanded="false" aria-haspopup="true">
                <?= strtoupper($currentLang) ?>
                <span class="lang-caret" aria-hidden="true"></span>
            </button>
            <div class="lang-menu" id="lang-menu" role="menu">
                <?php foreach (SUPPORTED_LANGS as $l): ?>
                    <a href="?lang=<?= $l ?>" class="lang-option <?= $currentLang === $l ? 'active' : '' ?>" role="menuitem">
                        <?= strtoupper($l) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="social-icons" id="social-icons">
    <button class="social-toggle" id="social-toggle" type="button" aria-label="Rețele sociale" aria-expanded="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 12h10M12 7v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </button>
    <div class="social-icons-list" id="social-icons-list">
        <a href="<?= e($instagramUrl) ?>" class="social-icon" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
        </a>
        <a href="<?= e($facebookUrl) ?>" class="social-icon" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <a href="<?= e($linkedinUrl) ?>" class="social-icon" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6.94 8.5A1.56 1.56 0 1 1 6.94 5.38a1.56 1.56 0 0 1 0 3.12ZM5.63 9.75h2.62V18H5.63V9.75Zm4.08 0h2.5v1.13h.04c.35-.66 1.2-1.35 2.47-1.35 2.64 0 3.13 1.74 3.13 4V18h-2.62v-3.96c0-.94-.02-2.15-1.31-2.15-1.31 0-1.51 1.02-1.51 2.08V18H9.71V9.75Z"/></svg>
        </a>
        <a href="<?= e($twitterUrl) ?>" class="social-icon" aria-label="Twitter" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.54 3H20.8l-7.12 8.13L22 21h-6.5l-5.08-5.9L5.2 21H1.92l7.61-8.69L2 3h6.66l4.58 5.36L17.54 3Zm-1.14 16h1.8L7.68 4.9H5.75L16.4 19Z"/></svg>
        </a>
    </div>
</div>