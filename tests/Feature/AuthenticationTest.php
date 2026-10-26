<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::create(['name' => 'Demo User', 'email' => 'demo@example.test', 'password' => Hash::make('correct-password'), 'role' => 'staff']);

        $this->post('/login', ['email' => 'demo@example.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_staff_cannot_open_admin_user_management(): void
    {
        $staff = User::create(['name' => 'Staff Demo', 'email' => 'staff@example.test', 'password' => Hash::make('password123'), 'role' => 'staff']);

        $this->actingAs($staff)->get('/users')->assertForbidden();
    }


    public function test_staff_cannot_open_another_users_sale(): void
    {
        $staff = User::create(['name' => 'Staff One', 'email' => 'staff-one@example.test', 'password' => Hash::make('password123'), 'role' => 'staff']);
        $other = User::create(['name' => 'Staff Two', 'email' => 'staff-two@example.test', 'password' => Hash::make('password123'), 'role' => 'staff']);
        $sale = Sale::create(['user_id' => $other->id, 'sale_number' => 'SAL-AUTH-001', 'total' => 1000, 'status' => 'completed', 'sold_at' => now()]);

        $this->actingAs($staff)->get('/sales/'.$sale->id)->assertForbidden();
    }

    public function test_admin_can_open_user_management(): void
    {
        $admin = User::create(['name' => 'Admin Demo', 'email' => 'admin@example.test', 'password' => Hash::make('password123'), 'role' => 'admin']);

        $this->actingAs($admin)->get('/users')->assertOk();
    }
}