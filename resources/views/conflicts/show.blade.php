@extends('layouts.app')
@section('title', 'Review Konflik')
@section('page-title', 'Review Single Support Conflict')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3 conflict-box" style="border-radius:0.75rem;">
            <div class="card-header" style="background:transparent;border-color:#ffc5c5;">
                <h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Prospect Baru (Ditahan)</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <div class="fw-bold fs-5">{{ $conflict->newProspect->prospect_name }}</div>
                        <small class="text-muted">{{ $conflict->newProspect->prospect_code }}</small>
                    </div>
                    <span class="badge badge-{{ $conflict->risk_level }} fs-6">{{ $conflict->risk_level }}</span>
                </div>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="35%">Cabang</td><td class="text-danger fw-semibold">{{ $conflict->newProspect->branch->name }}</td></tr>
                    <tr><td class="text-muted">Marketing</td><td>{{ $conflict->newProspect->marketing->name }}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge badge-status-{{ $conflict->newProspect->status }}">{{ str_replace('_', ' ', $conflict->newProspect->status) }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Tipe Konflik</td>
                        <td><span class="badge bg-warning text-dark">{{ str_replace('_', ' ', $conflict->conflict_type) }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Conflict Score</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-danger" style="width:{{ $conflict->conflict_score }}%"></div>
                                </div>
                                <strong>{{ $conflict->conflict_score }}</strong>
                            </div>
                        </td>
                    </tr>
                    @if($conflict->detected_alias)
                    <tr><td class="text-muted">Detected Alias</td><td><code>{{ $conflict->detected_alias }}</code></td></tr>
                    @endif
                    @if($conflict->matched_alias)
                    <tr><td class="text-muted">Matched Alias</td><td><code class="text-danger">{{ $conflict->matched_alias }}</code></td></tr>
                    @endif
                </table>

                @if(!empty($conflict->ai_reasons_json))
                <div class="mt-3">
                    <strong class="small">Alasan AI:</strong>
                    <ul class="mt-1 mb-0 ps-3">
                        @foreach($conflict->ai_reasons_json as $reason)
                        <li class="small">{{ $reason }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        {{-- Resolution --}}
        @if($conflict->status === 'OPEN' && auth()->user()->hasRole(['bc', 'supervisor']))
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Resolve Konflik</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('conflicts.resolve', $conflict) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keputusan</label>
                        <div class="d-flex flex-column gap-2">
                            <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer">
                                <input type="radio" name="status" value="APPROVED_AS_DIFFERENT" required>
                                <div>
                                    <div class="fw-semibold text-success">Setujui Sebagai Berbeda</div>
                                    <small class="text-muted">Prospect ini dianggap entity yang berbeda dan boleh dilanjutkan</small>
                                </div>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer">
                                <input type="radio" name="status" value="REJECTED_DUPLICATE">
                                <div>
                                    <div class="fw-semibold text-danger">Tolak Sebagai Duplikat</div>
                                    <small class="text-muted">Prospect ini adalah duplikat dari assignment yang sudah ada</small>
                                </div>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer">
                                <input type="radio" name="status" value="ESCALATED">
                                <div>
                                    <div class="fw-semibold text-warning">Eskalasi ke Supervisor</div>
                                    <small class="text-muted">Butuh keputusan level supervisor</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Keputusan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan dasar keputusan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Keputusan</button>
                </form>
            </div>
        </div>
        @elseif($conflict->status !== 'OPEN')
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <span class="badge bg-secondary fs-6 mb-2">{{ $conflict->status }}</span>
                    @if($conflict->reviewer)
                    <div class="small text-muted">Direview oleh {{ $conflict->reviewer->name }} pada {{ $conflict->reviewed_at->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="card" style="border: 2px solid #198754;border-radius:0.75rem;">
            <div class="card-header" style="background:#d1e7dd;">
                <h6 class="mb-0 fw-semibold text-success"><i class="bi bi-shield-fill-check me-2"></i>Active Assignment (Existing)</h6>
            </div>
            <div class="card-body">
                <div class="fw-bold fs-5 mb-1">{{ $conflict->existingAssignment->prospect->prospect_name }}</div>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="35%">Cabang</td><td class="text-success fw-semibold">{{ $conflict->existingAssignment->branch->name }}</td></tr>
                    <tr><td class="text-muted">Marketing</td><td>{{ $conflict->existingAssignment->marketing->name }}</td></tr>
                    <tr><td class="text-muted">Level</td><td><span class="badge bg-primary">{{ $conflict->existingAssignment->assignment_level }}</span></td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge bg-success">{{ $conflict->existingAssignment->status }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Efektif</td><td>{{ $conflict->existingAssignment->effective_from->format('d M Y') }}</td></tr>
                </table>

                <div class="mt-3">
                    <strong class="small">Protected Aliases:</strong>
                    <div class="mt-1">
                        @foreach($conflict->existingAssignment->protectedAliases as $alias)
                        <span class="badge bg-light text-dark border mt-1 me-1" style="font-size:0.7rem;">
                            {{ $alias->alias_name }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('assignments.show', $conflict->existingAssignment) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-eye me-1"></i> Lihat Assignment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
