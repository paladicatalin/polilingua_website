<?php
// Copy this file to config.local.php and fill in your real values.
// config.local.php is ignored by Git and must not be committed.

define('APP_ENV', 'development');
define('APP_DEBUG', true);

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'polilingua');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'http://localhost/projfinal');

// Optional local admin seed. Generate the hash privately with password_hash().
// define('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
// define('DEFAULT_ADMIN_PASSWORD_HASH', 'replace_with_private_password_hash');
