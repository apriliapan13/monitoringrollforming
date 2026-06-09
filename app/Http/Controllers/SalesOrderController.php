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
            'kg_batch' => 'nullable|numeric|min:0',
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

        $estimatedDays = $salesOrder->leadtime_days;

        $estimatedHours = 0;

        if ($machine && $machine->cycle_time_sec > 0) {
            $estimatedHours = round(
                ($salesOrder->quantity * $machine->cycle_time_sec) / 3600,
                1
            );
        }

        $targetPerDay = 0;

        if ($estimatedDays > 0) {
            $targetPerDay = ceil($salesOrder->quantity / $estimatedDays);
        }

        return view(
            'sales-orders.show',
            compact(
                'salesOrder',
                'machine',
                'estimatedDays',
                'estimatedHours',
                'targetPerDay'
            )
        );
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
            'kg_batch' => 'nullable|numeric|min:0',
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

    private function regenerateAllSchedules()
    {
        DB::transaction(function () {

            DailyTarget::query()->delete();

            $machine = MachineSetting::first();
            $machineCapacity = (int) ($machine?->daily_capacity ?? 312);

            $orders = SalesOrder::orderBy('deadline', 'asc')
                ->orderBy('order_date', 'asc')
                ->get();

            $dailyUsage = [];

            foreach ($orders as $order) {

                $remainingQty = $order->quantity;

                $currentDate = Carbon::parse($order->deadline);

                while (!$this->isWorkingDay($currentDate)) {
                    $currentDate = $this->previousWorkingDay($currentDate);
                }

                while ($remainingQty > 0) {

                    if ($currentDate->lt(Carbon::parse($order->order_date))) {
                        break;
                    }

                    $dateKey = $currentDate->format('Y-m-d');

                    if (!isset($dailyUsage[$dateKey])) {
                        $dailyUsage[$dateKey] = 0;
                    }

                    $availableCapacity =
                        $machineCapacity - $dailyUsage[$dateKey];

                    if ($availableCapacity > 0) {

                        $allocatedQty = min(
                            $remainingQty,
                            $availableCapacity
                        );

                        DailyTarget::create([
                            'sales_order_id' => $order->id,
                            'target_date' => $currentDate->copy(),
                            'target_qty' => $allocatedQty,
                            'created_by' => auth()->id(),
                        ]);

                        $dailyUsage[$dateKey] += $allocatedQty;
                        $remainingQty -= $allocatedQty;
                    }

                    $currentDate = $this->previousWorkingDay($currentDate);
                }
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
}
