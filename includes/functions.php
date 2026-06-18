<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// ── Language helpers ────────────────────────────────────────────────────────

function getCurrentLang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function t(string $key, string $lang = ''): string {
    if (!$lang) $lang = getCurrentLang();
    global $translations;
    return $translations[$key] ?? $key;
}

function loadLang(string $lang): void {
    global $translations;
    $file = __DIR__ . '/../lang/' . $lang . '.php';
    $translations = file_exists($file) ? require $file : [];
}

// ── Content helpers ─────────────────────────────────────────────────────────

function getContent(string $key, string $lang = ''): string {
    if (!$lang) $lang = getCurrentLang();
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT value_ro, value_ru, value_en FROM site_content WHERE content_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (!$row) return '';
        $col = 'value_' . $lang;
        return $row[$col] ?: $row['value_ro'] ?: '';
    } catch (Exception $e) {
        return '';
    }
}

function getTranslationProvider(): string {
    $provider = '';
    if (defined('TRANSLATION_PROVIDER') && trim((string) TRANSLATION_PROVIDER) !== '') {
        $provider = trim((string) TRANSLATION_PROVIDER);
    } else {
        $envProvider = getenv('TRANSLATION_PROVIDER');
        $provider = is_string($envProvider) ? trim($envProvider) : '';
    }

    $provider = strtolower($provider);
    return in_array($provider, ['mymemory', 'libretranslate', 'openai'], true) ? $provider : 'mymemory';
}

function getMyMemoryApiBase(): string {
    $base = '';
    if (defined('MYMEMORY_API_BASE') && trim((string) MYMEMORY_API_BASE) !== '') {
        $base = trim((string) MYMEMORY_API_BASE);
    } else {
        $envBase = getenv('MYMEMORY_API_BASE');
        $base = is_string($envBase) ? trim($envBase) : '';
    }

    if ($base === '') {
        $base = 'https://api.mymemory.translated.net';
    }

    $scheme = strtolower((string) parse_url($base, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        $base = 'https://api.mymemory.translated.net';
    }

    return rtrim($base, '/');
}

function getMyMemoryContactEmail(): string {
    if (defined('MYMEMORY_CONTACT_EMAIL') && trim((string) MYMEMORY_CONTACT_EMAIL) !== '') {
        return trim((string) MYMEMORY_CONTACT_EMAIL);
    }

    $envEmail = getenv('MYMEMORY_CONTACT_EMAIL');
    return is_string($envEmail) ? trim($envEmail) : '';
}

function getLibreTranslateApiBase(): string {
    $base = '';
    if (defined('LIBRETRANSLATE_API_BASE') && trim((string) LIBRETRANSLATE_API_BASE) !== '') {
        $base = trim((string) LIBRETRANSLATE_API_BASE);
    } else {
        $envBase = getenv('LIBRETRANSLATE_API_BASE');
        $base = is_string($envBase) ? trim($envBase) : '';
    }

    if ($base === '') {
        $base = 'https://libretranslate.de';
    }

    $scheme = strtolower((string) parse_url($base, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        $base = 'https://libretranslate.de';
    }

    return rtrim($base, '/');
}

function getLibreTranslateApiKey(): string {
    if (defined('LIBRETRANSLATE_API_KEY') && trim((string) LIBRETRANSLATE_API_KEY) !== '') {
        return trim((string) LIBRETRANSLATE_API_KEY);
    }
    $envKey = getenv('LIBRETRANSLATE_API_KEY');
    return is_string($envKey) ? trim($envKey) : '';
}

function isLikelyOpenAiSecret(string $value): bool {
    $trimmed = trim($value);
    return $trimmed !== '' && str_starts_with($trimmed, 'sk-');
}

function getOpenAiApiKey(): string {
    if (defined('OPENAI_API_KEY') && trim((string) OPENAI_API_KEY) !== '') {
        return trim((string) OPENAI_API_KEY);
    }
    $envKey = getenv('OPENAI_API_KEY');
    if (is_string($envKey) && trim($envKey) !== '') {
        return trim($envKey);
    }

    // Backward-compatibility: users sometimes paste the API key in OPENAI_API_BASE by mistake.
    $fallback = '';
    if (defined('OPENAI_API_BASE') && trim((string) OPENAI_API_BASE) !== '') {
        $fallback = trim((string) OPENAI_API_BASE);
    } else {
        $envBase = getenv('OPENAI_API_BASE');
        $fallback = is_string($envBase) ? trim($envBase) : '';
    }

    if (isLikelyOpenAiSecret($fallback)) {
        return $fallback;
    }

    return '';
}

function getOpenAiApiBase(): string {
    $base = '';
    if (defined('OPENAI_API_BASE') && trim((string) OPENAI_API_BASE) !== '') {
        $base = trim((string) OPENAI_API_BASE);
    } else {
        $envBase = getenv('OPENAI_API_BASE');
        $base = is_string($envBase) ? trim($envBase) : '';
    }
    if ($base === '' || isLikelyOpenAiSecret($base)) {
        $base = 'https://api.openai.com/v1';
    }

    $scheme = strtolower((string) parse_url($base, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        $base = 'https://api.openai.com/v1';
    }

    return rtrim($base, '/');
}

function getOpenAiTranslationModel(): string {
    if (defined('OPENAI_TRANSLATION_MODEL') && trim((string) OPENAI_TRANSLATION_MODEL) !== '') {
        return trim((string) OPENAI_TRANSLATION_MODEL);
    }
    $envModel = getenv('OPENAI_TRANSLATION_MODEL');
    if (is_string($envModel) && trim($envModel) !== '') {
        return trim($envModel);
    }
    return 'gpt-5-mini';
}

function postJsonHttp(string $url, array $headers, array $payload): array {
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'curl_missing'];
    }

    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($body)) {
        return ['ok' => false, 'error' => 'invalid_payload'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'curl_init_failed'];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $raw = curl_exec($ch);
    $curlErrNo = curl_errno($ch);
    $curlErr = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'request_failed', 'message' => $curlErr ?: ('curl_errno_' . $curlErrNo)];
    }

    $json = json_decode((string) $raw, true);
    if (!is_array($json)) {
        if ($status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'error' => 'request_failed',
                'status' => $status,
                'message' => trim((string) $raw),
            ];
        }
        return ['ok' => false, 'error' => 'invalid_response'];
    }

    if ($status < 200 || $status >= 300) {
        $apiMessage = '';
        if (isset($json['error']['message']) && is_string($json['error']['message'])) {
            $apiMessage = $json['error']['message'];
        }
        return ['ok' => false, 'error' => 'request_failed', 'message' => $apiMessage, 'status' => $status];
    }

    return ['ok' => true, 'data' => $json, 'status' => $status];
}

