@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')
@section('breadcrumb', 'User / Buat Baru')

@section('content')
<div class="card reveal" style="max-width:520px">
    <div class="card-header"><span class="card-title">Form User Baru</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}" id="form-create-user">
            @csrf
            <div class="form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required aria-required="true">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required aria-required="true">
            </div>
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select name="role" id="role" class="form-control" required aria-required="true">
                    <option value="ppc" {{ old('role') === 'ppc' ? 'selected' : '' }}>PPC</option>
                    <option value="spv" {{ old('role') === 'spv' ? 'selected' : '' }}>SPV</option>
                    <option value="korlap" {{ old('role') === 'korlap' ? 'selected' : '' }}>Korlap Cell 3</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6" aria-required="true">
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required aria-required="true">
            </div>
            <div class="flex gap-12 mt-24">
                <button type="submit" class="btn btn-primary" id="btn-submit-user"><i data-lucide="save"></i> Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
