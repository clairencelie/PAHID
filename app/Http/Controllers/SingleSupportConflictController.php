<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SingleSupportConflict;
use Illuminate\Http\Request;

class SingleSupportConflictController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor', 'admin'])) {
            abort(403);
        }

        $conflicts = SingleSupportConflict::with([
            'newProspect.branch', 'existingAssignment.branch', 'existingAssignment.marketing',
        ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('conflicts.index', compact('conflicts'));
    }

    public function show(SingleSupportConflict $conflict)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor', 'admin'])) {
            abort(403);
        }

        $conflict->load([
            'newProspect.branch', 'newProspect.marketing',
            'existingAssignment.prospect', 'existingAssignment.branch',
            'existingAssignment.marketing', 'existingAssignment.protectedAliases',
        ]);

        return view('conflicts.show', compact('conflict'));
    }

    public function resolve(Request $request, SingleSupportConflict $conflict)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:APPROVED_AS_DIFFERENT,REJECTED_DUPLICATE,ESCALATED',
            'notes' => 'nullable|string',
        ]);

        $conflict->update([
            'status' => $validated['status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::record('CONFLICT_RESOLVED', 'SingleSupportConflict', $conflict->id, null, [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // If rejected as duplicate, cancel the new prospect
        if ($validated['status'] === 'REJECTED_DUPLICATE') {
            $conflict->newProspect->update(['status' => 'REJECTED']);
            AuditLog::record('PROSPECT_REJECTED', 'Prospect', $conflict->new_prospect_id, null, [
                'reason' => 'Rejected as duplicate by ' . auth()->user()->name,
            ]);
        }

        return redirect()->route('conflicts.show', $conflict)
            ->with('success', 'Konflik berhasil diselesaikan.');
    }
}
