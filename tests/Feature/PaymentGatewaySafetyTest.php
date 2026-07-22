<?php

namespace Tests\Feature;

use App\Services\SimulatedPaymentGateway;
use RuntimeException;
use Tests\TestCase;

/** Impide que una configuración olvidada convierta pagos simulados en ventas reales. */
class PaymentGatewaySafetyTest extends TestCase
{
    public function test_production_rejects_simulated_payments_unless_explicitly_enabled(): void
    {
        $this->app['env'] = 'production';
        config(['services.payments.allow_simulation_in_production' => false]);

        $this->expectException(RuntimeException::class);

        app(SimulatedPaymentGateway::class)->authorize([
            'payment_method' => 'paypal',
        ], 1000, now());
    }
}
