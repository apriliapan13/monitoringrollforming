@extends('layouts.app')

@section('title', 'Aktual Produksi')
@section('page-title', 'Aktual Produksi')
@section('breadcrumb', 'Input data produksi aktual')

@section('content')
<div class="filter-bar">
    <form method="GET" action="{{ route('actual-production.index') }}" class="form-inline" id="filter-actual-date">
        <div class="form-group">
            <label class="form-label" for="date_actual">Tanggal</label>
            <input type="date" name="date" id="date_actual" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

<div class="stats-grid reveal">
    <div class="stat-card stat-success" id="stat-total-actual-today">
        <div class="stat-icon icon-success"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-label">Total Aktual Hari Ini</div>
        <div class="stat-value font-mono">{{ number_format($totalActual) }}</div>
        <div class="stat-sub">ea tercapai</div>
    </div>
</div>

<div class="grid-2 reveal">
    @if(auth()->user()->canInputActual())
    <div class="card" id="form-add-actual">
        <div class="card-header">
            <span class="card-title">Input Aktual Produksi</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('actual-production.store') }}" id="form-actual">
                @csrf
                <input type="hidden" name="production_date" value="{{ $date }}">

                <div class="form-group">
                    <label class="form-label" for="so_id_actual">Sales Order</label>
                    <select name="sales_order_id" id="so_id_actual" class="form-control" required aria-required="true">
                        <option value="">Pilih SO...</option>
                        @foreach($activeOrders as $order)
                        <option value="{{ $order->id }}">{{ $order->so_number }} - {{ $order->description }} (Sisa: {{ number_format(max(0, $order->quantity - $order->actualProductions()->sum('actual_qty'))) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="actual_qty">Jumlah Aktual (ea)</label>
                    <input type="number" name="actual_qty" id="actual_qty" class="form-control" required min="1" placeholder="Masukkan jumlah produksi" aria-required="true">
                </div>

                <div class="form-group">
                    <label class="form-label" for="product_type">Jenis Product</label>
                    <input type="text" name="product_type" id="product_type" class="form-control" placeholder="Misal: L3000, L6000">
                </div>

                <button type="submit" class="btn btn-primary" id="btn-save-actual">
                    <i data-lucide="plus"></i> Simpan Data Aktual
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="card" id="list-actual">
        <div class="card-header">
            <span class="card-title">Data Aktual</span>
            <span class="badge badge-accent">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
        </div>
        <div class="card-body compact">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. SO</th>
                            <th class="text-right">Qty</th>
                            <th>Jenis</th>
                            <th>User</th>
                            @if(auth()->user()->canInputActual())
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actuals as $actual)
                        <tr>
                            <td><strong>{{ $actual->salesOrder->so_number }}</strong></td>
                            <td class="text-right font-mono">{{ number_format($actual->actual_qty) }}</td>
                            <td>{{ $actual->product_type ?? '-' }}</td>
                            <td>{{ $actual->user?->name ?? '-' }}</td>
                            @if(auth()->user()->canInputActual())
                            <td>
                                <form action="{{ route('actual-production.destroy', $actual) }}" method="POST" class="confirm-delete" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm"><i data-lucide="trash-2"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:32px">Belum ada data aktual untuk tanggal ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
