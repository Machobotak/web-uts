<?php

namespace App\Models;

class Transaction
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected int $idTransaction;
    protected int $idUser;
    protected ?int $approvedBy;
    protected ?string $processedAt;
    protected string $status;
    protected string $notes;
    protected string $createAt;

    public function __construct(array $data)
    {
        $this->idTransaction = (int) ($data['id_transaction'] ?? 0);
        $this->idUser = (int) ($data['id_user'] ?? 0);
        $this->approvedBy = isset($data['approved_by']) && $data['approved_by'] !== ''
            ? (int) $data['approved_by']
            : null;
        $this->processedAt = !empty($data['processed_at'])
            ? (string) $data['processed_at']
            : null;
        $this->status = $data['status'] ?? self::STATUS_PENDING;
        $this->notes = $data['notes'] ?? '';
        $this->createAt = $data['create_at'] ?? date('Y-m-d H:i:s');
    }

    // Mengambil ID transaksi
    public function getIdTransaction(): int
    {
        return $this->idTransaction;
    }

    // Mengambil ID pengguna peminjam
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    // Mengambil ID admin yang menyetujui
    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    // Mengambil waktu transaksi diproses
    public function getProcessedAt(): ?string
    {
        return $this->processedAt;
    }

    // Mengambil status transaksi
    public function getStatus(): string
    {
        return $this->status;
    }

    // Mengambil catatan transaksi
    public function getNotes(): string
    {
        return $this->notes;
    }

    // Mengambil waktu transaksi dibuat
    public function getCreateAt(): string
    {
        return $this->createAt;
    }

    // Mengubah status transaksi
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::getAvailableStatuses(), true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        $this->status = $status;
    }

    // Mengubah catatan transaksi
    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    // Mencatat persetujuan transaksi oleh admin
    public function approve(int $approvedBy, ?string $processedAt = null): void
    {
        if ($approvedBy <= 0) {
            throw new \InvalidArgumentException('Admin tidak valid.');
        }

        $this->approvedBy = $approvedBy;
        $this->processedAt = $processedAt ?? date('Y-m-d H:i:s');
        $this->status = self::STATUS_APPROVED;
    }

    // Mencatat penolakan transaksi oleh admin
    public function reject(int $approvedBy, string $notes = '', ?string $processedAt = null): void
    {
        if ($approvedBy <= 0) {
            throw new \InvalidArgumentException('Admin tidak valid.');
        }

        $this->approvedBy = $approvedBy;
        $this->processedAt = $processedAt ?? date('Y-m-d H:i:s');
        $this->status = self::STATUS_REJECTED;
        $this->notes = $notes;
    }

    // Menandai seluruh ebook pada transaksi sudah dikembalikan
    public function complete(?string $processedAt = null): void
    {
        $this->processedAt = $this->processedAt
            ?? $processedAt
            ?? date('Y-m-d H:i:s');
        $this->status = self::STATUS_COMPLETED;
    }

    // Memeriksa apakah transaksi masih menunggu persetujuan
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Memeriksa apakah transaksi sudah disetujui
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // Memeriksa apakah seluruh ebook pada transaksi sudah dikembalikan
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Memeriksa apakah transaksi sudah diproses admin
    public function isProcessed(): bool
    {
        return $this->processedAt !== null;
    }

    // Daftar status transaksi yang valid
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED
        ];
    }

    // Mengubah data objek menjadi array
    public function toArray(): array
    {
        return [
            'id_transaction' => $this->idTransaction,
            'id_user' => $this->idUser,
            'approved_by' => $this->approvedBy,
            'processed_at' => $this->processedAt,
            'status' => $this->status,
            'notes' => $this->notes,
            'create_at' => $this->createAt
        ];
    }
}
