<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$db = getDB();
$allowedStatuses = ['new', 'reviewed', 'interview', 'hired', 'rejected'];
$statusLabels = [
    'new' => 'Nou',
    'reviewed' => 'Revizuit',
    'interview' => 'Interviu',
    'hired' => 'Angajat',
    'rejected' => 'Respins',
];
$statusFilterLabels = [
    '' => 'Toate',
    'new' => 'Noi',
    'reviewed' => 'Revizuite',
    'interview' => 'Interviu',
    'hired' => 'Angajați',
    'rejected' => 'Respinși',
];

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    $status = $_POST['status'];
    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, (int)$_POST['id']]);
    }
    $redirectStatus = $_POST['current_filter'] ?? '';
    $redirectQuery = '';
    if ($redirectStatus !== '' && in_array($redirectStatus, $allowedStatuses, true)) {
        $redirectQuery = '&status=' . urlencode($redirectStatus);
    }
    header('Location: applications.php?msg=updated' . $redirectQuery);
    exit;
}

// Delete application
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $redirectStatus = $_GET['status'] ?? '';
    $redirectQuery = '';
    if ($redirectStatus !== '' && in_array($redirectStatus, $allowedStatuses, true)) {
        $redirectQuery = '&status=' . urlencode($redirectStatus);
    }
    header('Location: applications.php?msg=deleted' . $redirectQuery);
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '' && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$sql = "
    SELECT a.*, j.title_ro as job_title
    FROM applications a
    LEFT JOIN jobs j ON a.job_id = j.id
";
$params = [];
if ($statusFilter !== '') {
    $sql .= " WHERE a.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$adminTitle = 'Aplicări';
$msg = $_GET['msg'] ?? '';
include __DIR__ . '/partials/header.php';
?>

<?php if ($msg === 'updated'): ?>
  <div class="alert alert-success">✅ Statusul a fost actualizat!</div>
<?php elseif ($msg === 'deleted'): ?>
  <div class="alert alert-success">✅ Aplicarea a fost ștearsă!</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Aplicări primite (<?= count($applications) ?>)</h2>
    <div style="display:flex;gap:8px;align-items:center;">
      <span style="font-size:0.82rem;color:#64748b;">Filtrează:</span>
      <?php
      $statuses = ['', 'new', 'reviewed', 'interview', 'hired', 'rejected'];
      foreach ($statuses as $s):
      ?>
        <a href="applications.php<?= $s ? '?status=' . $s : '' ?>"
           class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline' ?>">
          <?= $statusFilterLabels[$s] ?? $s ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nume</th>
          <th>Email / Telefon</th>
          <th>Post</th>
          <th>CV</th>
          <th>Status</th>
          <th>Data</th>
          <th>Acțiuni</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($applications)): ?>
          <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:28px;">Nicio aplicare deocamdată.</td></tr>
        <?php else: ?>
          <?php foreach ($applications as $app): ?>
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
            <tr>
              <td style="color:#94a3b8;"><?= $app['id'] ?></td>
              <td><strong><?= e($app['name']) ?></strong></td>
              <td>
                <a href="mailto:<?= e($app['email']) ?>" style="color:#2563EB;"><?= e($app['email']) ?></a>
                <br><small style="color:#94a3b8;"><?= e($app['phone']) ?></small>
              </td>
              <td style="font-size:0.85rem;"><?= e($app['job_title'] ?? '—') ?></td>
              <td>
                <?php if ($app['cv_file']): ?>
                  <a href="<?= UPLOAD_URL . e($app['cv_file']) ?>" target="_blank" class="btn btn-sm btn-outline">📎 CV</a>
                <?php else: ?>
                  <span style="color:#94a3b8;font-size:0.8rem;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $app['id'] ?>">
                  <input type="hidden" name="current_filter" value="<?= e($statusFilter) ?>">
                  <select name="status" onchange="this.form.submit()" class="badge <?= $cls ?>"
                          style="border:none;cursor:pointer;font-size:0.75rem;font-weight:600;padding:3px 8px;border-radius:50px;">
                    <?php foreach (['new', 'reviewed', 'interview', 'hired', 'rejected'] as $s): ?>
                      <option value="<?= $s ?>" <?= $app['status'] === $s ? 'selected' : '' ?>>
                        <?= $statusLabels[$s] ?? $s ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td style="font-size:0.8rem;color:#94a3b8;white-space:nowrap;">
                <?= date('d.m.Y H:i', strtotime($app['created_at'])) ?>
              </td>
              <td>
                <a href="applications.php?delete=<?= $app['id'] ?><?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '' ?>"
                   onclick="return confirm('Ștergi această aplicare?')"
                   class="btn btn-sm btn-danger">Șterge</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
