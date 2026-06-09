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
            $orders = SalesOrder::orderBy('order_date', 'desc')->get()->map(function ($order) {
                $order->total_actual = $order->actualProductions()->sum('actual_qty');
                $order->achievement = $order->quantity > 0
                    ? round(($order->total_actual / $order->quantity) * 100, 1) : 0;
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
                    'utilization' => $dailyCapacity > 0 ? round(($targetQty / $dailyCapacity) * 100, 1) : 0,
                ];
            }
            $pdf = Pdf::loadView('exports.capacity-pdf', compact('dailyData', 'machine', 'dailyCapacity', 'month', 'year'));
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download('kapasitas-mesin-' . $month . '-' . $year . '.pdf');
        }

        abort(404);
    }

    public function exportCsv(Request $request)
    {
        $type = $request->get('type', 'monitoring');

        if ($type === 'monitoring') {
            $orders = SalesOrder::orderBy('order_date', 'desc')->get();
            $filename = 'monitoring-produksi-' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['No', 'SO Number', 'Executive', 'Description', 'Qty', 'Actual', 'Achievement %', 'Deadline', 'Status']);

                foreach ($orders as $i => $order) {
                    $totalActual = $order->actualProductions()->sum('actual_qty');
                    $achievement = $order->quantity > 0 ? round(($totalActual / $order->quantity) * 100, 1) : 0;
                    fputcsv($file, [
                        $i + 1,
                        $order->so_number,
                        $order->project_executive,
                        $order->description,
                        $order->quantity,
                        $totalActual,
                        $achievement . '%',
                        $order->deadline?->format('Y-m-d'),
                        $order->status,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'capacity') {
            $machine = MachineSetting::first();
            $dailyCapacity = $machine ? $machine->daily_capacity : 311.64;
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $filename = 'kapasitas-mesin-' . $month . '-' . $year . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($startDate, $endDate, $dailyCapacity) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Tanggal', 'Hari', 'Target', 'Aktual', 'Kapasitas', 'Sisa', 'Utilisasi %', 'Status']);

                for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                    $dateStr = $d->toDateString();
                    $targetQty = DailyTarget::where('target_date', $dateStr)->sum('target_qty');
                    $actualQty = ActualProduction::where('production_date', $dateStr)->sum('actual_qty');
                    $balance = $dailyCapacity - $targetQty;
                    $util = $dailyCapacity > 0 ? round(($targetQty / $dailyCapacity) * 100, 1) : 0;

                    $status = $d->isWeekend() ? 'WEEKEND' : ($util > 100 ? 'OVERLOAD' : ($util >= 70 ? 'OPTIMAL' : ($util > 0 ? 'UNDERLOAD' : 'IDLE')));

                    fputcsv($file, [
                        $d->format('Y-m-d'),
                        $d->format('l'),
                        $targetQty,
                        $actualQty,
                        round($dailyCapacity, 2),
                        round($balance, 2),
                        $util . '%',
                        $status,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        abort(404);
    }
}
