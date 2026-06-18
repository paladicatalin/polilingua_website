<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // Această opțiune asigură că PDO comunică în UTF-8 încă de la început
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $canRetryTcp = DB_HOST === 'localhost'
                && str_contains($e->getMessage(), 'No such file or directory');
            if ($canRetryTcp) {
                $retryDsn = "mysql:host=127.0.0.1;port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $pdo = new PDO($retryDsn, DB_USER, DB_PASS, $options);
            } else {
                throw $e;
            }
        }
    }
    return $pdo;
}