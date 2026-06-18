<?php
// admin/partials/header.php
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle ?? 'Admin') ?> — PoliLingua Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/admin/admin.css">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <h1><?= e($adminTitle ?? 'Dashboard') ?></h1>
  <div class="admin-topbar-right">
    <span style="font-size:0.85rem;color:#64748b;">Bun venit, Admin</span>
    <a href="logout.php" class="btn btn-outline btn-sm">Ieșire</a>
  </div>
</div>
<div class="admin-content">
