<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

adminLogout();
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