function getJsonHttp(string $url, array $headers = []): array {
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'curl_missing'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'curl_init_failed'];
    }

    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 10,
    ];
    if (!empty($headers)) {
        $opts[CURLOPT_HTTPHEADER] = $headers;
    }

    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $curlErrNo = curl_errno($ch);
    $curlErr = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'request_failed', 'message' => $curlErr ?: ('curl_errno_' . $curlErrNo)];
    }

    $json = json_decode((string) $raw, true);
    if (!is_array($json)) {
        if ($status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'error' => 'request_failed',
                'status' => $status,
                'message' => trim((string) $raw),
            ];
        }
        return ['ok' => false, 'error' => 'invalid_response'];
    }

    if ($status < 200 || $status >= 300) {
        $apiMessage = '';
        if (isset($json['error']) && is_string($json['error'])) {
            $apiMessage = $json['error'];
        } elseif (isset($json['responseDetails']) && is_string($json['responseDetails'])) {
            $apiMessage = $json['responseDetails'];
        } elseif (isset($json['message']) && is_string($json['message'])) {
            $apiMessage = $json['message'];
        } elseif (isset($json['error']['message']) && is_string($json['error']['message'])) {
            $apiMessage = $json['error']['message'];
        }
        return ['ok' => false, 'error' => 'request_failed', 'message' => $apiMessage, 'status' => $status];
    }

    return ['ok' => true, 'data' => $json, 'status' => $status];
}

function extractTextFromAiResponse(array $data): string {
    if (isset($data['output_text']) && is_string($data['output_text']) && trim($data['output_text']) !== '') {
        return trim($data['output_text']);
    }

    if (isset($data['output']) && is_array($data['output'])) {
        $chunks = [];
        foreach ($data['output'] as $outputItem) {
            if (!is_array($outputItem)) {
                continue;
            }
            $contentItems = $outputItem['content'] ?? null;
            if (!is_array($contentItems)) {
                continue;
            }
            foreach ($contentItems as $content) {
                if (!is_array($content)) {
                    continue;
                }
                $type = (string)($content['type'] ?? '');
                if ($type === 'output_text' && isset($content['text']) && is_string($content['text'])) {
                    $chunks[] = $content['text'];
                }
            }
        }
        $combined = trim(implode("\n", $chunks));
        if ($combined !== '') {
            return $combined;
        }
    }

    if (isset($data['choices'][0]['message']['content']) && is_string($data['choices'][0]['message']['content'])) {
        return trim($data['choices'][0]['message']['content']);
    }

    if (isset($data['choices'][0]['message']['content']) && is_array($data['choices'][0]['message']['content'])) {
        $chunks = [];
        foreach ($data['choices'][0]['message']['content'] as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }
        return trim(implode("\n", $chunks));
    }

    return '';
}

