<?php

namespace App\Models;

class Admin extends User
{
    public function __construct(array $data)
    {
        // Role Admin ditentukan oleh class ini.
        $data['role'] = 'admin';

        parent::__construct($data);
    }

    // Memeriksa apakah pengguna merupakan Admin.
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}