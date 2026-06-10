@extends('layouts.app')
@section('title', 'Master Users')
@section('page-title', 'Master Users')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-semibold">Users</h5>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i> Tambah User</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Role</th><th>Cabang</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="fw-semibold">{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge bg-primary">{{ strtoupper($u->role) }}</span></td>
                    <td>{{ $u->branch->name ?? '—' }}</td>
                    <td><span class="badge {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td><a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
</div>
@endsection
