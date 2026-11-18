<?php

namespace App\Support;

/**
 * Label rak: nomor panggil, pita kategori, tanggal, dan sisa masa pinjam.
 * Semua nilai dihitung dari data yang sudah ada, tidak menyimpan apa pun.
 */
final class Shelf
{
    private const MONTHS = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    /** Huruf depan nomor panggil dari nama kategori, mis. "NO". */
    public static function mark(string $category): string
    {
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $category) ?? '');

        return $letters === '' ? 'UM' : substr($letters, 0, 2);
    }

    /** Nomor panggil dari dua huruf kategori + id ebook, mis. "NO·003". */
    public static function code(string $category, int $ebookId): string
    {
        return sprintf('%s·%03d', self::mark($category), $ebookId);
    }

    /** Pita warna kategori 1-4; kategori kosong memakai pita netral. */
    public static function band(string $category): int
    {
        if (trim($category) === '') {
            return 4;
        }

        return (crc32(strtolower(trim($category))) % 3) + 1;
    }

    /** Tanggal ringkas berbahasa Indonesia: "25 Nov 2026, 02:05". */
    public static function date(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '—';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return $value;
        }

        return sprintf(
            '%02d %s %d, %s',
            (int) date('j', $timestamp),
            self::MONTHS[(int) date('n', $timestamp)],
            (int) date('Y', $timestamp),
            date('H:i', $timestamp)
        );
    }

    /** Sisa hari menuju jatuh tempo; negatif berarti sudah lewat. */
    public static function daysLeft(?string $dueDate): ?int
    {
        if ($dueDate === null || trim($dueDate) === '') {
            return null;
        }

        $timestamp = strtotime($dueDate);

        return $timestamp === false
            ? null
            : (int) ceil(($timestamp - time()) / 86400);
    }

    /** Lebar bar sisa pinjam (0-100) terhadap masa pinjam 7 hari. */
    public static function remaining(?int $daysLeft, int $window = 7): int
    {
        if ($daysLeft === null) {
            return 0;
        }

        return max(0, min(100, (int) round($daysLeft / $window * 100)));
    }

    /** Keterangan sisa waktu yang dibaca manusia. */
    public static function remainingLabel(?int $daysLeft): string
    {
        if ($daysLeft === null) {
            return 'Belum ada tenggat';
        }

        if ($daysLeft < 0) {
            return 'Lewat ' . abs($daysLeft) . ' hari';
        }

        if ($daysLeft === 0) {
            return 'Jatuh tempo hari ini';
        }

        return 'Sisa ' . $daysLeft . ' hari';
    }
}
