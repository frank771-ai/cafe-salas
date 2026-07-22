<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ana Castillo',
            'email' => 'ana@example.test',
            'phone' => '8888-9999',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.test']);
        $this->assertNotSame('Segura123!', User::firstWhere('email', 'ana@example.test')->password);
    }

    public function test_a_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'Cliente123!']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Cliente123!'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_registration_rejects_weak_passwords_and_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'used@example.test']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Ana Castillo',
            'email' => 'used@example.test',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors(['email', 'password']);
    }
}
