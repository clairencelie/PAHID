@extends('layouts.app')
@section('title', 'Master Cabang')
@section('page-title', 'Master Cabang')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-semibold">Cabang</h5>
    <a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i> Tambah Cabang</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nama</th><th>Kode</th><th>Users</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($branches as $b)
                <tr>
                    <td class="fw-semibold">{{ $b->name }}</td>
                    <td><code>{{ $b->code }}</code></td>
                    <td>{{ $b->users_count }}</td>
                    <td><span class="badge {{ $b->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $b->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td><a href="{{ route('admin.branches.edit', $b) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada cabang</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())<div class="card-footer">{{ $branches->links() }}</div>@endif
</div>
@endsection
