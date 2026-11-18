<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk member yang sudah login.
$member = $middleware->requireRole('member');

$ebookId = (int) ($_GET['id'] ?? 0);

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$ebook = $catalogService->findEbookById($ebookId);

if ($ebook === null) {
    http_response_code(404);
    exit('Ebook tidak ditemukan.');
}

// Member hanya boleh membaca ebook dari transaksi yang sudah disetujui.
$hasAccess = false;

foreach ($transactionService->getTransactionsByUser($member->getIdUser()) as $transaction) {
    if (($transaction['status'] ?? '') !== 'approved') {
        continue;
    }

    $details = $transactionService->getTransactionDetailsWithEbook(
        (int) ($transaction['id_transaction'] ?? 0)
    );

    foreach ($details as $detail) {
        if ((int) ($detail['id_ebook'] ?? 0) === $ebookId) {
            $hasAccess = true;
            break 2;
        }
    }
}

if (!$hasAccess) {
    http_response_code(403);
    exit('Akses membaca ditolak. Ebook belum disetujui untuk akun Anda.');
}

// file_path hanya menyimpan nama file di dalam storage/ebooks.
$baseDirectory = realpath(__DIR__ . '/../../storage/ebooks');
$fileName = basename((string) ($ebook['file_path'] ?? ''));
$filePath = $baseDirectory === false || $fileName === ''
    ? false
    : realpath($baseDirectory . DIRECTORY_SEPARATOR . $fileName);

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (
    $filePath === false
    || !is_file($filePath)
    || !in_array($extension, ['pdf', 'epub'], true)
    || !str_starts_with(
        strtolower($filePath),
        strtolower($baseDirectory . DIRECTORY_SEPARATOR)
    )
) {
    http_response_code(404);
    exit('File ebook tidak tersedia.');
}

// Mode stream: mengirim file setelah hak akses diperiksa.
if (($_GET['stream'] ?? '') === '1') {
    $contentTypes = [
        'pdf' => 'application/pdf',
        'epub' => 'application/epub+zip',
    ];

    header('Content-Type: ' . $contentTypes[$extension]);
    header('Content-Disposition: inline; filename="ebook.' . $extension . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;
}

$pageTitle = $ebook['title'] . ' | Digital Library';
$pageHeading = $ebook['title'];
$pageSubtitle = 'Penulis: ' . ($ebook['author'] ?? '-') . ' — selamat membaca.';
$activeNav = 'borrowings';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="panel">

    <div class="panel-head">
        <h2>Pembaca Ebook</h2>

        <a
            class="btn btn-outline"
            href="<?= e(app_url('pages/member/borrowings.php')) ?>"
        >Kembali</a>
    </div>

    <?php if ($extension === 'pdf'): ?>
        <iframe
            class="reader-frame"
            src="<?= e(app_url('pages/member/reader.php?id=' . $ebookId . '&stream=1')) ?>"
            title="Pembaca PDF"
        ></iframe>
    <?php else: ?>
        <p class="empty">
            File EPUB tidak dapat ditampilkan langsung di browser.
            <a href="<?= e(app_url('pages/member/reader.php?id=' . $ebookId . '&stream=1')) ?>">
                Unduh ebook
            </a>
        </p>
    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
