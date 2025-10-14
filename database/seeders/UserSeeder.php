<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrateur Principal',
            'email' => 'admin@dvlotey.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Agent Service Client',
            'email' => 'agent@dvlotey.com',
            'password' => Hash::make('agent123'),
            'role' => 'agent',
            'is_active' => true,
        ]);

        // Créer quelques agents supplémentaires
        User::factory()->count(3)->create([
            'role' => 'agent'
        ]);
    }
}