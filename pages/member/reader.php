<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Support\Shelf;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);
$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$currentUser = $middleware->getCurrentUser();

// Tamu diarahkan masuk; pembaca hanya untuk akun member.
if ($currentUser === null) {
    header('Location: ' . app_url('pages/auth/login.php'));
    exit;
}

/**
 * Menampilkan kegagalan akses di dalam tata letak aplikasi,
 * bukan sebagai teks polos tanpa jalan keluar.
 */
$renderError = function (
    int $status,
    string $heading,
    string $message,
    array $actions
) use ($currentUser): void {
    http_response_code($status);

    $pageTitle = $heading . ' | Digital Library';
    $pageHeading = $heading;
    $pageSubtitle = '';
    $userRole = $currentUser->getRole();
    $userName = $currentUser->getName();
    $activeNav = $userRole === 'admin' ? 'dashboard' : 'borrowings';

    require __DIR__ . '/../partials/dashboard-header.php';
    ?>
    <section class="panel">

        <div class="panel-head">
            <h2><?= e($heading) ?></h2>
        </div>

        <p class="status-note"><?= e($message) ?></p>

        <div class="actions">
            <?php foreach ($actions as $action): ?>
                <a
                    class="btn <?= !empty($action['primary']) ? 'btn-primary' : 'btn-secondary' ?>"
                    href="<?= e($action['url']) ?>"
                ><?= e($action['label']) ?></a>
            <?php endforeach; ?>
        </div>

    </section>
    <?php
    require __DIR__ . '/../partials/dashboard-footer.php';
    exit;
};

if ($currentUser->getRole() !== 'member') {
    $renderError(
        403,
        'Halaman ini untuk member',
        'Akun admin mengelola koleksi dan transaksi, bukan meminjam ebook.',
        [
            [
                'label' => 'Buka dashboard admin',
                'url' => app_url('pages/admin/dashboard.php'),
                'primary' => true,
            ],
        ]
    );
}

$member = $currentUser;
$ebookId = (int) ($_GET['id'] ?? 0);
$ebook = $catalogService->findEbookById($ebookId);

if ($ebook === null) {
    $renderError(
        404,
        'Ebook tidak ditemukan',
        'Judul ini tidak ada di rak, atau sudah dihapus dari koleksi.',
        [
            [
                'label' => 'Kembali ke rak peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
                'primary' => true,
            ],
            [
                'label' => 'Buka katalog',
                'url' => app_url('pages/public/catalog.php'),
            ],
        ]
    );
}

// Member hanya boleh membaca ebook dari peminjaman yang masih berlaku.
$loan = $transactionService->findHeldLoan($member->getIdUser(), $ebookId);

if ($loan === null) {
    $renderError(
        403,
        'Peminjaman belum aktif',
        'Ebook ini belum ada di peminjaman yang disetujui untuk akunmu. '
            . 'Ajukan pinjam lebih dulu, lalu buka lagi setelah pengajuan disetujui.',
        [
            [
                'label' => 'Ajukan pinjaman',
                'url' => app_url('pages/member/borrowings.php?pilih=' . $ebookId),
                'primary' => true,
            ],
            [
                'label' => 'Lihat pengajuan saya',
                'url' => app_url('pages/member/borrowings.php'),
            ],
        ]
    );
}

$dueDate = (string) ($loan['detail']['due_date'] ?? '');

if ($loan['overdue']) {
    $renderError(
        403,
        'Masa pinjam berakhir',
        'Batas baca ebook ini sudah lewat pada ' . Shelf::date($dueDate)
            . '. Ajukan pinjaman baru untuk membukanya lagi.',
        [
            [
                'label' => 'Ajukan pinjaman baru',
                'url' => app_url('pages/member/borrowings.php?pilih=' . $ebookId),
                'primary' => true,
            ],
            [
                'label' => 'Buka rak peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
            ],
        ]
    );
}

// Berkas hanya dianggap tersedia bila lolos pemeriksaan CatalogService.
$filePath = $catalogService->resolveEbookFile($ebook);
$extension = strtolower(
    pathinfo((string) ($ebook['file_path'] ?? ''), PATHINFO_EXTENSION)
);

if ($filePath === null) {
    $renderError(
        404,
        'Berkas ebook tidak tersedia',
        'Berkas untuk judul ini hilang atau rusak di penyimpanan. '
            . 'Hubungi admin perpustakaan agar salinannya dipulihkan.',
        [
            [
                'label' => 'Kembali ke rak peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
                'primary' => true,
            ],
            [
                'label' => 'Buka katalog',
                'url' => app_url('pages/public/catalog.php'),
            ],
        ]
    );
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

$categoryNames = $catalogService->getCategoryNameMap();
$category = $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';

$layoutMode = 'reader';
$pageTitle = $ebook['title'] . ' | Digital Library';
$pageHeading = $ebook['title'];
$pageSubtitle = '';
$activeNav = 'borrowings';
$userName = $member->getName();
$userRole = 'member';
$backUrl = app_url('pages/member/borrowings.php');
$readerMeta = 'Nomor panggil ' . Shelf::code($category, $ebookId)
    . ' · ' . ($ebook['author'] ?? 'Tanpa penulis')
    . ($dueDate !== '' ? ' · batas baca ' . Shelf::date($dueDate) : '');

require __DIR__ . '/../partials/dashboard-header.php';

?>

<?php if ($extension === 'pdf'): ?>
    <div
        class="reader"
        id="pdfViewer"
        data-src="<?= e(app_url('pages/member/reader.php?id=' . $ebookId . '&stream=1')) ?>"
        data-worker="<?= e(asset_url('public/assets/pdfjs/pdf.worker.min.js')) ?>"
    >
        <div class="reader-bar">
            <button type="button" class="btn btn-secondary btn-sm" id="pdfPrev" disabled>
                <?= icon('chevron-left') ?>
                Sebelumnya
            </button>
            <span class="reader-page" id="pdfPage">Memuat ebook…</span>
            <button type="button" class="btn btn-secondary btn-sm" id="pdfNext" disabled>
                Berikutnya
                <?= icon('chevron-right') ?>
            </button>
        </div>

        <canvas id="pdfCanvas"></canvas>
    </div>

    <script src="<?= e(asset_url('public/assets/pdfjs/pdf.min.js')) ?>"></script>
    <script src="<?= e(asset_url('public/assets/js/reader.js')) ?>"></script>
<?php else: ?>
    <p class="empty">
        Ebook ini berformat EPUB dan tidak bisa ditampilkan langsung di browser.
        Hubungi admin perpustakaan untuk ketersediaan versi PDF.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
