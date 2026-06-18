<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$db = getDB();
$totalJobs = $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$activeJobs = $db->query("SELECT COUNT(*) FROM jobs WHERE is_active=1")->fetchColumn();
$totalApps = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$newApps = $db->query("SELECT COUNT(*) FROM applications WHERE status='new'")->fetchColumn();

$recentApps = $db->query("
    SELECT a.*, j.title_ro as job_title
    FROM applications a
    LEFT JOIN jobs j ON a.job_id = j.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();
$stats = [
    ['value' => $totalJobs, 'label' => 'Total posturi', 'token' => 'PV', 'token_class' => 'stat-token-blue'],
    ['value' => $activeJobs, 'label' => 'Posturi active', 'token' => 'ACT', 'token_class' => 'stat-token-green'],
    ['value' => $totalApps, 'label' => 'Total aplicări', 'token' => 'APL', 'token_class' => 'stat-token-pink'],
    ['value' => $newApps, 'label' => 'Aplicări noi', 'token' => 'NOI', 'token_class' => 'stat-token-indigo'],
];
$statusLabels = [
    'new' => 'Nou',
    'reviewed' => 'Revizuit',
    'interview' => 'Interviu',
    'hired' => 'Angajat',
    'rejected' => 'Respins',
];

$adminTitle = 'Dashboard';
include __DIR__ . '/partials/header.php';
?>

<div class="stats-grid">
  <?php foreach ($stats as $item): ?>
    <div class="stat-card">
      <div class="stat-token <?= e($item['token_class']) ?>"><?= e($item['token']) ?></div>
      <div class="stat-content">
        <div class="stat-value"><?= (int)$item['value'] ?></div>
        <div class="stat-label"><?= e($item['label']) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="dashboard-layout">
  <!-- Recent Applications -->
  <div class="card dashboard-recent">
    <div class="card-header">
      <h2>Aplicări recente</h2>
      <a href="applications.php" class="btn btn-outline btn-sm">Vezi toate</a>
    </div>
    <div class="table-wrap dashboard-table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nume</th>
            <th>Post</th>
            <th>Email</th>
            <th>Status</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentApps)): ?>
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:24px;">Nicio aplicare deocamdată.</td></tr>
          <?php else: ?>
            <?php foreach ($recentApps as $app): ?>
              <tr>
                <td><strong><?= e($app['name']) ?></strong></td>
                <td style="color:#64748b;font-size:0.82rem;"><?= e($app['job_title'] ?? '—') ?></td>
                <td style="font-size:0.82rem;"><?= e($app['email']) ?></td>
                <td>
                  <?php
                  $badges = [
                    'new' => 'badge-blue',
                    'reviewed' => 'badge-yellow',
                    'interview' => 'badge-green',
                    'hired' => 'badge-green',
                    'rejected' => 'badge-red',
                  ];
                  $cls = $badges[$app['status']] ?? 'badge-gray';
                  ?>
                  <span class="badge <?= $cls ?>"><?= e($statusLabels[$app['status']] ?? $app['status']) ?></span>
                </td>
                <td style="font-size:0.8rem;color:#94a3b8;"><?= date('d.m.Y', strtotime($app['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card dashboard-actions">
    <div class="card-header"><h2>Acțiuni rapide</h2></div>
    <div class="card-body dashboard-actions-body">
      <a href="job-create.php" class="btn btn-primary dashboard-action-btn">Adaugă post vacant</a>
      <a href="service-create.php" class="btn btn-outline dashboard-action-btn">Adaugă serviciu</a>
      <a href="applications.php" class="btn btn-outline dashboard-action-btn">Vezi aplicări</a>
      <a href="content.php" class="btn btn-outline dashboard-action-btn">Editează conținut</a>
      <a href="<?= SITE_URL ?>/index.php" target="_blank" class="btn btn-outline dashboard-action-btn">Vezi site-ul</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
