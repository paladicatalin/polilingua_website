<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
ensureServicesCatalog();
$iconOptions = getServiceIconOptions();

$id = (int)($_GET['id'] ?? 0);
$service = getServiceById($id);
if (!$service) {
    header('Location: services.php');
    exit;
}

$error = '';

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
            $setParts = [];
            $values = [];

            if (isset($serviceColumns['title_ro'])) {
                $setParts[] = 'title_ro = ?';
                $values[] = $titleRo;
            }
            if (isset($serviceColumns['title_ru'])) {
                $setParts[] = 'title_ru = ?';
                $values[] = $titleRu;
            }
            if (isset($serviceColumns['title_en'])) {
                $setParts[] = 'title_en = ?';
                $values[] = $titleEn;
            }
            if (isset($serviceColumns['description_ro'])) {
                $setParts[] = 'description_ro = ?';
                $values[] = $descriptionRo;
            }
            if (isset($serviceColumns['description_ru'])) {
                $setParts[] = 'description_ru = ?';
                $values[] = $descriptionRu;
            }
            if (isset($serviceColumns['description_en'])) {
                $setParts[] = 'description_en = ?';
                $values[] = $descriptionEn;
            }
            if (isset($serviceColumns['icon_key'])) {
                $setParts[] = 'icon_key = ?';
                $values[] = $iconKey;
            }
            if (isset($serviceColumns['sort_order'])) {
                $setParts[] = 'sort_order = ?';
                $values[] = (int)($_POST['sort_order'] ?? 0);
            }
            if (isset($serviceColumns['is_active'])) {
                $setParts[] = 'is_active = ?';
                $values[] = isset($_POST['is_active']) ? 1 : 0;
            }

            if (empty($setParts)) {
                throw new RuntimeException('Nu există coloane editabile în tabelul services.');
            }

            $values[] = $id;
            $sql = 'UPDATE services SET ' . implode(', ', $setParts) . ' WHERE id = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            header('Location: services.php?msg=updated&translation=' . rawurlencode($translationStatus));
            exit;
        } catch (Throwable $e) {
            $error = 'Nu am putut salva modificările. Verifică schema bazei de date (services) și încearcă din nou.';
        }
    }
}

$f = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $service;

$adminTitle = 'Editează serviciu';
include __DIR__ . '/partials/header.php';
?>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="max-width:760px;">
  <div class="card">
    <div class="card-header">
      <h2>Editează serviciu</h2>
      <a href="services.php" class="btn btn-outline btn-sm">Înapoi</a>
    </div>
    <div class="card-body">
      <div class="alert" style="background:#e0f2fe;color:#0c4a6e;border:1px solid #bae6fd;">
        Completezi în română titlul și descrierea. La salvare se traduc automat în rusă și engleză.
      </div>

      <form method="POST">
        <div class="form-group">
          <label>Titlu serviciu (RO) *</label>
          <input type="text" name="title_ro" required value="<?= e((string)($f['title_ro'] ?? '')) ?>">
        </div>

        <div class="form-group">
          <label>Descriere scurtă (RO)</label>
          <textarea name="description_ro" rows="3"><?= e((string)($f['description_ro'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
          <label>Icon serviciu</label>
          <?php $selectedIcon = normalizeServiceIconKey((string)($f['icon_key'] ?? 'clipboard-check')); ?>
          <select name="icon_key">
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
            <input type="number" name="sort_order" min="0" value="<?= e((string)($f['sort_order'] ?? '0')) ?>">
          </div>
          <div class="form-group" style="display:flex;align-items:center;gap:12px;margin-top:28px;">
            <input type="checkbox" name="is_active" id="is_active" value="1" <?= !empty($f['is_active']) ? 'checked' : '' ?> style="width:18px;height:18px;">
            <label for="is_active" style="margin:0;">Serviciu activ (vizibil pe site)</label>
          </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:10px;">
          <button type="submit" class="btn btn-primary">Salvează modificările</button>
          <a href="services.php" class="btn btn-outline">Anulează</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
