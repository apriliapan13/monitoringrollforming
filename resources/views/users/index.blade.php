@extends('layouts.app')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User')
@section('breadcrumb', 'Manajemen akun pengguna')

@section('topbar-actions')
<a href="{{ route('users.create') }}" class="btn btn-primary btn-sm" id="btn-create-user">
    <i data-lucide="user-plus"></i> Tambah User
</a>
@endsection

@section('content')
<div class="card reveal">
    <div class="card-body compact">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-accent">{{ strtoupper($user->role) }}</span></td>
                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-sm"><i data-lucide="pencil"></i></a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="confirm-delete" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm"><i data-lucide="trash-2"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada user</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($users->hasPages())
<div class="pagination-wrap">{{ $users->links() }}</div>
@endif
@endsection
