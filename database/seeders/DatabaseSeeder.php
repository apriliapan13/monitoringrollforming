<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MachineSetting;
use App\Models\SalesOrder;
use App\Models\DailyTarget;
use App\Models\ActualProduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@rfmonitor.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $ppc = User::create([
            'name' => 'PPC Staff',
            'email' => 'ppc@rfmonitor.com',
            'password' => Hash::make('password'),
            'role' => 'ppc',
        ]);

        $spv = User::create([
            'name' => 'Supervisor Cell 3',
            'email' => 'spv@rfmonitor.com',
            'password' => Hash::make('password'),
            'role' => 'spv',
        ]);

        $korlap = User::create([
            'name' => 'Korlap Cell 3',
            'email' => 'korlap@rfmonitor.com',
            'password' => Hash::make('password'),
            'role' => 'korlap',
        ]);

        MachineSetting::create([
            'product_name' => 'Straight Single Solid Channel 41x41/41x21xL3000',
            'machine_name' => 'Roll Forming 3',
            'shift_count' => 1,
            'work_time_sec' => 28800,
            'allowance_time_sec' => 3600,
            'changeover_time_sec' => 4320,
            'cycle_time_sec' => 67,
            'uptime_percentage' => 72.50,
            'man_power' => 2,
            'cost_man_hour' => 76267,
        ]);

        $executives = ['Ferry', 'Eliza', 'Kokom', 'Kuma'];
        $soData = [
            ['0608/P/DEC/01/2025', null, 2, '2025-12-28', '2026-01-05', '2026-01-05', 'ON TIME'],
            ['0614/P/DEC/01/2025', 'Eliza', 10, '2026-01-04', '2026-01-06', '2026-01-06', 'ON TIME'],
            ['0606/P/DEC/01/2025', 'Ferry', 900, '2026-01-07', '2026-01-13', '2026-01-13', 'ON TIME'],
            ['0007/P/JAN/01/2026', 'Ferry', 400, '2026-01-09', '2026-01-21', '2026-01-21', 'ON TIME'],
            ['0017/P/JAN/01/2026', 'Ferry', 840, '2026-01-09', '2026-02-04', '2026-02-04', 'ON TIME'],
            ['0013/P/JAN/01/2026', 'Eliza', 200, '2026-01-14', '2026-01-26', '2026-01-27', 'LATE'],
            ['0016/P/JAN/01/2026', 'Eliza', 200, '2026-01-14', '2026-01-26', '2026-01-28', 'LATE'],
            ['0033/P/JAN/01/2026', 'Ferry', 300, '2026-01-14', '2026-01-19', '2026-01-15', 'ON TIME'],
            ['0057/P/JAN/01/2026', 'Kokom', 50, '2026-02-03', '2026-02-04', '2026-02-04', 'ON TIME'],
            ['0068/P/FEB/01/2026', 'Ferry', 150, '2026-02-05', '2026-02-06', '2026-02-06', 'FINISH'],
            ['0073/P/FEB/01/2026', 'Eliza', 2780, '2026-02-05', '2026-03-12', null, 'ON PROCESS'],
            ['0013B/P/FEB/01/2026', 'Kokom', 50, '2026-02-05', '2026-02-26', null, 'ON PROCESS'],
        ];

        foreach ($soData as $data) {
            $so = SalesOrder::create([
                'so_number' => $data[0],
                'project_executive' => $data[1],
                'description' => 'Channel',
                'length' => 3000,
                'quantity' => $data[2],
                'order_date' => $data[3],
                'deadline' => $data[4],
                'finish_date' => $data[5],
                'cell' => '3',
                'status' => $data[6],
                'created_by' => $ppc->id,
            ]);

            if ($so->status !== 'ON PROCESS') {
                $totalQty = $so->quantity;
                $startDate = Carbon::parse($so->order_date);
                $endDate = $so->finish_date ? Carbon::parse($so->finish_date) : Carbon::parse($so->deadline);
                $days = max(1, $startDate->diffInDays($endDate));
                $perDay = ceil($totalQty / $days);

                for ($d = $startDate->copy(); $d->lte($endDate) && $totalQty > 0; $d->addDay()) {
                    if ($d->isWeekend()) continue;
                    $qty = min($perDay, $totalQty);

                    DailyTarget::create([
                        'sales_order_id' => $so->id,
                        'target_date' => $d->toDateString(),
                        'target_qty' => $qty,
                        'created_by' => $ppc->id,
                    ]);

                    ActualProduction::create([
                        'sales_order_id' => $so->id,
                        'production_date' => $d->toDateString(),
                        'actual_qty' => $qty,
                        'user_id' => $spv->id,
                    ]);

                    $totalQty -= $qty;
                }
            } else {
                $startDate = Carbon::parse($so->order_date);
                $today = Carbon::today();
                $totalProduced = 0;

                for ($d = $startDate->copy(); $d->lte($today) && $totalProduced < $so->quantity; $d->addDay()) {
                    if ($d->isWeekend()) continue;
                    $qty = min(rand(100, 200), $so->quantity - $totalProduced);
                    if ($qty <= 0) break;

                    DailyTarget::create([
                        'sales_order_id' => $so->id,
                        'target_date' => $d->toDateString(),
                        'target_qty' => min(200, $so->quantity - $totalProduced),
                        'created_by' => $ppc->id,
                    ]);

                    ActualProduction::create([
                        'sales_order_id' => $so->id,
                        'production_date' => $d->toDateString(),
                        'actual_qty' => $qty,
                        'user_id' => $spv->id,
                    ]);

                    $totalProduced += $qty;
                }
            }
        }
    }
}
