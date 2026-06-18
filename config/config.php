<?php
// PoliLingua - Database Configuration
// Keep real credentials in config/config.local.php or environment variables.

function configValue(string $name, string $default = ''): string {
    $value = getenv($name);
    return $value === false || $value === '' ? $default : $value;
}

function defineConfig(string $name, $value): void {
    if (!defined($name)) {
        define($name, $value);
    }
}

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    require $localConfig;
}

defineConfig('APP_ENV', configValue('APP_ENV', 'production'));
defineConfig('APP_DEBUG', filter_var(configValue('APP_DEBUG', '0'), FILTER_VALIDATE_BOOLEAN));

ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Lax');

defineConfig('DB_HOST', configValue('DB_HOST', 'localhost'));
defineConfig('DB_PORT', (int) configValue('DB_PORT', '3306'));
defineConfig('DB_NAME', configValue('DB_NAME', 'polilingua'));
defineConfig('DB_USER', configValue('DB_USER', 'your_db_user'));
defineConfig('DB_PASS', configValue('DB_PASS', ''));
defineConfig('DB_CHARSET', configValue('DB_CHARSET', 'utf8mb4'));

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
defineConfig('SITE_URL', configValue('SITE_URL', $scheme . '://' . $host));
defineConfig('UPLOAD_DIR', __DIR__ . '/../uploads/cv/');
defineConfig('UPLOAD_URL', rtrim(SITE_URL, '/') . '/uploads/cv/');
defineConfig('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
defineConfig('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

defineConfig('SESSION_NAME', 'polilingua_admin');
defineConfig('ADMIN_TITLE', 'PoliLingua Admin');
defineConfig('DEFAULT_ADMIN_EMAIL', configValue('DEFAULT_ADMIN_EMAIL', ''));
defineConfig('DEFAULT_ADMIN_PASSWORD_HASH', configValue('DEFAULT_ADMIN_PASSWORD_HASH', ''));

// Default language
defineConfig('DEFAULT_LANG', 'ro');
defineConfig('SUPPORTED_LANGS', ['ro', 'ru', 'en']);

// Auto translation provider (RO -> RU/EN)
// Available: 'mymemory' (free, default), 'libretranslate', 'openai'
defineConfig('TRANSLATION_PROVIDER', configValue('TRANSLATION_PROVIDER', 'mymemory'));

// Free provider (MyMemory)
defineConfig('MYMEMORY_API_BASE', configValue('MYMEMORY_API_BASE', 'https://api.mymemory.translated.net'));
defineConfig('MYMEMORY_CONTACT_EMAIL', configValue('MYMEMORY_CONTACT_EMAIL', ''));

// Free provider (public instance; can be replaced with your own LibreTranslate server)
defineConfig('LIBRETRANSLATE_API_BASE', configValue('LIBRETRANSLATE_API_BASE', 'https://libretranslate.de'));
defineConfig('LIBRETRANSLATE_API_KEY', configValue('LIBRETRANSLATE_API_KEY', ''));

// Optional paid provider
defineConfig('OPENAI_API_KEY', configValue('OPENAI_API_KEY', ''));
defineConfig('OPENAI_API_BASE', configValue('OPENAI_API_BASE', 'https://api.openai.com/v1'));
defineConfig('OPENAI_TRANSLATION_MODEL', configValue('OPENAI_TRANSLATION_MODEL', 'gpt-5-mini'));