function extractJsonObject(string $text): ?array {
    $trimmed = trim($text);
    if ($trimmed === '') {
        return null;
    }

    $decoded = json_decode($trimmed, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    $start = strpos($trimmed, '{');
    $end = strrpos($trimmed, '}');
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $candidate = substr($trimmed, $start, $end - $start + 1);
    $decoded = json_decode($candidate, true);
    return is_array($decoded) ? $decoded : null;
}

function normalizeTranslations(array $sourceTextsByKey, array $decoded): array {
    $translationsRoot = $decoded;
    if (isset($decoded['translations']) && is_array($decoded['translations'])) {
        $translationsRoot = $decoded['translations'];
    }

    $normalized = [];
    foreach ($sourceTextsByKey as $key => $_roText) {
        $entry = $translationsRoot[$key] ?? null;
        if (!is_array($entry)) {
            continue;
        }

        $ru = trim((string)($entry['ru'] ?? ''));
        $en = trim((string)($entry['en'] ?? ''));
        if ($ru === '' || $en === '') {
            continue;
        }

        $normalized[$key] = ['ru' => $ru, 'en' => $en];
    }

    return $normalized;
}

function classifyTranslationError(array $result, string $provider = 'openai'): string {
    $error = (string)($result['error'] ?? 'request_failed');
    if ($error !== 'request_failed') {
        return $error;
    }

    $provider = strtolower(trim($provider));
    $status = (int)($result['status'] ?? 0);
    $message = strtolower((string)($result['message'] ?? ''));

    if (
        str_contains($message, 'could not resolve host')
        || str_contains($message, 'failed to connect')
        || str_contains($message, 'operation timed out')
    ) {
        return 'network_error';
    }

    if ($provider === 'openai') {
        if ($status === 401 || str_contains($message, 'invalid api key')) {
            return 'invalid_api_key';
        }
        if ($status === 429 || str_contains($message, 'quota')) {
            return 'quota_exceeded';
        }
        if ($status === 404 && str_contains($message, 'model')) {
            return 'model_not_found';
        }
        if ($status >= 500) {
            return 'provider_unavailable';
        }
    }

    if ($provider === 'libretranslate') {
        if ($status === 301 || $status === 401 || $status === 403 || str_contains($message, 'api-schl') || str_contains($message, 'api key')) {
            return 'invalid_api_key';
        }
        if ($status === 429 || str_contains($message, 'rate limit') || str_contains($message, 'too many')) {
            return 'rate_limited';
        }
        if ($status >= 500 || str_contains($message, 'temporarily unavailable') || str_contains($message, 'server error')) {
            return 'provider_unavailable';
        }
        if (str_contains($message, 'unsupported') && str_contains($message, 'language')) {
            return 'unsupported_language';
        }
    }

    if ($provider === 'mymemory') {
        if ($status === 403) {
            if (str_contains($message, 'query length limit exceeded') || str_contains($message, 'max allowed query')) {
                return 'query_too_long';
            }
            return 'provider_unavailable';
        }
        if ($status === 429 || str_contains($message, 'you used all available free translations') || str_contains($message, 'rate')) {
            return 'rate_limited';
        }
        if ($status >= 500 || str_contains($message, 'temporarily unavailable') || str_contains($message, 'service unavailable')) {
            return 'provider_unavailable';
        }
        if (str_contains($message, 'language pair')) {
            return 'unsupported_language';
        }
    }

    return 'request_failed';
}

function translateRomanianTextsToRuEn(array $textsByKey): array {
    $sourceTexts = [];
    foreach ($textsByKey as $key => $text) {
        $cleanKey = trim((string)$key);
        $cleanText = trim((string)$text);
        if ($cleanKey === '' || $cleanText === '') {
            continue;
        }
        $sourceTexts[$cleanKey] = $cleanText;
    }

    if (empty($sourceTexts)) {
        return ['ok' => true, 'translations' => [], 'error' => ''];
    }

    $provider = getTranslationProvider();
    if ($provider === 'openai') {
        return translateWithOpenAi($sourceTexts);
    }

    if ($provider === 'libretranslate') {
        return translateWithLibreTranslate($sourceTexts);
    }

    return translateWithMyMemory($sourceTexts);
}

function translateWithMyMemory(array $sourceTexts): array {
    $apiBase = getMyMemoryApiBase();
    $contactEmail = getMyMemoryContactEmail();

    $translations = [];
    foreach ($sourceTexts as $key => $sourceText) {
        $ruResult = translateOneWithMyMemory($sourceText, 'ru', $apiBase, $contactEmail);
        if (!($ruResult['ok'] ?? false)) {
            return ['ok' => false, 'translations' => $translations, 'error' => (string)($ruResult['error'] ?? 'request_failed')];
        }

        $enResult = translateOneWithMyMemory($sourceText, 'en', $apiBase, $contactEmail);
        if (!($enResult['ok'] ?? false)) {
            return ['ok' => false, 'translations' => $translations, 'error' => (string)($enResult['error'] ?? 'request_failed')];
        }

        $translations[$key] = [
            'ru' => (string)$ruResult['text'],
            'en' => (string)$enResult['text'],
        ];
    }

    return ['ok' => true, 'translations' => $translations, 'error' => ''];
}

function translateOneWithGoogleFree(string $sourceText, string $targetLang): array {
    $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
        'client' => 'gtx',
        'sl' => 'ro',
        'tl' => $targetLang,
        'dt' => 't',
        'q' => $sourceText,
    ], '', '&', PHP_QUERY_RFC3986);

    $result = getJsonHttp($url);
    if (!($result['ok'] ?? false)) {
        return ['ok' => false, 'text' => '', 'error' => classifyTranslationError($result, 'mymemory')];
    }

    $data = $result['data'] ?? null;
    if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
        return ['ok' => false, 'text' => '', 'error' => 'invalid_response'];
    }

    $translatedParts = [];
    foreach ($data[0] as $part) {
        if (is_array($part) && isset($part[0]) && is_string($part[0])) {
            $translatedParts[] = $part[0];
        }
    }
    $translatedText = trim(implode('', $translatedParts));
    if ($translatedText === '') {
        return ['ok' => false, 'text' => '', 'error' => 'invalid_response'];
    }

    return ['ok' => true, 'text' => $translatedText, 'error' => ''];
}

