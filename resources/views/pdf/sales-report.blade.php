<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    {{-- Estilos simples y locales para máxima compatibilidad con Dompdf. --}}
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; color: #17251d; font-size: 9px; }
        h1 { font-size: 19px; color: #1f5c3e; margin: 0 0 4px; }
        .subtitle { color: #66736b; margin-bottom: 20px; }
        .summary { background: #f1eadc; padding: 12px; margin-bottom: 16px; }
        .summary strong { font-size: 14px; }
        .report { width: 100%; border-collapse: collapse; }
        .report th { background: #1f5c3e; color: #fff; text-align: left; padding: 7px; }
        .report td { padding: 7px; border-bottom: 1px solid #d9dedb; vertical-align: top; }
        .right { text-align: right; }
        .footer { margin-top: 20px; color: #66736b; font-size: 8px; }
        .empty { padding: 30px; text-align: center; background: #f4f0e8; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">{{ $subtitle }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
    <div class="summary">
        Pedidos incluidos: <strong>{{ $orders->count() }}</strong> &nbsp;&nbsp;
        Total vendido: <strong>₡{{ number_format($orders->sum('total'), 0, ',', '.') }}</strong> &nbsp;&nbsp;
        IVA recaudado: <strong>₡{{ number_format($orders->sum('tax'), 0, ',', '.') }}</strong>
    </div>

    @if ($orders->isEmpty())
        <div class="empty">No hay ventas para los criterios seleccionados.</div>
    @else
        <table class="report">
            <thead>
                <tr><th>Pedido</th><th>Fecha</th><th>ID / cliente</th><th>Productos</th><th>Pago</th><th class="right">Subtotal</th><th class="right">IVA</th><th class="right">Envío</th><th class="right">Total</th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->purchased_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->user_id }}<br>{{ $order->customer_name }}<br>{{ $order->customer_email }}</td>
                        <td>
                            @foreach ($order->items as $orderLine)
                                {{ $orderLine->quantity }} x {{ $orderLine->product_name }}@if (! $loop->last)<br>@endif
                            @endforeach
                        </td>
                        <td>{{ $order->payment->method === 'card' ? 'Tarjeta '.$order->payment->last_four : 'PayPal' }}<br>{{ $order->payment->provider_reference }}</td>
                        <td class="right">₡{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="right">₡{{ number_format($order->tax, 0, ',', '.') }}</td>
                        <td class="right">₡{{ number_format($order->shipping, 0, ',', '.') }}</td>
                        <td class="right"><strong>₡{{ number_format($order->total, 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Café Salas · Reporte administrativo confidencial · Los pedidos cancelados se excluyen de los totales.</p>
</body>
</html>
