<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

startAdminSession();

if (isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
$dbError = '';
try {
    $db = getDB();
    ensureDefaultAdminAccount($db);
} catch (Throwable $e) {
    $dbError = 'Conexiunea la baza de date a eșuat. Verifică setările DB din config/config.php și că schema este importată.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($dbError) {
        $error = $dbError;
    } elseif (adminLogin($email, $password)) {
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
        exit;
    } else {
        $error = 'Email sau parolă incorectă.';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — PoliLingua</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/admin/admin.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">Poli<span>Lingua</span></div>
    <p style="text-align:center;color:#64748b;font-size:0.9rem;margin-bottom:28px;">Autentifică-te în panoul de administrare</p>

    <?php if ($dbError && !$error): ?>
      <div class="alert alert-error"><?= e($dbError) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autofocus placeholder="email@example.com"
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Parolă</label>
        <input type="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">
        Autentificare
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.78rem;color:#94a3b8;">
      <a href="<?= SITE_URL ?>/index.php" style="color:#2563EB;">← Înapoi la site</a>
    </p>
  </div>
</div>
</body>
</html>
