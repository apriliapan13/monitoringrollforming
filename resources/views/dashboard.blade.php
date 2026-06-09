@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Monitoring Kapasitas Mesin Roll Forming 41x41')

@section('content')
<div class="machine-status-banner banner-{{ strtolower($todayStatus) }}" id="status-banner">
    <div class="status-dot"></div>
    <div class="banner-text">
        <strong>Status Mesin: {{ $todayStatus }}</strong>
        <span>Utilisasi hari ini {{ $todayUtilization }}% &mdash; Beban {{ $todayLoad }} ea / Kapasitas {{ number_format($dailyCapacity, 0) }} ea</span>
    </div>
    <div style="margin-left:auto">
        <span class="status-indicator status-{{ strtolower($todayStatus) }}">{{ $todayStatus }}</span>
    </div>
</div>

<div class="stats-grid reveal">
    <div class="stat-card stat-accent" id="stat-total-order">
        <div class="stat-icon icon-accent"><i data-lucide="file-text"></i></div>
        <div class="stat-label">Total Order</div>
        <div class="stat-value font-mono">{{ $totalOrders }}</div>
        <div class="stat-sub">{{ $activeOrders }} aktif</div>
    </div>

    <div class="stat-card stat-info" id="stat-total-plan">
        <div class="stat-icon icon-info"><i data-lucide="target"></i></div>
        <div class="stat-label">Total Rencana</div>
        <div class="stat-value font-mono">{{ number_format($totalPlanQty) }}</div>
        <div class="stat-sub">ea target keseluruhan</div>
    </div>

    <div class="stat-card stat-success" id="stat-total-actual">
        <div class="stat-icon icon-success"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-label">Total Aktual</div>
        <div class="stat-value font-mono">{{ number_format($totalActualQty) }}</div>
        <div class="stat-sub">ea tercapai</div>
    </div>

    <div class="stat-card {{ $avgAchievement >= 90 ? 'stat-success' : ($avgAchievement >= 70 ? 'stat-warning' : 'stat-danger') }}" id="stat-achievement">
        <div class="stat-icon {{ $avgAchievement >= 90 ? 'icon-success' : ($avgAchievement >= 70 ? 'icon-warning' : 'icon-danger') }}"><i data-lucide="trending-up"></i></div>
        <div class="stat-label">Capaian Rata-rata</div>
        <div class="stat-value font-mono">{{ $avgAchievement }}%</div>
        <div class="stat-sub">achievement rate</div>
    </div>

    <div class="stat-card stat-accent" id="stat-capacity">
        <div class="stat-icon icon-accent"><i data-lucide="gauge"></i></div>
        <div class="stat-label">Kapasitas / Shift</div>
        <div class="stat-value font-mono">{{ number_format($dailyCapacity, 0) }}</div>
        <div class="stat-sub">ea per hari</div>
    </div>

    <div class="stat-card {{ $todayRemaining > 0 ? 'stat-success' : 'stat-danger' }}" id="stat-remaining">
        <div class="stat-icon {{ $todayRemaining > 0 ? 'icon-success' : 'icon-danger' }}"><i data-lucide="battery-charging"></i></div>
        <div class="stat-label">Sisa Kapasitas Hari Ini</div>
        <div class="stat-value font-mono">{{ number_format($todayRemaining, 0) }}</div>
        <div class="stat-sub">ea tersedia</div>
    </div>
</div>