function translateOneWithMyMemory(string $sourceText, string $targetLang, string $apiBase, string $contactEmail = ''): array {
    // MyMemory free endpoint has a strict 500 chars/query limit.
    if (mb_strlen($sourceText, 'UTF-8') > 480) {
        return translateOneWithGoogleFree($sourceText, $targetLang);
    }

    $params = [
        'q' => $sourceText,
        'langpair' => 'ro|' . $targetLang,
    ];
    if ($contactEmail !== '') {
        $params['de'] = $contactEmail;
    }

    $url = $apiBase . '/get?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $result = getJsonHttp($url);
    if (!($result['ok'] ?? false)) {
        return ['ok' => false, 'text' => '', 'error' => classifyTranslationError($result, 'mymemory')];
    }

    $providerStatus = (int)($result['data']['responseStatus'] ?? 200);
    if ($providerStatus !== 200) {
        $providerMessage = (string)($result['data']['responseDetails'] ?? '');
        $mappedError = classifyTranslationError([
            'error' => 'request_failed',
            'status' => $providerStatus,
            'message' => $providerMessage,
        ], 'mymemory');

        if (in_array($mappedError, ['query_too_long', 'rate_limited', 'provider_unavailable', 'request_failed'], true)) {
            $fallback = translateOneWithGoogleFree($sourceText, $targetLang);
            if ($fallback['ok'] ?? false) {
                return $fallback;
            }
        }

        return [
            'ok' => false,
            'text' => '',
            'error' => $mappedError,
        ];
    }

    $translatedText = trim((string)($result['data']['responseData']['translatedText'] ?? ''));
    if ($translatedText === '') {
        return ['ok' => false, 'text' => '', 'error' => 'invalid_response'];
    }

    return ['ok' => true, 'text' => $translatedText, 'error' => ''];
}

function translateWithLibreTranslate(array $sourceTexts): array {
    $apiBase = getLibreTranslateApiBase();
    $apiKey = getLibreTranslateApiKey();
    $headers = ['Content-Type: application/json'];

    $translations = [];
    foreach ($sourceTexts as $key => $sourceText) {
        $ruResult = translateOneWithLibreTranslate($sourceText, 'ru', $apiBase, $headers, $apiKey);
        if (!($ruResult['ok'] ?? false)) {
            return ['ok' => false, 'translations' => $translations, 'error' => (string)($ruResult['error'] ?? 'request_failed')];
        }

        $enResult = translateOneWithLibreTranslate($sourceText, 'en', $apiBase, $headers, $apiKey);
        if (!($enResult['ok'] ?? false)) {
            return ['ok' => false, 'translations' => $translations, 'error' => (string)($enResult['error'] ?? 'request_failed')];
        }

        $translations[$key] = [
            'ru' => (string)$ruResult['text'],
            'en' => (string)$enResult['text'],
        ];
    }

    return ['ok' => true, 'translations' => $translations, 'error' => ''];
}

function translateOneWithLibreTranslate(
    string $sourceText,
    string $targetLang,
    string $apiBase,
    array $headers,
    string $apiKey = ''
): array {
    $payload = [
        'q' => $sourceText,
        'source' => 'ro',
        'target' => $targetLang,
        'format' => preg_match('/<[^>]+>/', $sourceText) ? 'html' : 'text',
    ];

    if ($apiKey !== '') {
        $payload['api_key'] = $apiKey;
    }

    $result = postJsonHttp($apiBase . '/translate', $headers, $payload);
    if (!($result['ok'] ?? false)) {
        return ['ok' => false, 'text' => '', 'error' => classifyTranslationError($result, 'libretranslate')];
    }

    $translatedText = trim((string)($result['data']['translatedText'] ?? ''));
    if ($translatedText === '') {
        return ['ok' => false, 'text' => '', 'error' => 'invalid_response'];
    }

    return ['ok' => true, 'text' => $translatedText, 'error' => ''];
}

