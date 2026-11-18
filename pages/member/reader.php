<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\TransactionDetail;
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

// Member hanya boleh membaca ebook dari peminjaman yang masih berlaku.
$access = null;
$dueDate = '';

foreach ($transactionService->getTransactionsByUser($member->getIdUser()) as $transaction) {
    if (($transaction['status'] ?? '') !== 'approved') {
        continue;
    }

    $details = $transactionService->getTransactionDetails(
        (int) ($transaction['id_transaction'] ?? 0)
    );

    foreach ($details as $detail) {
        if ((int) ($detail['id_ebook'] ?? 0) !== $ebookId) {
            continue;
        }

        $loan = new TransactionDetail($detail);

        if (!$loan->isHoldingLicense()) {
            continue;
        }

        if ($loan->isOverdue()) {
            $access = 'expired';
            continue;
        }

        $access = 'ok';
        $dueDate = (string) ($detail['due_date'] ?? '');
        break 2;
    }
}

if ($access === null) {
    http_response_code(403);
    exit('Akses membaca ditolak. Ebook belum disetujui untuk akun Anda.');
}

if ($access === 'expired') {
    http_response_code(403);
    exit('Masa peminjaman ebook ini sudah berakhir. Ajukan peminjaman baru untuk membacanya lagi.');
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
// Berkas hanya boleh diambil oleh pembaca di halaman ini (fetch/XHR),
// bukan dibuka sebagai dokumen di tab browser.
if (($_GET['stream'] ?? '') === '1') {
    $fetchDestination = $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '';

    if (in_array($fetchDestination, ['document', 'iframe', 'frame'], true)) {
        http_response_code(403);
        exit('Ebook hanya dapat dibaca melalui halaman pembaca.');
    }

    $contentTypes = [
        'pdf' => 'application/pdf',
        'epub' => 'application/epub+zip',
    ];

    header('Content-Type: ' . $contentTypes[$extension]);
    header('Content-Disposition: inline');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;
}

$pageTitle = $ebook['title'] . ' | Digital Library';
$pageHeading = $ebook['title'];
$pageSubtitle = 'Penulis: ' . ($ebook['author'] ?? '-')
    . ' — batas baca: '
    . ($dueDate !== '' ? date('d M Y H:i', strtotime($dueDate)) : '-') . '.';
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
        <div
            class="reader"
            id="pdfViewer"
            data-src="<?= e(app_url('pages/member/reader.php?id=' . $ebookId . '&stream=1')) ?>"
        >
            <div class="reader-bar">
                <button type="button" class="btn btn-outline" id="pdfPrev" disabled>Sebelumnya</button>
                <span class="reader-page" id="pdfPage">Memuat ebook...</span>
                <button type="button" class="btn btn-outline" id="pdfNext" disabled>Berikutnya</button>
            </div>

            <canvas id="pdfCanvas"></canvas>
        </div>

        <script type="module" src="<?= e(app_url('public/assets/js/reader.js')) ?>"></script>
    <?php else: ?>
        <p class="empty">
            Ebook ini berformat EPUB dan tidak bisa ditampilkan langsung di browser.
            Hubungi admin perpustakaan untuk ketersediaan versi PDF.
        </p>
    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
