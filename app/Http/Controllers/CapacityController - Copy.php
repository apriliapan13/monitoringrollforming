<?php

namespace App\Http\Controllers;

use App\Models\MachineSetting;
use App\Models\DailyTarget;
use App\Models\ActualProduction;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CapacityController extends Controller
{
    public function index(Request $request)
    {
        $machine = MachineSetting::first();
        $dailyCapacity = $machine ? $machine->daily_capacity : 311.64;

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $dailyData = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dateStr = $d->toDateString();
            $isWeekend = $d->isWeekend();

            $targetQty = DailyTarget::where('target_date', $dateStr)->sum('target_qty');
            $actualQty = ActualProduction::where('production_date', $dateStr)->sum('actual_qty');
            $balance = $dailyCapacity - $targetQty;
            $utilization = $dailyCapacity > 0 ? round(($targetQty / $dailyCapacity) * 100, 1) : 0;

            if ($isWeekend) {
                $status = 'WEEKEND';
            } elseif ($utilization > 100) {
                $status = 'OVERLOAD';
            } elseif ($utilization >= 70) {
                $status = 'OPTIMAL';
            } elseif ($utilization > 0) {
                $status = 'UNDERLOAD';
            } else {
                $status = 'IDLE';
            }

            $orders = DailyTarget::with('salesOrder')
                ->where('target_date', $dateStr)
                ->get();

            $dailyData[] = [
                'date' => $d->copy(),
                'date_str' => $dateStr,
                'day_name' => $d->format('D'),
                'day_num' => $d->day,
                'is_weekend' => $isWeekend,
                'target_qty' => $targetQty,
                'actual_qty' => $actualQty,
                'balance' => round($balance, 2),
                'utilization' => $utilization,
                'status' => $status,
                'orders' => $orders,
            ];
        }

        $weeks = [];
        $currentWeek = [];
        $weekNum = 1;
        foreach ($dailyData as $day) {
            $currentWeek[] = $day;
            if ($day['date']->isSunday() || $day === end($dailyData)) {
                $weeks[$weekNum] = $currentWeek;
                $currentWeek = [];
                $weekNum++;
            }
        }

        $monthlyTarget = DailyTarget::whereBetween('target_date', [$startDate, $endDate])->sum('target_qty');
        $monthlyActual = ActualProduction::whereBetween('production_date', [$startDate, $endDate])->sum('actual_qty');
        $workDays = collect($dailyData)->where('is_weekend', false)->count();
        $monthlyCapacity = round($dailyCapacity * $workDays, 2);
        $monthlyUtilization = $monthlyCapacity > 0 ? round(($monthlyTarget / $monthlyCapacity) * 100, 1) : 0;

        return view('capacity.index', compact(
            'machine', 'dailyCapacity', 'dailyData', 'weeks',
            'month', 'year', 'startDate', 'endDate',
            'monthlyTarget', 'monthlyActual', 'monthlyCapacity',
            'monthlyUtilization', 'workDays'
        ));
    }

    public function settings()
    {
        $machine = MachineSetting::first();
        return view('capacity.settings', compact('machine'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'machine_name' => 'required|string|max:100',
            'shift_count' => 'required|integer|min:1|max:3',
            'work_time_sec' => 'required|integer|min:1',
            'allowance_time_sec' => 'required|integer|min:0',
            'changeover_time_sec' => 'required|integer|min:0',
            'cycle_time_sec' => 'required|integer|min:1',
            'uptime_percentage' => 'required|numeric|min:0|max:100',
            'man_power' => 'required|integer|min:1',
            'cost_man_hour' => 'required|numeric|min:0',
        ]);

        $machine = MachineSetting::first();
        if ($machine) {
            $machine->update($validated);
        } else {
            MachineSetting::create($validated);
        }

        return redirect()->route('capacity.settings')
            ->with('success', 'Pengaturan mesin berhasil diperbarui.');
    }
}