function translateWithOpenAi(array $sourceTexts): array {
    $apiKey = getOpenAiApiKey();
    if ($apiKey === '') {
        return ['ok' => false, 'translations' => [], 'error' => 'missing_api_key'];
    }

    $models = array_values(array_unique(array_filter([
        getOpenAiTranslationModel(),
        'gpt-5-mini',
        'gpt-4.1-mini',
        'gpt-4o-mini',
    ], static fn($model) => is_string($model) && trim($model) !== '')));

    $inputJson = json_encode($sourceTexts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($inputJson)) {
        return ['ok' => false, 'translations' => [], 'error' => 'invalid_payload'];
    }

    $systemPrompt = <<<PROMPT
You are a professional localization translator.
Translate Romanian website copy to Russian (ru) and English (en).
Keep meaning and tone natural for a careers/recruitment website.
Preserve HTML tags, placeholders, URLs, emails, phone numbers, punctuation, and line breaks.
Return valid JSON only.
PROMPT;

    $userPrompt = <<<PROMPT
Translate every value from this JSON object (Romanian source text):
$inputJson

Return JSON with the exact same keys and this structure:
{
  "hero_title": {"ru": "...", "en": "..."},
  "another_key": {"ru": "...", "en": "..."}
}
No markdown. No explanations. JSON only.
PROMPT;

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ];

    $apiBase = getOpenAiApiBase();
    $lastError = 'request_failed';

    foreach ($models as $model) {
        $responsesPayload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        ['type' => 'input_text', 'text' => $systemPrompt],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'input_text', 'text' => $userPrompt],
                    ],
                ],
            ],
        ];

        $responsesResult = postJsonHttp($apiBase . '/responses', $headers, $responsesPayload);
        if ($responsesResult['ok'] ?? false) {
            $text = extractTextFromAiResponse($responsesResult['data']);
            $decoded = extractJsonObject($text);
            if (is_array($decoded)) {
                $translations = normalizeTranslations($sourceTexts, $decoded);
                if (!empty($translations)) {
                    return ['ok' => true, 'translations' => $translations, 'error' => ''];
                }
                $lastError = 'invalid_response';
            } else {
                $lastError = 'invalid_response';
            }
        } else {
            $lastError = classifyTranslationError($responsesResult, 'openai');
        }

        $chatPayload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $chatResult = postJsonHttp($apiBase . '/chat/completions', $headers, $chatPayload);
        if ($chatResult['ok'] ?? false) {
            $text = extractTextFromAiResponse($chatResult['data']);
            $decoded = extractJsonObject($text);
            if (is_array($decoded)) {
                $translations = normalizeTranslations($sourceTexts, $decoded);
                if (!empty($translations)) {
                    return ['ok' => true, 'translations' => $translations, 'error' => ''];
                }
                $lastError = 'invalid_response';
            } else {
                $lastError = 'invalid_response';
            }
        } else {
            $lastError = classifyTranslationError($chatResult, 'openai');
        }
    }

    return ['ok' => false, 'translations' => [], 'error' => $lastError];
}

function getServiceIconOptions(): array {
    return [
        'clipboard-check' => 'Checklist',
        'globe' => 'Globe',
        'file-text' => 'Document',
        'megaphone' => 'Megaphone',
        'laptop' => 'Laptop',
        'network' => 'Network',
        'headset' => 'Headset',
        'toolbox' => 'Toolbox',
    ];
}

function getServiceIconSvgs(): array {
    return [
        'clipboard-check' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M9 5.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 5.5"/><path d="m10 13 2 2 4-4"/></svg>',
        'globe' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8"/><path d="M4 12h16"/><path d="M12 4a13 13 0 0 1 0 16"/><path d="M12 4a13 13 0 0 0 0 16"/></svg>',
        'file-text' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 3H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M10 13h6"/><path d="M10 17h4"/></svg>',
        'megaphone' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12v1a2 2 0 0 0 2 2h2l2 4h2l-1.5-4H14l5 3V6l-5 3H6a2 2 0 0 0-2 2Z"/></svg>',
        'laptop' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="5" y="6" width="14" height="10" rx="1.5"/><path d="M3 18h18"/><path d="M10 18h4"/></svg>',
        'network' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="6" cy="6" r="2"/><circle cx="18" cy="6" r="2"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/><path d="M8 6h8"/><path d="M6 8v8"/><path d="M18 8v8"/><path d="M8 18h8"/></svg>',
        'headset' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12a8 8 0 0 1 16 0"/><rect x="3" y="12" width="4" height="6" rx="2"/><rect x="17" y="12" width="4" height="6" rx="2"/><path d="M17 18a3 3 0 0 1-3 3h-2"/></svg>',
        'toolbox' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M8 9V7a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 13h4"/></svg>',
    ];
}

function normalizeServiceIconKey(string $iconKey): string {
    $iconKey = trim($iconKey);
    $icons = getServiceIconOptions();
    if ($iconKey !== '' && array_key_exists($iconKey, $icons)) {
        return $iconKey;
    }
    $fallback = array_key_first($icons);
    return is_string($fallback) ? $fallback : 'clipboard-check';
}

