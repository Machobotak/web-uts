<?php

// Autoload class berdasarkan namespace.
spl_autoload_register(function ($class) {
    $prefix = 'App\\';

    $baseDirectory = dirname(__DIR__) . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));

    $file = $baseDirectory .
        str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) .
        '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Memulai session jika belum aktif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Membangun URL absolut yang tetap benar meski aplikasi
// diakses lewat subfolder (mis. /web-uts).
function app_url(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $root = '';

    if (preg_match('#^(.*?)/(handlers|pages|public)/#', $scriptName, $matches)) {
        $root = $matches[1];
    }

    return rtrim($root, '/') . '/' . ltrim($path, '/');
}

// Menentukan apakah response sebaiknya JSON.
// Klien HTML (browser) mengirim Accept berisi text/html,
// sedangkan Postman/HTTP client lain tidak.
function wants_json(): bool
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
        return true;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    return $accept !== '' && !str_contains($accept, 'text/html');
}

// Membuat instance penyimpanan JSON.
use App\Storage\JsonStorage;

$storage = new JsonStorage(dirname(__DIR__) . '/data');
