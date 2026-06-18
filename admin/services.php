<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$db = getDB();
ensureServicesCatalog();

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $db->prepare("UPDATE services SET is_active = 1 - is_active WHERE id = ?");
    $stmt->execute([(int)$_GET['toggle']]);
    header('Location: services.php?msg=updated');
    exit;
}

$services = getAllServices();
$serviceIconSvgs = getServiceIconSvgs();
$adminTitle = 'Servicii';
$msg = $_GET['msg'] ?? '';
$translation = $_GET['translation'] ?? '';

include __DIR__ . '/partials/header.php';
?>

<?php if ($msg === 'created'): ?>
  <div class="alert alert-success">Serviciul a fost creat cu succes.</div>
<?php elseif ($msg === 'updated'): ?>
  <div class="alert alert-success">Serviciul a fost actualizat.</div>
<?php elseif ($msg === 'deleted'): ?>
  <div class="alert alert-success">Serviciul a fost șters.</div>
<?php endif; ?>

<?php
$translationWarnings = [
  'missing_api_key' => 'Serviciul a fost salvat în română, dar traducerea nu a rulat. Dacă folosești OpenAI, setează OPENAI_API_KEY.',
  'invalid_api_key' => 'Serviciul a fost salvat în română, dar cheia API este invalidă (401/403).',
  'quota_exceeded' => 'Serviciul a fost salvat în română, dar quota OpenAI este depășită (429).',
  'model_not_found' => 'Serviciul a fost salvat în română, dar modelul OpenAI de traducere nu este disponibil.',
  'rate_limited' => 'Serviciul a fost salvat în română, dar providerul gratuit a limitat cererile (rate limit).',
  'query_too_long' => 'Serviciul a fost salvat în română, dar unele texte depășesc limita providerului gratuit.',
  'provider_unavailable' => 'Serviciul a fost salvat în română, dar serviciul de traducere este temporar indisponibil.',
  'unsupported_language' => 'Serviciul a fost salvat în română, dar providerul curent nu acceptă limba cerută.',
  'network_error' => 'Serviciul a fost salvat în română, dar serverul nu poate accesa providerul de traducere (DNS/rețea).',
  'curl_missing' => 'Serviciul a fost salvat în română, dar extensia cURL lipsește pe server.',
  'request_failed' => 'Serviciul a fost salvat în română, dar traducerea automată a eșuat.',
  'invalid_response' => 'Serviciul a fost salvat în română, dar răspunsul de traducere nu a putut fi procesat.',
  'partial' => 'Serviciul a fost salvat, iar unele câmpuri RU/EN au fost completate prin fallback.',
];
?>
<?php if (isset($translationWarnings[$translation])): ?>
  <div class="alert alert-warning"><?= e($translationWarnings[$translation]) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Servicii (<?= count($services) ?>)</h2>
    <a href="service-create.php" class="btn btn-primary">Adaugă serviciu</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Pictogramă</th>
          <th>Titlu (RO)</th>
          <th>Descriere (RO)</th>
          <th>Ordine</th>
          <th>Status</th>
          <th>Acțiuni</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($services)): ?>
          <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px;">Nu există servicii. <a href="service-create.php">Adaugă primul serviciu</a></td></tr>
        <?php else: ?>
          <?php foreach ($services as $service): ?>
            <tr>
              <td style="color:#94a3b8;"><?= (int)$service['id'] ?></td>
              <td>
                <?php
                  $iconKey = normalizeServiceIconKey((string)($service['icon_key'] ?? ''));
                  $iconSvg = $serviceIconSvgs[$iconKey] ?? '';
                  $iconSvg = str_replace('<svg ', '<svg width="24" height="24" ', $iconSvg);
                ?>
                <div style="width:24px;height:24px;color:#2f76d9;">
                  <?= $iconSvg ?>
                </div>
                <small style="color:#94a3b8;display:block;line-height:1.1;"><?= e($iconKey) ?></small>
              </td>
              <td><strong><?= e((string)$service['title_ro']) ?></strong></td>
              <td style="color:#64748b;font-size:0.82rem;max-width:360px;">
                <?php
                  $descRo = trim((string)($service['description_ro'] ?? ''));
                  $descPreview = $descRo !== '' ? mb_strimwidth($descRo, 0, 130, '…', 'UTF-8') : '—';
                ?>
                <?= e($descPreview) ?>
              </td>
              <td><?= (int)$service['sort_order'] ?></td>
              <td>
                <?php if (!empty($service['is_active'])): ?>
                  <span class="badge badge-green">Activ</span>
                <?php else: ?>
                  <span class="badge badge-gray">Inactiv</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="table-actions">
                  <a href="service-edit.php?id=<?= (int)$service['id'] ?>" class="btn btn-sm btn-outline">Editează</a>
                  <a href="services.php?toggle=<?= (int)$service['id'] ?>" class="btn btn-sm <?= !empty($service['is_active']) ? 'btn-danger' : 'btn-success' ?>">
                    <?= !empty($service['is_active']) ? 'Dezactivează' : 'Activează' ?>
                  </a>
                  <a href="service-delete.php?id=<?= (int)$service['id'] ?>"
                     onclick="return confirm('Sigur vrei să ștergi acest serviciu?')"
                     class="btn btn-sm btn-danger">Șterge</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
