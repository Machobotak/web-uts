<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Models\Member;
use App\Storage\JsonStorage;

class AuthService
{
    private JsonStorage $storage;
    private string $filename = 'users.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Mengambil seluruh data pengguna.
     */
    public function getAllUsers(): array
    {
        return $this->storage->read($this->filename);
    }

    /**
     * Mencari pengguna berdasarkan email.
     */
    public function findByEmail(string $email): ?array
    {
        $users = $this->getAllUsers();

        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Mencari pengguna berdasarkan ID.
     */
    public function findById(int $id): ?array
    {
        $users = $this->getAllUsers();

        foreach ($users as $user) {
            if ((int) $user['id_user'] === $id) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Membuat ID pengguna berikutnya.
     */
    private function generateId(): int
    {
        $users = $this->getAllUsers();

        if (empty($users)) {
            return 1;
        }

        $ids = array_column($users, 'id_user');

        return max($ids) + 1;
    }

    /**
     * Registrasi Member baru.
     */
    public function register(array $data): Member
    {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');

        // Validasi nama.
        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama wajib diisi.'
            );
        }

        // Validasi email.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                'Format email tidak valid.'
            );
        }

        // Validasi password.
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException(
                'Password minimal 8 karakter.'
            );
        }

        // Memeriksa email duplikat.
        if ($this->findByEmail($email) !== null) {
            throw new \InvalidArgumentException(
                'Email sudah terdaftar.'
            );
        }

        // Menyiapkan data Member.
        $member = new Member([
            'id_user' => $this->generateId(),
            'name' => $name,
            'email' => $email,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'phone' => $phone,
            'address' => $address,
            'status' => 'active'
        ]);

        // Menambahkan Member ke data JSON.
        $users = $this->getAllUsers();
        $users[] = $member->toArray();

        $this->storage->write($this->filename, $users);

        return $member;
    }

    /**
     * Login pengguna.
     */
    public function login(string $email, string $password): ?User
    {
        $email = strtolower(trim($email));

        // Mencari pengguna berdasarkan email.
        $userData = $this->findByEmail($email);

        if ($userData === null) {
            return null;
        }

        // Memeriksa password.
        if (!password_verify($password, $userData['password'] ?? '')) {
            return null;
        }

        // Memeriksa status akun.
        if (($userData['status'] ?? '') !== 'active') {
            return null;
        }

        // Membuat objek berdasarkan role.
        $user = $this->createUserObject($userData);

        // Memastikan session sudah dimulai.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerasi ID session untuk keamanan.
        session_regenerate_id(true);

        // Menyimpan informasi pengguna ke session.
        $_SESSION['user_id'] = $user->getIdUser();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['user_name'] = $user->getName();

        return $user;
    }

    /**
     * Membuat objek sesuai role pengguna.
     */
    private function createUserObject(array $data): User
    {
        if ($data['role'] === 'admin') {
            return new Admin($data);
        }

        return new Member($data);
    }

    /**
     * Logout pengguna.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Membuat akun Admin melalui proses khusus.
     * Jangan panggil method ini dari registrasi publik.
     */
    public function createAdmin(array $data): Admin
    {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama Admin wajib diisi.'
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

        if ($this->findByEmail($email) !== null) {
            throw new \InvalidArgumentException(
                'Email sudah terdaftar.'
            );
        }

        $admin = new Admin([
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

        $users = $this->getAllUsers();
        $users[] = $admin->toArray();

        $this->storage->write($this->filename, $users);

        return $admin;
    }
}
