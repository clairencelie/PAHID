@extends('layouts.app')
@section('title', $entity->legal_name)
@section('page-title', 'Detail Entity')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">{{ $entity->legal_name }}</h6>
                <a href="{{ route('admin.entities.edit', $entity) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="35%">NPWP</td><td>{{ $entity->npwp ?? '—' }}</td></tr>
                    <tr><td class="text-muted">NIB</td><td>{{ $entity->nib ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Kota</td><td>{{ $entity->city ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Jenis Usaha</td><td>{{ $entity->occupation ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Alamat</td><td>{{ $entity->address ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Catatan</td><td>{{ $entity->notes ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Group Membership</h6></div>
            <div class="card-body">
                @forelse($entity->groups as $group)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <a href="{{ route('admin.entity-groups.show', $group) }}">{{ $group->group_name }}</a>
                    <span class="badge bg-secondary">{{ $group->pivot->relationship_type }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Tidak tergabung dalam group manapun.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Aliases ({{ $entity->aliases->count() }})</h6></div>
            <div class="card-body">
                @forelse($entity->aliases as $alias)
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>{{ $alias->alias_name }}</span>
                    <span class="badge bg-light text-dark">{{ $alias->alias_type }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Belum ada alias.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
