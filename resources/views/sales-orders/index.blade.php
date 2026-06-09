@extends('layouts.app')

@section('title', 'Sales Order')
@section('page-title', 'Sales Order')
@section('breadcrumb', 'Kelola Sales Order & Target')

@section('topbar-actions')
@if(auth()->user()->canInputTarget())
<a href="{{ route('sales-orders.create') }}" class="btn btn-primary btn-sm" id="btn-create-so">
    <i data-lucide="plus"></i>
    <span>Tambah SO</span>
</a>
@endif
<div class="flex gap-8">
    <a href="{{ route('export.pdf', ['type' => 'monitoring']) }}" class="btn btn-outline btn-sm" id="btn-export-pdf">
        <i data-lucide="file-down"></i> PDF
    </a>
    <a href="{{ route('export.csv', ['type' => 'monitoring']) }}" class="btn btn-outline btn-sm" id="btn-export-csv">
        <i data-lucide="table"></i> CSV
    </a>
</div>
@endsection

@section('content')
<div class="filter-bar">
    <form method="GET" action="{{ route('sales-orders.index') }}" class="form-inline" id="filter-form">
        <div class="search-input">
            <i data-lucide="search"></i>
            <input type="text" name="search" class="form-control" placeholder="Cari SO atau executive..." value="{{ request('search') }}" id="search-input">
        </div>
        <select name="status" class="form-control" onchange="this.form.submit()" id="filter-status" style="width:auto">
            <option value="">Semua Status</option>
            <option value="ON PROCESS" {{ request('status') === 'ON PROCESS' ? 'selected' : '' }}>On Process</option>
            <option value="FINISH" {{ request('status') === 'FINISH' ? 'selected' : '' }}>Finish</option>
            <option value="ON TIME" {{ request('status') === 'ON TIME' ? 'selected' : '' }}>On Time</option>
            <option value="LATE" {{ request('status') === 'LATE' ? 'selected' : '' }}>Late</option>
            <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
        </select>
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    </form>
</div>

<div class="card reveal">
    <div class="card-body compact">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. SO</th>
                        <th>Executive</th>
                        <th>Deskripsi</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Length</th>
                        <th>Tgl Order</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $i => $order)
                    <tr>
                        <td>{{ $orders->firstItem() + $i }}</td>
                        <td><a href="{{ route('sales-orders.show', $order) }}"><strong>{{ $order->so_number }}</strong></a></td>
                        <td>{{ $order->project_executive ?? '-' }}</td>
                        <td>{{ $order->description }}</td>
                        <td class="text-right font-mono">{{ number_format($order->quantity) }}</td>
                        <td class="text-right font-mono">{{ number_format($order->length) }}</td>
                        <td>{{ $order->order_date?->format('d/m/Y') }}</td>
                        <td>{{ $order->deadline?->format('d/m/Y') }}</td>
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
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('sales-orders.show', $order) }}" class="btn btn-ghost btn-sm" aria-label="Detail"><i data-lucide="eye"></i></a>
                                @if(auth()->user()->canInputTarget())
                                <a href="{{ route('sales-orders.edit', $order) }}" class="btn btn-ghost btn-sm" aria-label="Edit"><i data-lucide="pencil"></i></a>
                                <form action="{{ route('sales-orders.destroy', $order) }}" method="POST" class="confirm-delete" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" aria-label="Hapus"><i data-lucide="trash-2"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding:40px">
                            Belum ada Sales Order.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($orders->hasPages())
<div class="pagination-wrap">
    {{ $orders->links() }}
</div>
@endif
@endsection
