<?php
namespace App\Models;

class Transaction
{
    private int $id;
    private int $memberId;
    private string $status;
    private string $borrowDate;
    private string $dueDate;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->memberId = (int) ($data['member_id'] ?? 0);
        $this->status = $data['status'] ?? 'pending';
        $this->borrowDate = $data['borrow_date'] ?? date('Y-m-d');
        $this->dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function getMemberId(): int { return $this->memberId; }
    public function getStatus(): string { return $this->status; }
    public function getBorrowDate(): string { return $this->borrowDate; }
    public function getDueDate(): string { return $this->dueDate; }

    public function setStatus(string $status): void
    {
        $allowed = ['pending', 'approved', 'rejected', 'cancelled', 'returned'];

        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Status transaksi tidak valid.');
        }

        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->memberId,
            'status' => $this->status,
            'borrow_date' => $this->borrowDate,
            'due_date' => $this->dueDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}