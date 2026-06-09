@extends('layouts.app')

@section('title', 'Detail SO ' . $salesOrder->so_number)
@section('page-title', $salesOrder->so_number)
@section('breadcrumb', 'Sales Order / Detail')

@section('content')
<div class="grid-2 reveal">
    <div class="card" id="so-detail">
        <div class="card-header">
            <span class="card-title">Detail Sales Order</span>
            @if($salesOrder->status === 'ON TIME' || $salesOrder->status === 'FINISH')
                <span class="badge badge-success">{{ $salesOrder->status }}</span>
            @elseif($salesOrder->status === 'LATE')
                <span class="badge badge-danger">{{ $salesOrder->status }}</span>
            @elseif($salesOrder->status === 'ON PROCESS')
                <span class="badge badge-info">{{ $salesOrder->status }}</span>
            @else
                <span class="badge badge-muted">{{ $salesOrder->status }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <span class="detail-label">Nomor SO</span>
                <span class="detail-value">{{ $salesOrder->so_number }}</span>
                <span class="detail-label">Executive</span>
                <span class="detail-value">{{ $salesOrder->project_executive ?? '-' }}</span>
                <span class="detail-label">Deskripsi</span>
                <span class="detail-value">{{ $salesOrder->description }}</span>
                <span class="detail-label">Qty Order</span>
                <span class="detail-value font-mono">{{ number_format($salesOrder->quantity) }} ea</span>
                <span class="detail-label">Length</span>
                <span class="detail-value font-mono">{{ number_format($salesOrder->length) }} mm</span>
                <span class="detail-label">Tanggal Order</span>
                <span class="detail-value">{{ $salesOrder->order_date?->format('d M Y') }}</span>
                <span class="detail-label">Deadline</span>
                <span class="detail-value">{{ $salesOrder->deadline?->format('d M Y') }}</span>
                <span class="detail-label">Selesai</span>
                <span class="detail-value">{{ $salesOrder->finish_date?->format('d M Y') ?? '-' }}</span>
                <span class="detail-label">Cell</span>
                <span class="detail-value">Cell {{ $salesOrder->cell }}</span>
                <span class="detail-label">Keterangan</span>
                <span class="detail-value">{{ $salesOrder->comment ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="card" id="so-analysis">
        <div class="card-header">
            <span class="card-title">Analisis Kapasitas</span>
        </div>
        <div class="card-body">
            <div class="stats-grid" style="margin-bottom:16px">
                <div class="stat-card stat-accent" style="padding:16px">
                    <div class="stat-label">Estimasi Durasi</div>
                    <div class="stat-value font-mono" style="font-size:1.25rem">{{ $estimatedDays }} hari</div>
                    <div class="stat-sub">{{ $estimatedHours }} jam kerja</div>
                </div>
                <div class="stat-card stat-info" style="padding:16px">
                    <div class="stat-label">Aktual Tercapai</div>
                    <div class="stat-value font-mono" style="font-size:1.25rem">{{ number_format($salesOrder->total_actual) }}</div>
                    <div class="stat-sub">dari {{ number_format($salesOrder->quantity) }} ea</div>
                </div>
            </div>

            <div class="mb-16">
                <div class="flex justify-between mb-8">
                    <span class="form-label" style="margin:0">Progress</span>
                    <span class="form-label" style="margin:0">{{ $salesOrder->achievement_percentage }}%</span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar {{ $salesOrder->achievement_percentage >= 100 ? 'progress-success' : ($salesOrder->achievement_percentage >= 50 ? 'progress-accent' : 'progress-warning') }}"
                         style="width: {{ min(100, $salesOrder->achievement_percentage) }}%"></div>
                </div>
            </div>

            <div class="detail-grid" style="font-size:0.8125rem">
                <span class="detail-label">Sisa Qty</span>
                <span class="detail-value font-mono">{{ number_format($salesOrder->remaining_qty) }} ea</span>
                <span class="detail-label">Leadtime</span>
                <span class="detail-value font-mono">{{ $salesOrder->leadtime_days }} hari</span>
                @if($machine)
                <span class="detail-label">Target/Hari</span>
                <span class="detail-value font-mono">
                    {{ number_format($targetPerDay) }} ea
                    @if($targetPerDay > $machine->daily_capacity)
                        <span class="badge badge-danger" style="margin-left:6px;font-size:0.65rem">OVERLOAD</span>
                    @endif
                </span>
                <span class="detail-label">Kapasitas/Hari</span>
                <span class="detail-value font-mono">{{ number_format($machine->daily_capacity, 0) }} ea</span>
                <span class="detail-label">Cycle Time</span>
                <span class="detail-value font-mono">{{ $machine->cycle_time_sec }} detik</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid-2 reveal mt-24">
    <div class="card" id="so-targets">
        <div class="card-header">
            <span class="card-title">Riwayat Target Harian</span>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th class="text-right">Target Qty</th></tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrder->dailyTargets->sortByDesc('target_date') as $target)
                        <tr>
                            <td>{{ $target->target_date->format('d M Y') }}</td>
                            <td class="text-right font-mono">{{ number_format($target->target_qty) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Belum ada target</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" id="so-actuals">
        <div class="card-header">
            <span class="card-title">Riwayat Aktual Produksi</span>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th class="text-right">Aktual Qty</th><th>Jenis</th></tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrder->actualProductions->sortByDesc('production_date') as $actual)
                        <tr>
                            <td>{{ $actual->production_date->format('d M Y') }}</td>
                            <td class="text-right font-mono">{{ number_format($actual->actual_qty) }}</td>
                            <td>{{ $actual->product_type ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data aktual</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-24">
    <a href="{{ route('sales-orders.index') }}" class="btn btn-outline"><i data-lucide="arrow-left"></i> Kembali</a>
</div>
@endsection
