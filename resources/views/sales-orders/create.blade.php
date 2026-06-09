@extends('layouts.app')

@section('title', 'Tambah Sales Order')
@section('page-title', 'Tambah Sales Order')
@section('breadcrumb', 'Sales Order / Buat Baru')

@section('content')
<div class="card reveal" style="max-width:720px">
    <div class="card-header">
        <span class="card-title">Form Sales Order Baru</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('sales-orders.store') }}" id="form-create-so">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="so_number">Nomor SO</label>
                    <input type="text" name="so_number" id="so_number" class="form-control" value="{{ old('so_number') }}" required placeholder="0001/P/JAN/01/2026" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="project_executive">Project Executive</label>
                    <input type="text" name="project_executive" id="project_executive" class="form-control" value="{{ old('project_executive') }}" placeholder="Nama executive">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <input type="text" name="description" id="description" class="form-control" value="{{ old('description', 'Channel') }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="batch">Batch</label>
                    <input type="text" name="batch" id="batch" class="form-control" value="{{ old('batch') }}" placeholder="- / Add">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity (ea)</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}" required min="1" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="length">Length (mm)</label>
                    <input type="number" name="length" id="length" class="form-control" value="{{ old('length', 3000) }}" required min="1" aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="kg_batch">Berat (Kg)</label>
                    <input type="number" name="kg_batch" id="kg_batch" class="form-control" value="{{ old('kg_batch') }}" step="0.01" placeholder="0.00">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="order_date">Tanggal Order</label>
                    <input type="date" name="order_date" id="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="deadline">Deadline</label>
                    <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}" required aria-required="true">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cell">Cell</label>
                    <select name="cell" id="cell" class="form-control" required aria-required="true">
                        <option value="3" {{ old('cell') == '3' ? 'selected' : '' }}>Cell 3</option>
                        <option value="1" {{ old('cell') == '1' ? 'selected' : '' }}>Cell 1</option>
                        <option value="2" {{ old('cell') == '2' ? 'selected' : '' }}>Cell 2</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="comment">Keterangan</label>
                <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('comment') }}</textarea>
            </div>

            <div class="flex gap-12 mt-24">
                <button type="submit" class="btn btn-primary" id="btn-submit-so">
                    <i data-lucide="save"></i> Simpan
                </button>
                <a href="{{ route('sales-orders.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