function getDefaultServicesCatalog(): array {
    return [
        [
            'ro' => 'Servicii de traducere',
            'ru' => 'Услуги перевода',
            'en' => 'Translation services',
            'description_ro' => 'Traduceri clare și corecte, adaptate domeniului proiectului tău.',
            'description_ru' => 'Четкие и точные переводы, адаптированные к тематике вашего проекта.',
            'description_en' => 'Clear and accurate translations tailored to your project domain.',
            'icon_key' => 'clipboard-check',
        ],
        [
            'ro' => 'Localizare',
            'ru' => 'Локализация',
            'en' => 'Localization',
            'description_ro' => 'Adaptăm conținutul pentru publicul local și contextul cultural potrivit.',
            'description_ru' => 'Адаптируем контент для локальной аудитории и культурного контекста.',
            'description_en' => 'We adapt content for local audiences and the right cultural context.',
            'icon_key' => 'globe',
        ],
        [
            'ro' => 'Autentificare de documente',
            'ru' => 'Аутентификация документов',
            'en' => 'Document authentication',
            'description_ro' => 'Gestionăm documente oficiale cu atenție la detalii și conformitate.',
            'description_ru' => 'Работаем с официальными документами с вниманием к деталям и требованиям.',
            'description_en' => 'We handle official documents with attention to detail and compliance.',
            'icon_key' => 'file-text',
        ],
        [
            'ro' => 'Spoturi publicitare, inclusiv multilingve',
            'ru' => 'Рекламные ролики, включая многоязычные',
            'en' => 'Advertising spots, including multilingual',
            'description_ro' => 'Mesaje persuasive pentru campanii și spoturi, inclusiv versiuni multilingve.',
            'description_ru' => 'Убедительные тексты для кампаний и роликов, включая многоязычные версии.',
            'description_en' => 'Persuasive copy for campaigns and spots, including multilingual versions.',
            'icon_key' => 'megaphone',
        ],
        [
            'ro' => 'Traducere și localizare site-uri web',
            'ru' => 'Перевод и локализация веб-сайтов',
            'en' => 'Website translation and localization',
            'description_ro' => 'Traducem și optimizăm conținutul web pentru o experiență coerentă.',
            'description_ru' => 'Переводим и оптимизируем веб-контент для целостного пользовательского опыта.',
            'description_en' => 'We translate and optimize web content for a consistent user experience.',
            'icon_key' => 'laptop',
        ],
        [
            'ro' => 'Elaborare site-uri în mai multe limbi',
            'ru' => 'Разработка сайтов на нескольких языках',
            'en' => 'Multilingual website development',
            'description_ro' => 'Dezvoltăm soluții digitale moderne pentru prezență online multilingvă.',
            'description_ru' => 'Разрабатываем современные цифровые решения для многоязычного онлайн-присутствия.',
            'description_en' => 'We build modern digital solutions for multilingual online presence.',
            'icon_key' => 'network',
        ],
        [
            'ro' => 'Interpretare și traduceri sincronice: conferințe, întâlniri de afaceri, apeluri video și telefonice, publicații și materiale tripartite etc.',
            'ru' => 'Устный и синхронный перевод: конференции, деловые встречи, видео- и телефонные звонки, публикации и трехсторонние материалы и т.д.',
            'en' => 'Interpreting and simultaneous translation for conferences, business meetings, video and phone calls, publications and tripartite materials, etc.',
            'description_ro' => 'Interpretare profesionistă pentru conferințe, întâlniri și comunicare live.',
            'description_ru' => 'Профессиональный устный перевод для конференций, встреч и live-коммуникации.',
            'description_en' => 'Professional interpreting for conferences, meetings, and live communication.',
            'icon_key' => 'headset',
        ],
        [
            'ro' => 'Elaborare website-uri',
            'ru' => 'Разработка веб-сайтов',
            'en' => 'Website development',
            'description_ro' => 'Soluții profesionale de limbă, livrate rapid și cu standarde înalte.',
            'description_ru' => 'Профессиональные языковые решения с быстрым выполнением и высоким качеством.',
            'description_en' => 'Professional language solutions delivered fast with high standards.',
            'icon_key' => 'toolbox',
        ],
    ];
}

function getTableColumns(string $table, bool $refresh = false): array {
    static $columnsCache = [];
    if (!$refresh && isset($columnsCache[$table])) {
        return $columnsCache[$table];
    }

    try {
        $db = getDB();
        $safeTable = str_replace('`', '``', $table);
        $rows = $db->query("SHOW COLUMNS FROM `{$safeTable}`")->fetchAll();
        $columns = [];
        foreach ($rows as $row) {
            $field = (string)($row['Field'] ?? '');
            if ($field !== '') {
                $columns[$field] = true;
            }
        }
        $columnsCache[$table] = $columns;
        return $columns;
    } catch (Throwable $e) {
        return [];
    }
}

