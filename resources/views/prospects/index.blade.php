@extends('layouts.app')
@section('title', 'Daftar Prospect')
@section('page-title', 'Daftar Prospect')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-semibold">Prospect</h5>
    @if(auth()->user()->isMarketing())
    <a href="{{ route('prospects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Buat Prospect
    </a>
    @endif
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama prospect..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(['DRAFT','SUBMITTED','AI_VERIFICATION','NEED_CLARIFICATION','DUPLICATE_REVIEW','LOA_REVIEW','BC_REVIEW','UW_REVIEW','DOCUMENT_COMPLETION','APPROVED_FOR_FOLLOW_UP','READY_FOR_POLICY','POLICY_ISSUED','REJECTED','CANCELLED'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', $s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="risk_level" class="form-select form-select-sm">
                    <option value="">Semua Risk</option>
                    @foreach(['LOW','MEDIUM','HIGH','VERY_HIGH'] as $r)
                    <option value="{{ $r }}" {{ request('risk_level') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            @if(!auth()->user()->isMarketing())
            <div class="col-md-2">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('prospects.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Prospect</th>
                        <th>Tipe</th>
                        <th>Kota</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Risk</th>
                        <th>Score</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prospects as $p)
                    <tr>
                        <td><code class="text-muted" style="font-size:0.72rem;">{{ $p->prospect_code }}</code></td>
                        <td>
                            <div class="fw-semibold">{{ $p->prospect_name }}</div>
                            @if($p->legal_entity_name)
                            <small class="text-muted">{{ $p->legal_entity_name }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark" style="font-size:0.7rem;">{{ $p->input_type }}</span></td>
                        <td><small>{{ $p->city ?? '—' }}</small></td>
                        <td><small>{{ $p->branch->name }}</small></td>
                        <td>
                            <span class="badge badge-status-{{ $p->status }}" style="font-size:0.7rem;">
                                {{ str_replace('_', ' ', $p->status) }}
                            </span>
                        </td>
                        <td>
                            @if($p->risk_level)
                            <span class="badge badge-{{ $p->risk_level }}">{{ $p->risk_level }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($p->duplicate_score > 0)
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress" style="width:40px;height:6px;">
                                    <div class="progress-bar {{ $p->duplicate_score >= 70 ? 'bg-danger' : ($p->duplicate_score >= 50 ? 'bg-warning' : 'bg-success') }}"
                                         style="width:{{ $p->duplicate_score }}%"></div>
                                </div>
                                <small>{{ $p->duplicate_score }}</small>
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $p->created_at->format('d M Y') }}</small></td>
                        <td>
                            <a href="{{ route('prospects.show', $p) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada prospect ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($prospects->hasPages())
    <div class="card-footer">
        {{ $prospects->links() }}
    </div>
    @endif
</div>
@endsection
