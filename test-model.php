<?php

require_once __DIR__ . '/config/bootstrap.php';

use App\Models\User;
use App\Models\Admin;
use App\Models\Member;

// Membuat objek Admin
$admin = new Admin([
    'id_user' => 1,
    'name' => 'Administrator',
    'email' => 'admin@library.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'status' => 'active'
]);

// Membuat objek Member
$member = new Member([
    'id_user' => 2,
    'name' => 'Ayub',
    'email' => 'ayub@library.com',
    'password' => password_hash('member123', PASSWORD_DEFAULT),
    'status' => 'active'
]);

echo "<h3>Data Admin</h3>";
echo "Nama: " . $admin->getName() . "<br>";
echo "Role: " . $admin->getRole() . "<br>";
echo "Apakah Admin? " . ($admin->isAdmin() ? 'Ya' : 'Tidak');

echo "<hr>";

echo "<h3>Data Member</h3>";
echo "Nama: " . $member->getName() . "<br>";
echo "Role: " . $member->getRole() . "<br>";
echo "Apakah Member? " . ($member->isMember() ? 'Ya' : 'Tidak') . "<br>";
echo "Status Aktif? " . ($member->isActive() ? 'Ya' : 'Tidak');