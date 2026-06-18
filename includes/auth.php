<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function startAdminSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function ensureDefaultAdminAccount(PDO $db): void {
    if (DEFAULT_ADMIN_EMAIL === '' || DEFAULT_ADMIN_PASSWORD_HASH === '') {
        return;
    }

    try {
        $stmt = $db->prepare("SELECT id, password_hash FROM admins WHERE email = ?");
        $stmt->execute([DEFAULT_ADMIN_EMAIL]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $insert = $db->prepare("INSERT INTO admins (email, password_hash) VALUES (?, ?)");
            $insert->execute([DEFAULT_ADMIN_EMAIL, DEFAULT_ADMIN_PASSWORD_HASH]);
            return;
        }
    } catch (Throwable $e) {
        // Keep login flow safe even if table is missing/misconfigured.
    }
}

function requireAdminAuth(): void {
    startAdminSession();
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function adminLogin(string $email, string $password): bool {
    try {
        startAdminSession();
        $db = getDB();
        ensureDefaultAdminAccount($db);

        $email = strtolower(trim($email));
        $stmt = $db->prepare("SELECT id, password_hash FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $email;
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function adminLogout(): void {
    startAdminSession();
    session_destroy();
}

function isAdminLoggedIn(): bool {
    startAdminSession();
    return !empty($_SESSION['admin_id']);
}
