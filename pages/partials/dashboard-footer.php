<?php
/**
 * Partial tata letak aplikasi: penutup layout dan dokumen.
 *
 * Dipakai halaman: require __DIR__ . '/../partials/dashboard-footer.php';
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}

$isReader = ($layoutMode ?? 'app') === 'reader';
?>

<?php if (!$isReader): ?>
        </main>

    </div>
<?php else: ?>
    </main>
<?php endif; ?>

<script src="<?= e(asset_url('public/assets/js/ui.js')) ?>" defer></script>

</body>
</html>
