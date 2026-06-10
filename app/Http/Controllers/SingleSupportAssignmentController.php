<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Entity;
use App\Models\EntityGroup;
use App\Models\LoaDocument;
use App\Models\Prospect;
use App\Models\SingleSupportAssignment;
use App\Services\Prospect\SingleSupportAssignmentService;
use Illuminate\Http\Request;

class SingleSupportAssignmentController extends Controller
{
    public function __construct(private SingleSupportAssignmentService $assignmentService) {}

    public function index()
    {
        $assignments = SingleSupportAssignment::with(['prospect', 'branch', 'marketing', 'protectedAliases'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('assignments.index', compact('assignments'));
    }

    public function show(SingleSupportAssignment $assignment)
    {
        $assignment->load([
            'prospect', 'branch', 'marketing', 'approver',
            'entity', 'entityGroup', 'loaDocument',
            'protectedAliases', 'conflicts.newProspect',
        ]);

        return view('assignments.show', compact('assignment'));
    }

    public function store(Request $request, Prospect $prospect)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor'])) {
            abort(403);
        }

        if ($prospect->status !== 'APPROVED_FOR_FOLLOW_UP') {
            return back()->with('error', 'Prospect harus berstatus APPROVED_FOR_FOLLOW_UP untuk membuat assignment.');
        }

        $validated = $request->validate([
            'assignment_level' => 'required|in:ENTITY,BRAND,SITE,GROUP',
            'approval_source' => 'required|in:FIRST_VALID_REGISTRATION,VALID_LOA,SUPERVISOR_DECISION,MANUAL_OVERRIDE',
            'approval_reason' => 'required|string',
            'entity_id' => 'nullable|exists:entities,id',
            'entity_group_id' => 'nullable|exists:entity_groups,id',
            'loa_document_id' => 'nullable|exists:loa_documents,id',
        ]);

        $assignment = $this->assignmentService->createFromProspect($prospect, $validated);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Single Support Assignment berhasil dibuat.');
    }

    public function revoke(Request $request, SingleSupportAssignment $assignment)
    {
        if (!auth()->user()->hasRole(['supervisor'])) {
            abort(403);
        }

        $validated = $request->validate(['reason' => 'required|string']);

        $assignment->update(['status' => 'REVOKED']);
        AuditLog::record('SINGLE_SUPPORT_REVOKED', 'SingleSupportAssignment', $assignment->id, null, ['reason' => $validated['reason']]);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Assignment dicabut.');
    }
}
