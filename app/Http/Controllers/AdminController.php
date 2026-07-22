<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;

/** Gestiona el tablero administrativo, sus métricas y los estados de pedidos. */
class AdminController extends Controller
{
    /** Reúne indicadores mensuales, pedidos recientes e inventario bajo. */
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

    /** Valida el nuevo estado contra la lista permitida antes de actualizarlo. */
    public function updateStatus(Request $request, Order $order, OrderStatusService $statuses)
    {
        $data = $request->validate(['status' => ['required', 'string', 'in:'.implode(',', Order::STATUSES)]]);
        $statuses->transition($order, $data['status']);

        return back()->with('success', "Estado de {$order->order_number} actualizado.");
    }
}
