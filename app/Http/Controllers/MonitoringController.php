<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\ActualProduction;
use App\Models\DailyTarget;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('order_date', 'desc')->get()->map(function ($order) {
            $order->total_actual = $order->actualProductions()->sum('actual_qty');
            $order->total_target = $order->dailyTargets()->sum('target_qty');
            $order->achievement = $order->quantity > 0
                ? round(($order->total_actual / $order->quantity) * 100, 1) : 0;
            $order->remaining = max(0, $order->quantity - $order->total_actual);
            return $order;
        });

        return view('monitoring.index', compact('orders'));
    }
}
