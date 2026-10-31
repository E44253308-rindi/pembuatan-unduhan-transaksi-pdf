<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@komik.test'],
            [
                'name' => 'Admin Utama',
                'password' => 'admin1234',
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'kasir@komik.test'],
            [
                'name' => 'Kasir Demo',
                'password' => 'admin1234',
                'role' => 'kasir',
            ],
        );
    }
}
