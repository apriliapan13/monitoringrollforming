<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\ActualProduction;
use App\Models\DailyTarget;
use App\Models\MachineSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $type = $request->get('type', 'monitoring');

        if ($type === 'monitoring') {

            $query = SalesOrder::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month')) {
                $query->whereMonth('order_date', $request->month)
                      ->orderBy('order_date', 'asc');
            } else {
                $query->orderBy('created_at', 'asc');
            }

            $orders = $query->get()->map(function ($order) {
                $order->total_actual = $order->actualProductions()->sum('actual_qty');
                $order->achievement = $order->quantity > 0
                    ? round(($order->total_actual / $order->quantity) * 100, 1)
                    : 0;
                $order->remaining = max(0, $order->quantity - $order->total_actual);
                return $order;
            });

            $pdf = Pdf::loadView('exports.monitoring-pdf', compact('orders'));
            return $pdf->download('monitoring-produksi-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($type === 'capacity') {
            $machine = MachineSetting::first();
            $dailyCapacity = $machine ? $machine->daily_capacity : 311.64;
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $dailyData = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dateStr = $d->toDateString();
                $targetQty = DailyTarget::where('target_date', $dateStr)->sum('target_qty');
                $actualQty = ActualProduction::where('production_date', $dateStr)->sum('actual_qty');

                $dailyData[] = [
                    'date' => $d->copy(),
                    'target' => $targetQty,
                    'actual' => $actualQty,
                    'balance' => round($dailyCapacity - $targetQty, 2),
                    'utilization' => $dailyCapacity > 0
                        ? round(($targetQty / $dailyCapacity) * 100, 1)
                        : 0,
                ];
            }

            $pdf = Pdf::loadView('exports.capacity-pdf',
                compact('dailyData', 'machine', 'dailyCapacity', 'month', 'year'));

            $pdf->setPaper('A4', 'landscape');

            return $pdf->download('kapasitas-mesin-' . $month . '-' . $year . '.pdf');
        }

        abort(404);
    }

    public function exportCsv(Request $request)
    {
        $type = $request->get('type', 'monitoring');

        if ($type === 'monitoring') {

            $query = SalesOrder::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month')) {
                $query->whereMonth('order_date', $request->month)
                      ->orderBy('order_date', 'asc');
            } else {
                $query->orderBy('created_at', 'asc');
            }

            $orders = $query->get();

            $filename = 'monitoring-produksi-' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($orders) {

                $file = fopen('php://output', 'w');

               fputcsv($file, [
    'No',
    'Bulan',
    'Tanggal',
    'SO Number',
    'Executive',
    'Description',
    'Dimension',
    'Qty',
    'Actual',
    'Achievement %',
    'Deadline',
    'Status'
]);

                foreach ($orders as $i => $order) {

                    $actual = $order->actualProductions()->sum('actual_qty');

                    $achievement = $order->quantity > 0
                        ? round(($actual / $order->quantity) * 100, 1)
                        : 0;

                  fputcsv($file, [
    $i + 1,
    optional($order->order_date)->translatedFormat('F'),
    optional($order->order_date)->format('d'),
    $order->so_number,
    $order->project_executive,
    $order->description,
    $order->size,
    $order->quantity,
    $actual,
    $achievement . '%',
    optional($order->deadline)->format('Y-m-d'),
    $order->status,
]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Capacity export tetap seperti sebelumnya
        return abort(404);
    }
}
