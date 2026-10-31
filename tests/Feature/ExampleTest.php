<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'password']);
        $cashier = User::factory()->create(['role' => 'kasir', 'password' => 'password']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        auth()->logout();

        $this->post('/login', ['email' => $cashier->email, 'password' => 'password'])
            ->assertRedirect(route('kasir.dashboard'));
    }
}
