@extends('layouts.app')

@section('title', 'Target Harian')
@section('page-title', 'Target Harian')
@section('breadcrumb', 'Input target produksi per hari')

@section('content')
<div class="filter-bar">
    <form method="GET" action="{{ route('daily-targets.index') }}" class="form-inline" id="filter-date-form">
        <div class="form-group">
            <label class="form-label" for="date">Tanggal</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

<div class="stats-grid reveal">
    <div class="stat-card stat-accent" id="stat-daily-capacity">
        <div class="stat-icon icon-accent"><i data-lucide="gauge"></i></div>
        <div class="stat-label">Kapasitas Harian</div>
        <div class="stat-value font-mono">{{ number_format($dailyCapacity, 0) }}</div>
        <div class="stat-sub">ea per shift</div>
    </div>
    <div class="stat-card stat-info" id="stat-total-target">
        <div class="stat-icon icon-info"><i data-lucide="target"></i></div>
        <div class="stat-label">Total Target</div>
        <div class="stat-value font-mono">{{ number_format($totalTarget) }}</div>
        <div class="stat-sub">ea hari ini</div>
    </div>
    <div class="stat-card {{ $remainingCapacity > 0 ? 'stat-success' : 'stat-danger' }}" id="stat-remaining-cap">
        <div class="stat-icon {{ $remainingCapacity > 0 ? 'icon-success' : 'icon-danger' }}"><i data-lucide="battery-charging"></i></div>
        <div class="stat-label">Sisa Kapasitas</div>
        <div class="stat-value font-mono">{{ number_format($remainingCapacity, 0) }}</div>
        <div class="stat-sub">ea tersisa</div>
    </div>
    <div class="stat-card {{ $utilization > 100 ? 'stat-danger' : ($utilization >= 70 ? 'stat-success' : 'stat-warning') }}" id="stat-utilization">
        <div class="stat-icon {{ $utilization > 100 ? 'icon-danger' : ($utilization >= 70 ? 'icon-success' : 'icon-warning') }}"><i data-lucide="activity"></i></div>
        <div class="stat-label">Utilisasi</div>
        <div class="stat-value font-mono">{{ $utilization }}%</div>
        <div class="stat-sub">{{ $utilization > 100 ? 'OVERLOAD' : ($utilization >= 70 ? 'OPTIMAL' : 'UNDERLOAD') }}</div>
    </div>
</div>

<div class="mb-16">
    <div class="progress-bar-wrap" style="height:12px">
        <div class="progress-bar {{ $utilization > 100 ? 'progress-danger' : ($utilization >= 70 ? 'progress-success' : 'progress-warning') }}" style="width:{{ min(100, $utilization) }}%"></div>
    </div>
</div>

<div class="grid-2 reveal">
    @if(auth()->user()->canInputTarget())
    <div class="card" id="form-add-target">
        <div class="card-header">
            <span class="card-title">Tambah Target</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('daily-targets.store') }}" id="form-target">
                @csrf
                <input type="hidden" name="target_date" value="{{ $date }}">

                <div class="form-group">
                    <label class="form-label" for="sales_order_id">Sales Order</label>
                    <select name="sales_order_id" id="sales_order_id" class="form-control" required aria-required="true">
                        <option value="">Pilih SO...</option>
                        @foreach($activeOrders as $order)
                        <option value="{{ $order->id }}">{{ $order->so_number }} - {{ $order->description }} (Qty: {{ number_format($order->quantity) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="target_qty">Target Qty</label>
                    <input type="number" name="target_qty" id="target_qty" class="form-control" required min="1" placeholder="Masukkan jumlah target" aria-required="true">
                </div>

                <button type="submit" class="btn btn-primary" id="btn-save-target">
                    <i data-lucide="plus"></i> Simpan Target
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="card" id="list-targets">
        <div class="card-header">
            <span class="card-title">Target Hari Ini</span>
            <span class="badge badge-accent">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. SO</th>
                            <th class="text-right">Target Qty</th>
                            @if(auth()->user()->canInputTarget())
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                        <tr>
                            <td><strong>{{ $target->salesOrder->so_number }}</strong></td>
                            <td class="text-right font-mono">{{ number_format($target->target_qty) }}</td>
                            @if(auth()->user()->canInputTarget())
                            <td>
                                <form action="{{ route('daily-targets.destroy', $target) }}" method="POST" class="confirm-delete" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm"><i data-lucide="trash-2"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted" style="padding:32px">Belum ada target untuk tanggal ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
index