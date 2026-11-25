<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

User::updateOrCreate(
    ['email' => 'admin@lacteos.local'],
    [
        'name' => 'Admin',
        'password' => Hash::make('TuClaveSegura'),
        // 'rol' => 'admin',
    ]
);
