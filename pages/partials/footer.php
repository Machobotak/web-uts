<?php
/**
 * Partial tata letak: footer + penutup dokumen.
 *
 * Dipakai halaman: require __DIR__ . '/../partials/footer.php';
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}
?>

<footer class="footer">
    <p>
        &copy; <?= date('Y') ?> Digital Library. All rights reserved.
    </p>
</footer>

</body>
</html>