function ensureServicesCatalog(): void {
    static $initialized = false;
    if ($initialized) {
        return;
    }

    try {
        $db = getDB();
        $db->exec("
            CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title_ro VARCHAR(255) NOT NULL,
                title_ru VARCHAR(255) DEFAULT '',
                title_en VARCHAR(255) DEFAULT '',
                description_ro TEXT,
                description_ru TEXT,
                description_en TEXT,
                icon_key VARCHAR(64) NOT NULL DEFAULT 'clipboard-check',
                sort_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                UNIQUE KEY uniq_services_title_ro (title_ro),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $columnMigrations = [
            'description_ro' => "ALTER TABLE services ADD COLUMN description_ro TEXT AFTER title_en",
            'description_ru' => "ALTER TABLE services ADD COLUMN description_ru TEXT AFTER description_ro",
            'description_en' => "ALTER TABLE services ADD COLUMN description_en TEXT AFTER description_ru",
            'icon_key' => "ALTER TABLE services ADD COLUMN icon_key VARCHAR(64) NOT NULL DEFAULT 'clipboard-check' AFTER description_en",
        ];
        $columns = getTableColumns('services', true);
        foreach ($columnMigrations as $column => $alterSql) {
            if (!isset($columns[$column])) {
                try {
                    $db->exec($alterSql);
                } catch (Throwable $e) {
                    // Ignore migration errors here and continue with available columns.
                }
                $columns = getTableColumns('services', true);
            }
        }

        $hasDescRo = isset($columns['description_ro']);
        $hasDescRu = isset($columns['description_ru']);
        $hasDescEn = isset($columns['description_en']);
        $hasIconKey = isset($columns['icon_key']);

        $defaults = getDefaultServicesCatalog();
        $count = (int)$db->query("SELECT COUNT(*) FROM services")->fetchColumn();
        if ($count === 0) {
            $insertColumns = ['title_ro', 'title_ru', 'title_en', 'sort_order', 'is_active'];
            if ($hasDescRo) $insertColumns[] = 'description_ro';
            if ($hasDescRu) $insertColumns[] = 'description_ru';
            if ($hasDescEn) $insertColumns[] = 'description_en';
            if ($hasIconKey) $insertColumns[] = 'icon_key';

            $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
            $insertSql = 'INSERT INTO services (' . implode(', ', $insertColumns) . ') VALUES (' . $placeholders . ')';
            $insert = $db->prepare($insertSql);

            foreach ($defaults as $idx => $service) {
                $values = [];
                foreach ($insertColumns as $column) {
                    if ($column === 'title_ro') {
                        $values[] = $service['ro'];
                    } elseif ($column === 'title_ru') {
                        $values[] = $service['ru'];
                    } elseif ($column === 'title_en') {
                        $values[] = $service['en'];
                    } elseif ($column === 'sort_order') {
                        $values[] = $idx + 1;
                    } elseif ($column === 'is_active') {
                        $values[] = 1;
                    } elseif ($column === 'description_ro') {
                        $values[] = $service['description_ro'];
                    } elseif ($column === 'description_ru') {
                        $values[] = $service['description_ru'];
                    } elseif ($column === 'description_en') {
                        $values[] = $service['description_en'];
                    } elseif ($column === 'icon_key') {
                        $values[] = normalizeServiceIconKey((string)$service['icon_key']);
                    }
                }
                $insert->execute($values);
            }
        } elseif ($hasDescRo || $hasDescRu || $hasDescEn || $hasIconKey) {
            $selectColumns = ['id', 'title_ro'];
            if ($hasDescRo) $selectColumns[] = 'description_ro';
            if ($hasDescRu) $selectColumns[] = 'description_ru';
            if ($hasDescEn) $selectColumns[] = 'description_en';
            if ($hasIconKey) $selectColumns[] = 'icon_key';

            $rows = $db->query("SELECT " . implode(', ', $selectColumns) . " FROM services")->fetchAll();
            $defaultsByTitle = [];
            foreach ($defaults as $defaultRow) {
                $defaultsByTitle[$defaultRow['ro']] = $defaultRow;
            }

            $updateColumns = [];
            if ($hasDescRo) $updateColumns[] = 'description_ro';
            if ($hasDescRu) $updateColumns[] = 'description_ru';
            if ($hasDescEn) $updateColumns[] = 'description_en';
            if ($hasIconKey) $updateColumns[] = 'icon_key';

            $setParts = [];
            foreach ($updateColumns as $updateColumn) {
                $setParts[] = $updateColumn . ' = ?';
            }
            $updateStmt = $db->prepare("UPDATE services SET " . implode(', ', $setParts) . " WHERE id = ?");

            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $titleRo = (string)($row['title_ro'] ?? '');
                $descRo = trim((string)($row['description_ro'] ?? ''));
                $descRu = trim((string)($row['description_ru'] ?? ''));
                $descEn = trim((string)($row['description_en'] ?? ''));
                $iconKey = normalizeServiceIconKey((string)($row['icon_key'] ?? ''));

                $changed = false;
                if ($hasDescRo && $descRo === '' && isset($defaultsByTitle[$titleRo])) {
                    $defaultRow = $defaultsByTitle[$titleRo];
                    $descRo = $defaultRow['description_ro'];
                    if ($hasDescRu) {
                        $descRu = $descRu !== '' ? $descRu : $defaultRow['description_ru'];
                    }
                    if ($hasDescEn) {
                        $descEn = $descEn !== '' ? $descEn : $defaultRow['description_en'];
                    }
                    $changed = true;
                }

                if ($hasIconKey && (trim((string)($row['icon_key'] ?? '')) === '' || (string)($row['icon_key'] ?? '') !== $iconKey)) {
                    $changed = true;
                }

                if ($changed) {
                    $values = [];
                    foreach ($updateColumns as $column) {
                        if ($column === 'description_ro') {
                            $values[] = $descRo;
                        } elseif ($column === 'description_ru') {
                            $values[] = $descRu;
                        } elseif ($column === 'description_en') {
                            $values[] = $descEn;
                        } elseif ($column === 'icon_key') {
                            $values[] = $iconKey;
                        }
                    }
                    $values[] = $id;
                    $updateStmt->execute($values);
                }
            }
        }
    } catch (Throwable $e) {
        // Avoid breaking the public site if services setup fails.
    }

    $initialized = true;
}

