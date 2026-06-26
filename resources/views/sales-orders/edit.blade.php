@extends('layouts.app')

@section('title', 'Edit Sales Order')
@section('page-title', 'Edit Sales Order')
@section('breadcrumb', 'Sales Order / Edit / ' . $salesOrder->so_number)

@section('content')
<div class="card reveal" style="max-width:720px">
    <div class="card-header">
<span class="card-title">
    Form Edit Sales Order
</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('sales-orders.update', $salesOrder) }}" id="form-edit-so">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="so_number">Nomor SO</label>
                    <input type="text" name="so_number" id="so_number" class="form-control" value="{{ old('so_number', $salesOrder->so_number) }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="project_executive">Project Executive</label>
                    <input type="text" name="project_executive" id="project_executive" class="form-control" value="{{ old('project_executive', $salesOrder->project_executive) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
    <label class="form-label" for="description">Deskripsi</label>

    <select name="description"
        id="description"
        class="form-control"
        required>

        <option value="Single Channel"
            {{ old('description', $salesOrder->description) == 'Single Channel' ? 'selected' : '' }}>
            Single Channel
        </option>

        <option value="Double Channel"
            {{ old('description', $salesOrder->description) == 'Double Channel' ? 'selected' : '' }}>
            Double Channel
        </option>

    </select>
</div>
                <div class="form-group">
                    <label class="form-label" for="batch">Batch</label>
                    <input type="text" name="batch" id="batch" class="form-control"value="{{ old('batch', $salesOrder->batch) }}"
placeholder="- / Add"="{{ old('batch', $salesOrder->batch) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity (ea)</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', $salesOrder->quantity) }}" required min="1" aria-required="true">
                </div>
                <div class="form-group">
    <label class="form-label" for="length">Length (MM)</label>

    <select name="length"
        id="length"
        class="form-control"
        required>

        <option value="3000"
            {{ old('length', $salesOrder->length) == '3000' ? 'selected' : '' }}>
            3000
        </option>

        <option value="6000"
            {{ old('length', $salesOrder->length) == '6000' ? 'selected' : '' }}>
            6000
        </option>

    </select>
</div>
<div class="form-group">
    <label class="form-label" for="size">Ukuran</label>

    <select name="size"
        id="size"
        class="form-control"
        required>

        <option value="41X41"
            {{ old('size', $salesOrder->size) == '41X41' ? 'selected' : '' }}>
            41X41
        </option>

        <option value="41X21"
            {{ old('size', $salesOrder->size) == '41X21' ? 'selected' : '' }}>
            41X21
        </option>

    </select>
</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="order_date">Tanggal Order</label>
                    <input type="date" name="order_date" id="order_date" class="form-control" value="{{ old('order_date', $salesOrder->order_date?->format('Y-m-d')) }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="deadline">Deadline</label>
                    <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline', $salesOrder->deadline?->format('Y-m-d')) }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="finish_date">Tanggal Selesai</label>
                    <input type="date" name="finish_date" id="finish_date" class="form-control" value="{{ old('finish_date', $salesOrder->finish_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="cell">Cell</label>
                    <select name="cell" id="cell" class="form-control" required aria-required="true">
                        <option value="3" {{ old('cell', $salesOrder->cell) == '3' ? 'selected' : '' }}>Cell 3</option>
                        <option value="1" {{ old('cell', $salesOrder->cell) == '1' ? 'selected' : '' }}>Cell 1</option>
                        <option value="2" {{ old('cell', $salesOrder->cell) == '2' ? 'selected' : '' }}>Cell 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-control" required aria-required="true">
                        @foreach(['ON PROCESS', 'FINISH', 'ON TIME', 'LATE', 'PENDING'] as $s)
                        <option value="{{ $s }}" {{ old('status', $salesOrder->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="comment">Keterangan</label>
                <textarea name="comment" id="comment" class="form-control" rows="2">{{ old('comment', $salesOrder->comment) }}</textarea>
            </div>

            <div class="flex gap-12 mt-24">
<button type="submit" class="btn btn-primary" id="btn-submit-so">
    <i data-lucide="save"></i> Simpan Perubahan
</button>
                <a href="{{ route('sales-orders.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
