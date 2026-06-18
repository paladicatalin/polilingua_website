<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
ensureServicesCatalog();

$error = '';
$iconOptions = getServiceIconOptions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titleRo = trim((string)($_POST['title_ro'] ?? ''));
    $descriptionRo = trim((string)($_POST['description_ro'] ?? ''));
    $iconKey = normalizeServiceIconKey((string)($_POST['icon_key'] ?? ''));
    if ($titleRo === '') {
        $error = 'Titlul în română este obligatoriu.';
    } else {
        $translationStatus = 'ok';
        $textsForAi = ['title' => $titleRo];
        if ($descriptionRo !== '') {
            $textsForAi['description'] = $descriptionRo;
        }
        $translationResult = translateRomanianTextsToRuEn($textsForAi);
        if (!($translationResult['ok'] ?? false)) {
            $translationStatus = (string)($translationResult['error'] ?? 'request_failed');
        }

        $titleRu = trim((string)($translationResult['translations']['title']['ru'] ?? ''));
        $titleEn = trim((string)($translationResult['translations']['title']['en'] ?? ''));
        $descriptionRu = trim((string)($translationResult['translations']['description']['ru'] ?? ''));
        $descriptionEn = trim((string)($translationResult['translations']['description']['en'] ?? ''));
        if ($titleRu === '' || $titleEn === '') {
            $titleRu = $titleRu !== '' ? $titleRu : $titleRo;
            $titleEn = $titleEn !== '' ? $titleEn : $titleRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }
        if ($descriptionRo !== '' && ($descriptionRu === '' || $descriptionEn === '')) {
            $descriptionRu = $descriptionRu !== '' ? $descriptionRu : $descriptionRo;
            $descriptionEn = $descriptionEn !== '' ? $descriptionEn : $descriptionRo;
            if ($translationStatus === 'ok') $translationStatus = 'partial';
        }

        try {
            $db = getDB();
            $serviceColumns = getTableColumns('services', true);
            $insertColumns = [];
            $values = [];

            if (isset($serviceColumns['title_ro'])) {
                $insertColumns[] = 'title_ro';
                $values[] = $titleRo;
            }
            if (isset($serviceColumns['title_ru'])) {
                $insertColumns[] = 'title_ru';
                $values[] = $titleRu;
            }
            if (isset($serviceColumns['title_en'])) {
                $insertColumns[] = 'title_en';
                $values[] = $titleEn;
            }
            if (isset($serviceColumns['description_ro'])) {
                $insertColumns[] = 'description_ro';
                $values[] = $descriptionRo;
            }
            if (isset($serviceColumns['description_ru'])) {
                $insertColumns[] = 'description_ru';
                $values[] = $descriptionRu;
            }
            if (isset($serviceColumns['description_en'])) {
                $insertColumns[] = 'description_en';
                $values[] = $descriptionEn;
            }
            if (isset($serviceColumns['icon_key'])) {
                $insertColumns[] = 'icon_key';
                $values[] = $iconKey;
            }
            if (isset($serviceColumns['sort_order'])) {
                $insertColumns[] = 'sort_order';
                $values[] = (int)($_POST['sort_order'] ?? 0);
            }
            if (isset($serviceColumns['is_active'])) {
                $insertColumns[] = 'is_active';
                $values[] = isset($_POST['is_active']) ? 1 : 0;
            }

            if (!in_array('title_ro', $insertColumns, true)) {
                throw new RuntimeException('Lipsește coloana title_ro din tabelul services.');
            }

            $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
            $sql = 'INSERT INTO services (' . implode(', ', $insertColumns) . ') VALUES (' . $placeholders . ')';
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            header('Location: services.php?msg=created&translation=' . rawurlencode($translationStatus));
            exit;
        } catch (Throwable $e) {
            $error = 'Nu am putut salva serviciul. Verifică schema bazei de date (services) și încearcă din nou.';
        }
    }
}

$adminTitle = 'Adaugă serviciu';
include __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="max-width:760px;">
  <div class="card">
    <div class="card-header">
      <h2>Serviciu nou</h2>
      <a href="services.php" class="btn btn-outline btn-sm">Înapoi</a>
    </div>
    <div class="card-body">
      <div class="alert" style="background:#e0f2fe;color:#0c4a6e;border:1px solid #bae6fd;">
        Completezi în română titlul și descrierea. La salvare se traduc automat în rusă și engleză.
      </div>

      <form method="POST">
        <div class="form-group">
          <label>Titlu serviciu (RO) *</label>
          <input type="text" name="title_ro" required value="<?= e($_POST['title_ro'] ?? '') ?>" placeholder="ex: Servicii de traducere">
        </div>

        <div class="form-group">
          <label>Descriere scurtă (RO)</label>
          <textarea name="description_ro" rows="3" placeholder="Scurtă descriere care apare sub titlu pe card"><?= e((string)($_POST['description_ro'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
          <label>Icon serviciu</label>
          <select name="icon_key">
            <?php $selectedIcon = normalizeServiceIconKey((string)($_POST['icon_key'] ?? 'clipboard-check')); ?>
            <?php foreach ($iconOptions as $iconValue => $iconLabel): ?>
              <option value="<?= e($iconValue) ?>" <?= $selectedIcon === $iconValue ? 'selected' : '' ?>>
                <?= e($iconLabel) ?> (<?= e($iconValue) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Ordine afișare</label>
            <input type="number" name="sort_order" min="0" value="<?= e($_POST['sort_order'] ?? '0') ?>">
          </div>
          <div class="form-group" style="display:flex;align-items:center;gap:12px;margin-top:28px;">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked style="width:18px;height:18px;">
            <label for="is_active" style="margin:0;">Serviciu activ (vizibil pe site)</label>
          </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:10px;">
          <button type="submit" class="btn btn-primary">Salvează serviciul</button>
          <a href="services.php" class="btn btn-outline">Anulează</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
