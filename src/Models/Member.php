<?php

namespace App\Models;

class Member extends User
{
    public function __construct(array $data)
    {
        // Role Member ditentukan oleh class ini.
        $data['role'] = 'member';

        parent::__construct($data);
    }

    // Memeriksa apakah pengguna merupakan Member.
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    // Memeriksa apakah akun Member aktif.
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}