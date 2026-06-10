@extends('layouts.app')
@section('title', 'Single Support Conflicts')
@section('page-title', 'Review Konflik Single Support')

@section('content')
<div class="card">
    <div class="card-header"><h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konflik Single Support</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prospect Baru</th>
                        <th>Cabang Baru</th>
                        <th>Tipe Konflik</th>
                        <th>Risk</th>
                        <th>Score</th>
                        <th>Assignment Existing</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conflicts as $c)
                    <tr>
                        <td>
                            <a href="{{ route('prospects.show', $c->newProspect) }}" class="fw-semibold">{{ $c->newProspect->prospect_name }}</a>
                            <div><code class="text-muted" style="font-size:0.7rem;">{{ $c->newProspect->prospect_code }}</code></div>
                        </td>
                        <td><small>{{ $c->newProspect->branch->name }}</small></td>
                        <td><span class="badge bg-warning text-dark" style="font-size:0.7rem;">{{ str_replace('_', ' ', $c->conflict_type) }}</span></td>
                        <td><span class="badge badge-{{ $c->risk_level }}">{{ $c->risk_level }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress" style="width:40px;height:6px;">
                                    <div class="progress-bar bg-danger" style="width:{{ $c->conflict_score }}%"></div>
                                </div>
                                <strong>{{ $c->conflict_score }}</strong>
                            </div>
                        </td>
                        <td>
                            <small class="fw-semibold">{{ $c->existingAssignment->branch->name }}</small>
                            <div><small class="text-muted">{{ $c->existingAssignment->marketing->name }}</small></div>
                        </td>
                        <td>
                            <span class="badge {{ $c->status === 'OPEN' ? 'bg-danger' : ($c->status === 'ESCALATED' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $c->created_at->format('d M Y') }}</small></td>
                        <td>
                            <a href="{{ route('conflicts.show', $c) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-eye"></i> Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada konflik</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($conflicts->hasPages())
    <div class="card-footer">{{ $conflicts->links() }}</div>
    @endif
</div>
@endsection
