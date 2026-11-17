<?php

namespace App\Models;

class TransactionDetail
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected int $idDetail;
    protected int $idTransaction;
    protected int $idEbook;
    protected string $startDate;
    protected string $dueDate;
    protected string $status;
    protected ?string $returnedAt;

    public function __construct(array $data)
    {
        $this->idDetail = (int) ($data['id_detail'] ?? 0);
        $this->idTransaction = (int) ($data['id_transaction'] ?? 0);
        $this->idEbook = (int) ($data['id_ebook'] ?? 0);
        $this->startDate = $data['start_date'] ?? date('Y-m-d H:i:s');
        $this->dueDate = $data['due_date'] ?? date('Y-m-d H:i:s');
        $this->status = $data['status'] ?? self::STATUS_ACTIVE;
        $this->returnedAt = !empty($data['returned_at'])
            ? (string) $data['returned_at']
            : null;
    }

    // Mengambil ID detail transaksi
    public function getIdDetail(): int
    {
        return $this->idDetail;
    }

    // Mengambil ID transaksi induk
    public function getIdTransaction(): int
    {
        return $this->idTransaction;
    }

    // Mengambil ID ebook yang dipinjam
    public function getIdEbook(): int
    {
        return $this->idEbook;
    }

    // Mengambil tanggal mulai peminjaman
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    // Mengambil tanggal jatuh tempo
    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    // Mengambil status detail transaksi
    public function getStatus(): string
    {
        return $this->status;
    }

    // Mengambil waktu ebook dikembalikan
    public function getReturnedAt(): ?string
    {
        return $this->returnedAt;
    }

    // Mengubah tanggal mulai peminjaman
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }

    // Mengubah tanggal jatuh tempo
    public function setDueDate(string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    // Mengubah waktu pengembalian ebook
    public function setReturnedAt(?string $returnedAt): void
    {
        $this->returnedAt = $returnedAt;
    }

    // Menandai ebook sudah dikembalikan beserta waktunya
    public function markReturned(?string $returnedAt = null): void
    {
        $this->status = self::STATUS_RETURNED;
        $this->returnedAt = $returnedAt ?? date('Y-m-d H:i:s');
    }

    // Menandai peminjaman dibatalkan
    public function markCancelled(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->returnedAt = null;
    }

    // Mengubah status detail transaksi
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::getAvailableStatuses(), true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        $this->status = $status;
    }

    // Memeriksa apakah peminjaman sedang berjalan
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Memeriksa apakah ebook sudah dikembalikan
    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    // Memeriksa apakah peminjaman dibatalkan
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Memeriksa apakah peminjaman masih menahan lisensi ebook
    public function isHoldingLicense(): bool
    {
        return in_array(
            $this->status,
            [self::STATUS_ACTIVE, self::STATUS_OVERDUE],
            true
        );
    }

    // Memeriksa apakah peminjaman melewati jatuh tempo pada tanggal tertentu
    public function isOverdue(?string $referenceDate = null): bool
    {
        $referenceDate = $referenceDate ?? date('Y-m-d H:i:s');

        if (!$this->isHoldingLicense()) {
            return false;
        }

        return strtotime($this->dueDate) < strtotime($referenceDate);
    }

    // Daftar status detail transaksi yang valid
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_RETURNED,
            self::STATUS_OVERDUE,
            self::STATUS_CANCELLED
        ];
    }

    // Mengubah data objek menjadi array
    public function toArray(): array
    {
        return [
            'id_detail' => $this->idDetail,
            'id_transaction' => $this->idTransaction,
            'id_ebook' => $this->idEbook,
            'start_date' => $this->startDate,
            'due_date' => $this->dueDate,
            'status' => $this->status,
            'returned_at' => $this->returnedAt
        ];
    }
}
