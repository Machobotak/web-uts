<?php

namespace App\Middleware;

use App\Models\User;
use App\Models\Admin;
use App\Models\Member;
use App\Services\AuthService;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Mengambil pengguna yang sedang login.
     */
    public function getCurrentUser(): ?User
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Memeriksa apakah session login tersedia.
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        // Mengambil data terbaru dari users.json.
        $userData = $this->authService->findById(
            (int) $_SESSION['user_id']
        );

        if ($userData === null) {
            return null;
        }

        // Memeriksa status akun.
        if (($userData['status'] ?? '') !== 'active') {
            return null;
        }

        // Memastikan role session sesuai dengan data pengguna.
        if (
            ($_SESSION['user_role'] ?? '') !==
            ($userData['role'] ?? '')
        ) {
            return null;
        }

        // Membuat objek berdasarkan role.
        if ($userData['role'] === 'admin') {
            return new Admin($userData);
        }

        if ($userData['role'] === 'member') {
            return new Member($userData);
        }

        return null;
    }

    /**
     * Memastikan pengguna sudah login.
     */
    public function requireLogin(): User
    {
        $user = $this->getCurrentUser();

        if ($user === null) {
            $this->denyAccess(
                401,
                'Silakan login terlebih dahulu.'
            );
        }

        return $user;
    }

    /**
     * Memastikan pengguna memiliki role tertentu.
     */
    public function requireRole(string $role): User
    {
        $user = $this->requireLogin();

        if ($user->getRole() !== $role) {
            $this->denyAccess(
                403,
                'Anda tidak memiliki izin untuk mengakses halaman ini.'
            );
        }

        return $user;
    }

    /**
     * Memeriksa apakah pengguna sudah login.
     */
    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Menghentikan akses jika tidak memiliki izin.
     */
    private function denyAccess(int $status, string $message): void
    {
        http_response_code($status);

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        $isJson = str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json');

        if ($isJson) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        } else {
            echo htmlspecialchars($message);
        }

        exit;
    }
}
