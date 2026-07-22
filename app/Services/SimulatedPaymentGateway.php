<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Representa la pasarela académica de pagos del proyecto.
 *
 * La clase imita la respuesta que entregaría PayPal o un procesador de tarjetas,
 * pero no realiza cargos externos. Aislar esta responsabilidad permite reemplazar
 * esta implementación por un SDK real sin modificar el proceso de pedidos.
 */
class SimulatedPaymentGateway
{
    /**
     * Autoriza una transacción simulada y devuelve únicamente datos seguros para guardar.
     *
     * @param  array<string, mixed>  $checkoutData  Datos previamente validados.
     * @return array{method: string, status: string, provider_reference: string, last_four: ?string, amount: int, processed_at: CarbonInterface}
     */
    public function authorize(array $checkoutData, int $amount, CarbonInterface $processedAt): array
    {
        $method = (string) $checkoutData['payment_method'];

        return [
            'method' => $method,
            'status' => 'approved',
            'provider_reference' => 'SIM-'.Str::upper(Str::random(16)),
            // Nunca se conserva el número completo de tarjeta ni el CVV.
            'last_four' => $method === 'card' ? substr((string) $checkoutData['card_number'], -4) : null,
            'amount' => $amount,
            'processed_at' => $processedAt,
        ];
    }
}
