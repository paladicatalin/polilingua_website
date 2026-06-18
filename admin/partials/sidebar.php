<?php
// admin/partials/sidebar.php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
  <div class="sidebar-logo">
    <a href="dashboard.php" class="sidebar-brand" aria-label="PoliLingua Admin">
      <img src="<?= SITE_URL ?>/assets/images/logo/logo.svg" alt="PoliLingua" class="sidebar-brand-logo">
    </a>
    <small class="sidebar-brand-subtitle">Admin Panel</small>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="<?= $currentFile==='dashboard.php'?'active':'' ?>">
      Dashboard
    </a>
    <a href="jobs.php" class="<?= in_array($currentFile,['jobs.php','job-create.php','job-edit.php'])?'active':'' ?>">
      Posturi vacante
    </a>
    <a href="applications.php" class="<?= $currentFile==='applications.php'?'active':'' ?>">
      Aplicări
    </a>
    <a href="services.php" class="<?= in_array($currentFile,['services.php','service-create.php','service-edit.php','service-delete.php'])?'active':'' ?>">
      Servicii
    </a>
    <a href="content.php" class="<?= $currentFile==='content.php'?'active':'' ?>">
      Conținut site
    </a>
    <a href="<?= SITE_URL ?>/index.php" target="_blank">
      Vezi site-ul
    </a>
    <a href="logout.php" style="margin-top:auto;color:rgba(255,100,100,0.8);">
      Deconectare
    </a>
  </nav>
  <div class="sidebar-footer">
    Logat ca: <?= e($_SESSION['admin_email'] ?? '') ?>
  </div>
</aside>