<div class="grid-2 reveal" style="animation-delay: 100ms">
    <div class="card" id="chart-weekly">
        <div class="card-header">
            <span class="card-title">Performa Mingguan</span>
            <span class="badge badge-accent">Minggu Ini</span>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card" id="chart-utilization">
        <div class="card-header">
            <span class="card-title">Utilisasi Harian</span>
            <span class="badge badge-accent">Kapasitas vs Beban</span>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="utilizationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="grid-2 reveal" style="animation-delay: 200ms">
    <div class="card" id="table-executive">
        <div class="card-header">
            <span class="card-title">Performa Per Executive</span>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Executive</th>
                            <th class="text-right">Total SO</th>
                            <th class="text-right">Target</th>
                            <th class="text-right">Aktual</th>
                            <th class="text-right">Capaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($executivePerformance as $exec)
                        <tr>
                            <td><strong>{{ $exec->project_executive ?? '-' }}</strong></td>
                            <td class="text-right font-mono">{{ $exec->total }}</td>
                            <td class="text-right font-mono">{{ number_format($exec->total_qty) }}</td>
                            <td class="text-right font-mono">{{ number_format($exec->actual_qty) }}</td>
                            <td class="text-right">
                                <span class="badge {{ $exec->achievement >= 90 ? 'badge-success' : ($exec->achievement >= 70 ? 'badge-warning' : 'badge-danger') }}">{{ $exec->achievement }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" id="table-recent">
        <div class="card-header">
            <span class="card-title">Order Terbaru</span>
            <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. SO</th>
                            <th class="text-right">Qty</th>
                            <th>Deadline</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('sales-orders.show', $order) }}">
                                    <strong>{{ $order->so_number }}</strong>
                                </a>
                            </td>
                            <td class="text-right font-mono">{{ number_format($order->quantity) }}</td>
                            <td>{{ $order->deadline?->format('d M Y') }}</td>
                            <td>
                                @if($order->status === 'ON TIME' || $order->status === 'FINISH')
                                    <span class="badge badge-success">{{ $order->status }}</span>
                                @elseif($order->status === 'LATE')
                                    <span class="badge badge-danger">{{ $order->status }}</span>
                                @elseif($order->status === 'ON PROCESS')
                                    <span class="badge badge-info">{{ $order->status }}</span>
                                @else
                                    <span class="badge badge-muted">{{ $order->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card reveal" style="animation-delay: 300ms" id="deadline-overview">
    <div class="card-header">
        <span class="card-title">Status Deadline</span>
        <div class="flex gap-16">
            <span class="badge badge-success">On Time: {{ $finishedOrders }}</span>
            <span class="badge badge-danger">Late: {{ $lateOrders }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-container" style="height: 200px">
            <canvas id="deadlineChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function() {
    var weeklyData = @json($weeklyData);

    var weeklyCtx = document.getElementById('weeklyChart');
    if (weeklyCtx) {
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: weeklyData.map(function(d) { return d.date; }),
                datasets: [
                    {
                        label: 'Target',
                        data: weeklyData.map(function(d) { return d.target; }),
                        backgroundColor: 'rgba(20, 184, 166, 0.3)',
                        borderColor: '#14b8a6',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Aktual',
                        data: weeklyData.map(function(d) { return d.actual; }),
                        backgroundColor: 'rgba(59, 130, 246, 0.3)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'DM Sans', size: 11 }, padding: 16, usePointStyle: true, pointStyle: 'rectRounded' } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { family: 'DM Sans', size: 11 } }, grid: { color: 'rgba(20,184,166,0.06)' } },
                    x: { ticks: { font: { family: 'DM Sans', size: 11 } }, grid: { display: false } },
                }
            }
        });
    }

    var utilCtx = document.getElementById('utilizationChart');
    if (utilCtx) {
        new Chart(utilCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(function(d) { return d.date; }),
                datasets: [
                    {
                        label: 'Utilisasi (%)',
                        data: weeklyData.map(function(d) { return d.utilization; }),
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20, 184, 166, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#14b8a6',
                    },
                    {
                        label: 'Batas Optimal (100%)',
                        data: weeklyData.map(function() { return 100; }),
                        borderColor: 'rgba(239, 68, 68, 0.4)',
                        borderDash: [6, 4],
                        pointRadius: 0,
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'DM Sans', size: 11 }, padding: 16, usePointStyle: true } },
                },
                scales: {
                    y: { beginAtZero: true, max: 150, ticks: { font: { family: 'DM Sans', size: 11 }, callback: function(v) { return v + '%'; } }, grid: { color: 'rgba(20,184,166,0.06)' } },
                    x: { ticks: { font: { family: 'DM Sans', size: 11 } }, grid: { display: false } },
                }
            }
        });
    }

    var deadlineCtx = document.getElementById('deadlineChart');
    if (deadlineCtx) {
        new Chart(deadlineCtx, {
            type: 'doughnut',
            data: {
                labels: ['On Time / Finish', 'Late', 'On Process'],
                datasets: [{
                    data: [{{ $finishedOrders }}, {{ $lateOrders }}, {{ $activeOrders }}],
                    backgroundColor: ['rgba(16,185,129,0.7)', 'rgba(239,68,68,0.7)', 'rgba(59,130,246,0.7)'],
                    borderColor: ['#10b981', '#ef4444', '#3b82f6'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { font: { family: 'DM Sans', size: 12 }, padding: 16, usePointStyle: true } },
                },
                cutout: '65%',
            }
        });
    }
});
</script>
@endpush
