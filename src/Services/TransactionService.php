<?php

namespace App\Services;

use App\Models\Ebook;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Storage\JsonStorage;

class TransactionService
{
    /** Lama peminjaman bawaan dalam hari. */
    public const DEFAULT_LOAN_DAYS = 7;

    private JsonStorage $storage;
    private string $transactionFile = 'transactions.json';
    private string $detailFile = 'transaction_details.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil seluruh transaksi, terbaru lebih dahulu.
     */
    public function getAllTransactions(): array
    {
        $transactions = $this->storage->read($this->transactionFile);

        usort($transactions, function (array $a, array $b) {
            return (int) ($b['id_transaction'] ?? 0)
                <=> (int) ($a['id_transaction'] ?? 0);
        });

        return $transactions;
    }

    /**
     * Mencari transaksi berdasarkan ID.
     */
    public function findTransactionById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        foreach ($this->getAllTransactions() as $transaction) {
            if ((int) ($transaction['id_transaction'] ?? 0) === $id) {
                return $transaction;
            }
        }

        return null;
    }

    /**
     * Mengambil seluruh transaksi milik satu pengguna.
     */
    public function getTransactionsByUser(int $idUser): array
    {
        if ($idUser < 1) {
            return [];
        }

        return array_values(array_filter(
            $this->getAllTransactions(),
            fn($transaction) => (int) ($transaction['id_user'] ?? 0) === $idUser
        ));
    }

    /**
     * Mencari transaksi berdasarkan filter status dan pengguna.
     */
    public function searchTransactions(array $filters = []): array
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $idUser = (int) ($filters['id_user'] ?? 0);

        $transactions = $this->getAllTransactions();

        if ($status !== '') {
            $transactions = array_values(array_filter(
                $transactions,
                fn($transaction) => ($transaction['status'] ?? '') === $status
            ));
        }

        if ($idUser > 0) {
            $transactions = array_values(array_filter(
                $transactions,
                fn($transaction) => (int) ($transaction['id_user'] ?? 0) === $idUser
            ));
        }

        return $transactions;
    }

    /**
     * Membuat ID transaksi berikutnya.
     */
    private function generateTransactionId(): int
    {
        $transactions = $this->storage->read($this->transactionFile);

        if (empty($transactions)) {
            return 1;
        }

        return max(array_map(
            fn($transaction) => (int) ($transaction['id_transaction'] ?? 0),
            $transactions
        )) + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Detail
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil seluruh detail transaksi.
     */
    public function getAllDetails(): array
    {
        $details = $this->storage->read($this->detailFile);

        usort($details, function (array $a, array $b) {
            return (int) ($a['id_detail'] ?? 0)
                <=> (int) ($b['id_detail'] ?? 0);
        });

        return $details;
    }

    /**
     * Mengambil detail milik satu transaksi.
     */
    public function getTransactionDetails(int $idTransaction): array
    {
        if ($idTransaction < 1) {
            return [];
        }

        return array_values(array_filter(
            $this->getAllDetails(),
            fn($detail) => (int) ($detail['id_transaction'] ?? 0) === $idTransaction
        ));
    }

    /**
     * Mengambil detail transaksi beserta informasi ebook.
     */
    public function getTransactionDetailsWithEbook(int $idTransaction): array
    {
        $details = $this->getTransactionDetails($idTransaction);

        if (empty($details)) {
            return [];
        }

        $catalogService = new CatalogService($this->storage);

        return array_map(function (array $detail) use ($catalogService) {
            $model = new TransactionDetail($detail);
            $ebook = $catalogService->findEbookById($model->getIdEbook());

            $detail['ebook_title'] = $ebook['title'] ?? '';
            $detail['ebook_author'] = $ebook['author'] ?? '';
            $detail['is_overdue'] = $model->isOverdue();

            return $detail;
        }, $details);
    }

    /**
     * Mencari peminjaman aktif milik member untuk satu ebook.
     *
     * Mengembalikan ['detail' => array, 'overdue' => bool] atau null bila
     * tidak ada detail berstatus aktif/overdue pada transaksi yang disetujui.
     */
    public function findHeldLoan(int $idUser, int $idEbook): ?array
    {
        if ($idUser < 1 || $idEbook < 1) {
            return null;
        }

        foreach ($this->getTransactionsByUser($idUser) as $transaction) {
            if (($transaction['status'] ?? '') !== Transaction::STATUS_APPROVED) {
                continue;
            }

            $details = $this->getTransactionDetails(
                (int) ($transaction['id_transaction'] ?? 0)
            );

            foreach ($details as $detail) {
                if ((int) ($detail['id_ebook'] ?? 0) !== $idEbook) {
                    continue;
                }

                $loan = new TransactionDetail($detail);

                if (!$loan->isHoldingLicense()) {
                    continue;
                }

                return [
                    'detail' => $detail,
                    'overdue' => $loan->isOverdue(),
                ];
            }
        }

        return null;
    }

    /**
     * Mencari detail transaksi berdasarkan ID.
     */
    public function findDetailById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        foreach ($this->getAllDetails() as $detail) {
            if ((int) ($detail['id_detail'] ?? 0) === $id) {
                return $detail;
            }
        }

        return null;
    }

    /**
     * Menghitung jumlah ebook pada satu transaksi.
     */
    public function countTransactionDetails(int $idTransaction): int
    {
        return count($this->getTransactionDetails($idTransaction));
    }

    /**
     * Membuat ID detail transaksi berikutnya.
     */
    private function generateDetailId(): int
    {
        $details = $this->storage->read($this->detailFile);

        if (empty($details)) {
            return 1;
        }

        return max(array_map(
            fn($detail) => (int) ($detail['id_detail'] ?? 0),
            $details
        )) + 1;
    }

    /**
     * Menghitung salinan ebook yang masih ditahan oleh detail transaksi.
     * Detail pada transaksi yang ditolak/dibatalkan sudah berstatus
     * cancelled sehingga lisensinya tidak ikut terhitung.
     */
    public function countActiveBorrowings(int $idEbook): int
    {
        if ($idEbook < 1) {
            return 0;
        }

        $count = 0;

        foreach ($this->getAllDetails() as $data) {
            if ((int) ($data['id_ebook'] ?? 0) !== $idEbook) {
                continue;
            }

            if ((new TransactionDetail($data))->isHoldingLicense()) {
                $count++;
            }
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Alur peminjaman
    |--------------------------------------------------------------------------
    */

    /**
     * Menormalkan daftar ebook yang akan dipinjam dari payload.
     */
    private function normalizeItems(array $data): array
    {
        $items = $data['items'] ?? null;

        if (!is_array($items) || empty($items)) {
            if (!isset($data['id_ebook'])) {
                throw new \InvalidArgumentException(
                    'Minimal satu ebook harus dipinjam.'
                );
            }

            $items = [
                [
                    'id_ebook' => $data['id_ebook'],
                    'due_date' => $data['due_date'] ?? null
                ]
            ];
        }

        $now = date('Y-m-d H:i:s');
        $normalized = [];
        $usedEbooks = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException('Data ebook tidak valid.');
            }

            $idEbook = filter_var(
                $item['id_ebook'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$idEbook || $idEbook < 1) {
                throw new \InvalidArgumentException('ID Ebook tidak valid.');
            }

            if (isset($usedEbooks[$idEbook])) {
                throw new \InvalidArgumentException(
                    'Ebook yang sama tidak dapat dipinjam dua kali dalam satu transaksi.'
                );
            }

            $usedEbooks[$idEbook] = true;

            $dueDate = trim((string) ($item['due_date'] ?? ''));

            if ($dueDate === '') {
                $dueDate = date(
                    'Y-m-d H:i:s',
                    strtotime($now . ' + ' . self::DEFAULT_LOAN_DAYS . ' days')
                );
            } else {
                $timestamp = strtotime($dueDate);

                if ($timestamp === false) {
                    throw new \InvalidArgumentException(
                        'Tanggal jatuh tempo tidak valid.'
                    );
                }

                if ($timestamp <= strtotime($now)) {
                    throw new \InvalidArgumentException(
                        'Tanggal jatuh tempo harus setelah tanggal pinjam.'
                    );
                }

                $dueDate = date('Y-m-d H:i:s', $timestamp);
            }

            $normalized[] = [
                'id_ebook' => $idEbook,
                'due_date' => $dueDate
            ];
        }

        return $normalized;
    }

    /**
     * Memvalidasi ketersediaan ebook dan sisa lisensi.
     */
    private function validateItemsAvailability(array $items): void
    {
        $catalogService = new CatalogService($this->storage);

        foreach ($items as $item) {
            $ebookData = $catalogService->findEbookById($item['id_ebook']);

            if ($ebookData === null) {
                throw new \InvalidArgumentException(
                    'Ebook dengan ID ' . $item['id_ebook'] . ' tidak ditemukan.'
                );
            }

            $ebook = new Ebook($ebookData);

            if (!$ebook->isAvailable()) {
                throw new \InvalidArgumentException(
                    'Ebook "' . $ebook->getTitle() . '" sedang tidak tersedia.'
                );
            }

            // license_limit bersifat mutable: berkurang setiap kali dipinjam
            // dan pulih saat dikembalikan, sehingga tidak perlu menghitung
            // ulang detail transaksi yang masih aktif.
            if (!$ebook->hasLicenseAvailable()) {
                throw new \InvalidArgumentException(
                    'Stok lisensi ebook "' . $ebook->getTitle() . '" tidak mencukupi.'
                );
            }
        }
    }

    /**
     * Membuat pengajuan peminjaman beserta detailnya.
     */
    public function createBorrowing(int $idUser, array $data): Transaction
    {
        if ($idUser < 1) {
            throw new \InvalidArgumentException('Pengguna tidak valid.');
        }

        $items = $this->normalizeItems($data);

        if (empty($items)) {
            throw new \InvalidArgumentException(
                'Minimal satu ebook harus dipinjam.'
            );
        }

        $this->validateItemsAvailability($items);

        $now = date('Y-m-d H:i:s');
        $idTransaction = $this->generateTransactionId();

        $transaction = new Transaction([
            'id_transaction' => $idTransaction,
            'id_user' => $idUser,
            'approved_by' => null,
            'processed_at' => null,
            'status' => Transaction::STATUS_PENDING,
            'notes' => trim((string) ($data['notes'] ?? '')),
            'create_at' => $now
        ]);

        $details = [];
        $idDetail = $this->generateDetailId();

        foreach ($items as $item) {
            $detail = new TransactionDetail([
                'id_detail' => $idDetail++,
                'id_transaction' => $idTransaction,
                'id_ebook' => $item['id_ebook'],
                'start_date' => $now,
                'due_date' => $item['due_date'],
                'status' => TransactionDetail::STATUS_ACTIVE
            ]);

            $details[] = $detail->toArray();
        }

        // Lisensi dipesan sejak pengajuan dibuat agar ebook tidak dapat
        // dipinjam melebihi kapasitas sebelum admin menyetujuinya.
        $reserved = [];

        try {
            foreach ($items as $item) {
                $this->adjustEbookLicense($item['id_ebook'], -1);
                $reserved[] = $item['id_ebook'];
            }

            $transactions = $this->storage->read($this->transactionFile);
            $transactions[] = $transaction->toArray();

            $this->storage->write($this->transactionFile, $transactions);

            $allDetails = array_merge(
                $this->storage->read($this->detailFile),
                $details
            );

            $this->storage->write($this->detailFile, $allDetails);
        } catch (\Throwable $exception) {
            // Penyimpanan berupa file terpisah sehingga pemesanan lisensi
            // harus dikembalikan bila salah satu penulisan gagal.
            foreach ($reserved as $idEbook) {
                $this->adjustEbookLicense($idEbook, 1);
            }

            throw $exception;
        }

        return $transaction;
    }

    /**
     * Mengubah sisa lisensi ebook melalui CatalogService.
     */
    private function adjustEbookLicense(int $idEbook, int $delta): void
    {
        $catalogService = new CatalogService($this->storage);

        $catalogService->adjustEbookLicense($idEbook, $delta);
    }

    /**
     * Mengembalikan lisensi ebook yang ditahan oleh detail transaksi.
     */
    private function releaseDetailLicenses(int $idTransaction): void
    {
        foreach ($this->getTransactionDetails($idTransaction) as $data) {
            $detail = new TransactionDetail($data);

            if (!$detail->isHoldingLicense()) {
                continue;
            }

            $this->adjustEbookLicense($detail->getIdEbook(), 1);
        }
    }

    /**
     * Menyelaraskan status detail dengan status transaksi induknya.
     */
    private function syncDetailsStatus(int $idTransaction, string $status): void
    {
        $details = $this->storage->read($this->detailFile);
        $changed = false;

        foreach ($details as $index => $data) {
            if ((int) ($data['id_transaction'] ?? 0) !== $idTransaction) {
                continue;
            }

            $detail = new TransactionDetail($data);

            if ($status === TransactionDetail::STATUS_CANCELLED) {
                if ($detail->isCancelled() || $detail->isReturned()) {
                    continue;
                }

                $detail->markCancelled();
            } else {
                continue;
            }

            $details[$index] = $detail->toArray();
            $changed = true;
        }

        if ($changed) {
            $this->storage->write($this->detailFile, $details);
        }
    }

    /**
     * Menyetujui pengajuan peminjaman.
     */
    public function approveTransaction(int $id, int $approvedBy): Transaction
    {
        return $this->processTransaction(
            $id,
            Transaction::STATUS_APPROVED,
            $approvedBy,
            ''
        );
    }

    /**
     * Menolak pengajuan peminjaman.
     */
    public function rejectTransaction(
        int $id,
        int $approvedBy,
        string $notes = ''
    ): Transaction {
        return $this->processTransaction(
            $id,
            Transaction::STATUS_REJECTED,
            $approvedBy,
            $notes
        );
    }

    /**
     * Mengubah status transaksi yang masih pending.
     */
    private function processTransaction(
        int $id,
        string $status,
        int $approvedBy,
        string $notes
    ): Transaction {
        if ($approvedBy < 1) {
            throw new \InvalidArgumentException('Admin tidak valid.');
        }

        $transactions = $this->storage->read($this->transactionFile);

        foreach ($transactions as $index => $data) {
            if ((int) ($data['id_transaction'] ?? 0) !== $id) {
                continue;
            }

            $transaction = new Transaction($data);

            if (!$transaction->isPending()) {
                throw new \InvalidArgumentException(
                    'Hanya transaksi berstatus pending yang dapat diproses.'
                );
            }

            if ($this->countTransactionDetails($id) === 0) {
                throw new \InvalidArgumentException(
                    'Transaksi tidak memiliki detail peminjaman.'
                );
            }

            if ($status === Transaction::STATUS_REJECTED) {
                $transaction->reject($approvedBy, trim($notes));

                $transactions[$index] = $transaction->toArray();

                $this->storage->write($this->transactionFile, $transactions);

                // Penolakan mengembalikan lisensi yang sempat dipesan
                // dan membatalkan seluruh detail peminjamannya.
                $this->releaseDetailLicenses($id);
                $this->syncDetailsStatus(
                    $id,
                    TransactionDetail::STATUS_CANCELLED
                );

                return $transaction;
            }

            $transaction->approve($approvedBy);

            $transactions[$index] = $transaction->toArray();

            $this->storage->write($this->transactionFile, $transactions);

            return $transaction;
        }

        throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
    }

    /**
     * Membatalkan transaksi yang masih pending.
     */
    public function cancelTransaction(int $id): Transaction
    {
        $transactions = $this->storage->read($this->transactionFile);

        foreach ($transactions as $index => $data) {
            if ((int) ($data['id_transaction'] ?? 0) !== $id) {
                continue;
            }

            $transaction = new Transaction($data);

            if (!$transaction->isPending()) {
                throw new \InvalidArgumentException(
                    'Hanya transaksi berstatus pending yang dapat dibatalkan.'
                );
            }

            $transaction->setStatus(Transaction::STATUS_CANCELLED);

            $transactions[$index] = $transaction->toArray();

            $this->storage->write($this->transactionFile, $transactions);

            // Pembatalan juga mengembalikan lisensi ke katalog
            // dan membatalkan seluruh detail peminjamannya.
            $this->releaseDetailLicenses($id);
            $this->syncDetailsStatus($id, TransactionDetail::STATUS_CANCELLED);

            return $transaction;
        }

        throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
    }

    /**
     * Menandai ebook pada detail transaksi sudah dikembalikan.
     */
    public function returnDetail(int $idDetail): TransactionDetail
    {
        $details = $this->storage->read($this->detailFile);

        foreach ($details as $index => $data) {
            if ((int) ($data['id_detail'] ?? 0) !== $idDetail) {
                continue;
            }

            $detail = new TransactionDetail($data);

            if ($detail->isCancelled()) {
                throw new \InvalidArgumentException(
                    'Ebook pada transaksi ini sudah dibatalkan sehingga tidak dapat dikembalikan.'
                );
            }

            if ($detail->isReturned()) {
                throw new \InvalidArgumentException(
                    'Ebook pada transaksi ini sudah dikembalikan.'
                );
            }

            $transaction = $this->findTransactionById(
                $detail->getIdTransaction()
            );

            if ($transaction === null) {
                throw new \InvalidArgumentException(
                    'Transaksi tidak ditemukan.'
                );
            }

            if (($transaction['status'] ?? '') !== Transaction::STATUS_APPROVED) {
                throw new \InvalidArgumentException(
                    'Transaksi belum disetujui sehingga ebook belum dapat dikembalikan.'
                );
            }

            $detail->markReturned();

            $details[$index] = $detail->toArray();

            $this->storage->write($this->detailFile, $details);

            // Pengembalian memulihkan sisa lisensi ebook dan statusnya
            // otomatis kembali menjadi tersedia bila sebelumnya habis.
            $this->adjustEbookLicense($detail->getIdEbook(), 1);

            $this->completeTransactionIfDone($detail->getIdTransaction());

            return $detail;
        }

        throw new \InvalidArgumentException('Detail transaksi tidak ditemukan.');
    }

    /**
     * Menandai seluruh ebook pada satu transaksi sudah dikembalikan.
     * Detail yang sudah returned atau cancelled dilewati sehingga
     * pengembalian bersifat idempoten.
     *
     * @return TransactionDetail[]
     */
    public function returnTransaction(int $idTransaction): array
    {
        $transaction = $this->findTransactionById($idTransaction);

        if ($transaction === null) {
            throw new \InvalidArgumentException('Transaksi tidak ditemukan.');
        }

        if (($transaction['status'] ?? '') === Transaction::STATUS_COMPLETED) {
            throw new \InvalidArgumentException(
                'Transaksi ini sudah selesai, seluruh ebook telah dikembalikan.'
            );
        }

        if (($transaction['status'] ?? '') !== Transaction::STATUS_APPROVED) {
            throw new \InvalidArgumentException(
                'Transaksi belum disetujui sehingga ebook belum dapat dikembalikan.'
            );
        }

        $details = $this->storage->read($this->detailFile);
        $returned = [];
        $restoredEbooks = [];
        $returnedAt = date('Y-m-d H:i:s');

        foreach ($details as $index => $data) {
            if ((int) ($data['id_transaction'] ?? 0) !== $idTransaction) {
                continue;
            }

            $detail = new TransactionDetail($data);

            if (!$detail->isHoldingLicense()) {
                continue;
            }

            $detail->markReturned($returnedAt);

            $details[$index] = $detail->toArray();
            $returned[] = $detail;
            $restoredEbooks[] = $detail->getIdEbook();
        }

        if (empty($returned)) {
            throw new \InvalidArgumentException(
                'Tidak ada ebook yang masih dipinjam pada transaksi ini.'
            );
        }

        $this->storage->write($this->detailFile, $details);

        // Lisensi dipulihkan setelah detail tersimpan agar jumlah
        // pengembalian dan sisa lisensi tetap konsisten.
        foreach ($restoredEbooks as $idEbook) {
            $this->adjustEbookLicense($idEbook, 1);
        }

        $this->completeTransactionIfDone($idTransaction);

        return $returned;
    }

    /**
     * Menandai transaksi selesai bila seluruh detailnya sudah dikembalikan.
     */
    private function completeTransactionIfDone(int $idTransaction): void
    {
        $details = $this->getTransactionDetails($idTransaction);

        if (empty($details)) {
            return;
        }

        foreach ($details as $data) {
            $detail = new TransactionDetail($data);

            // Detail yang dibatalkan tidak lagi menahan lisensi sehingga
            // tidak menghalangi transaksi dianggap selesai.
            if ($detail->isCancelled()) {
                continue;
            }

            if (!$detail->isReturned()) {
                return;
            }
        }

        $transactions = $this->storage->read($this->transactionFile);

        foreach ($transactions as $index => $data) {
            if ((int) ($data['id_transaction'] ?? 0) !== $idTransaction) {
                continue;
            }

            $transaction = new Transaction($data);

            if ($transaction->isCompleted()) {
                return;
            }

            $transaction->complete();

            $transactions[$index] = $transaction->toArray();

            $this->storage->write($this->transactionFile, $transactions);

            return;
        }
    }
}
