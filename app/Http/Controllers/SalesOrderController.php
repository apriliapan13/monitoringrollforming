<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\MachineSetting;
use App\Models\DailyTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('so_number', 'like', '%' . $request->search . '%')
                    ->orWhere('project_executive', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(15);

        return view('sales-orders.index', compact('orders'));
    }

    public function create()
    {
        return view('sales-orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|unique:sales_orders',
            'project_executive' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'batch' => 'nullable|string|max:50',
            'size' => 'required|string|in:41X41,41X21',
            'length' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'order_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:order_date',
            'cell' => 'required|in:1,2,3',
            'comment' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'ON PROCESS';

        SalesOrder::create($validated);
        $this->regenerateAllSchedules();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales Order berhasil ditambahkan.');
    }

    public function show(SalesOrder $salesOrder)
{
    $salesOrder->load(['dailyTargets', 'actualProductions']);

    $machine = MachineSetting::first();

    // Tanggal mulai produksi mengikuti jam input SO
    $startDate = $salesOrder->order_date->copy();

    if ($salesOrder->created_at && $salesOrder->created_at->hour >= 13) {
        $startDate->addDay();

        // Jika jatuh di Sabtu/Minggu, geser ke hari kerja berikutnya
        while ($startDate->isSaturday() || $startDate->isSunday()) {
            $startDate->addDay();
        }
    }

 // Estimasi hari dihitung dari tanggal mulai produksi
// Hari mulai ikut dihitung
$estimatedDays = $startDate->diffInDays($salesOrder->deadline) + 1;
    $estimatedHours = 0;

    if ($machine && $machine->cycle_time_sec > 0) {
        $estimatedHours = round(($salesOrder->quantity * $machine->cycle_time_sec) / 3600, 1);
    }

    $targetPerDay = $estimatedDays > 0
        ? ceil($salesOrder->quantity / $estimatedDays)
        : $salesOrder->quantity;

    return view('sales-orders.show', compact(
        'salesOrder',
        'machine',
        'estimatedDays',
        'estimatedHours',
        'targetPerDay'
    ));
}
    public function edit(SalesOrder $salesOrder)
    {
        return view('sales-orders.edit', compact('salesOrder'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'so_number' => 'required|string|unique:sales_orders,so_number,' . $salesOrder->id,
            'project_executive' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'batch' => 'nullable|string|max:50',
            'size' => 'required|string|in:41X41,41X21',
            'length' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'order_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:order_date',
            'finish_date' => 'nullable|date',
            'cell' => 'required|in:1,2,3',
            'comment' => 'nullable|string|max:500',
            'status' => 'required|in:ON PROCESS,FINISH,LATE,ON TIME,PENDING',
        ]);

        $salesOrder->update($validated);
        $this->regenerateAllSchedules();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales Order berhasil diperbarui.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        DailyTarget::where('sales_order_id', $salesOrder->id)->delete();

        $salesOrder->delete();
        $this->regenerateAllSchedules();

        return redirect()->route('sales-orders.index')
            ->with('success', 'Sales Order berhasil dihapus.');
    }

    private function getStartProductionDate(SalesOrder $order): Carbon
    {
        $startDate = Carbon::parse($order->order_date);

        if ($order->created_at && $order->created_at->hour >= 13) {
            $startDate->addDay();
        }

        $startDate = $startDate->startOfDay();

        while (!$this->isWorkingDay($startDate)) {
            $startDate = $this->nextWorkingDay($startDate);
        }

        return $startDate;
    }

    
    private function regenerateAllSchedules()
    {
        DB::transaction(function () {

            DailyTarget::query()->delete();

            $machine = MachineSetting::first();
            $machineCapacity = (int) ($machine?->daily_capacity ?? 312);

            $orders = SalesOrder::orderBy('deadline', 'asc')->get();

            $remaining = [];
            foreach ($orders as $o) {
                $remaining[$o->id] = $o->quantity;
            }

            $currentDate = $orders->count()
                ? $this->getStartProductionDate($orders->first())
                : now();

            while (collect($remaining)->sum() > 0) {

                if (!$this->isWorkingDay($currentDate)) {
                    $currentDate = $this->nextWorkingDay($currentDate);
                    continue;
                }

                $capacity = $machineCapacity;

                $activeOrders = $orders->filter(function ($o) use ($remaining, $currentDate) {
                    return $remaining[$o->id] > 0
                        && $this->getStartProductionDate($o)->lte($currentDate);
                });

                if ($activeOrders->isEmpty()) {
                    $currentDate = $this->nextWorkingDay($currentDate);
                    continue;
                }

                $groups = $activeOrders->groupBy(function ($o) {
                    return Carbon::parse($o->deadline)->format('Y-m-d');
                })->sortKeys();

                foreach ($groups as $group) {

                    if ($capacity <= 0) {
                        break;
                    }

$unfinished = $group->filter(fn($o) => $remaining[$o->id] > 0);

if ($unfinished->isEmpty()) {
    continue;
}
$totalNeed = 0;

foreach ($unfinished as $order) {
    $leadTime = max(1, $order->leadtime_days);
    $totalNeed += (int) ceil($order->quantity / $leadTime);
}
$availableCapacity = min($capacity, $totalNeed);

foreach ($unfinished as $order) {

    if ($capacity <= 0) {
        break;
    }

// Hitung target harian berdasarkan lead time
$leadTime = max(1, $order->leadtime_days);

$dailyTarget = (int) ceil($order->quantity / $leadTime);

// Hitung proporsi kebutuhan SO terhadap total kebutuhan group
$proportion = $totalNeed > 0
    ? ($dailyTarget / $totalNeed)
    : 0;

// Alokasi kapasitas berdasarkan proporsi
$plannedAllocation = (int) round($availableCapacity * $proportion);

$alloc = min(
    $plannedAllocation,
    $remaining[$order->id],
    $capacity
);

    if ($alloc <= 0) {
        continue;
    }

    $target = DailyTarget::firstOrNew([
        'sales_order_id' => $order->id,
        'target_date' => $currentDate->format('Y-m-d'),
    ]);

    $target->target_qty = ($target->target_qty ?? 0) + $alloc;
    $target->created_by = auth()->id();
    $target->save();

    $remaining[$order->id] -= $alloc;
    $capacity -= $alloc;
}
                }


                $currentDate = $this->nextWorkingDay($currentDate);
            }

            foreach ($orders as $order) {
                $order->status = now()->gt(Carbon::parse($order->deadline))
                    ? 'LATE'
                    : 'ON PROCESS';
                $order->save();
            }
        });
    }


    private function isWorkingDay(Carbon $date): bool
    {
        return !$date->isSaturday() && !$date->isSunday();
    }

    private function previousWorkingDay(Carbon $date): Carbon
    {
        $date = $date->copy();

        do {
            $date->subDay();
        } while ($date->isSaturday() || $date->isSunday());

        return $date;
    }

    private function nextWorkingDay(Carbon $date): Carbon
    {
        $date = $date->copy();

        do {
            $date->addDay();
        } while ($date->isSaturday() || $date->isSunday());

        return $date;
    }
}