function getActiveServices(string $lang = ''): array {
    if (!$lang) $lang = getCurrentLang();

    try {
        ensureServicesCatalog();
        $db = getDB();
        $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $services = $stmt->fetchAll();
        foreach ($services as &$service) {
            $titleLang = trim((string)($service['title_' . $lang] ?? ''));
            $titleRo = trim((string)($service['title_ro'] ?? ''));
            $descriptionLang = trim((string)($service['description_' . $lang] ?? ''));
            $descriptionRo = trim((string)($service['description_ro'] ?? ''));
            $service['title'] = $titleLang !== '' ? $titleLang : $titleRo;
            $service['description'] = $descriptionLang !== '' ? $descriptionLang : $descriptionRo;
            $service['icon_key'] = normalizeServiceIconKey((string)($service['icon_key'] ?? ''));
        }
        return $services;
    } catch (Throwable $e) {
        return [];
    }
}

function getAllServices(): array {
    try {
        ensureServicesCatalog();
        $db = getDB();
        $stmt = $db->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC");
        $services = $stmt->fetchAll();
        foreach ($services as &$service) {
            $service['icon_key'] = normalizeServiceIconKey((string)($service['icon_key'] ?? ''));
        }
        return $services;
    } catch (Throwable $e) {
        return [];
    }
}

function getServiceById(int $id): ?array {
    try {
        ensureServicesCatalog();
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch() ?: null;
        if (!$service) {
            return null;
        }
        $service['icon_key'] = normalizeServiceIconKey((string)($service['icon_key'] ?? ''));
        return $service;
    } catch (Throwable $e) {
        return null;
    }
}

// ── Job helpers ─────────────────────────────────────────────────────────────

function getActiveJobs(string $lang = ''): array {
    if (!$lang) $lang = getCurrentLang();
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM jobs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $jobs = $stmt->fetchAll();
        foreach ($jobs as &$job) {
            $job['title'] = $job['title_' . $lang] ?: $job['title_ro'];
            $job['short_desc'] = $job['short_desc_' . $lang] ?: $job['short_desc_ro'];
            $job['full_desc'] = $job['full_desc_' . $lang] ?: $job['full_desc_ro'];
        }
        return $jobs;
    } catch (Exception $e) {
        return [];
    }
}

function getAllJobs(): array {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM jobs ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getJobById(int $id): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ── Application helpers ──────────────────────────────────────────────────────

function saveApplication(array $data): bool|string {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO applications (job_id, name, email, phone, cv_file) VALUES (?,?,?,?,?)");
        $stmt->execute([$data['job_id'], $data['name'], $data['email'], $data['phone'], $data['cv_file']]);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function handleCvUpload(array $file, ?string &$errorMessage = null): string|false {
    $errorMessage = null;
    $uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
        $errorMessage = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fișierul depășește limita permisă. Acceptăm PDF, DOC, DOCX până la 5MB.',
            UPLOAD_ERR_PARTIAL => 'Fișierul a fost încărcat parțial. Te rugăm să încerci din nou.',
            UPLOAD_ERR_NO_FILE => 'Nu ai selectat niciun fișier.',
            UPLOAD_ERR_NO_TMP_DIR => 'Lipsește folderul temporar de upload pe server.',
            UPLOAD_ERR_CANT_WRITE => 'Serverul nu poate scrie fișierul pe disc.',
            UPLOAD_ERR_EXTENSION => 'Încărcarea fișierului a fost oprită de o extensie PHP a serverului.',
            default => 'A apărut o eroare la încărcarea fișierului.'
        };
        return false;
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        $errorMessage = 'Fișierul selectat este gol sau invalid.';
        return false;
    }
    if ($size > MAX_FILE_SIZE) {
        $errorMessage = 'Fișierul depășește limita permisă. Acceptăm PDF, DOC, DOCX până la 5MB.';
        return false;
    }

    $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!$ext || !in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $errorMessage = 'Format invalid. Acceptăm doar PDF, DOC sau DOCX.';
        return false;
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errorMessage = 'Fișierul nu a fost primit corect de server.';
        return false;
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        $errorMessage = 'Nu putem crea folderul de upload pe server.';
        return false;
    }

    if (!is_writable(UPLOAD_DIR)) {
        $errorMessage = 'Folderul de upload nu are permisiuni de scriere.';
        return false;
    }

    $newName = uniqid('cv_', true) . '.' . $ext;
    $dest = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $newName;
    if (!move_uploaded_file($tmpName, $dest)) {
        $errorMessage = 'Nu am putut salva fișierul pe server.';
        return false;
    }

    return $newName;
}

// ── Security helpers ─────────────────────────────────────────────────────────

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

function generateSlug(string $str): string {
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace(['ă','â','î','ș','ț','ş','ţ'], ['a','a','i','s','t','s','t'], $str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', trim($str));
    return $str;
}
