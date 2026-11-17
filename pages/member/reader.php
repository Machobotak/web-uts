<?php

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

// Pastikan hanya member aktif yang dapat mengakses.
$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);
$member = $middleware->requireRole('member');

function reader_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$ebookId = trim($_GET['id'] ?? '');

if ($ebookId === '') {
    http_response_code(400);
    exit('ID ebook tidak valid.');
}

$ebook = $catalogService->findById($ebookId);

if (!$ebook) {
    http_response_code(404);
    exit('Ebook tidak ditemukan.');
}

// Periksa apakah ebook dimiliki transaksi member yang sudah disetujui.
$memberId = (int) $_SESSION['user_id'];
$transactions = $transactionService->getMemberTransactions($memberId);
$hasAccess = false;

foreach ($transactions as $transaction) {
    if (($transaction['status'] ?? '') !== 'approved') {
        continue;
    }

    foreach (($transaction['details'] ?? []) as $detail) {
        if ((string) ($detail['ebook_id'] ?? '') === $ebookId) {
            $hasAccess = true;
            break 2;
        }
    }
}

if (!$hasAccess) {
    http_response_code(403);
    exit('Akses membaca ditolak. Ebook belum disetujui untuk akun Anda.');
}

// Pastikan lokasi file berada di folder penyimpanan ebook.
$relativePath = str_replace('\\', '/', $ebook['file_path'] ?? '');

if (
    !str_starts_with($relativePath, 'storage/ebooks/') ||
    basename($relativePath) !== basename($relativePath)
) {
    http_response_code(404);
    exit('File PDF tidak tersedia.');
}

$filename = basename($relativePath);

if ($filename === '' || strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'pdf') {
    http_response_code(404);
    exit('File PDF tidak valid.');
}

$baseDirectory = realpath(dirname(__DIR__, 2) . '/storage/ebooks');
$filePath = realpath(dirname(__DIR__, 2) . '/' . $relativePath);

if (
    $baseDirectory === false ||
    $filePath === false ||
    !is_file($filePath) ||
    !str_starts_with(
        strtolower($filePath),
        strtolower($baseDirectory . DIRECTORY_SEPARATOR)
    )
) {
    http_response_code(404);
    exit('File PDF tidak ditemukan.');
}

// Mode stream: mengirim PDF setelah hak akses diperiksa.
if (($_GET['stream'] ?? '') === '1') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="ebook.pdf"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= reader_e($ebook['title']) ?> | Digital Library</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #172554;
            color: white;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        header h1 {
            font-size: 20px;
        }

        .back {
            color: white;
            text-decoration: none;
            background: #2563eb;
            padding: 10px 15px;
            border-radius: 7px;
            font-size: 14px;
        }

        main {
            max-width: 1200px;
            margin: 25px auto;
            padding: 0 20px;
        }

        .info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .info h2 {
            margin-bottom: 8px;
        }

        .info p {
            color: #64748b;
            font-size: 14px;
        }

        .reader {
            background: white;
            padding: 10px;
            border-radius: 10px;
        }

        iframe {
            display: block;
            width: 100%;
            height: 78vh;
            border: none;
            border-radius: 6px;
            background: #e2e8f0;
        }

        @media(max-width: 600px) {
            header {
                padding: 16px;
            }

            main {
                padding: 0 12px;
            }

            iframe {
                height: 70vh;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Digital Library - Reader</h1>

    <a class="back"
       href="<?= reader_e(app_url('pages/member/borrowings.php')) ?>">
        Kembali
    </a>
</header>

<main>
    <section class="info">
        <h2><?= reader_e($ebook['title']) ?></h2>
        <p>Penulis: <?= reader_e($ebook['author'] ?? '-') ?></p>
        <p>Selamat membaca ebook Anda.</p>
    </section>

    <section class="reader">
        <iframe
            src="<?= reader_e(app_url(
                'pages/member/reader.php?id=' . urlencode($ebookId) . '&stream=1'
            )) ?>"
            title="Pembaca PDF">
        </iframe>
    </section>
</main>

</body>
</html>