<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monitoring Produksi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        h2 { font-size: 12px; color: #666; margin-bottom: 16px; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #0c1a18; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 6px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e3a8a; }
        .footer { margin-top: 20px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Monitoring Produksi</h1>
    <h2>Roll Forming 41x41 - Cell 3 | Dicetak: {{ now()->format('d M Y H:i') }}</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. SO</th>
                <th>Executive</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Aktual</th>
                <th class="text-right">Capaian</th>
                <th>Deadline</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $i => $order)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $order->so_number }}</td>
                <td>{{ $order->project_executive ?? '-' }}</td>
                <td class="text-right">{{ number_format($order->quantity) }}</td>
                <td class="text-right">{{ number_format($order->total_actual) }}</td>
                <td class="text-right">{{ $order->achievement }}%</td>
                <td>{{ $order->deadline?->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ in_array($order->status, ['ON TIME','FINISH']) ? 'badge-success' : ($order->status === 'LATE' ? 'badge-danger' : 'badge-info') }}">{{ $order->status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Sistem Monitoring Kapasitas Mesin RF 41x41</div>
</body>
</html>
