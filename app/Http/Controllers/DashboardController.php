<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\SingleSupportConflict;
use App\Models\SlaLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Prospect::query();

        if ($user->isMarketing()) {
            $query->where('marketing_user_id', $user->id);
        } elseif ($user->isBc() || $user->isUnderwriter()) {
            $query->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'submitted' => (clone $query)->where('status', 'SUBMITTED')->count(),
            'need_clarification' => (clone $query)->where('status', 'NEED_CLARIFICATION')->count(),
            'duplicate_review' => (clone $query)->where('status', 'DUPLICATE_REVIEW')->count(),
            'loa_review' => (clone $query)->where('status', 'LOA_REVIEW')->count(),
            'bc_review' => (clone $query)->where('status', 'BC_REVIEW')->count(),
            'approved' => (clone $query)->where('status', 'APPROVED_FOR_FOLLOW_UP')->count(),
            'high_risk' => (clone $query)->whereIn('risk_level', ['HIGH', 'VERY_HIGH'])->count(),
        ];

        $overdueCount = SlaLog::whereNull('completed_at')
            ->where('is_overdue', true)
            ->count();

        $openConflicts = SingleSupportConflict::where('status', 'OPEN')->count();

        $recentProspects = (clone $query)
            ->with(['branch', 'marketing'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'overdueCount', 'openConflicts', 'recentProspects'));
    }
}
