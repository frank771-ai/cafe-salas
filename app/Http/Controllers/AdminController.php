<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth();

        return view('admin.dashboard', [
            'orders' => Order::with('user', 'payment')->latest('purchased_at')->paginate(12),
            'monthlySales' => Order::where('purchased_at', '>=', $monthStart)->where('status', '!=', 'cancelled')->sum('total'),
            'monthlyOrders' => Order::where('purchased_at', '>=', $monthStart)->count(),
            'customers' => User::where('is_admin', false)->count(),
            'lowStock' => Product::where('stock', '<=', 5)->active()->orderBy('stock')->get(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);
        $order->update($data);

        return back()->with('success', "Estado de {$order->order_number} actualizado.");
    }
}
