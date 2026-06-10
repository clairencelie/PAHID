<?php

namespace App\Http\Controllers;

use App\Models\DocumentChecklist;
use App\Models\Prospect;
use App\Services\Prospect\DocumentChecklistService;
use Illuminate\Http\Request;

class DocumentChecklistController extends Controller
{
    public function __construct(private DocumentChecklistService $checklistService) {}

    public function generate(Prospect $prospect)
    {
        $this->checklistService->generate($prospect);
        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Checklist berhasil digenerate.');
    }

    public function update(Request $request, DocumentChecklist $checklist)
    {
        if (!auth()->user()->hasRole(['bc', 'underwriter', 'supervisor'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:COMPLETE,INCOMPLETE,INVALID,NEED_CLARIFICATION',
            'notes' => 'nullable|string',
        ]);

        $checklist->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Checklist item diperbarui.');
    }
}
