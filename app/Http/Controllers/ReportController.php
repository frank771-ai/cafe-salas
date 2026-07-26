<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\PdfService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Genera reportes administrativos de ventas por periodo o por cliente. */
class ReportController extends Controller
{
    /** Muestra los parámetros disponibles para ambos tipos de reporte. */
    public function index()
    {
        return view('admin.reports', [
            'customers' => User::where('is_admin', false)->whereHas('orders')->orderBy('name')->get(),
            'defaultMonth' => now()->format('Y-m'),
        ]);
    }

    /** Descarga las ventas no canceladas del mes solicitado. */
    public function monthly(Request $request, PdfService $pdf)
    {
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $start = CarbonImmutable::createFromFormat('!Y-m', $validated['month'])->startOfMonth();
        $end = $start->endOfMonth();
        $orders = Order::with('user', 'items', 'payment')
            ->whereBetween('purchased_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->orderBy('purchased_at')
            ->get();

        return $pdf->download('pdf.sales-report', [
            'title' => 'Reporte mensual de ventas - '.$start->translatedFormat('F Y'),
            'subtitle' => 'Periodo: '.$start->format('d/m/Y').' al '.$end->format('d/m/Y'),
            'orders' => $orders,
        ], 'ventas-'.$validated['month'].'.pdf', 'landscape');
    }

    /** Descarga el historial de ventas no canceladas de un cliente. */
    public function customer(Request $request, PdfService $pdf)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('is_admin', 0)],
        ]);
        $customer = User::findOrFail($validated['user_id']);
        $orders = $customer->orders()->with('items', 'payment')
            ->where('status', '!=', 'cancelled')
            ->orderBy('purchased_at')
            ->get();

        return $pdf->download('pdf.sales-report', [
            'title' => 'Reporte de ventas por cliente',
            'subtitle' => $customer->name.' - '.$customer->email,
            'orders' => $orders,
        ], 'ventas-cliente-'.$customer->id.'.pdf', 'landscape');
    }
}
