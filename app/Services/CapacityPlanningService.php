<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\MachineSetting;

class CapacityPlanningService
{
    public function generate()
    {
        // Ambil setting mesin
        $machine = MachineSetting::first();

        // Jika belum ada setting gunakan default
        $dailyCapacity = $machine
            ? $machine->daily_capacity
            : 311.64;

        // Ambil semua SO yang masih ON PROCESS
        // Urutkan berdasarkan deadline terdekat
        $salesOrders = SalesOrder::where('status', 'ON PROCESS')
            ->orderBy('deadline', 'asc')
            ->get();

        // Kelompokkan berdasarkan deadline
        $groupedOrders = $salesOrders->groupBy(function ($order) {
            return $order->deadline->format('Y-m-d');
        });

        return [
            'daily_capacity' => $dailyCapacity,
            'orders' => $groupedOrders,
        ];
    }
}