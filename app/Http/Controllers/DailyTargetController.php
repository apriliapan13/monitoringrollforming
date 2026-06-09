<?php

namespace App\Http\Controllers;

use App\Models\DailyTarget;
use App\Models\SalesOrder;
use App\Models\MachineSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyTargetController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $machine = MachineSetting::first();
        $dailyCapacity = $machine ? $machine->daily_capacity : 311.64;

        $targets = DailyTarget::with('salesOrder')
            ->where('target_date', $date)
            ->get();

        $totalTarget = $targets->sum('target_qty');
        $remainingCapacity = max(0, $dailyCapacity - $totalTarget);
        $utilization = $dailyCapacity > 0 ? round(($totalTarget / $dailyCapacity) * 100, 1) : 0;

        $activeOrders = SalesOrder::where('status', 'ON PROCESS')->get();

        return view('daily-targets.index', compact(
            'targets', 'date', 'dailyCapacity', 'totalTarget',
            'remainingCapacity', 'utilization', 'activeOrders'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'target_date' => 'required|date',
            'target_qty' => 'required|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();

        $existing = DailyTarget::where('sales_order_id', $validated['sales_order_id'])
            ->where('target_date', $validated['target_date'])
            ->first();

        if ($existing) {
            $existing->update(['target_qty' => $validated['target_qty']]);
        } else {
            DailyTarget::create($validated);
        }

        return redirect()->route('daily-targets.index', ['date' => $validated['target_date']])
            ->with('success', 'Target harian berhasil disimpan.');
    }

    public function destroy(DailyTarget $dailyTarget)
    {
        $date = $dailyTarget->target_date->toDateString();
        $dailyTarget->delete();
        return redirect()->route('daily-targets.index', ['date' => $date])
            ->with('success', 'Target berhasil dihapus.');
    }
}