@extends('layouts.app')
@section('title', 'Factura '.$order->order_number)

@section('content')
<div class="container section-space">
    <div class="invoice web-invoice">
        <header class="invoice-header"><div><span class="brand-mark">OT</span><h1 class="h3 mt-3">Factura de compra</h1><p class="mb-0">Origen Tico · Costa Rica</p></div><div class="text-md-end"><span class="eyebrow">Pedido</span><strong>{{ $order->order_number }}</strong><span>{{ $order->purchased_at->format('d/m/Y H:i') }}</span></div></header>
        <div class="row g-4 invoice-parties"><div class="col-md-6"><h2 class="h6 text-uppercase">Facturado a</h2><strong>{{ $order->customer_name }}</strong><span>ID usuario: {{ $order->user_id }}</span><span>{{ $order->customer_email }}</span><span>{{ $order->customer_phone }}</span></div><div class="col-md-6"><h2 class="h6 text-uppercase">Entrega</h2><span>{{ $order->shipping_address }}</span><span>Seguimiento: {{ $order->tracking_number }}</span></div></div>
        <div class="table-responsive"><table class="table invoice-table"><thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td class="text-center">{{ $item->quantity }}</td><td class="text-end">₡{{ number_format($item->unit_price, 0, ',', '.') }}</td><td class="text-end">₡{{ number_format($item->line_total, 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div>
        <div class="invoice-totals"><dl><div><dt>Subtotal</dt><dd>₡{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div><div><dt>IVA (13%)</dt><dd>₡{{ number_format($order->tax, 0, ',', '.') }}</dd></div><div><dt>Envío</dt><dd>₡{{ number_format($order->shipping, 0, ',', '.') }}</dd></div><div class="summary-total"><dt>Total pagado</dt><dd>₡{{ number_format($order->total, 0, ',', '.') }}</dd></div></dl></div>
        <div class="invoice-actions"><a class="btn btn-primary" href="{{ route('orders.invoice.pdf', $order) }}">Descargar PDF</a><a class="btn btn-link" href="{{ route('profile.show') }}">Volver al perfil</a></div>
    </div>
</div>
@endsection
