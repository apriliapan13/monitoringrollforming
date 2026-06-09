@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb', 'User / Edit / ' . $user->name)

@section('content')
<div class="card reveal" style="max-width:520px">
    <div class="card-header"><span class="card-title">Edit {{ $user->name }}</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}" id="form-edit-user">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required aria-required="true">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required aria-required="true">
            </div>
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select name="role" id="role" class="form-control" required aria-required="true">
                    <option value="ppc" {{ old('role', $user->role) === 'ppc' ? 'selected' : '' }}>PPC</option>
                    <option value="spv" {{ old('role', $user->role) === 'spv' ? 'selected' : '' }}>SPV</option>
                    <option value="korlap" {{ old('role', $user->role) === 'korlap' ? 'selected' : '' }}>Korlap Cell 3</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password Baru (kosongkan jika tidak ubah)</label>
                <input type="password" name="password" id="password" class="form-control" minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
            </div>
            <div class="flex gap-12 mt-24">
                <button type="submit" class="btn btn-primary" id="btn-update-user"><i data-lucide="save"></i> Perbarui</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
