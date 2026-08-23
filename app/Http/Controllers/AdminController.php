<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;

/** Centraliza los indicadores de ventas, inventario y gestión de pedidos. */
class AdminController extends Controller
{
    /** Construye el resumen administrativo del mes y alerta sobre inventario bajo. */
    public function index()
    {
        $monthStart = now()->startOfMonth();

        return view('admin.dashboard', [
            'orders' => Order::with('user', 'payment')->latest('purchased_at')->paginate(12),
            'monthlySales' => Order::where('purchased_at', '>=', $monthStart)->where('status', '!=', 'cancelled')->sum('total'),
            'monthlyOrders' => Order::where('purchased_at', '>=', $monthStart)->where('status', '!=', 'cancelled')->count(),
            'customers' => User::where('is_admin', false)->count(),
            'lowStock' => Product::where('stock', '<=', 5)->active()->orderBy('stock')->get(),
        ]);
    }

    /** Valida el nuevo estado y delega los cambios de pago e inventario al servicio. */
    public function updateStatus(Request $request, Order $order, OrderStatusService $statuses)
    {
        $validated = $request->validate(['status' => ['required', 'string', 'in:'.implode(',', Order::STATUSES)]]);
        $statuses->transition($order, $validated['status']);

        return back()->with('success', "Estado de {$order->order_number} actualizado.");
    }
}
