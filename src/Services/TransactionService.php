<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Storage\JsonStorage;

class TransactionService
{
    private JsonStorage $storage;
    private string $transactionFile = 'transactions.json';
    private string $detailFile = 'transaction_details.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getAllTransactions(): array
    {
        $transactions = $this->storage->read($this->transactionFile);
        $details = $this->storage->read($this->detailFile);
        $users = $this->storage->read('users.json');
        $ebooks = $this->storage->read('ebooks.json');

        foreach ($transactions as &$transaction) {
            $transaction['member'] = null;
            $transaction['details'] = [];

            foreach ($users as $user) {
                if ((int) ($user['id_user'] ?? 0) === (int) ($transaction['member_id'] ?? 0)) {
                    $transaction['member'] = [
                        'id_user' => $user['id_user'],
                        'name' => $user['name'],
                        'email' => $user['email']
                    ];
                    break;
                }
            }

            foreach ($details as $detail) {
                if ((int) $detail['transaction_id'] === (int) $transaction['id']) {
                    foreach ($ebooks as $ebook) {
                        if ((string) $ebook['id'] === (string) $detail['ebook_id']) {
                            $detail['ebook'] = $ebook;
                            break;
                        }
                    }

                    $transaction['details'][] = $detail;
                }
            }
        }

        unset($transaction);

        return $transactions;
    }

    public function getMemberTransactions(int $memberId): array
    {
        return array_values(array_filter(
            $this->getAllTransactions(),
            fn($transaction) => (int) $transaction['member_id'] === $memberId
        ));
    }

    public function findById(int $id): ?array
    {
        foreach ($this->getAllTransactions() as $transaction) {
            if ((int) $transaction['id'] === $id) {
                return $transaction;
            }
        }

        return null;
    }

    public function borrow(int $memberId, array $ebookIds): Transaction
    {
        $ebookIds = array_values(array_unique(array_map('strval', $ebookIds)));

        if ($memberId <= 0 || empty($ebookIds)) {
            throw new \InvalidArgumentException('Pilih minimal satu ebook untuk dipinjam.');
        }

        $users = $this->storage->read('users.json');
        $member = null;

        foreach ($users as $user) {
            if ((int) ($user['id_user'] ?? 0) === $memberId && ($user['role'] ?? '') === 'member') {
                $member = $user;
                break;
            }
        }

        if (!$member || ($member['status'] ?? '') !== 'active') {
            throw new \InvalidArgumentException('Akun member tidak ditemukan atau tidak aktif.');
        }

        $ebooks = $this->storage->read('ebooks.json');
        $selected = [];

        foreach ($ebookIds as $ebookId) {
            $found = null;

            foreach ($ebooks as $ebook) {
                if ((string) ($ebook['id'] ?? '') === $ebookId) {
                    $found = $ebook;
                    break;
                }
            }

            if (!$found) {
                throw new \InvalidArgumentException('Ebook tidak ditemukan: ' . $ebookId);
            }

            $selected[] = $found;
        }

        $transactions = $this->storage->read($this->transactionFile);
        $nextId = 1;

        foreach ($transactions as $item) {
            $nextId = max($nextId, (int) ($item['id'] ?? 0) + 1);
        }

        $now = date('Y-m-d H:i:s');

        $transaction = new Transaction([
            'id' => $nextId,
            'member_id' => $memberId,
            'status' => 'pending',
            'borrow_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $details = $this->storage->read($this->detailFile);
        $detailId = 1;

        foreach ($details as $detail) {
            $detailId = max($detailId, (int) ($detail['id'] ?? 0) + 1);
        }

        foreach ($selected as $ebook) {
            $detail = new TransactionDetail([
                'id' => $detailId++,
                'transaction_id' => $nextId,
                'ebook_id' => (string) $ebook['id'],
                'ebook_title' => (string) $ebook['title']
            ]);

            $details[] = $detail->toArray();
        }

        $transactions[] = $transaction->toArray();

        $this->storage->write($this->detailFile, $details);
        $this->storage->write($this->transactionFile, $transactions);

        return $transaction;
    }

    public function updateStatus(int $id, string $status): Transaction
    {
        $allowed = ['approved', 'rejected', 'cancelled', 'returned'];

        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Status transaksi tidak valid.');
        }

        $transactions = $this->storage->read($this->transactionFile);
        $index = null;

        foreach ($transactions as $i => $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
        }

        $transaction = new Transaction($transactions[$index]);

        if ($transaction->getStatus() !== 'pending') {
            throw new \InvalidArgumentException('Hanya transaksi pending yang dapat diproses.');
        }

        $transaction->setStatus($status);
        $transactions[$index] = $transaction->toArray();

        $this->storage->write($this->transactionFile, $transactions);

        return $transaction;
    }

    public function cancel(int $id, int $memberId): Transaction
    {
        $transaction = $this->findById($id);

        if (!$transaction || (int) $transaction['member_id'] !== $memberId) {
            throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
        }

        if ($transaction['status'] !== 'pending') {
            throw new \InvalidArgumentException('Hanya pengajuan pending yang dapat dibatalkan.');
        }

        return $this->updateStatus($id, 'cancelled');
    }
}