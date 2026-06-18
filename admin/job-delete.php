<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: jobs.php?msg=deleted');
exit;
