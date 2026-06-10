@extends('layouts.app')
@section('title', $prospect->prospect_name)
@section('page-title', 'Detail Prospect')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('prospects.index') }}" class="text-muted text-decoration-none" style="font-size:0.85rem;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h5 class="fw-bold mt-1 mb-0">{{ $prospect->prospect_name }}</h5>
        <small class="text-muted">{{ $prospect->prospect_code }} &middot; Dibuat {{ $prospect->created_at->format('d M Y H:i') }}</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="badge badge-status-{{ $prospect->status }} fs-6">{{ str_replace('_', ' ', $prospect->status) }}</span>
        @if($prospect->risk_level)
        <span class="badge badge-{{ $prospect->risk_level }} fs-6">{{ $prospect->risk_level }} RISK</span>
        @endif
    </div>
</div>

{{-- Action buttons --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            @if($prospect->status === 'DRAFT' && auth()->user()->isMarketing())
            <form method="POST" action="{{ route('prospects.submit', $prospect) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-send me-1"></i> Submit Prospect
                </button>
            </form>
            @endif

            @if(in_array($prospect->status, ['SUBMITTED','NEED_CLARIFICATION']) && auth()->user()->hasRole(['bc','supervisor','admin']))
            <form method="POST" action="{{ route('prospects.trigger-ai', $prospect) }}">
                @csrf
                <button type="submit" class="btn btn-info btn-sm text-white">
                    <i class="bi bi-robot me-1"></i> Jalankan Verifikasi AI
                </button>
            </form>
            @endif

            @if(in_array($prospect->status, ['BC_REVIEW','DUPLICATE_REVIEW','LOA_REVIEW']) && auth()->user()->hasRole(['bc','supervisor']))
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-circle me-1"></i> Setujui Follow-Up
            </button>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle me-1"></i> Tolak
            </button>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#clarifyModal">
                <i class="bi bi-chat-dots me-1"></i> Minta Klarifikasi
            </button>
            @endif

            @if($prospect->status === 'APPROVED_FOR_FOLLOW_UP' && !$prospect->singleSupportAssignment && auth()->user()->hasRole(['bc','supervisor']))
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                <i class="bi bi-shield-plus me-1"></i> Buat Single Support Assignment
            </button>
            @endif

            @if($prospect->status === 'NEED_CLARIFICATION' && auth()->user()->isMarketing())
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#respondModal">
                <i class="bi bi-reply me-1"></i> Kirim Klarifikasi
            </button>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Left Column --}}
    <div class="col-lg-6">

        {{-- Prospect Data --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-semibold"><i class="bi bi-building me-2"></i>Data Prospect</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="40%">Nama</td><td class="fw-semibold">{{ $prospect->prospect_name }}</td></tr>
                    <tr><td class="text-muted">Tipe Input</td><td><span class="badge bg-secondary">{{ $prospect->input_type }}</span></td></tr>
                    @if($prospect->legal_entity_name)
                    <tr><td class="text-muted">Legal Entity</td><td>{{ $prospect->legal_entity_name }}</td></tr>
                    @endif
                    @if($prospect->brand_name)
                    <tr><td class="text-muted">Brand</td><td>{{ $prospect->brand_name }}</td></tr>
                    @endif
                    @if($prospect->group_name)
                    <tr><td class="text-muted">Group</td><td>{{ $prospect->group_name }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Kota</td><td>{{ $prospect->city ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Jenis Usaha</td><td>{{ $prospect->occupation ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Est. Premi</td><td>{{ $prospect->estimated_premium ? 'Rp ' . number_format($prospect->estimated_premium, 0, ',', '.') : '—' }}</td></tr>
                    <tr><td class="text-muted">PIC Client</td><td>{{ $prospect->client_pic_name ? $prospect->client_pic_name . ' (' . $prospect->client_pic_position . ')' : '—' }}</td></tr>
                    <tr><td class="text-muted">Cabang</td><td>{{ $prospect->branch->name }}</td></tr>
                    <tr><td class="text-muted">Marketing</td><td>{{ $prospect->marketing->name }}</td></tr>
                    <tr><td class="text-muted">Dup. Score</td>
                        <td>
                            @if($prospect->duplicate_score > 0)
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar {{ $prospect->duplicate_score >= 70 ? 'bg-danger' : ($prospect->duplicate_score >= 50 ? 'bg-warning' : 'bg-success') }}"
                                         style="width:{{ $prospect->duplicate_score }}%"></div>
                                </div>
                                <strong>{{ $prospect->duplicate_score }}</strong>
                            </div>
                            @else
                            <span class="text-muted">Belum dihitung</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- AI Verification Result --}}
        @if($prospect->latestAiVerification)
        <div class="card mb-3 ai-result-box" style="border-radius:0.75rem;">
            <div class="card-header" style="background:transparent;border-color:#d0d9ff;">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-robot me-2 text-primary"></i>Hasil Verifikasi AI</h6>
            </div>
            <div class="card-body">
                @php $ai = $prospect->latestAiVerification->response_json; @endphp
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-white rounded p-2 text-center">
                            <div class="fw-bold text-primary" style="font-size:1.5rem;">{{ $ai['confidence_score'] ?? '—' }}</div>
                            <small class="text-muted">Confidence Score</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white rounded p-2 text-center">
                            <span class="badge badge-{{ $ai['duplicate_risk'] ?? 'LOW' }} fs-6">{{ $ai['duplicate_risk'] ?? '—' }}</span>
                            <div><small class="text-muted">Duplicate Risk</small></div>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <strong>Tipe Terdeteksi:</strong>
                    <span class="badge bg-primary ms-1">{{ $ai['detected_type'] ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <strong>Recommended Action:</strong>
                    <span class="badge {{ ($ai['recommended_action'] ?? '') === 'CLEAR' ? 'bg-success' : (($ai['recommended_action'] ?? '') === 'NEED_CLARIFICATION' ? 'bg-warning text-dark' : 'bg-danger') }} ms-1">
                        {{ $ai['recommended_action'] ?? '—' }}
                    </span>
                </div>

                @if(!empty($ai['possible_legal_entities']))
                <div class="mb-2">
                    <strong>Kemungkinan Legal Entity:</strong>
                    @foreach($ai['possible_legal_entities'] as $entity)
                    <div class="badge bg-light text-dark border mt-1 me-1">{{ $entity }}</div>
                    @endforeach
                </div>
                @endif

                @if(!empty($ai['reasons']))
                <div class="mb-2">
                    <strong>Alasan AI:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($ai['reasons'] as $reason)
                        <li class="small">{{ $reason }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($ai['missing_data']))
                <div>
                    <strong>Data Tidak Lengkap:</strong>
                    @foreach($ai['missing_data'] as $missing)
                    <span class="badge bg-warning text-dark mt-1 me-1">{{ $missing }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- LOA Documents --}}
        <div class="card mb-3 loa-box" style="border-radius:0.75rem;">
            <div class="card-header" style="background:transparent;border-color:#ffe58f;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>LOA Documents</h6>
                    @if(auth()->user()->isMarketing())
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#loaModal">
                        <i class="bi bi-upload me-1"></i> Upload LOA
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @forelse($prospect->loaDocuments as $loa)
                <div class="border rounded p-2 mb-2 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="small">LOA #{{ $loa->id }}</strong>
                        <div class="d-flex gap-1">
                            @if(!$loa->loa_status)
                            <form method="POST" action="{{ route('loa.check', $loa) }}">@csrf
                                <button type="submit" class="btn btn-xs btn-outline-info" style="font-size:0.72rem;padding:2px 8px;">
                                    <i class="bi bi-robot"></i> Cek AI
                                </button>
                            </form>
                            @endif
                            @if($loa->loa_status && auth()->user()->hasRole(['bc','supervisor']))
                            <button class="btn btn-xs btn-outline-secondary" style="font-size:0.72rem;padding:2px 8px;"
                                data-bs-toggle="modal" data-bs-target="#loaReviewModal{{ $loa->id }}">
                                Review
                            </button>
                            @endif
                        </div>
                    </div>
                    @if($loa->loa_status)
                    <span class="badge {{ $loa->loa_status === 'VALID' ? 'bg-success' : ($loa->loa_status === 'SUSPICIOUS' ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ $loa->loa_status }}
                    </span>
                    <span class="badge bg-secondary ms-1">Score: {{ $loa->loa_score ?? 'N/A' }}</span>
                    @if(!empty($loa->red_flags_json))
                    <div class="mt-1">
                        @foreach($loa->red_flags_json as $flag)
                        <div class="small text-danger"><i class="bi bi-flag-fill me-1"></i>{{ $flag }}</div>
                        @endforeach
                    </div>
                    @endif
                    @else
                    <span class="badge bg-secondary">Belum dicek</span>
                    @endif
                    @if($loa->extracted_text)
                    <div class="mt-1 p-1 bg-light rounded small text-muted" style="max-height:60px;overflow:hidden;">
                        {{ Str::limit($loa->extracted_text, 120) }}
                    </div>
                    @endif
                </div>

                {{-- LOA Review Modal --}}
                @if($loa->loa_status)
                <div class="modal fade" id="loaReviewModal{{ $loa->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Review LOA #{{ $loa->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="POST" action="{{ route('loa.review', $loa) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status LOA</label>
                                        <select name="loa_status" class="form-select">
                                            @foreach(['VALID','NEED_CLARIFICATION','SUSPICIOUS','REJECT_RECOMMENDED'] as $s)
                                            <option value="{{ $s }}" {{ $loa->loa_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Simpan Review</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <p class="text-muted small mb-0">Belum ada LOA diupload.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Right Column --}}
    <div class="col-lg-6">

        {{-- Single Support Assignment --}}
        @if($prospect->singleSupportAssignment)
        @php $assignment = $prospect->singleSupportAssignment; @endphp
        <div class="card mb-3" style="border: 2px solid {{ $assignment->isActive() ? '#198754' : '#6c757d' }}; border-radius:0.75rem;">
            <div class="card-header" style="background: {{ $assignment->isActive() ? '#d1e7dd' : '#f8f9fa' }};">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-shield-fill-check me-2 {{ $assignment->isActive() ? 'text-success' : 'text-secondary' }}"></i>
                        Single Support Assignment
                    </h6>
                    <span class="badge {{ $assignment->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $assignment->status }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6"><small class="text-muted">Cabang</small><div class="fw-semibold">{{ $assignment->branch->name }}</div></div>
                    <div class="col-6"><small class="text-muted">Level</small><div><span class="badge bg-primary">{{ $assignment->assignment_level }}</span></div></div>
                    <div class="col-6 mt-2"><small class="text-muted">Approval Source</small><div class="small">{{ str_replace('_', ' ', $assignment->approval_source) }}</div></div>
                    <div class="col-6 mt-2"><small class="text-muted">Efektif Dari</small><div class="small">{{ $assignment->effective_from->format('d M Y') }}</div></div>
                </div>

                @if($assignment->protectedAliases->count())
                <div class="mt-2">
                    <small class="text-muted fw-semibold">Protected Aliases ({{ $assignment->protectedAliases->count() }})</small>
                    <div class="mt-1">
                        @foreach($assignment->protectedAliases as $alias)
                        <span class="badge bg-light text-dark border mt-1 me-1" style="font-size:0.72rem;">
                            <span class="text-muted">{{ $alias->alias_type }}</span>: {{ $alias->alias_name }}
                            @if($alias->source === 'AI_DETECTED')
                            <i class="bi bi-robot ms-1 text-primary" title="AI detected"></i>
                            @endif
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-2">
                    <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-eye me-1"></i> Lihat Detail Assignment
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Single Support Conflicts --}}
        @if($prospect->singleSupportConflicts->count())
        <div class="card mb-3 conflict-box" style="border-radius:0.75rem;">
            <div class="card-header" style="background:transparent;border-color:#ffc5c5;">
                <h6 class="mb-0 fw-semibold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Single Support Conflict ({{ $prospect->singleSupportConflicts->count() }})
                </h6>
            </div>
            <div class="card-body">
                @foreach($prospect->singleSupportConflicts as $conflict)
                <div class="border border-danger rounded p-2 mb-2 bg-white">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="badge bg-danger">{{ $conflict->conflict_type }}</span>
                        <span class="badge badge-{{ $conflict->risk_level }}">{{ $conflict->risk_level }}</span>
                    </div>
                    <div class="small mb-1">
                        <strong>Match dengan:</strong> {{ $conflict->existingAssignment->branch->name }}<br>
                        @if($conflict->matched_alias)
                        <strong>Alias cocok:</strong> <code>{{ $conflict->matched_alias }}</code>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge {{ $conflict->status === 'OPEN' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $conflict->status }}</span>
                        <a href="{{ route('conflicts.show', $conflict) }}" class="btn btn-xs btn-outline-danger" style="font-size:0.72rem;padding:2px 8px;">
                            Review Konflik
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Prospect Conflicts --}}
        @if($prospect->prospectConflicts->count())
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-files me-2"></i>Duplikat Prospect ({{ $prospect->prospectConflicts->count() }})</h6>
            </div>
            <div class="card-body">
                @foreach($prospect->prospectConflicts as $conflict)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="badge bg-warning text-dark">Score: {{ $conflict->score }}</span>
                        <span class="badge badge-{{ $conflict->risk_level }}">{{ $conflict->risk_level }}</span>
                    </div>
                    @if($conflict->matchedProspect)
                    <div class="small">
                        <strong>Match:</strong>
                        <a href="{{ route('prospects.show', $conflict->matchedProspect) }}">{{ $conflict->matchedProspect->prospect_name }}</a>
                        ({{ $conflict->matchedProspect->prospect_code }})
                    </div>
                    @endif
                    @if($conflict->matchedEntity)
                    <div class="small"><strong>Entity:</strong> {{ $conflict->matchedEntity->legal_name }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Document Checklist --}}
        @if($prospect->documentChecklists->count())
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-check2-square me-2"></i>Document Checklist</h6>
                @php
                    $complete = $prospect->documentChecklists->where('status', 'COMPLETE')->count();
                    $total = $prospect->documentChecklists->count();
                @endphp
                <span class="badge bg-{{ $complete === $total ? 'success' : 'warning text-dark' }}">{{ $complete }}/{{ $total }}</span>
            </div>
            <div class="card-body">
                @foreach($prospect->documentChecklists->groupBy('checklist_type') as $type => $items)
                <div class="mb-3">
                    <h6 class="text-muted small fw-semibold text-uppercase mb-2">{{ str_replace('_', ' ', $type) }}</h6>
                    @foreach($items as $item)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <div class="small">
                            @if($item->is_critical)<i class="bi bi-asterisk text-danger me-1" title="Wajib"></i>@endif
                            {{ $item->item_name }}
                        </div>
                        @if(auth()->user()->hasRole(['bc','underwriter','supervisor']))
                        <form method="POST" action="{{ route('checklist.update', $item) }}" class="d-flex align-items-center gap-1">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="width:auto;font-size:0.7rem;"
                                onchange="this.form.submit()">
                                @foreach(['COMPLETE','INCOMPLETE','INVALID','NEED_CLARIFICATION'] as $s)
                                <option value="{{ $s }}" {{ $item->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                        @else
                        <span class="badge {{ $item->status === 'COMPLETE' ? 'bg-success' : ($item->status === 'INVALID' ? 'bg-danger' : 'bg-warning text-dark') }}" style="font-size:0.68rem;">
                            {{ $item->status }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @elseif(auth()->user()->hasRole(['bc','supervisor']))
        <div class="card mb-3">
            <div class="card-body text-center py-3">
                <small class="text-muted">Checklist belum digenerate</small>
                <form method="POST" action="{{ route('checklist.generate', $prospect) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Generate Checklist</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Workflow & SLA --}}
        @if($prospect->slaLogs->count())
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Workflow & SLA</h6></div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($prospect->slaLogs->sortBy('started_at') as $sla)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between">
                            <span class="badge badge-status-{{ $sla->status }}" style="font-size:0.7rem;">{{ str_replace('_', ' ', $sla->status) }}</span>
                            @if($sla->is_overdue)<span class="badge bg-danger" style="font-size:0.7rem;">OVERDUE</span>@endif
                        </div>
                        <small class="text-muted">{{ $sla->started_at->format('d M Y H:i') }}</small>
                        @if($sla->due_at)
                        <small class="text-muted"> · Due: {{ $sla->due_at->format('d M Y H:i') }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Setujui Prospect</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('prospects.approve', $prospect) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Setujui Follow-Up</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Tolak Prospect</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('prospects.reject', $prospect) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Alasan Penolakan</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Tolak Prospect</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="clarifyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Minta Klarifikasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('prospects.request-clarification', $prospect) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Hal yang Perlu Diklarifikasi <span class="text-danger">*</span></label>
                <textarea name="notes" class="form-control" rows="3" required placeholder="Jelaskan apa yang perlu dilengkapi marketing..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Kirim Permintaan</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="respondModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Kirim Klarifikasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('prospects.respond-clarification', $prospect) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Jawaban Klarifikasi <span class="text-danger">*</span></label>
                <textarea name="notes" class="form-control" rows="3" required placeholder="Jelaskan informasi tambahan..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Kirim Klarifikasi</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="loaModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Upload LOA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('loa.store', $prospect) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">File LOA (PDF/JPG/PNG)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="mb-3">
                    <label class="form-label">Teks LOA (Manual Input)</label>
                    <textarea name="extracted_text" class="form-control" rows="5"
                        placeholder="Masukkan teks LOA secara manual untuk dianalisis AI..."></textarea>
                    <small class="text-muted">Teks ini akan dianalisis AI untuk mendeteksi red flags</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload LOA</button>
            </div>
        </form>
    </div></div>
</div>

@if($prospect->status === 'APPROVED_FOR_FOLLOW_UP' && !$prospect->singleSupportAssignment)
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Buat Single Support Assignment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('assignments.store', $prospect) }}">
            @csrf
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Level Assignment <span class="text-danger">*</span></label>
                        <select name="assignment_level" class="form-select" required>
                            <option value="ENTITY">ENTITY</option>
                            <option value="BRAND">BRAND</option>
                            <option value="SITE">SITE</option>
                            <option value="GROUP">GROUP</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Approval Source <span class="text-danger">*</span></label>
                        <select name="approval_source" class="form-select" required>
                            @foreach(['FIRST_VALID_REGISTRATION','VALID_LOA','SUPERVISOR_DECISION','MANUAL_OVERRIDE'] as $s)
                            <option value="{{ $s }}">{{ str_replace('_', ' ', $s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alasan Approval <span class="text-danger">*</span></label>
                        <textarea name="approval_reason" class="form-control" rows="3" required
                            placeholder="Jelaskan alasan approval dan dasar pertimbangannya..."></textarea>
                    </div>
                </div>
                <div class="alert alert-info mt-3 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Sistem akan otomatis membuat protected aliases berdasarkan data prospect dan hasil verifikasi AI.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Buat Assignment</button>
            </div>
        </form>
    </div></div>
</div>
@endif

@endsection
