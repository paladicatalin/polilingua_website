<?php
// apply.php - handles form submission via AJAX
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$jobId = (int)($_POST['job_id'] ?? 0);
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');

// Validate
$errors = [];
if (!$name) $errors[] = 'Numele este obligatoriu';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalid';
if (!$phone) $errors[] = 'Telefonul este obligatoriu';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Handle CV upload
$cvFile = '';
if (!empty($_FILES['cv_file']['name'])) {
    $uploadErrorMessage = null;
    $uploaded = handleCvUpload($_FILES['cv_file'], $uploadErrorMessage);
    if ($uploaded === false) {
        echo json_encode(['success' => false, 'message' => $uploadErrorMessage ?: 'Fișier invalid. Acceptăm PDF, DOC, DOCX până la 5MB.']);
        exit;
    }
    $cvFile = $uploaded;
}

$result = saveApplication([
    'job_id' => $jobId ?: null,
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'cv_file' => $cvFile,
]);

if ($result === true) {
    echo json_encode(['success' => true]);
} else {
    $rawError = (string)$result;
    error_log('[apply.php] saveApplication failed: ' . $rawError);

    $message = 'A apărut o eroare. Te rugăm să încerci din nou.';
    if (str_contains($rawError, 'SQLSTATE[HY000] [2002]')) {
        $message = 'Nu ne putem conecta la baza de date. Verifică dacă MySQL este pornit.';
    } elseif (str_contains($rawError, 'applications') && str_contains($rawError, 'doesn\'t exist')) {
        $message = 'Tabela aplicațiilor nu există în baza de date. Rulează schema SQL.';
    } elseif (str_contains(strtolower($rawError), 'foreign key')) {
        $message = 'Postul selectat nu este valid. Reîncarcă pagina și încearcă din nou.';
    } elseif (str_contains($rawError, 'Data too long')) {
        $message = 'Unul dintre câmpuri este prea lung. Te rugăm să scurtezi datele introduse.';
    }

    echo json_encode(['success' => false, 'message' => $message]);
}
