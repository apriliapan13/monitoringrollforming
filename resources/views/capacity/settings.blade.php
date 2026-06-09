@extends('layouts.app')

@section('title', 'Pengaturan Mesin')
@section('page-title', 'Pengaturan Mesin')
@section('breadcrumb', 'Konfigurasi parameter mesin Roll Forming')

@section('content')
<div class="card reveal" style="max-width:720px">
    <div class="card-header">
        <span class="card-title">Parameter Mesin</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('capacity.settings.update') }}" id="form-settings">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="product_name">Nama Produk</label>
                    <input type="text" name="product_name" id="product_name" class="form-control" value="{{ old('product_name', $machine?->product_name ?? 'Straight Single Solid Channel 41x41/41x21xL3000') }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="machine_name">Nama Mesin</label>
                    <input type="text" name="machine_name" id="machine_name" class="form-control" value="{{ old('machine_name', $machine?->machine_name ?? 'Roll Forming 3') }}" required aria-required="true">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="shift_count">Jumlah Shift</label>
                    <input type="number" name="shift_count" id="shift_count" class="form-control" value="{{ old('shift_count', $machine?->shift_count ?? 1) }}" required min="1" max="3" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="work_time_sec">Work Time (detik)</label>
                    <input type="number" name="work_time_sec" id="work_time_sec" class="form-control" value="{{ old('work_time_sec', $machine?->work_time_sec ?? 28800) }}" required aria-required="true">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="allowance_time_sec">Allowance Time (detik)</label>
                    <input type="number" name="allowance_time_sec" id="allowance_time_sec" class="form-control" value="{{ old('allowance_time_sec', $machine?->allowance_time_sec ?? 3600) }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="changeover_time_sec">Changeover Time (detik)</label>
                    <input type="number" name="changeover_time_sec" id="changeover_time_sec" class="form-control" value="{{ old('changeover_time_sec', $machine?->changeover_time_sec ?? 4320) }}" required aria-required="true">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="cycle_time_sec">Cycle Time (detik)</label>
                    <input type="number" name="cycle_time_sec" id="cycle_time_sec" class="form-control" value="{{ old('cycle_time_sec', $machine?->cycle_time_sec ?? 67) }}" required min="1" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="uptime_percentage">Uptime (%)</label>
                    <input type="number" name="uptime_percentage" id="uptime_percentage" class="form-control" value="{{ old('uptime_percentage', $machine?->uptime_percentage ?? 72.50) }}" step="0.01" required aria-required="true">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="man_power">Man Power</label>
                    <input type="number" name="man_power" id="man_power" class="form-control" value="{{ old('man_power', $machine?->man_power ?? 2) }}" required min="1" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cost_man_hour">Cost Man Hour (IDR)</label>
                    <input type="number" name="cost_man_hour" id="cost_man_hour" class="form-control" value="{{ old('cost_man_hour', $machine?->cost_man_hour ?? 76267) }}" step="0.01" required aria-required="true">
                </div>
            </div>

            @if($machine)
            <div class="card mt-24" style="background:var(--accent-soft);border-color:var(--accent)">
                <div class="card-body" style="padding:16px">
                    <div class="detail-grid" style="font-size:0.8125rem">
                        <span class="detail-label">Available Work Time</span>
                        <span class="detail-value font-mono">{{ number_format($machine->available_work_time) }} detik</span>
                        <span class="detail-label">Kapasitas / Shift</span>
                        <span class="detail-value font-mono">{{ number_format($machine->capacity_per_shift, 2) }} ea</span>
                        <span class="detail-label">Kapasitas Harian</span>
                        <span class="detail-value font-mono">{{ number_format($machine->daily_capacity, 2) }} ea</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex gap-12 mt-24">
                <button type="submit" class="btn btn-primary" id="btn-save-settings"><i data-lucide="save"></i> Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endsection
