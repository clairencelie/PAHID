@extends('layouts.app')
@section('title', 'Single Support Assignments')
@section('page-title', 'Single Support Assignments')

@section('content')
<div class="card">
    <div class="card-header"><h6 class="mb-0 fw-semibold"><i class="bi bi-shield-fill-check me-2"></i>Active Single Support Assignments</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prospect</th>
                        <th>Cabang</th>
                        <th>Marketing</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Protected Aliases</th>
                        <th>Efektif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $a->prospect->prospect_name }}</div>
                            <small class="text-muted">{{ $a->prospect->prospect_code }}</small>
                        </td>
                        <td>{{ $a->branch->name }}</td>
                        <td><small>{{ $a->marketing->name }}</small></td>
                        <td><span class="badge bg-primary">{{ $a->assignment_level }}</span></td>
                        <td>
                            <span class="badge {{ $a->status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $a->status }}
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $a->protectedAliases->count() }} alias</span></td>
                        <td><small class="text-muted">{{ $a->effective_from->format('d M Y') }}</small></td>
                        <td>
                            <a href="{{ route('assignments.show', $a) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada assignment</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($assignments->hasPages())
    <div class="card-footer">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
