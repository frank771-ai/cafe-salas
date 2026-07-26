<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/** Cubre registro, contraseña segura, login, logout y errores de autenticación. */
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

    /** Comprueba que una contraseña incorrecta no inicia sesión ni pierde el correo válido. */
    public function test_login_rejects_incorrect_credentials(): void
    {
        $user = User::factory()->create(['password' => 'Cliente123!']);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Incorrecta123!',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', $user->email)
            ->assertSessionMissing('password');

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

    public function test_registration_normalizes_identity_and_cannot_assign_admin_role(): void
    {
        $this->post(route('register.store'), [
            'name' => '  Ana Castillo  ',
            'email' => '  ANA@EXAMPLE.TEST  ',
            'phone' => ' 8888-9999 ',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
            'is_admin' => true,
        ])->assertRedirect(route('home'));

        $user = User::firstWhere('email', 'ana@example.test');
        $this->assertNotNull($user);
        $this->assertSame('Ana Castillo', $user->name);
        $this->assertSame('8888-9999', $user->phone);
        $this->assertFalse($user->is_admin);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'unknown@example.test',
                'password' => 'Incorrecta123!',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'unknown@example.test',
            'password' => 'Incorrecta123!',
        ])->assertTooManyRequests();
    }
}
