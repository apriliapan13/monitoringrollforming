<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\ActualProduction;
use App\Models\DailyTarget;
use App\Models\MachineSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $machine = MachineSetting::first();
        $dailyCapacity = $machine ? $machine->daily_capacity : 311.64;

        $totalOrders = SalesOrder::count();
        $activeOrders = SalesOrder::where('status', 'ON PROCESS')->count();
        $finishedOrders = SalesOrder::whereIn('status', ['FINISH', 'ON TIME'])->count();
        $lateOrders = SalesOrder::where('status', 'LATE')->count();

        $totalPlanQty = SalesOrder::sum('quantity');
        $totalActualQty = ActualProduction::sum('actual_qty');
        $avgAchievement = $totalPlanQty > 0 ? round(($totalActualQty / $totalPlanQty) * 100, 1) : 0;

        $today = Carbon::today();
        $todayTargets = DailyTarget::where('target_date', $today)->sum('target_qty');
        $todayActual = ActualProduction::where('production_date', $today)->sum('actual_qty');
        $todayLoad = $todayTargets;
        $todayRemaining = max(0, $dailyCapacity - $todayLoad);
        $todayUtilization = $dailyCapacity > 0 ? round(($todayLoad / $dailyCapacity) * 100, 1) : 0;

        if ($todayUtilization > 100) {
            $todayStatus = 'OVERLOAD';
        } elseif ($todayUtilization >= 70) {
            $todayStatus = 'OPTIMAL';
        } elseif ($todayUtilization > 0) {
            $todayStatus = 'UNDERLOAD';
        } else {
            $todayStatus = 'IDLE';
        }

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $weeklyData = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $dayTargets = DailyTarget::where('target_date', $d->toDateString())->sum('target_qty');
            $dayActual = ActualProduction::where('production_date', $d->toDateString())->sum('actual_qty');
            $weeklyData[] = [
                'date' => $d->format('D d/m'),
                'target' => $dayTargets,
                'actual' => $dayActual,
                'capacity' => $dailyCapacity,
                'utilization' => $dailyCapacity > 0 ? round(($dayTargets / $dailyCapacity) * 100, 1) : 0,
            ];
        }

        $executivePerformance = SalesOrder::selectRaw('project_executive, COUNT(*) as total, SUM(quantity) as total_qty')
            ->whereNotNull('project_executive')
            ->groupBy('project_executive')
            ->get()
            ->map(function ($exec) {
                $actualQty = ActualProduction::whereIn('sales_order_id',
                    SalesOrder::where('project_executive', $exec->project_executive)->pluck('id')
                )->sum('actual_qty');
                $exec->actual_qty = $actualQty;
                $exec->achievement = $exec->total_qty > 0 ? round(($actualQty / $exec->total_qty) * 100, 1) : 0;
                return $exec;
            });

        $recentOrders = SalesOrder::latest('order_date')->take(5)->get();

        return view('dashboard', compact(
            'machine', 'dailyCapacity', 'totalOrders', 'activeOrders',
            'finishedOrders', 'lateOrders', 'totalPlanQty', 'totalActualQty',
            'avgAchievement', 'todayTargets', 'todayActual', 'todayLoad',
            'todayRemaining', 'todayUtilization', 'todayStatus', 'weeklyData',
            'executivePerformance', 'recentOrders'
        ));
    }
}
