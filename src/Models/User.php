<?php
namespace App\Models;

class User
{
    protected int $idUser;
    protected string $name;
    protected string $email;
    protected string $password;
    protected string $phone;
    protected string $address;
    protected string $role;
    protected string $status;
    protected string $createdAt;
    protected string $updatedAt;

    public function __construct(array $data)
    {
        $this->idUser = (int) ($data['id_user'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->role = $data['role'] ?? 'member';
        $this->status = $data['status'] ?? 'active';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // Mengambil ID pengguna
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    // Mengambil nama pengguna
    public function getName(): string
    {
        return $this->name;
    }

    // Mengambil email pengguna
    public function getEmail(): string
    {
        return $this->email;
    }

    // Mengambil hash password
    public function getPassword(): string
    {
        return $this->password;
    }

    // Mengambil role pengguna
    public function getRole(): string
    {
        return $this->role;
    }

    // Mengambil status pengguna
    public function getStatus(): string
    {
        return $this->status;
    }

    // Mengubah nama pengguna
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah email pengguna
    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah nomor telepon
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah alamat pengguna
    public function setAddress(string $address): void
    {
        $this->address = $address;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah password
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah status pengguna
    public function setStatus(string $status): void
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Mengubah data objek menjadi array
    public function toArray(): array
    {
        return [
            'id_user' => $this->idUser,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Data yang aman untuk ditampilkan ke frontend
    public function toPublicArray(): array
    {
        $data = $this->toArray();
        unset($data['password']);

        return $data;
    }
}
