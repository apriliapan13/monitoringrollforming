@extends('layouts.app')

@section('title', 'Monitoring Produksi')
@section('page-title', 'Monitoring Produksi')
@section('breadcrumb', 'Evaluasi perbandingan target dan aktual produksi')

@section('topbar-actions')
<div class="flex gap-8">
<a href="{{ route('export.pdf', [
    'type' => 'monitoring',
    'month' => request('month'),
    'status' => request('status')
]) }}" class="btn btn-outline btn-sm" id="btn-mon-pdf">
        <i data-lucide="file-down"></i> PDF
    </a>
<a href="{{ route('export.csv', [
    'type' => 'monitoring',
    'month' => request('month'),
    'status' => request('status')
]) }}"
class="btn btn-outline btn-sm"
id="btn-mon-csv">
        <i data-lucide="table"></i> CSV
    </a>
</div>
@endsection

@section('content')
<div class="filter-bar">
    <form method="GET" action="{{ route('monitoring.index') }}" class="form-inline" id="filter-monitoring">
        <select name="status" class="form-control" id="mon-filter-status" style="width:auto">
            <option value="">Semua Status</option>
            <option value="ON PROCESS" {{ request('status') === 'ON PROCESS' ? 'selected' : '' }}>On Process</option>
            <option value="FINISH" {{ request('status') === 'FINISH' ? 'selected' : '' }}>Finish</option>
            <option value="ON TIME" {{ request('status') === 'ON TIME' ? 'selected' : '' }}>On Time</option>
            <option value="LATE" {{ request('status') === 'LATE' ? 'selected' : '' }}>Late</option>
        </select>

        <select name="month" class="form-control" style="width:auto">
            <option value="">Semua Bulan</option>
            @foreach(range(1,12) as $month)
                <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm">
            Filter
        </button>

        <a href="{{ route('monitoring.index') }}" class="btn btn-outline btn-sm">
            Reset
        </a>
    </form>
</div>

<div class="card reveal">
    <div class="card-body compact">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Bulan</th>
                        <th>Tanggal</th>
                        <th>No. SO</th>
                        <th>Executive</th>
<th class="text-right">Qty Order</th>
<th>Dimension</th>
<th class="text-right">Aktual</th>
                        <th class="text-right">Sisa</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Finish</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $i => $order)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $order->order_date?->translatedFormat('F') }}</td>
                        <td>{{ $order->order_date?->format('d') }}</td>
                        <td><a href="{{ route('sales-orders.show', $order) }}"><strong>{{ $order->so_number }}</strong></a></td>
                        <td>{{ $order->project_executive ?? '-' }}</td>
                        <td class="text-right font-mono">{{ number_format($order->quantity) }}</td>
<td>
    <strong>{{ $order->size }}</strong>
</td>
                        <td class="text-right font-mono">{{ number_format($order->total_actual) }}</td>
                        <td class="text-right font-mono {{ $order->remaining > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($order->remaining) }}</td>
                        <td style="min-width:140px">
                            <div class="flex items-center gap-8">
                                <div class="progress-bar-wrap" style="flex:1">
                                    <div class="progress-bar {{ $order->achievement >= 100 ? 'progress-success' : ($order->achievement >= 50 ? 'progress-accent' : 'progress-warning') }}" style="width:{{ min(100, $order->achievement) }}%"></div>
                                </div>
                                <span class="font-mono" style="font-size:0.75rem;min-width:40px;text-align:right">{{ $order->achievement }}%</span>
                            </div>
                        </td>
                        <td>{{ $order->deadline?->format('d/m/Y') }}</td>
                        <td>{{ $order->finish_date?->format('d/m/Y') ?? '-' }}</td>
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
                    <tr><td colspan="13" class="text-center text-muted" style="padding:40px">Belum ada data monitoring</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
