<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query();

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Bulan
        if ($request->filled('month')) {

            $query->whereMonth('order_date', $request->month);

            // Jika memilih bulan, urutkan berdasarkan tanggal
            $query->orderBy('order_date', 'asc');

        } else {

            // Tampilan awal tetap mengikuti urutan SO masuk
            $query->orderBy('created_at', 'asc');

        }

        $orders = $query->get()->map(function ($order) {

            $order->total_actual = $order->actualProductions()->sum('actual_qty');
            $order->total_target = $order->dailyTargets()->sum('target_qty');

            $order->achievement = $order->quantity > 0
                ? round(($order->total_actual / $order->quantity) * 100, 1)
                : 0;

            $order->remaining = max(0, $order->quantity - $order->total_actual);

            return $order;
        });

        return view('monitoring.index', compact('orders'));
    }
}
