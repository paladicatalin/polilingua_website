<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$error = '';
$translationStatus = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_ro = sanitize($_POST['title_ro'] ?? '');
    if (!$title_ro) {
        $error = 'Titlul în română este obligatoriu.';
    } else {
        $shortDescRo = (string)($_POST['short_desc_ro'] ?? '');
        $fullDescRo = (string)($_POST['full_desc_ro'] ?? '');
        $locationRo = sanitize($_POST['location'] ?? 'Chișinău, Moldova');
        $scheduleRo = sanitize($_POST['schedule'] ?? 'Full-time');

        $textsForAi = ['title' => $title_ro];
        if (trim($shortDescRo) !== '') $textsForAi['short_desc'] = $shortDescRo;
        if (trim($fullDescRo) !== '') $textsForAi['full_desc'] = $fullDescRo;
        if ($locationRo !== '') $textsForAi['location'] = $locationRo;
        if ($scheduleRo !== '') $textsForAi['schedule'] = $scheduleRo;

        $translationResult = translateRomanianTextsToRuEn($textsForAi);
        if (!($translationResult['ok'] ?? false)) {
            $translationStatus = (string)($translationResult['error'] ?? 'request_failed');
        }

        $titleRu = trim((string)($translationResult['translations']['title']['ru'] ?? ''));
        $titleEn = trim((string)($translationResult['translations']['title']['en'] ?? ''));
        $shortDescRu = trim((string)($translationResult['translations']['short_desc']['ru'] ?? ''));
        $shortDescEn = trim((string)($translationResult['translations']['short_desc']['en'] ?? ''));
        $fullDescRu = trim((string)($translationResult['translations']['full_desc']['ru'] ?? ''));
        $fullDescEn = trim((string)($translationResult['translations']['full_desc']['en'] ?? ''));
        $locationRu = trim((string)($translationResult['translations']['location']['ru'] ?? ''));
        $locationEn = trim((string)($translationResult['translations']['location']['en'] ?? ''));
        $scheduleRu = trim((string)($translationResult['translations']['schedule']['ru'] ?? ''));
        $scheduleEn = trim((string)($translationResult['translations']['schedule']['en'] ?? ''));

        if ($titleRu === '' || $titleEn === '') {
            $titleRu = $titleRu !== '' ? $titleRu : $title_ro;
            $titleEn = $titleEn !== '' ? $titleEn : $title_ro;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }
        if (trim($shortDescRo) !== '' && ($shortDescRu === '' || $shortDescEn === '')) {
            $shortDescRu = $shortDescRu !== '' ? $shortDescRu : $shortDescRo;
            $shortDescEn = $shortDescEn !== '' ? $shortDescEn : $shortDescRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }
        if (trim($fullDescRo) !== '' && ($fullDescRu === '' || $fullDescEn === '')) {
            $fullDescRu = $fullDescRu !== '' ? $fullDescRu : $fullDescRo;
            $fullDescEn = $fullDescEn !== '' ? $fullDescEn : $fullDescRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }
        if ($locationRo !== '' && ($locationRu === '' || $locationEn === '')) {
            $locationRu = $locationRu !== '' ? $locationRu : $locationRo;
            $locationEn = $locationEn !== '' ? $locationEn : $locationRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }
        if ($scheduleRo !== '' && ($scheduleRu === '' || $scheduleEn === '')) {
            $scheduleRu = $scheduleRu !== '' ? $scheduleRu : $scheduleRo;
            $scheduleEn = $scheduleEn !== '' ? $scheduleEn : $scheduleRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }

        $slug = generateSlug($title_ro) . '-' . time();
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO jobs (slug, title_ro, title_ru, title_en,
                short_desc_ro, short_desc_ru, short_desc_en,
                full_desc_ro, full_desc_ru, full_desc_en,
                location, schedule, sticky_color, sticky_rotation, sort_order, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $slug,
            $title_ro,
            $titleRu,
            $titleEn,
            $shortDescRo,
            $shortDescRu,
            $shortDescEn,
            $fullDescRo,
            $fullDescRu,
            $fullDescEn,
            $locationRo,
            $scheduleRo,
            sanitize($_POST['sticky_color'] ?? '#4CAF82'),
            (float)($_POST['sticky_rotation'] ?? -2.5),
            (int)($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
        ]);
        header('Location: jobs.php?msg=created&translation=' . rawurlencode($translationStatus));
        exit;
    }
}

$adminTitle = 'Adaugă post vacant';
include __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="max-width:900px;">
<div class="card">
  <div class="card-header">
    <h2>Post nou</h2>
    <a href="jobs.php" class="btn btn-outline btn-sm">← Înapoi</a>
  </div>
  <div class="card-body">
    <div class="alert" style="background:#e0f2fe;color:#0c4a6e;border:1px solid #bae6fd;">
      Completezi doar în română. La salvare, câmpurile în rusă și engleză se traduc automat.
    </div>
    <form method="POST">

      <h3 style="margin-bottom:16px;font-size:0.95rem;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Titlu</h3>
      <div class="form-group">
        <label>Titlu Română *</label>
        <input type="text" name="title_ro" required value="<?= e($_POST['title_ro'] ?? '') ?>" placeholder="ex: Manager de vânzări">
      </div>

      <h3 style="margin:24px 0 16px;font-size:0.95rem;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Descriere scurtă</h3>
      <div class="form-group">
        <label>Scurtă descriere Română</label>
        <textarea name="short_desc_ro" rows="3" placeholder="Descriere scurtă pentru sticky note..."><?= e($_POST['short_desc_ro'] ?? '') ?></textarea>
      </div>

      <h3 style="margin:24px 0 16px;font-size:0.95rem;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Descriere completă (HTML permis)</h3>
      <div class="form-group">
        <label>Descriere completă Română</label>
        <textarea name="full_desc_ro" rows="6" placeholder="<p>Descriere completă a postului...</p><ul><li>Cerință 1</li></ul>"><?= e($_POST['full_desc_ro'] ?? '') ?></textarea>
      </div>

      <h3 style="margin:24px 0 16px;font-size:0.95rem;color:#64748b;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">Detalii și aspect</h3>
      <div class="form-row">
        <div class="form-group">
          <label>Locație</label>
          <input type="text" name="location" value="<?= e($_POST['location'] ?? 'Chișinău, Moldova') ?>">
        </div>
        <div class="form-group">
          <label>Program</label>
          <input type="text" name="schedule" value="<?= e($_POST['schedule'] ?? 'Full-time') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Culoare sticky note</label>
          <input type="color" name="sticky_color" value="<?= e($_POST['sticky_color'] ?? '#4CAF82') ?>" style="height:42px;padding:4px;">
        </div>
        <div class="form-group">
          <label>Rotație sticky note (grade, ex: -3.5)</label>
          <input type="number" name="sticky_rotation" step="0.5" min="-15" max="15" value="<?= e($_POST['sticky_rotation'] ?? '-2.5') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Ordine afișare</label>
          <input type="number" name="sort_order" min="0" value="<?= e($_POST['sort_order'] ?? '0') ?>">
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:12px;margin-top:28px;">
          <input type="checkbox" name="is_active" id="is_active" value="1" <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?> style="width:18px;height:18px;">
          <label for="is_active" style="margin:0;">Post activ (vizibil pe site)</label>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:10px;">
        <button type="submit" class="btn btn-primary">Salvează postul</button>
        <a href="jobs.php" class="btn btn-outline">Anulează</a>
      </div>
    </form>
  </div>
</div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
