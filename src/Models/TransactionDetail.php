<?php
namespace App\Models;

class TransactionDetail
{
    private int $id;
    private int $transactionId;
    private string $ebookId;
    private string $ebookTitle;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->transactionId = (int) ($data['transaction_id'] ?? 0);
        $this->ebookId = (string) ($data['ebook_id'] ?? '');
        $this->ebookTitle = (string) ($data['ebook_title'] ?? '');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransactionId(): int
    {
        return $this->transactionId;
    }

    public function getEbookId(): string
    {
        return $this->ebookId;
    }

    public function getEbookTitle(): string
    {
        return $this->ebookTitle;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transactionId,
            'ebook_id' => $this->ebookId,
            'ebook_title' => $this->ebookTitle
        ];
    }
}