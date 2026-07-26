<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Centraliza la autorización, normalización y validación del formulario de compra.
 *
 * Mantener estas reglas fuera del controlador permite reutilizarlas, probarlas y
 * evitar que datos de pago sin validar lleguen a la capa de negocio.
 */
class CheckoutRequest extends FormRequest
{
    /** Solo un usuario autenticado puede confirmar un pedido. */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Normaliza identidad, contacto y pago antes de ejecutar las reglas. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => trim((string) $this->input('customer_name')),
            'customer_phone' => trim((string) $this->input('customer_phone')),
            'shipping_address' => trim((string) $this->input('shipping_address')),
            'card_holder' => $this->filled('card_holder') ? trim((string) $this->input('card_holder')) : null,
            'paypal_email' => $this->filled('paypal_email')
                ? Str::lower(trim((string) $this->input('paypal_email')))
                : null,
            'card_number' => preg_replace('/\D+/', '', (string) $this->input('card_number')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', 'in:card,paypal'],
            'card_holder' => ['nullable', 'required_if:payment_method,card', 'string', 'min:3', 'max:120'],
            'card_number' => [
                'nullable',
                'required_if:payment_method,card',
                'digits_between:13,19',
                $this->validateCardNumber(...),
            ],
            'card_expiry' => [
                'nullable', 'required_if:payment_method,card', 'regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
                $this->validateCardExpiry(...),
            ],
            'card_cvv' => ['nullable', 'required_if:payment_method,card', 'digits_between:3,4'],
            'paypal_email' => ['nullable', 'required_if:payment_method,paypal', 'email:rfc', 'max:255'],
        ];
    }

    /** Rechaza números que no cumplen el control matemático de Luhn. */
    private function validateCardNumber(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && ! $this->passesLuhnCheck((string) $value)) {
            $fail('El número de tarjeta no supera la validación de seguridad.');
        }
    }

    /** Rechaza tarjetas cuya fecha MM/AA ya terminó. */
    private function validateCardExpiry(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value || ! preg_match('/^(\d{2})\/(\d{2})$/', (string) $value, $matches)) {
            return;
        }

        $expiry = now()->setYear(2000 + (int) $matches[2])->setMonth((int) $matches[1])->endOfMonth();

        if ($expiry->isPast()) {
            $fail('La fecha de vencimiento de la tarjeta ya pasó.');
        }
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Digite un teléfono válido.',
            'card_number.digits_between' => 'La tarjeta debe contener entre 13 y 19 dígitos.',
            'card_expiry.regex' => 'Use el formato MM/AA para el vencimiento.',
            'payment_method.in' => 'Seleccione tarjeta o PayPal.',
        ];
    }

    /**
     * Aplica el algoritmo de Luhn usado para detectar números de tarjeta mal digitados.
     * Esta comprobación no sustituye la autorización de una pasarela bancaria real.
     */
    private function passesLuhnCheck(string $number): bool
    {
        $sum = 0;
        $shouldDouble = false;

        for ($position = strlen($number) - 1; $position >= 0; $position--) {
            $digit = (int) $number[$position];

            if ($shouldDouble) {
                $digit *= 2;
                $digit = $digit > 9 ? $digit - 9 : $digit;
            }

            $sum += $digit;
            $shouldDouble = ! $shouldDouble;
        }

        return $sum > 0 && $sum % 10 === 0;
    }
}
