@extends('layouts.app')
@section('title', $entityGroup->group_name)
@section('page-title', 'Detail Entity Group')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">{{ $entityGroup->group_name }}</h6>
                <a href="{{ route('admin.entity-groups.edit', $entityGroup) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            </div>
            <div class="card-body">
                @if($entityGroup->notes)<p class="text-muted">{{ $entityGroup->notes }}</p>@endif
                <h6 class="fw-semibold mb-2">Members ({{ $entityGroup->members->count() }})</h6>
                @forelse($entityGroup->members as $member)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <a href="{{ route('admin.entities.show', $member) }}" class="fw-semibold">{{ $member->legal_name }}</a>
                    <div>
                        <span class="badge bg-secondary me-1">{{ $member->pivot->relationship_type }}</span>
                        <span class="text-muted small">{{ $member->city ?? '' }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted small">Belum ada member.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
