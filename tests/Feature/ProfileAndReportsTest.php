<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_and_see_order_history(): void
    {
        $this->seed();
        $user = User::where('email', 'cliente@origentico.test')->firstOrFail();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'María Solano',
            'email' => $user->email,
            'phone' => '2222-3333',
            'address' => 'Nueva dirección exacta en San José',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('María Solano')
            ->assertSee($user->orders()->first()->order_number);
    }

    public function test_only_admin_can_access_sales_reports(): void
    {
        $this->seed();
        $customer = User::where('email', 'cliente@origentico.test')->firstOrFail();
        $admin = User::where('email', 'admin@origentico.test')->firstOrFail();

        $this->actingAs($customer)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_admin_can_download_monthly_and_customer_pdf_reports(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@origentico.test')->firstOrFail();
        $customer = User::where('email', 'cliente@origentico.test')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.reports.monthly', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)->get(route('admin.reports.customer', ['user_id' => $customer->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
