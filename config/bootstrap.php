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

// Membuat instance penyimpanan JSON.
use App\Storage\JsonStorage;

$storage = new JsonStorage(dirname(__DIR__) . '/data');
