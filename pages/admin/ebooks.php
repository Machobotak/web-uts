<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$catalogService = new CatalogService($storage);

$ebooks = $catalogService->getAllEbooks();
$categories = $catalogService->getAllCategories();
$categoryNames = $catalogService->getCategoryNameMap();

// Mode edit bila tersedia ?edit=<id_ebook>.
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editEbook = $editId ? $catalogService->findEbookById($editId) : null;

$pageTitle = 'Manajemen Ebook | Digital Library';
$pageHeading = 'Manajemen ebook';
$pageSubtitle = 'Tambah koleksi, atur kategori, dan pantau sisa lisensi.';
$activeNav = 'ebooks';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="panel">

    <div class="panel-head">
        <h2><?= $editEbook ? 'Edit ebook' : 'Tambah ebook baru' ?></h2>

        <?php if ($editEbook): ?>
            <span class="badge badge-completed">
                ID <?= e($editEbook['id_ebook'] ?? '') ?>
            </span>
        <?php endif; ?>
    </div>

    <form id="ebookForm" enctype="multipart/form-data">

        <input
            type="hidden"
            name="id_ebook"
            value="<?= e($editEbook['id_ebook'] ?? '') ?>"
        >

        <div class="form-grid">

            <div class="field">
                <label for="ebook-title">Judul Ebook</label>

                <input
                    type="text"
                    id="ebook-title"
                    name="title"
                    required
                    value="<?= e($editEbook['title'] ?? '') ?>"
                    placeholder="Masukkan judul ebook"
                >
            </div>

            <div class="field">
                <label for="ebook-author">Penulis</label>

                <input
                    type="text"
                    id="ebook-author"
                    name="author"
                    required
                    value="<?= e($editEbook['author'] ?? '') ?>"
                    placeholder="Masukkan nama penulis"
                >
            </div>

            <div class="field">
                <label for="ebook-category">Kategori</label>

                <select id="ebook-category" name="id_category" required>
                    <option value="">Pilih kategori</option>

                    <?php foreach ($categories as $category): ?>
                        <?php $categoryId = (string) ($category['id_category'] ?? ''); ?>
                        <option
                            value="<?= e($categoryId) ?>"
                            <?= (string) ($editEbook['id_category'] ?? '') === $categoryId
                                ? 'selected'
                                : '' ?>
                        ><?= e($category['name'] ?? 'Kategori') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="ebook-status">Status</label>

                <?php $currentStatus = $editEbook['status'] ?? 'available'; ?>
                <select id="ebook-status" name="status">
                    <option value="available" <?= $currentStatus === 'available' ? 'selected' : '' ?>>
                        Tersedia
                    </option>
                    <option value="unavailable" <?= $currentStatus === 'unavailable' ? 'selected' : '' ?>>
                        Tidak Tersedia
                    </option>
                </select>
            </div>

            <div class="field">
                <label for="ebook-publisher">Penerbit</label>

                <input
                    type="text"
                    id="ebook-publisher"
                    name="publisher"
                    value="<?= e($editEbook['publisher'] ?? '') ?>"
                    placeholder="Masukkan nama penerbit"
                >
            </div>

            <div class="field">
                <label for="ebook-year">Tahun Terbit</label>

                <input
                    type="number"
                    id="ebook-year"
                    name="publication_year"
                    min="1000"
                    max="<?= (int) date('Y') + 1 ?>"
                    value="<?= (int) ($editEbook['publication_year'] ?? 0) > 0
                        ? (int) $editEbook['publication_year']
                        : '' ?>"
                    placeholder="Contoh: 2024"
                >
            </div>

            <div class="field">
                <label for="ebook-isbn">ISBN</label>

                <input
                    type="text"
                    id="ebook-isbn"
                    name="isbn"
                    value="<?= e($editEbook['isbn'] ?? '') ?>"
                    placeholder="Masukkan ISBN"
                >
            </div>

            <div class="field">
                <label for="ebook-license">Jumlah Lisensi</label>

                <input
                    type="number"
                    id="ebook-license"
                    name="license_total"
                    min="0"
                    value="<?= e($editEbook['license_total'] ?? 1) ?>"
                    placeholder="Jumlah salinan"
                >
            </div>

        </div>

        <div class="field">
            <label for="ebook-description">Deskripsi</label>

            <textarea
                id="ebook-description"
                name="description"
                placeholder="Masukkan deskripsi ebook"
            ><?= e($editEbook['description'] ?? '') ?></textarea>
        </div>

        <div class="form-grid">

            <div class="field">
                <label for="ebook-cover">Cover (jpg, jpeg, png, webp &mdash; maks. 2 MB)</label>

                <input
                    type="file"
                    id="ebook-cover"
                    name="cover"
                    accept=".jpg,.jpeg,.png,.webp"
                >
            </div>

            <div class="field">
                <label for="ebook-file">File Ebook (pdf, epub &mdash; maks. 20 MB)</label>

                <input
                    type="file"
                    id="ebook-file"
                    name="file"
                    accept=".pdf,.epub"
                >
            </div>

        </div>

        <?php if ($editEbook && ($editEbook['file_path'] ?? '') !== ''): ?>
            <p class="hint">
                File ebook sudah tersedia. Kosongkan input file bila tidak ingin menggantinya.
            </p>
        <?php endif; ?>

        <div class="actions">
            <button type="submit" class="btn btn-primary">
                <?= $editEbook ? 'Simpan Perubahan' : 'Tambah Ebook' ?>
            </button>

            <?php if ($editEbook): ?>
                <a
                    href="<?= e(app_url('pages/admin/ebooks.php')) ?>"
                    class="btn btn-outline"
                >Batal</a>
            <?php endif; ?>
        </div>

    </form>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Daftar ebook</h2>
        <span class="hint"><?= count($ebooks) ?> judul di rak</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th class="number">Lisensi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($ebooks)): ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada data ebook.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ebooks as $index => $ebook): ?>
                    <?php
                    $status = $ebook['status'] ?? 'available';
                    $licenseLimit = (int) ($ebook['license_limit'] ?? 0);
                    $licenseTotal = (int) ($ebook['license_total'] ?? 0);
                    ?>
                    <tr>
                        <td class="number"><?= $index + 1 ?></td>
                        <td>
                            <strong><?= e($ebook['title'] ?? '-') ?></strong>
                            <span class="id">#<?= (int) ($ebook['id_ebook'] ?? 0) ?></span>
                        </td>
                        <td><?= e($ebook['author'] ?? '-') ?></td>
                        <td><?= e($categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $status === 'available'
                                ? 'badge-approved'
                                : 'badge-rejected' ?>">
                                <?= $status === 'available' ? 'Tersedia' : 'Habis' ?>
                            </span>
                        </td>
                        <td class="number">
                            <span class="license">
                                <?= $licenseLimit ?>/<?= $licenseTotal ?>
                                <small>salinan</small>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a
                                    class="btn btn-secondary btn-sm"
                                    href="<?= e(app_url('pages/admin/ebooks.php?edit=' . urlencode((string) ($ebook['id_ebook'] ?? '')))) ?>"
                                >Edit</a>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm delete-ebook"
                                    data-id="<?= (int) ($ebook['id_ebook'] ?? 0) ?>"
                                >Hapus</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<script>
const messageBox = document.getElementById('message');
const ebooksUrl = <?= json_encode(app_url('pages/admin/ebooks.php')) ?>;
const createUrl = <?= json_encode(app_url('handlers/ebook/create.php')) ?>;
const updateUrl = <?= json_encode(app_url('handlers/ebook/update.php')) ?>;
const deleteUrl = <?= json_encode(app_url('handlers/ebook/delete.php')) ?>;

function showMessage(text, success) {
    messageBox.textContent = text;
    messageBox.className = 'notice ' + (success ? 'notice-success' : 'notice-error');
    messageBox.hidden = false;

    if (success && window.flashMessage) {
        window.flashMessage(text, true);
    }
}

async function sendRequest(url, formData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Permintaan gagal.');
    }

    return result;
}

document.getElementById('ebookForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    const isEdit = formData.get('id_ebook') !== '';

    try {
        const result = await sendRequest(isEdit ? updateUrl : createUrl, formData);
        showMessage(result.message, true);

        setTimeout(() => {
            window.location.href = ebooksUrl;
        }, 700);
    } catch (error) {
        showMessage(error.message, false);
    }
});

document.querySelectorAll('.delete-ebook').forEach(button => {
    button.addEventListener('click', async function () {
        if (!window.armAction(this, 'Klik lagi untuk menghapus')) return;

        const formData = new FormData();
        formData.append('id_ebook', this.dataset.id);

        try {
            const result = await sendRequest(deleteUrl, formData);

            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
