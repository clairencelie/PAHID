@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-primary">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Prospect</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-warning">{{ $stats['need_clarification'] }}</div>
            <div class="stat-label">Perlu Klarifikasi</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-danger">{{ $stats['duplicate_review'] }}</div>
            <div class="stat-label">Review Duplikat</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-success">{{ $stats['approved'] }}</div>
            <div class="stat-label">Approved Follow-Up</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-info">{{ $stats['bc_review'] }}</div>
            <div class="stat-label">BC Review</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-danger">{{ $stats['high_risk'] }}</div>
            <div class="stat-label">High Risk</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-danger">{{ $overdueCount }}</div>
            <div class="stat-label">SLA Overdue</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card bg-white">
            <div class="stat-number text-danger">{{ $openConflicts }}</div>
            <div class="stat-label">Konflik Terbuka</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Prospect Terbaru</h6>
        <a href="{{ route('prospects.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Prospect</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Risk</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentProspects as $p)
                    <tr>
                        <td><code class="text-muted" style="font-size:0.75rem;">{{ $p->prospect_code }}</code></td>
                        <td class="fw-semibold">{{ $p->prospect_name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $p->branch->name }}</span></td>
                        <td>
                            <span class="badge badge-status-{{ $p->status }}" style="font-size:0.72rem;">
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
                        <td><small class="text-muted">{{ $p->created_at->format('d M Y') }}</small></td>
                        <td>
                            <a href="{{ route('prospects.show', $p) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada prospect</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
