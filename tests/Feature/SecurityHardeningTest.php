<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Comprueba permisos, cabeceras defensivas, HTTPS y caché de datos privados. */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_https_responses_include_hsts(): void
    {
        $this->withServerVariables(['HTTPS' => 'on', 'SERVER_PORT' => 443])
            ->get('https://localhost/')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_authenticated_pages_are_not_browser_cacheable(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_protected_areas_require_login_and_admin_role(): void
    {
        $customer = User::factory()->create();

        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_mutating_requests_reject_missing_csrf_token_outside_test_environment(): void
    {
        $this->app['env'] = 'local';

        $this->post(route('register.store'), [
            'name' => 'Intento sin token',
            'email' => 'csrf@example.test',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
        ])->assertStatus(419);
    }
}
