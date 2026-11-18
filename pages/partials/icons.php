<?php
/**
 * Ikon garis SVG 24×24 (stroke 1.6, currentColor).
 *
 * Dipakai partial dan halaman lewat icon('nama'). Semua ikon satu keluarga:
 * kotak 24, sudut membulat, tanpa isian.
 */
if (!function_exists('icon')) {
    function icon(string $name, string $class = ''): string
    {
        $paths = [
            'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
            'book' => '<path d="M4 4.5A1.5 1.5 0 0 1 5.5 3H19v18H5.5A1.5 1.5 0 0 1 4 19.5z"/><path d="M4 17h15"/><path d="M9 3v9l2.5-2 2.5 2V3"/>',
            'shelf' => '<path d="M3 5h18"/><path d="M3 19h18"/><path d="M6 5v14"/><path d="M18 5v14"/><path d="M9 9h2v6H9z"/><path d="M13 9h2v6h-2z"/>',
            'tag' => '<path d="M3 11.5V4h7.5L21 14.5 14.5 21z"/><circle cx="7.5" cy="8" r="1.4"/>',
            'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M18 20c0-2.6-1-4.4-2.6-5.2"/>',
            'swap' => '<path d="M4 8h13l-3-3"/><path d="M20 16H7l3 3"/>',
            'history' => '<path d="M3.5 12a8.5 8.5 0 1 0 2.7-6.2"/><path d="M3 4v5h5"/><path d="M12 8v4.5l3 1.8"/>',
            'user' => '<circle cx="12" cy="8" r="3.6"/><path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6"/>',
            'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
            'arrow-left' => '<path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/>',
            'chevron-right' => '<path d="M9 5l7 7-7 7"/>',
            'chevron-left' => '<path d="M15 5l-7 7 7 7"/>',
            'logout' => '<path d="M15 5H6.5A1.5 1.5 0 0 0 5 6.5v11A1.5 1.5 0 0 0 6.5 19H15"/><path d="M13 12h8"/><path d="M18 8l4 4-4 4"/>',
            'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
            'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3.2 2"/>',
            'check' => '<path d="M4.5 12.5l5 5 10-11"/>',
            'lock' => '<rect x="4.5" y="10" width="15" height="10.5" rx="2"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>',
            'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
            'download-off' => '<path d="M4 4l16 16"/><path d="M12 4v7"/><path d="M8.5 7.5L12 4l3.5 3.5"/><path d="M5 16v3h9"/>',
        ];

        if (!isset($paths[$name])) {
            return '';
        }

        $classAttribute = $class === ''
            ? ' class="icon"'
            : ' class="icon ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';

        return '<svg' . $classAttribute . ' viewBox="0 0 24 24" fill="none" '
            . 'stroke="currentColor" stroke-width="1.6" stroke-linecap="round" '
            . 'stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $paths[$name] . '</svg>';
    }
}
