@extends('layouts.app')
@section('title', 'Pedido confirmado')

@section('content')
<div class="container section-space">
    <div class="confirmation-card"><div class="confirmation-check" aria-hidden="true">✓</div><span class="eyebrow">Pago aprobado</span><h1>¡Gracias por su compra!</h1><p>Su pedido <strong>{{ $order->order_number }}</strong> ya está confirmado.</p><div class="tracking-box"><span>Número de seguimiento</span><strong>{{ $order->tracking_number }}</strong><small>Consérvelo para consultar su envío.</small></div><div class="row g-3 mt-4"><div class="col-md-4"><div class="fact-tile"><span>Fecha</span><strong>{{ $order->purchased_at->format('d/m/Y H:i') }}</strong></div></div><div class="col-md-4"><div class="fact-tile"><span>Método</span><strong>{{ $order->payment->method === 'card' ? 'Tarjeta •••• '.$order->payment->last_four : 'PayPal' }}</strong></div></div><div class="col-md-4"><div class="fact-tile"><span>Total</span><strong>₡{{ number_format($order->total, 0, ',', '.') }}</strong></div></div></div><div class="d-flex flex-wrap justify-content-center gap-3 mt-4"><a class="btn btn-primary" href="{{ route('orders.invoice', $order) }}">Ver factura</a><a class="btn btn-outline-primary" href="{{ route('orders.invoice.pdf', $order) }}">Descargar PDF</a><a class="btn btn-link" href="{{ route('profile.show') }}">Ir a mi perfil</a></div></div>
</div>
@endsection
