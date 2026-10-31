<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_admin_and_cashier_accounts_with_the_requested_password(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Utama',
            'email' => 'admin@komik.test',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Kasir Demo',
            'email' => 'kasir@komik.test',
            'role' => 'kasir',
        ]);

        $this->assertTrue(Hash::check('admin1234', User::where('email', 'admin@komik.test')->firstOrFail()->password));
        $this->assertTrue(Hash::check('admin1234', User::where('email', 'kasir@komik.test')->firstOrFail()->password));
    }

    public function test_user_seeder_can_be_rerun_without_creating_duplicate_accounts(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 2);
    }
}
