<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kapasitas Mesin</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        h1 { font-size: 14px; margin-bottom: 4px; }
        h2 { font-size: 11px; color: #666; margin-bottom: 12px; font-weight: normal; }
        .info { margin-bottom: 12px; font-size: 10px; }
        .info span { margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #0c1a18; color: #fff; padding: 6px 4px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 5px 4px; border-bottom: 1px solid #e0e0e0; font-size: 10px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .overload { background: #fee2e2; color: #991b1b; font-weight: bold; }
        .optimal { color: #065f46; }
        .footer { margin-top: 16px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Kapasitas Mesin</h1>
    <h2>Roll Forming 41x41 - {{ \Carbon\Carbon::create(null, $month, 1)->format('F') }} {{ $year }}</h2>

    @if($machine)
    <div class="info">
        <span>Mesin: {{ $machine->machine_name }}</span>
        <span>Kapasitas/Hari: {{ number_format($dailyCapacity, 2) }} ea</span>
        <span>Cycle Time: {{ $machine->cycle_time_sec }}s</span>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th class="text-right">Kapasitas</th>
                <th class="text-right">Target</th>
                <th class="text-right">Aktual</th>
                <th class="text-right">Sisa</th>
                <th class="text-right">Utilisasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyData as $day)
            <tr>
                <td>{{ $day['date']->format('d/m/Y') }}</td>
                <td>{{ $day['date']->format('l') }}</td>
                <td class="text-right">{{ number_format($dailyCapacity, 0) }}</td>
                <td class="text-right">{{ $day['target'] }}</td>
                <td class="text-right">{{ $day['actual'] }}</td>
                <td class="text-right {{ $day['balance'] < 0 ? 'overload' : '' }}">{{ number_format($day['balance'], 0) }}</td>
                <td class="text-right">{{ $day['utilization'] }}%</td>
                <td>{{ $day['utilization'] > 100 ? 'OVERLOAD' : ($day['utilization'] >= 70 ? 'OPTIMAL' : ($day['utilization'] > 0 ? 'UNDERLOAD' : 'IDLE')) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Dicetak: {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
