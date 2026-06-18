<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
ensureServicesCatalog();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: services.php?msg=deleted');
exit;
