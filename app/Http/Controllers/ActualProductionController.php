<?php

namespace App\Http\Controllers;

use App\Models\ActualProduction;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActualProductionController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $actuals = ActualProduction::with(['salesOrder', 'user'])
            ->where('production_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalActual = $actuals->sum('actual_qty');
        $activeOrders = SalesOrder::where('status', 'ON PROCESS')->get();

        return view('actual-production.index', compact('actuals', 'date', 'totalActual', 'activeOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'production_date' => 'required|date',
            'actual_qty' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
        ]);

        $validated['user_id'] = auth()->id();

        ActualProduction::create($validated);

        $so = SalesOrder::find($validated['sales_order_id']);
        if ($so) {
            $totalActual = $so->actualProductions()->sum('actual_qty');
            if ($totalActual >= $so->quantity) {
                $finishDate = Carbon::parse($validated['production_date']);
                $status = $finishDate->lte($so->deadline) ? 'ON TIME' : 'LATE';
                $so->update([
                    'status' => $status,
                    'finish_date' => $finishDate,
                ]);
            }
        }

        return redirect()->route('actual-production.index', ['date' => $validated['production_date']])
            ->with('success', 'Data aktual produksi berhasil ditambahkan.');
    }

    public function destroy(ActualProduction $actualProduction)
    {
        $date = $actualProduction->production_date->toDateString();
        $actualProduction->delete();
        return redirect()->route('actual-production.index', ['date' => $date])
            ->with('success', 'Data aktual berhasil dihapus.');
    }
}
