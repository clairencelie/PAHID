@extends('layouts.app')
@section('title', 'Master Legal Entity')
@section('page-title', 'Master Legal Entity')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-semibold">Legal Entities</h5>
    <a href="{{ route('admin.entities.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i> Tambah Entity</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Nama Legal</th><th>Kota</th><th>Usaha</th><th>NPWP</th><th>Aliases</th><th></th></tr></thead>
            <tbody>
                @forelse($entities as $e)
                <tr>
                    <td class="fw-semibold">{{ $e->legal_name }}</td>
                    <td>{{ $e->city ?? '—' }}</td>
                    <td><small>{{ $e->occupation ?? '—' }}</small></td>
                    <td><small>{{ $e->npwp ?? '—' }}</small></td>
                    <td><span class="badge bg-light text-dark">{{ $e->aliases_count }} alias</span></td>
                    <td>
                        <a href="{{ route('admin.entities.show', $e) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada entity</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($entities->hasPages())<div class="card-footer">{{ $entities->links() }}</div>@endif
</div>
@endsection
