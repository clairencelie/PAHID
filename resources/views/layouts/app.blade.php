<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PAHID') — AI-Assisted Health Insurance Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a2332;
            --sidebar-text: #a8b8d0;
            --sidebar-active: #fff;
            --accent: #3d6aff;
        }

        body { background: #f0f3f9; font-family: 'Segoe UI', sans-serif; }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-brand h5 { color: #fff; margin: 0; font-weight: 700; font-size: 1rem; }
        .sidebar-brand small { color: var(--sidebar-text); font-size: 0.72rem; }

        .sidebar-nav { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .sidebar-section { padding: 0.25rem 1.5rem; margin-top: 0.5rem; }
        .sidebar-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.08em; color: #566880; font-weight: 600; }

        .sidebar-nav .nav-link {
            color: var(--sidebar-text);
            padding: 0.5rem 1.5rem;
            display: flex; align-items: center; gap: 0.65rem;
            font-size: 0.875rem;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }

        .sidebar-nav .nav-link:hover { color: var(--sidebar-active); background: rgba(255,255,255,0.05); }
        .sidebar-nav .nav-link.active { color: var(--sidebar-active); border-left-color: var(--accent); background: rgba(61,106,255,0.1); }
        .sidebar-nav .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }

        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
        }

        .topbar .page-title { font-weight: 600; font-size: 1rem; color: #1a2332; margin: 0; }

        .content-area { padding: 1.5rem; }

        .badge-LOW { --bs-badge-color: #fff; background-color: #28a745; }
        .badge-MEDIUM { --bs-badge-color: #000; background-color: #ffc107; }
        .badge-HIGH { --bs-badge-color: #fff; background-color: #fd7e14; }
        .badge-VERY_HIGH { --bs-badge-color: #fff; background-color: #dc3545; }

        .badge-status-DRAFT { background-color: #6c757d; color: #fff; }
        .badge-status-SUBMITTED { background-color: #0d6efd; color: #fff; }
        .badge-status-AI_VERIFICATION { background-color: #6f42c1; color: #fff; }
        .badge-status-NEED_CLARIFICATION { background-color: #ffc107; color: #000; }
        .badge-status-DUPLICATE_REVIEW, .badge-status-LOA_REVIEW { background-color: #dc3545; color: #fff; }
        .badge-status-BC_REVIEW, .badge-status-UW_REVIEW, .badge-status-DOCUMENT_COMPLETION { background-color: #0dcaf0; color: #000; }
        .badge-status-APPROVED_FOR_FOLLOW_UP, .badge-status-READY_FOR_POLICY, .badge-status-POLICY_ISSUED { background-color: #198754; color: #fff; }
        .badge-status-REJECTED, .badge-status-CANCELLED { background-color: #6c757d; color: #fff; }

        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 0.75rem; }
        .card-header { background: transparent; border-bottom: 1px solid #f0f3f9; padding: 1rem 1.25rem 0.75rem; }

        .stat-card { border-radius: 0.75rem; padding: 1.25rem; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }

        .timeline { position: relative; padding-left: 1.5rem; }
        .timeline::before { content: ''; position: absolute; left: 0.4rem; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
        .timeline-item { position: relative; margin-bottom: 1rem; }
        .timeline-item::before { content: ''; position: absolute; left: -1.1rem; top: 0.3rem; width: 0.7rem; height: 0.7rem; border-radius: 50%; background: var(--accent); }

        .ai-result-box { background: #f8f9ff; border: 1px solid #d0d9ff; border-radius: 0.75rem; }
        .conflict-box { background: #fff5f5; border: 1px solid #ffc5c5; border-radius: 0.75rem; }
        .loa-box { background: #fffbf0; border: 1px solid #ffe58f; border-radius: 0.75rem; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <h5><i class="bi bi-shield-check me-1"></i> PAHID</h5>
        <small>Health Insurance Verification</small>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="sidebar-section mt-2"><span class="sidebar-section-label">Prospect</span></div>
        <a href="{{ route('prospects.index') }}" class="nav-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-person"></i> Daftar Prospect
        </a>
        @if(auth()->user()->isMarketing())
        <a href="{{ route('prospects.create') }}" class="nav-link {{ request()->routeIs('prospects.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> Buat Prospect
        </a>
        @endif

        @if(auth()->user()->hasRole(['bc', 'supervisor', 'admin']))
        <div class="sidebar-section mt-2"><span class="sidebar-section-label">Single Support</span></div>
        <a href="{{ route('assignments.index') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
            <i class="bi bi-shield-fill-check"></i> Assignments
        </a>
        <a href="{{ route('conflicts.index') }}" class="nav-link {{ request()->routeIs('conflicts.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Konflik
        </a>
        @endif

        @if(auth()->user()->hasRole(['admin', 'supervisor']))
        <div class="sidebar-section mt-2"><span class="sidebar-section-label">Master Data</span></div>
        <a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Cabang
        </a>
        <a href="{{ route('admin.entities.index') }}" class="nav-link {{ request()->routeIs('admin.entities.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> Legal Entity
        </a>
        <a href="{{ route('admin.entity-groups.index') }}" class="nav-link {{ request()->routeIs('admin.entity-groups.*') ? 'active' : '' }}">
            <i class="bi bi-collection"></i> Group Entity
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Users
        </a>
        @endif
    </nav>

    <div class="p-3 border-top" style="border-color: rgba(255,255,255,0.08)!important;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width:32px;height:32px;font-size:0.8rem;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <div class="text-white" style="font-size:0.8rem;font-weight:600;">{{ auth()->user()->name }}</div>
                <div style="font-size:0.7rem;color:#a8b8d0;">{{ strtoupper(auth()->user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary w-100" style="font-size:0.75rem;color:#a8b8d0;border-color:rgba(255,255,255,0.15);">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>
</div>

<div class="main-wrapper">
    <div class="topbar">
        <h6 class="page-title">@yield('page-title', 'Dashboard')</h6>
        <div class="d-flex align-items-center gap-2">
            @if($openConflicts ?? 0 > 0)
            <a href="{{ route('conflicts.index') }}" class="btn btn-sm btn-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $openConflicts }} Konflik Terbuka
            </a>
            @endif
            <span class="badge bg-secondary">{{ auth()->user()->branch->name ?? 'Kantor Pusat' }}</span>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
