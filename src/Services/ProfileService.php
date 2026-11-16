<?php

namespace App\Services;

use App\Models\Member;
use App\Storage\JsonStorage;

class ProfileService
{
    private JsonStorage $storage;
    private string $filename = 'users.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Mengambil profil Member berdasarkan ID.
     */
    public function getProfile(int $id): ?Member
    {
        $users = $this->storage->read($this->filename);

        foreach ($users as $user) {
            if (
                (int) $user['id_user'] === $id &&
                ($user['role'] ?? '') === 'member'
            ) {
                return new Member($user);
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah email sudah digunakan pengguna lain.
     */
    private function emailExists(string $email, int $excludeId): bool
    {
        $users = $this->storage->read($this->filename);

        foreach ($users as $user) {
            if (
                strtolower($user['email']) === strtolower($email) &&
                (int) $user['id_user'] !== $excludeId
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memperbarui informasi profil Member.
     */
    public function updateProfile(int $id, array $data): Member
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
                'Profil Member tidak ditemukan.'
            );
        }

        $current = $users[$memberIndex];

        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));

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
                'Email sudah digunakan.'
            );
        }

        // Membuat objek Member dari data yang tersimpan.
        $member = new Member($current);

        $member->setName($name);
        $member->setEmail($email);

        $member->setPhone(
            trim($data['phone'] ?? '')
        );

        $member->setAddress(
            trim($data['address'] ?? '')
        );

        // Menyimpan perubahan.
        $users[$memberIndex] = $member->toArray();

        $this->storage->write($this->filename, $users);

        return $member;
    }

    /**
     * Mengganti password Member.
     */
    public function changePassword(
        int $id,
        string $oldPassword,
        string $newPassword
    ): bool {
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

        // Memastikan password lama benar.
        if (!password_verify(
            $oldPassword,
            $current['password']
        )) {
            throw new \InvalidArgumentException(
                'Password lama tidak sesuai.'
            );
        }

        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException(
                'Password baru minimal 8 karakter.'
            );
        }

        if ($oldPassword === $newPassword) {
            throw new \InvalidArgumentException(
                'Password baru harus berbeda dari password lama.'
            );
        }

        // Membuat objek dan mengubah password.
        $member = new Member($current);
        $member->setPassword($newPassword);

        $users[$memberIndex] = $member->toArray();

        $this->storage->write($this->filename, $users);

        return true;
    }
}
