@extends('layouts.app')

@section('title', 'Kapasitas Mesin')
@section('page-title', 'Kapasitas Mesin')
@section('breadcrumb', 'Roll Forming - Kapasitas per hari')

@section('topbar-actions')
<div class="flex gap-8">
    <a href="{{ route('export.pdf', ['type' => 'capacity', 'month' => $month, 'year' => $year]) }}" class="btn btn-outline btn-sm" id="btn-cap-pdf">
        <i data-lucide="file-down"></i> PDF
    </a>
    <a href="{{ route('export.csv', ['type' => 'capacity', 'month' => $month, 'year' => $year]) }}" class="btn btn-outline btn-sm" id="btn-cap-csv">
        <i data-lucide="table"></i> CSV
    </a>
</div>
@endsection

@section('content')
<div class="filter-bar">
    <form method="GET" action="{{ route('capacity.index') }}" class="form-inline" id="filter-capacity">
        <div class="form-group">
            <label class="form-label" for="cap_month">Bulan</label>
            <select name="month" id="cap_month" class="form-control" style="width:auto">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="cap_year">Tahun</label>
            <select name="year" id="cap_year" class="form-control" style="width:auto">
                @for($y = 2025; $y <= 2027; $y++)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="search"></i> Tampilkan</button>
    </form>
</div>

<div class="stats-grid reveal">
    <div class="stat-card stat-accent" id="stat-monthly-cap">
        <div class="stat-icon icon-accent"><i data-lucide="gauge"></i></div>
        <div class="stat-label">Kapasitas Bulanan</div>
        <div class="stat-value font-mono">{{ number_format($monthlyCapacity, 0) }}</div>
        <div class="stat-sub">ea ({{ $workDays }} hari kerja)</div>
    </div>
    <div class="stat-card stat-info" id="stat-monthly-target">
        <div class="stat-icon icon-info"><i data-lucide="target"></i></div>
        <div class="stat-label">Total Target</div>
        <div class="stat-value font-mono">{{ number_format($monthlyTarget) }}</div>
        <div class="stat-sub">ea bulan ini</div>
    </div>
    <div class="stat-card stat-success" id="stat-monthly-actual">
        <div class="stat-icon icon-success"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-label">Total Aktual</div>
        <div class="stat-value font-mono">{{ number_format($monthlyActual) }}</div>
        <div class="stat-sub">ea tercapai</div>
    </div>
    <div class="stat-card {{ $monthlyUtilization > 100 ? 'stat-danger' : ($monthlyUtilization >= 70 ? 'stat-success' : 'stat-warning') }}" id="stat-monthly-util">
        <div class="stat-icon {{ $monthlyUtilization > 100 ? 'icon-danger' : ($monthlyUtilization >= 70 ? 'icon-success' : 'icon-warning') }}"><i data-lucide="activity"></i></div>
        <div class="stat-label">Utilisasi Bulanan</div>
        <div class="stat-value font-mono">{{ $monthlyUtilization }}%</div>
    </div>
</div>

<div class="card reveal" id="capacity-calendar">
    <div class="card-header">
        <span class="card-title">Kapasitas Mesin Per Hari</span>
        <div class="flex gap-16">
            <span class="badge badge-success">Optimal</span>
            <span class="badge badge-warning">Underload</span>
            <span class="badge badge-danger">Overload</span>
            <span class="badge badge-muted">Idle / Weekend</span>
        </div>
    </div>
    <div class="card-body">
        <div class="capacity-grid-header" style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;margin-bottom:8px;">
            @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dayName)
            <div style="text-align:center;font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);font-weight:600;padding:4px 0;">{{ $dayName }}</div>
            @endforeach
        </div>

        <div class="capacity-grid">
            @php
                $firstDay = $startDate->copy()->dayOfWeekIso;
                for ($i = 1; $i < $firstDay; $i++) {
                    echo '<div class="capacity-cell cell-idle" style="opacity:0.2"></div>';
                }
            @endphp

            @foreach($dailyData as $day)
            <div class="capacity-cell cell-{{ strtolower($day['status']) }}" title="{{ $day['date']->format('d M Y') }} - {{ $day['status'] }}: Target {{ $day['target_qty'] }}, Balance {{ $day['balance'] }}">
                <div class="cell-day">{{ $day['day_name'] }}</div>
                <div class="cell-date">{{ $day['day_num'] }}</div>
                @if(!$day['is_weekend'])
                <div class="cell-load font-mono">{{ $day['target_qty'] > 0 ? number_format($day['target_qty']) : '-' }}</div>
                <div class="cell-util">{{ $day['utilization'] }}%</div>
                @else
                <div class="cell-load text-muted" style="font-size:0.625rem">LIBUR</div>
                @endif
            </div>
            @endforeach
        </div>

        @if($machine)
        <div class="mt-24" style="font-size:0.75rem;color:var(--muted)">
            Kapasitas per hari: <strong class="text-accent font-mono">{{ number_format($dailyCapacity, 2) }} ea</strong> |
            Cycle time: <strong class="font-mono">{{ $machine->cycle_time_sec }}s</strong> |
            Available work time: <strong class="font-mono">{{ number_format($machine->available_work_time) }}s</strong>
        </div>
        @endif
    </div>
</div>

<div class="card reveal mt-24" id="capacity-detail-table">
    <div class="card-header">
        <span class="card-title">Detail Kapasitas Harian</span>
    </div>
    <div class="card-body compact">
        <div class="table-wrap">
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
                        <td>{{ $day['date']->translatedFormat('l') }}</td>
                        <td class="text-right font-mono">{{ $day['is_weekend'] ? '-' : number_format($dailyCapacity, 0) }}</td>
                        <td class="text-right font-mono">{{ $day['target_qty'] > 0 ? number_format($day['target_qty']) : '-' }}</td>
                        <td class="text-right font-mono">{{ $day['actual_qty'] > 0 ? number_format($day['actual_qty']) : '-' }}</td>
                        <td class="text-right font-mono {{ $day['balance'] < 0 ? 'text-danger' : '' }}">{{ $day['is_weekend'] ? '-' : number_format($day['balance'], 0) }}</td>
                        <td class="text-right font-mono">{{ $day['is_weekend'] ? '-' : $day['utilization'].'%' }}</td>
                        <td>
                            @if($day['status'] === 'OPTIMAL')
                                <span class="badge badge-success">{{ $day['status'] }}</span>
                            @elseif($day['status'] === 'OVERLOAD')
                                <span class="badge badge-danger">{{ $day['status'] }}</span>
                            @elseif($day['status'] === 'UNDERLOAD')
                                <span class="badge badge-warning">{{ $day['status'] }}</span>
                            @else
                                <span class="badge badge-muted">{{ $day['status'] }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
