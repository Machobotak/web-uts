<?php

namespace App\Services;

use App\Models\Member;
use App\Storage\JsonStorage;

class MemberService
{
    private JsonStorage $storage;
    private string $filename = 'users.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Mengambil seluruh data Member.
     */
    public function getAllMembers(): array
    {
        $users = $this->storage->read($this->filename);

        return array_values(array_filter(
            $users,
            fn($user) => ($user['role'] ?? '') === 'member'
        ));
    }

    /**
     * Mencari Member berdasarkan ID.
     */
    public function findById(int $id): ?array
    {
        foreach ($this->getAllMembers() as $member) {
            if ((int) $member['id_user'] === $id) {
                return $member;
            }
        }

        return null;
    }

    /**
     * Membuat ID pengguna berikutnya.
     */
    private function generateId(): int
    {
        $users = $this->storage->read($this->filename);

        if (empty($users)) {
            return 1;
        }

        $ids = array_column($users, 'id_user');

        return max($ids) + 1;
    }

    /**
     * Memeriksa apakah email sudah digunakan.
     */
    private function emailExists(
        string $email,
        ?int $excludeId = null
    ): bool {
        $users = $this->storage->read($this->filename);

        foreach ($users as $user) {
            $sameEmail = strtolower($user['email']) === $email;
            $differentId = (int) $user['id_user'] !== $excludeId;

            if ($sameEmail && $differentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menambahkan Member baru.
     */
    public function createMember(array $data): Member
    {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama wajib diisi.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                'Format email tidak valid.'
            );
        }

        if (strlen($password) < 8) {
            throw new \InvalidArgumentException(
                'Password minimal 8 karakter.'
            );
        }

        if ($this->emailExists($email)) {
            throw new \InvalidArgumentException(
                'Email sudah digunakan.'
            );
        }

        $member = new Member([
            'id_user' => $this->generateId(),
            'name' => $name,
            'email' => $email,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'phone' => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'status' => 'active'
        ]);

        $users = $this->storage->read($this->filename);
        $users[] = $member->toArray();

        $this->storage->write($this->filename, $users);

        return $member;
    }

    /**
     * Mengubah informasi Member.
     */
    public function updateMember(int $id, array $data): Member
    {
        $users = $this->storage->read($this->filename);
        $memberIndex = null;

        foreach ($users as $index => $user) {
            if (
                (int) $user['id_user'] === $id &&
                ($user['role'] ?? '') === 'member'
            ) {
                $memberIndex = $index;
                break;
            }
        }

        if ($memberIndex === null) {
            throw new \InvalidArgumentException(
                'Member tidak ditemukan.'
            );
        }

        $current = $users[$memberIndex];

        $name = trim($data['name'] ?? $current['name']);
        $email = strtolower(
            trim($data['email'] ?? $current['email'])
        );

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama wajib diisi.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                'Format email tidak valid.'
            );
        }

        if ($this->emailExists($email, $id)) {
            throw new \InvalidArgumentException(
                'Email sudah digunakan oleh pengguna lain.'
            );
        }

        // Membuat objek Member dari data yang ada.
        $member = new Member($current);

        $member->setName($name);
        $member->setEmail($email);

        $member->setPhone(
            trim($data['phone'] ?? $current['phone'])
        );

        $member->setAddress(
            trim($data['address'] ?? $current['address'])
        );

        // Password hanya diubah jika diisi.
        $password = $data['password'] ?? '';

        if ($password !== '') {
            if (strlen($password) < 8) {
                throw new \InvalidArgumentException(
                    'Password minimal 8 karakter.'
                );
            }

            $member->setPassword($password);
        }

        $users[$memberIndex] = $member->toArray();

        $this->storage->write($this->filename, $users);

        return $member;
    }

    /**
     * Mengaktifkan atau menonaktifkan akun Member.
     */
    public function updateStatus(int $id, string $status): Member
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new \InvalidArgumentException(
                'Status tidak valid.'
            );
        }

        $users = $this->storage->read($this->filename);

        foreach ($users as $index => $user) {
            if (
                (int) $user['id_user'] === $id &&
                ($user['role'] ?? '') === 'member'
            ) {
                $member = new Member($user);

                $member->setStatus($status);

                $users[$index] = $member->toArray();

                $this->storage->write(
                    $this->filename,
                    $users
                );

                return $member;
            }
        }

        throw new \InvalidArgumentException(
            'Member tidak ditemukan.'
        );
    }
}
