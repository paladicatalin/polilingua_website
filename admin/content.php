<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$db = getDB();
$msg = '';
$translationStatus = '';
$allowedViews = ['home', 'careers'];
$activeView = $_GET['view'] ?? 'home';
if (!in_array($activeView, $allowedViews, true)) {
    $activeView = 'home';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postView = (string)($_POST['view'] ?? $activeView);
    if (!in_array($postView, $allowedViews, true)) {
        $postView = 'home';
    }

    $existingRows = $db->query("SELECT content_key, value_ru, value_en FROM site_content")->fetchAll();
    $existingMap = [];
    foreach ($existingRows as $existingRow) {
        $existingMap[$existingRow['content_key']] = $existingRow;
    }

    $updates = [];
    $textsForAi = [];
    $keysWithoutTranslation = [
        'contact_phone',
        'contact_email',
        'contact_quote_author',
        'social_instagram_url',
        'social_facebook_url',
        'social_linkedin_url',
        'social_twitter_url',
    ];

    foreach ($_POST as $key => $values) {
        if (!is_array($values)) continue;

        $val_ro = trim((string)($values['ro'] ?? ''));
        $currentRu = (string)($existingMap[$key]['value_ru'] ?? '');
        $currentEn = (string)($existingMap[$key]['value_en'] ?? '');

        $updates[$key] = [
            'ro' => $val_ro,
            'ru' => $currentRu,
            'en' => $currentEn,
        ];

        if ($val_ro === '') {
            $updates[$key]['ru'] = '';
            $updates[$key]['en'] = '';
            continue;
        }

        if (in_array($key, $keysWithoutTranslation, true)) {
            $updates[$key]['ru'] = $val_ro;
            $updates[$key]['en'] = $val_ro;
            continue;
        }

        $textsForAi[$key] = $val_ro;
    }

    $translationResult = ['ok' => true, 'translations' => [], 'error' => ''];
    $translationStatus = 'ok';
    if (!empty($textsForAi)) {
        $translationResult = translateRomanianTextsToRuEn($textsForAi);
        if (!($translationResult['ok'] ?? false)) {
            $translationStatus = (string)($translationResult['error'] ?? 'request_failed');
        }
    }

    foreach ($textsForAi as $key => $valRo) {
        $translatedRu = trim((string)($translationResult['translations'][$key]['ru'] ?? ''));
        $translatedEn = trim((string)($translationResult['translations'][$key]['en'] ?? ''));

        if ($translatedRu !== '' && $translatedEn !== '') {
            $updates[$key]['ru'] = $translatedRu;
            $updates[$key]['en'] = $translatedEn;
            continue;
        }

        if ($updates[$key]['ru'] === '') {
            $updates[$key]['ru'] = $valRo;
        }
        if ($updates[$key]['en'] === '') {
            $updates[$key]['en'] = $valRo;
        }

        if ($translationStatus === 'ok') {
            $translationStatus = 'partial';
        }
    }

    $stmt = $db->prepare("
        UPDATE site_content
        SET value_ro = ?, value_ru = ?, value_en = ?
        WHERE content_key = ?
    ");
    foreach ($updates as $key => $vals) {
        $stmt->execute([$vals['ro'], $vals['ru'], $vals['en'], $key]);
    }

    $msg = 'saved';
    header('Location: content.php?view=' . rawurlencode($postView) . '&msg=saved&translation=' . rawurlencode($translationStatus));
    exit;
}

// Group into sections for display
$sectionsByView = [
    'home' => [
        'Hero' => ['hero_title', 'hero_subtitle'],
        'Posturi vacante' => ['jobs_title', 'jobs_subtitle', 'jobs_empty'],
        'Despre' => ['about_title', 'about_text'],
        'Servicii (Titlu secțiune)' => ['services_title'],
        'Contact' => ['contact_title_line1', 'contact_title_line2', 'contact_quote', 'contact_quote_author', 'contact_call_label', 'contact_message_label', 'contact_phone', 'contact_email', 'contact_address'],
        'Rețele sociale' => ['social_instagram_url', 'social_facebook_url', 'social_linkedin_url', 'social_twitter_url'],
        'Cariere' => ['careers_title', 'careers_subtitle'],
        'Footer' => ['footer_about'],
    ],
    'careers' => [
        'Hero Cariere' => ['careers_title', 'careers_subtitle'],
        'De ce să te alături echipei' => [
            'why_join_team',
            'why_innovate',
            'why_innovate_text',
            'why_empower',
            'why_empower_text',
            'why_unite',
            'why_unite_text',
            'why_sustain',
            'why_sustain_text',
            'why_inspire',
            'why_inspire_text',
            'why_grow',
            'why_grow_text',
        ],
        'Posturi (pagina Cariere)' => ['jobs_title', 'jobs_subtitle', 'jobs_empty'],
    ],
];

$sections = $sectionsByView[$activeView];

$defaultRoValues = [
    'hero_title' => 'Descoperim noi oportunități de lucru',
    'hero_subtitle' => "Bun venit la PoliLingua,\no companie globală de recrutare profesională care împlinește experiența",
    'jobs_title' => 'Posturi vacante',
    'jobs_subtitle' => 'Suntem în căutare continuă de noi profesioniști, atât angajați înalt specializați, cât și noi tinere talente.',
    'jobs_empty' => 'Nu există posturi vacante momentan.',
    'about_title' => 'Despre PoliLingua',
    'about_text' => 'La PoliLingua, folosim o combinație puternică de creativitate umană și inteligență automată pentru a crea traduceri de calitate consecventă în viteză. Echipa noastră talentată este unită prin pasiunea pentru limbă și cultură. Cea mai bună motivație pentru noi este cunoașterea faptului că ajutăm mărcile globale să crească, să se implice și să ajungă la publicul lor internațional, transformând conținutul multilingv pentru ei. Lucrăm cu unele dintre cele mai bune și cunoscute companii din lume și am dezvoltat o cultură de învățare și îmbunătățire continuă, în care cheia principală a succesului este echipa noastră',
    'services_title' => 'Servicii',
    'careers_title' => 'Cariere',
    'careers_subtitle' => 'Descoperă-ți cariera de vis în compania noastră dinamică și inovatoare! Ești în căutarea unei cariere stimulatoare într-o companie care prețuiește inovația și creșterea?',
    'why_join_team' => 'De ce ar trebui să te alături echipei?',
    'why_innovate' => 'Inovați',
    'why_innovate_text' => 'Alătură-te nouă pentru a modifica și modela viitorul cu soluții inovatoare.',
    'why_empower' => 'Împuternicire',
    'why_empower_text' => 'Faceți parte dintr-o echipă care dă putere persoanelor să își atingă potențialul maxim.',
    'why_unite' => 'Uniți',
    'why_unite_text' => 'Uniți forțele cu noi pentru a crea o lume unită în progres și prosperitate.',
    'why_sustain' => 'Susținere',
    'why_sustain_text' => 'Contribuiți la eforturile de sustenabilitate și construiți un viitor mai ecologic cu noi.',
    'why_inspire' => 'Inspiră',
    'why_inspire_text' => 'Deveniți o inspirație lucrând cu o echipă dedicată să aibă un impact pozitiv.',
    'why_grow' => 'Creștere',
    'why_grow_text' => 'Dezvoltă-ți cariera într-un mediu care susține învățarea continuă și evoluția profesională.',
    'footer_about' => 'Colaborăm cu lingviști atent selectați pentru a livra servicii de calitate, adaptate fiecărui proiect.',
    'contact_title_line1' => 'Viitorul tău începe aici!',
    'contact_title_line2' => 'Fii parte din ceva măreț!',
    'contact_quote' => '„Puterea echipei este fiecare membru individual. Puterea fiecărui membru este echipa.”',
    'contact_quote_author' => 'Phil Jackson',
    'contact_call_label' => 'Sună acum',
    'contact_message_label' => 'Trimite mesaj',
    'contact_phone' => '+37360933888',
    'contact_email' => 'hr@polilingua.co.uk',
    'contact_address' => 'BD. DECEBAL 6, CHIȘINĂU, MOLDOVA',
    'social_instagram_url' => 'https://www.instagram.com/',
    'social_facebook_url' => 'https://www.facebook.com/',
    'social_linkedin_url' => 'https://www.linkedin.com/',
    'social_twitter_url' => 'https://x.com/',
];

$socialUrlKeys = [
    'social_instagram_url',
    'social_facebook_url',
    'social_linkedin_url',
    'social_twitter_url',
];

$requiredKeys = [];
foreach ($sectionsByView as $viewSections) {
    foreach ($viewSections as $keys) {
        foreach ($keys as $key) {
            $requiredKeys[$key] = true;
        }
    }
}

if (!empty($requiredKeys)) {
    $insertStmt = $db->prepare("
        INSERT INTO site_content (content_key, value_ro, value_ru, value_en)
        VALUES (?, ?, '', '')
        ON DUPLICATE KEY UPDATE
            value_ro = IF(value_ro IS NULL OR value_ro = '', VALUES(value_ro), value_ro)
    ");
    foreach (array_keys($requiredKeys) as $key) {
        $insertStmt->execute([$key, $defaultRoValues[$key] ?? '']);
    }
}

$contentRows = $db->query("SELECT * FROM site_content ORDER BY id")->fetchAll();

$contentMap = [];
foreach ($contentRows as $row) {
    $contentMap[$row['content_key']] = $row;
}

$adminTitle = 'Conținut site';
$msgGet = $_GET['msg'] ?? '';
$translationGet = $_GET['translation'] ?? '';
include __DIR__ . '/partials/header.php';
?>

<?php if ($msgGet === 'saved'): ?>
  <div class="alert alert-success">Conținutul a fost salvat cu succes.</div>
<?php endif; ?>

<?php
$translationWarnings = [
  'missing_api_key' => 'Conținutul RO a fost salvat, dar traducerea nu a rulat. Dacă folosești OpenAI, setează OPENAI_API_KEY.',
  'invalid_api_key' => 'Conținutul RO a fost salvat, dar cheia API setată este invalidă (401/403).',
  'quota_exceeded' => 'Conținutul RO a fost salvat, dar quota OpenAI este depășită (429).',
  'model_not_found' => 'Conținutul RO a fost salvat, dar modelul OpenAI setat nu este disponibil.',
  'rate_limited' => 'Conținutul RO a fost salvat, dar serviciul gratuit de traducere a limitat cererile (rate limit).',
  'query_too_long' => 'Conținutul RO a fost salvat, dar unele texte depășesc limita providerului gratuit.',
  'provider_unavailable' => 'Conținutul RO a fost salvat, dar serviciul de traducere este temporar indisponibil.',
  'unsupported_language' => 'Conținutul RO a fost salvat, dar providerul curent nu acceptă combinația de limbi cerută.',
  'network_error' => 'Conținutul RO a fost salvat, dar serverul nu poate ajunge la providerul de traducere (DNS/rețea).',
  'curl_missing' => 'Conținutul RO a fost salvat, dar extensia cURL nu este disponibilă pe server.',
  'request_failed' => 'Conținutul RO a fost salvat, dar apelul către serviciul de traducere a eșuat.',
  'invalid_response' => 'Conținutul RO a fost salvat, dar răspunsul de traducere nu a putut fi procesat complet.',
  'partial' => 'Conținutul RO a fost salvat, iar unele traduceri RU/EN au fost completate prin fallback.',
];
?>
<?php if ($msgGet === 'saved' && isset($translationWarnings[$translationGet])): ?>
  <div class="alert alert-warning"><?= e($translationWarnings[$translationGet]) ?></div>
<?php endif; ?>

<div class="alert" style="background:#e0f2fe;color:#0c4a6e;border:1px solid #bae6fd;">
  Editezi doar coloana în română. La salvare, textele sunt traduse automat în rusă și engleză.
</div>

<div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;">
  <a href="content.php?view=home" class="btn btn-sm <?= $activeView === 'home' ? 'btn-primary' : 'btn-outline' ?>">Home</a>
  <a href="content.php?view=careers" class="btn btn-sm <?= $activeView === 'careers' ? 'btn-primary' : 'btn-outline' ?>">Cariere</a>
</div>

<form method="POST" class="content-form">
<input type="hidden" name="view" value="<?= e($activeView) ?>">
<?php $sectionIndex = 0; ?>
<?php foreach ($sections as $sectionName => $keys): ?>
  <?php
    $sectionPanelId = 'content-section-' . $activeView . '-' . $sectionIndex;
    $sectionIndex++;
  ?>
  <div class="card content-section-card is-collapsed" style="margin-bottom:20px;">
    <div
      class="card-header content-section-toggle"
      role="button"
      tabindex="0"
      aria-expanded="false"
      aria-controls="<?= e($sectionPanelId) ?>"
    >
      <h2><?= e($sectionName) ?></h2>
      <span class="content-section-chevron" aria-hidden="true"></span>
    </div>
    <div class="card-body content-section-body" id="<?= e($sectionPanelId) ?>">
      <?php foreach ($keys as $key): ?>
        <?php $row = $contentMap[$key] ?? null; if (!$row) continue; ?>
        <div style="margin-bottom:24px;padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;">
          <p style="font-size:0.8rem;font-weight:700;color:#475569;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.06em;">
            Cheie: <?= e($key) ?>
          </p>
          <div class="form-row" style="grid-template-columns:1fr 1fr 1fr;gap:14px;">
            <?php
              $valRo = (string)($row['value_ro'] ?? '');
              $valRu = (string)($row['value_ru'] ?? '');
              $valEn = (string)($row['value_en'] ?? '');
              $isSocialUrlKey = in_array($key, $socialUrlKeys, true);
              $isLong = mb_strlen($valRo) > 100 || str_contains($key, 'text') || str_contains($key, 'subtitle');
            ?>
            <?php if ($isSocialUrlKey): ?>
              <div class="form-group" style="margin-bottom:0;grid-column:1 / -1;">
                <label style="font-size:0.78rem;">Link (același pentru toate limbile)</label>
                <input type="url" name="<?= e($key) ?>[ro]" value="<?= e($valRo) ?>" placeholder="https://">
              </div>
            <?php else: ?>
              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Română (editabil)</label>
                <?php if ($isLong): ?>
                  <textarea name="<?= e($key) ?>[ro]" rows="3"><?= e($valRo) ?></textarea>
                <?php else: ?>
                  <input type="text" name="<?= e($key) ?>[ro]" value="<?= e($valRo) ?>">
                <?php endif; ?>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Rusă (view only)</label>
                <?php if ($isLong || mb_strlen($valRu) > 100): ?>
                  <textarea rows="3" readonly style="background:#f8fafc;color:#334155;"><?= e($valRu) ?></textarea>
                <?php else: ?>
                  <input type="text" value="<?= e($valRu) ?>" readonly style="background:#f8fafc;color:#334155;">
                <?php endif; ?>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:0.78rem;">Engleză (view only)</label>
                <?php if ($isLong || mb_strlen($valEn) > 100): ?>
                  <textarea rows="3" readonly style="background:#f8fafc;color:#334155;"><?= e($valEn) ?></textarea>
                <?php else: ?>
                  <input type="text" value="<?= e($valEn) ?>" readonly style="background:#f8fafc;color:#334155;">
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="content-sticky-actions">
  <div class="content-sticky-card">
    <button type="submit" class="btn btn-primary">Salvează toate modificările</button>
  </div>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const sectionToggles = document.querySelectorAll('.content-section-toggle');

  const toggleSection = (toggle) => {
    const card = toggle.closest('.content-section-card');
    if (!card) return;
    const isCollapsed = card.classList.toggle('is-collapsed');
    toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
  };

  sectionToggles.forEach((toggle) => {
    toggle.addEventListener('click', () => toggleSection(toggle));
    toggle.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      toggleSection(toggle);
    });
  });
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
