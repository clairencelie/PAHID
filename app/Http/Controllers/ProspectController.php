<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Prospect;
use App\Models\SlaLog;
use App\Services\Prospect\AiEntityVerificationService;
use App\Services\Prospect\DuplicateScoringService;
use App\Services\Prospect\DocumentChecklistService;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function __construct(
        private AiEntityVerificationService $aiService,
        private DuplicateScoringService $duplicateService,
        private DocumentChecklistService $checklistService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Prospect::with(['branch', 'marketing']);

        if ($user->isMarketing()) {
            $query->where('marketing_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('search')) {
            $query->where('prospect_name', 'like', '%' . $request->search . '%');
        }

        $prospects = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $branches = Branch::where('is_active', true)->get();

        return view('prospects.index', compact('prospects', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('prospects.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prospect_name' => 'required|string|max:255',
            'input_type' => 'required|in:LEGAL_ENTITY,BRAND,GROUP,PROPERTY,SUBSIDIARY,UNKNOWN',
            'legal_entity_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'group_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'estimated_premium' => 'nullable|numeric|min:0',
            'client_pic_name' => 'nullable|string|max:255',
            'client_pic_position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $validated['branch_id'] = $user->branch_id;
        $validated['marketing_user_id'] = $user->id;
        $validated['status'] = 'DRAFT';
        $validated['prospect_code'] = 'PRO-' . strtoupper(uniqid());

        $prospect = Prospect::create($validated);

        AuditLog::record('PROSPECT_CREATED', 'Prospect', $prospect->id, null, $prospect->toArray());

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Prospect berhasil dibuat.');
    }

    public function show(Prospect $prospect)
    {
        $prospect->load([
            'branch', 'marketing', 'latestAiVerification', 'loaDocuments',
            'documentChecklists', 'workflowTasks.assignedUser', 'slaLogs',
            'prospectConflicts.matchedProspect', 'prospectConflicts.matchedEntity',
            'singleSupportAssignment.protectedAliases',
            'singleSupportAssignment.branch',
            'singleSupportConflicts.existingAssignment.branch',
        ]);

        return view('prospects.show', compact('prospect'));
    }

    public function edit(Prospect $prospect)
    {
        $this->authorize('edit', $prospect);
        $branches = Branch::where('is_active', true)->get();
        return view('prospects.edit', compact('prospect', 'branches'));
    }

    public function update(Request $request, Prospect $prospect)
    {
        $this->authorize('edit', $prospect);

        $validated = $request->validate([
            'prospect_name' => 'required|string|max:255',
            'input_type' => 'required|in:LEGAL_ENTITY,BRAND,GROUP,PROPERTY,SUBSIDIARY,UNKNOWN',
            'legal_entity_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'group_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'estimated_premium' => 'nullable|numeric|min:0',
            'client_pic_name' => 'nullable|string|max:255',
            'client_pic_position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $old = $prospect->toArray();
        $prospect->update($validated);

        AuditLog::record('PROSPECT_UPDATED', 'Prospect', $prospect->id, $old, $prospect->fresh()->toArray());

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Prospect berhasil diperbarui.');
    }

    public function destroy(Prospect $prospect)
    {
        $this->authorize('delete', $prospect);
        $prospect->update(['status' => 'CANCELLED']);
        AuditLog::record('PROSPECT_CANCELLED', 'Prospect', $prospect->id);
        return redirect()->route('prospects.index')->with('success', 'Prospect dibatalkan.');
    }

    public function submit(Prospect $prospect)
    {
        if ($prospect->status !== 'DRAFT') {
            return back()->with('error', 'Hanya prospect berstatus DRAFT yang bisa disubmit.');
        }

        $prospect->update(['status' => 'SUBMITTED']);
        $this->createSlaLog($prospect, 'SUBMITTED', 1);
        AuditLog::record('PROSPECT_SUBMITTED', 'Prospect', $prospect->id);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Prospect berhasil disubmit. Silakan jalankan verifikasi AI.');
    }

    public function triggerAi(Prospect $prospect)
    {
        if (!in_array($prospect->status, ['SUBMITTED', 'NEED_CLARIFICATION'])) {
            return back()->with('error', 'Status tidak valid untuk verifikasi AI.');
        }

        $prospect->update(['status' => 'AI_VERIFICATION']);

        try {
            $aiResult = $this->aiService->verify($prospect);
            $duplicateResult = $this->duplicateService->score($prospect);

            $riskLevel = $this->getRiskLevel($duplicateResult['score']);
            $newStatus = $this->determineStatus($duplicateResult['score'], $aiResult);

            $prospect->update([
                'risk_level' => $riskLevel,
                'duplicate_score' => $duplicateResult['score'],
                'status' => $newStatus,
            ]);

            $this->createSlaLog($prospect, $newStatus, $this->getSlaHours($newStatus));
            AuditLog::record('AI_VERIFICATION_EXECUTED', 'Prospect', $prospect->id, null, [
                'risk_level' => $riskLevel,
                'duplicate_score' => $duplicateResult['score'],
                'status' => $newStatus,
            ]);

            if (in_array($newStatus, ['DUPLICATE_REVIEW', 'BC_REVIEW'])) {
                $this->duplicateService->createConflicts($prospect, $duplicateResult);
            }

            $this->checklistService->generate($prospect);

        } catch (\Exception $e) {
            $prospect->update(['status' => 'SUBMITTED']);
            return back()->with('error', 'Verifikasi AI gagal: ' . $e->getMessage());
        }

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Verifikasi AI selesai.');
    }

    public function approve(Prospect $prospect)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor'])) {
            abort(403);
        }

        $request = request()->validate([
            'notes' => 'nullable|string',
        ]);

        $old = $prospect->status;
        $prospect->update(['status' => 'APPROVED_FOR_FOLLOW_UP']);
        $this->createSlaLog($prospect, 'APPROVED_FOR_FOLLOW_UP', 0);
        AuditLog::record('PROSPECT_APPROVED', 'Prospect', $prospect->id, ['status' => $old], ['status' => 'APPROVED_FOR_FOLLOW_UP', 'notes' => $request['notes'] ?? null]);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Prospect disetujui untuk follow-up.');
    }

    public function reject(Prospect $prospect)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor'])) {
            abort(403);
        }

        $request = request()->validate(['notes' => 'nullable|string']);

        $prospect->update(['status' => 'REJECTED']);
        AuditLog::record('PROSPECT_REJECTED', 'Prospect', $prospect->id, null, ['notes' => $request['notes'] ?? null]);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Prospect ditolak.');
    }

    public function requestClarification(Prospect $prospect)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor', 'underwriter'])) {
            abort(403);
        }

        $validated = request()->validate(['notes' => 'required|string']);

        $prospect->update(['status' => 'NEED_CLARIFICATION']);
        $this->createSlaLog($prospect, 'NEED_CLARIFICATION', 24);
        AuditLog::record('CLARIFICATION_REQUESTED', 'Prospect', $prospect->id, null, ['notes' => $validated['notes']]);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Permintaan klarifikasi terkirim.');
    }

    public function respondClarification(Prospect $prospect)
    {
        $validated = request()->validate(['notes' => 'required|string']);

        $prospect->update(['status' => 'SUBMITTED']);
        AuditLog::record('CLARIFICATION_ANSWERED', 'Prospect', $prospect->id, null, ['notes' => $validated['notes']]);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Klarifikasi berhasil dikirim.');
    }

    private function getRiskLevel(int $score): string
    {
        if ($score >= 85) return 'VERY_HIGH';
        if ($score >= 70) return 'HIGH';
        if ($score >= 50) return 'MEDIUM';
        return 'LOW';
    }

    private function determineStatus(int $score, array $aiResult): string
    {
        if ($score >= 70) return 'DUPLICATE_REVIEW';
        if ($score >= 50) return 'NEED_CLARIFICATION';
        if (isset($aiResult['recommended_action']) && $aiResult['recommended_action'] === 'NEED_CLARIFICATION') {
            return 'NEED_CLARIFICATION';
        }
        return 'BC_REVIEW';
    }

    private function getSlaHours(string $status): int
    {
        return match ($status) {
            'AI_VERIFICATION' => 0,
            'BC_REVIEW' => 8,
            'DUPLICATE_REVIEW' => 16,
            'LOA_REVIEW' => 16,
            'UW_REVIEW' => 24,
            'DOCUMENT_COMPLETION' => 8,
            'NEED_CLARIFICATION' => 24,
            default => 8,
        };
    }

    private function createSlaLog(Prospect $prospect, string $status, int $hours): void
    {
        $now = now();
        SlaLog::create([
            'prospect_id' => $prospect->id,
            'status' => $status,
            'started_at' => $now,
            'due_at' => $hours > 0 ? $now->addHours($hours) : null,
            'is_overdue' => false,
        ]);
    }
}
