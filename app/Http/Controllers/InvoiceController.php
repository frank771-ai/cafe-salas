<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        return view('orders.invoice', ['order' => $order->load('items', 'payment', 'user')]);
    }

    public function pdf(Request $request, Order $order, PdfService $pdf)
    {
        $this->authorizeOrder($request, $order);

        return $pdf->download(
            'pdf.invoice',
            ['order' => $order->load('items', 'payment', 'user')],
            "factura-{$order->order_number}.pdf",
        );
    }

    /** Impide que un cliente consulte facturas pertenecientes a otra cuenta. */
    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($request->user()->id === $order->user_id || $request->user()->is_admin, 403);
    }
}
