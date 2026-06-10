@extends('layouts.app')
@section('title', 'Master Group Entity')
@section('page-title', 'Master Group Entity')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-semibold">Entity Groups</h5>
    <a href="{{ route('admin.entity-groups.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i> Tambah Group</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nama Group</th><th>Members</th><th>Catatan</th><th></th></tr></thead>
            <tbody>
                @forelse($groups as $g)
                <tr>
                    <td class="fw-semibold">{{ $g->group_name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $g->members_count }} entity</span></td>
                    <td><small class="text-muted">{{ Str::limit($g->notes, 60) ?? '—' }}</small></td>
                    <td><a href="{{ route('admin.entity-groups.show', $g) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada group</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())<div class="card-footer">{{ $groups->links() }}</div>@endif
</div>
@endsection
