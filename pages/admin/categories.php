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

// Kategori beserta jumlah ebook yang memakainya.
$categories = $catalogService->getCategoriesWithEbookCount();

$pageTitle = 'Manajemen Kategori | Digital Library';
$pageHeading = 'Manajemen Kategori';
$pageSubtitle = 'Tambah, ubah, dan hapus kategori koleksi ebook.';
$activeNav = 'categories';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="panel">

    <div class="panel-head">
        <h2>Tambah Kategori Baru</h2>
    </div>

    <div id="message" class="notice" hidden></div>

    <form id="categoryForm">

        <div class="form-grid">

            <div class="field">
                <label for="name">Nama Kategori</label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    placeholder="Masukkan nama kategori"
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
                placeholder="Masukkan deskripsi kategori"
            ></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Tambah Kategori
        </button>

    </form>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Daftar Kategori (<?= count($categories) ?>)</h2>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Ebook</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="5" class="empty">Belum ada data kategori.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $index => $category): ?>
                    <?php
                    $categoryId = (int) ($category['id_category'] ?? 0);
                    $categoryName = $category['name'] ?? '';
                    $categoryDescription = $category['description'] ?? '';
                    ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($categoryName) ?></td>
                        <td><?= e($categoryDescription !== '' ? $categoryDescription : '-') ?></td>
                        <td class="number"><?= (int) ($category['ebook_count'] ?? 0) ?></td>
                        <td>
                            <div class="actions">
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    onclick="editCategory(
                                        <?= $categoryId ?>,
                                        <?= e(json_encode($categoryName)) ?>,
                                        <?= e(json_encode($categoryDescription)) ?>
                                    )"
                                >Edit</button>

                                <button
                                    type="button"
                                    class="btn btn-outline"
                                    onclick="deleteCategory(<?= $categoryId ?>)"
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

async function editCategory(id, oldName, oldDescription) {
    const name = prompt('Nama kategori:', oldName);
    if (name === null) return;

    const description = prompt('Deskripsi kategori:', oldDescription);
    if (description === null) return;

    try {
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id_category: id, name: name, description: description})
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengubah kategori.');
        }

        alert(result.message);
        location.reload();
    } catch (error) {
        alert(error.message);
    }
}

async function deleteCategory(id) {
    if (!confirm('Yakin ingin menghapus kategori ini?')) return;

    try {
        const response = await fetch(deleteUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id_category: id})
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menghapus kategori.');
        }

        alert(result.message);
        location.reload();
    } catch (error) {
        alert(error.message);
    }
}
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
