<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Support\Shelf;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$catalogService = new CatalogService($storage);

// Kategori beserta jumlah ebook yang memakainya.
$categories = $catalogService->getCategoriesWithEbookCount();

$pageTitle = 'Manajemen Kategori | Digital Library';
$pageHeading = 'Manajemen kategori';
$pageSubtitle = 'Kategori menentukan pita warna dan huruf depan nomor panggil.';
$activeNav = 'categories';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="panel">

    <div class="panel-head">
        <h2>Tambah kategori</h2>
    </div>

    <form id="categoryForm">

        <div class="form-grid">

            <div class="field">
                <label for="name">Nama kategori</label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    placeholder="Mis. Novel, Sejarah, Teknik"
                    required
                    maxlength="100"
                >
            </div>

        </div>

        <div class="field">
            <label for="description">Deskripsi</label>

            <textarea
                id="description"
                name="description"
                placeholder="Keterangan singkat isi kategori"
            ></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">
                <?= icon('plus') ?>
                Tambah kategori
            </button>
        </div>

    </form>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Daftar kategori</h2>
        <span class="hint"><?= count($categories) ?> kategori</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th>Pita</th>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th class="number">Ebook</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6" class="empty">Belum ada data kategori.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $index => $category): ?>
                    <?php
                    $categoryId = (int) ($category['id_category'] ?? 0);
                    $categoryName = $category['name'] ?? '';
                    $categoryDescription = $category['description'] ?? '';
                    ?>
                    <tr>
                        <td class="number"><?= $index + 1 ?></td>
                        <td>
                            <span class="book-cat band-<?= Shelf::band($categoryName) ?>">
                                <?= e(Shelf::mark($categoryName)) ?>
                            </span>
                        </td>
                        <td><?= e($categoryName) ?></td>
                        <td><?= e($categoryDescription !== '' ? $categoryDescription : '—') ?></td>
                        <td class="number"><?= (int) ($category['ebook_count'] ?? 0) ?></td>
                        <td>
                            <div class="actions">
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm edit-category"
                                    data-id="<?= $categoryId ?>"
                                    data-name="<?= e($categoryName) ?>"
                                    data-description="<?= e($categoryDescription) ?>"
                                >Edit</button>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm delete-category"
                                    data-id="<?= $categoryId ?>"
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
const createUrl = <?= json_encode(app_url('handlers/category/create.php')) ?>;
const updateUrl = <?= json_encode(app_url('handlers/category/update.php')) ?>;
const deleteUrl = <?= json_encode(app_url('handlers/category/delete.php')) ?>;

const form = document.getElementById('categoryForm');
const message = document.getElementById('message');

function showMessage(text, success) {
    message.textContent = text;
    message.className = 'notice ' + (success ? 'notice-success' : 'notice-error');
    message.hidden = false;

    if (success && window.flashMessage) {
        window.flashMessage(text, true);
    }
}

form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const data = {
        name: document.getElementById('name').value.trim(),
        description: document.getElementById('description').value.trim()
    };

    try {
        const response = await fetch(createUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menambahkan kategori.');
        }

        showMessage(result.message, true);
        form.reset();
        setTimeout(() => location.reload(), 700);
    } catch (error) {
        showMessage(error.message, false);
    }
});

function closeEditor() {
    document.querySelectorAll('.editor-row').forEach(row => row.remove());
}

document.querySelectorAll('.edit-category').forEach(button => {
    button.addEventListener('click', function () {
        closeEditor();

        const row = this.closest('tr');
        const editor = document.createElement('tr');

        editor.className = 'editor-row';
        editor.innerHTML = `
            <td colspan="6">
                <form class="row-editor">
                    <div class="field">
                        <label for="editCategoryName">Nama kategori</label>
                        <input
                            id="editCategoryName"
                            name="name"
                            type="text"
                            maxlength="100"
                            required
                        >
                    </div>
                    <div class="field">
                        <label for="editCategoryDescription">Deskripsi</label>
                        <input
                            id="editCategoryDescription"
                            name="description"
                            type="text"
                        >
                    </div>
                    <div class="row-actions">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-cancel>Batal</button>
                    </div>
                </form>
            </td>`;

        const form = editor.querySelector('form');
        const nameInput = form.elements.name;
        const descriptionInput = form.elements.description;

        nameInput.value = this.dataset.name;
        descriptionInput.value = this.dataset.description;

        row.after(editor);
        nameInput.focus();
        nameInput.select();

        form.querySelector('[data-cancel]').addEventListener('click', closeEditor);

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const name = nameInput.value.trim();

            if (name === '') {
                showMessage('Nama kategori tidak boleh kosong.', false);
                nameInput.focus();
                return;
            }

            const data = {
                id_category: Number(button.dataset.id),
                name: name,
                description: descriptionInput.value.trim()
            };

            try {
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal mengubah kategori.');
                }

                closeEditor();
                showMessage(result.message, true);
                setTimeout(() => location.reload(), 600);
            } catch (error) {
                showMessage(error.message, false);
            }
        });
    });
});

document.querySelectorAll('.delete-category').forEach(button => {
    button.addEventListener('click', async function () {
        if (!window.armAction(this, 'Klik lagi untuk menghapus')) {
            return;
        }

        try {
            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id_category: Number(this.dataset.id)})
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menghapus kategori.');
            }

            showMessage(result.message, true);
            setTimeout(() => location.reload(), 600);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
