@extends('layouts.app')
@section('title', 'Detail Assignment')
@section('page-title', 'Single Support Assignment Detail')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">Assignment Info</h6>
                <span class="badge {{ $assignment->status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">{{ $assignment->status }}</span>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="40%">Prospect</td>
                        <td><a href="{{ route('prospects.show', $assignment->prospect) }}" class="fw-semibold">{{ $assignment->prospect->prospect_name }}</a></td>
                    </tr>
                    <tr><td class="text-muted">Cabang</td><td class="fw-semibold text-success">{{ $assignment->branch->name }}</td></tr>
                    <tr><td class="text-muted">Marketing</td><td>{{ $assignment->marketing->name }}</td></tr>
                    <tr><td class="text-muted">Level</td><td><span class="badge bg-primary">{{ $assignment->assignment_level }}</span></td></tr>
                    <tr><td class="text-muted">Approval Source</td><td><span class="badge bg-secondary">{{ str_replace('_', ' ', $assignment->approval_source) }}</span></td></tr>
                    <tr><td class="text-muted">Approved By</td><td>{{ $assignment->approver->name }}</td></tr>
                    <tr><td class="text-muted">Approval Reason</td><td><small>{{ $assignment->approval_reason }}</small></td></tr>
                    <tr><td class="text-muted">Efektif</td><td>{{ $assignment->effective_from->format('d M Y') }}</td></tr>
                    @if($assignment->entity)
                    <tr><td class="text-muted">Entity</td><td>{{ $assignment->entity->legal_name }}</td></tr>
                    @endif
                </table>

                @if(auth()->user()->isSupervisor() && $assignment->isActive())
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#revokeModal">
                        <i class="bi bi-shield-x me-1"></i> Cabut Assignment
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Conflicts --}}
        @if($assignment->conflicts->count())
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Konflik Terkait ({{ $assignment->conflicts->count() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Prospect Baru</th><th>Risk</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($assignment->conflicts as $c)
                            <tr>
                                <td>
                                    <a href="{{ route('prospects.show', $c->newProspect) }}">{{ $c->newProspect->prospect_name }}</a>
                                    <div><small class="text-muted">{{ $c->newProspect->branch->name }}</small></div>
                                </td>
                                <td><span class="badge badge-{{ $c->risk_level }}">{{ $c->risk_level }}</span></td>
                                <td><span class="badge {{ $c->status === 'OPEN' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $c->status }}</span></td>
                                <td><a href="{{ route('conflicts.show', $c) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.7rem;padding:2px 8px;">Detail</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold"><i class="bi bi-tags me-2"></i>Protected Aliases ({{ $assignment->protectedAliases->count() }})</h6></div>
            <div class="card-body">
                @forelse($assignment->protectedAliases->groupBy('alias_type') as $type => $aliases)
                <div class="mb-3">
                    <h6 class="small text-muted text-uppercase fw-semibold mb-1">{{ str_replace('_', ' ', $type) }}</h6>
                    @foreach($aliases as $alias)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <div>
                            <span class="fw-semibold small">{{ $alias->alias_name }}</span>
                            @if($alias->source === 'AI_DETECTED')
                            <span class="badge bg-info text-dark ms-1" style="font-size:0.65rem;">AI</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress" style="width:40px;height:4px;">
                                <div class="progress-bar bg-success" style="width:{{ $alias->confidence_score }}%"></div>
                            </div>
                            <small class="text-muted">{{ $alias->confidence_score }}%</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @empty
                <p class="text-muted small">Belum ada alias terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Cabut Assignment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('assignments.revoke', $assignment) }}">
            @csrf @method('PATCH')
            <div class="modal-body">
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i> Assignment yang dicabut tidak dapat dikembalikan.</div>
                <div class="mb-3"><label class="form-label fw-semibold">Alasan Pencabutan <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Cabut Assignment</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
