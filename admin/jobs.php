<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$db = getDB();

// Toggle active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $db->prepare("UPDATE jobs SET is_active = 1 - is_active WHERE id = ?");
    $stmt->execute([(int)$_GET['toggle']]);
    header('Location: jobs.php?msg=updated');
    exit;
}

$jobs = getAllJobs();
$adminTitle = 'Posturi vacante';
$msg = $_GET['msg'] ?? '';
$translation = $_GET['translation'] ?? '';

include __DIR__ . '/partials/header.php';
?>

<?php if ($msg === 'created'): ?>
  <div class="alert alert-success">Postul a fost creat cu succes.</div>
<?php elseif ($msg === 'updated'): ?>
  <div class="alert alert-success">Postul a fost actualizat.</div>
<?php elseif ($msg === 'deleted'): ?>
  <div class="alert alert-success">Postul a fost șters.</div>
<?php endif; ?>

<?php
$translationWarnings = [
  'missing_api_key' => 'Postul a fost salvat în română, dar traducerea nu a rulat. Dacă folosești OpenAI, setează OPENAI_API_KEY.',
  'invalid_api_key' => 'Postul a fost salvat în română, dar cheia API este invalidă (401/403).',
  'quota_exceeded' => 'Postul a fost salvat în română, dar quota OpenAI este depășită (429).',
  'model_not_found' => 'Postul a fost salvat în română, dar modelul OpenAI de traducere nu este disponibil.',
  'rate_limited' => 'Postul a fost salvat în română, dar providerul gratuit a limitat cererile (rate limit).',
  'query_too_long' => 'Postul a fost salvat în română, dar unele texte depășesc limita providerului gratuit.',
  'provider_unavailable' => 'Postul a fost salvat în română, dar serviciul de traducere este temporar indisponibil.',
  'unsupported_language' => 'Postul a fost salvat în română, dar providerul curent nu acceptă limba cerută.',
  'network_error' => 'Postul a fost salvat în română, dar serverul nu poate accesa providerul de traducere (DNS/rețea).',
  'curl_missing' => 'Postul a fost salvat în română, dar extensia cURL lipsește pe server.',
  'request_failed' => 'Postul a fost salvat în română, dar traducerea automată a eșuat.',
  'invalid_response' => 'Postul a fost salvat în română, dar răspunsul de traducere nu a putut fi procesat.',
  'partial' => 'Postul a fost salvat, iar unele câmpuri RU/EN au fost completate prin fallback.',
];
?>
<?php if (isset($translationWarnings[$translation])): ?>
  <div class="alert alert-warning"><?= e($translationWarnings[$translation]) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Posturi vacante (<?= count($jobs) ?>)</h2>
    <a href="job-create.php" class="btn btn-primary">+ Adaugă post</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Titlu (RO)</th>
          <th>Locație</th>
          <th>Program</th>
          <th>Culoare</th>
          <th>Ordine</th>
          <th>Status</th>
          <th>Acțiuni</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($jobs)): ?>
          <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:28px;">Nu există posturi. <a href="job-create.php">Adaugă primul post</a></td></tr>
        <?php else: ?>
          <?php foreach ($jobs as $job): ?>
            <tr>
              <td style="color:#94a3b8;"><?= $job['id'] ?></td>
              <td>
                <strong><?= e($job['title_ro']) ?></strong>
                <?php if ($job['title_en']): ?>
                  <br><small style="color:#94a3b8;"><?= e($job['title_en']) ?></small>
                <?php endif; ?>
              </td>
              <td><?= e($job['location']) ?></td>
              <td><?= e($job['schedule']) ?></td>
              <td>
                <span class="color-preview" style="background:<?= e($job['sticky_color']) ?>;"></span>
                <small style="color:#94a3b8;margin-left:6px;"><?= e($job['sticky_color']) ?></small>
              </td>
              <td><?= $job['sort_order'] ?></td>
              <td>
                <?php if ($job['is_active']): ?>
                  <span class="badge badge-green">Activ</span>
                <?php else: ?>
                  <span class="badge badge-gray">Inactiv</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="table-actions">
                  <a href="job-edit.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline">Editează</a>
                  <a href="jobs.php?toggle=<?= $job['id'] ?>" class="btn btn-sm <?= $job['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                    <?= $job['is_active'] ? 'Dezactivează' : 'Activează' ?>
                  </a>
                  <a href="job-delete.php?id=<?= $job['id'] ?>"
                     onclick="return confirm('Sigur vrei să ștergi acest post?')"
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
