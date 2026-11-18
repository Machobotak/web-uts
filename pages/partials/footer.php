<?php
/**
 * Partial tata letak: footer + penutup dokumen.
 *
 * Dipakai halaman: require __DIR__ . '/../partials/footer.php';
 * Halaman tidak perlu membuka <main> sendiri; header.php sudah melakukannya.
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}
?>

</main>

<footer class="footer">

    <div class="footer-inner">

        <span class="logo">
            <?= icon('shelf', 'logo-mark') ?>
            Digital Library
        </span>

        <p>&copy; <?= date('Y') ?> Digital Library. Seluruh hak cipta dilindungi.</p>

    </div>

</footer>

<script src="<?= htmlspecialchars(asset_url('public/assets/js/ui.js')) ?>" defer></script>

</body>
</html>
