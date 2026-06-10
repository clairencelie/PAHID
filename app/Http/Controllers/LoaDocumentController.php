<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoaDocument;
use App\Models\Prospect;
use App\Services\Prospect\AiLoaCheckerService;
use Illuminate\Http\Request;

class LoaDocumentController extends Controller
{
    public function __construct(private AiLoaCheckerService $loaService) {}

    public function store(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'extracted_text' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('loa-documents', 'local');
        }

        $loa = LoaDocument::create([
            'prospect_id' => $prospect->id,
            'file_path' => $filePath,
            'extracted_text' => $validated['extracted_text'] ?? null,
        ]);

        AuditLog::record('LOA_UPLOADED', 'Prospect', $prospect->id, null, ['loa_id' => $loa->id]);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'LOA berhasil diupload.');
    }

    public function check(LoaDocument $loa)
    {
        try {
            $result = $this->loaService->check($loa);
            AuditLog::record('LOA_CHECKED', 'Prospect', $loa->prospect_id, null, [
                'loa_id' => $loa->id,
                'status' => $result['loa_status'],
                'score' => $result['loa_score'],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Pengecekan LOA gagal: ' . $e->getMessage());
        }

        return redirect()->route('prospects.show', $loa->prospect)
            ->with('success', 'LOA berhasil dicek.');
    }

    public function review(Request $request, LoaDocument $loa)
    {
        if (!auth()->user()->hasRole(['bc', 'supervisor'])) {
            abort(403);
        }

        $validated = $request->validate([
            'loa_status' => 'required|in:VALID,NEED_CLARIFICATION,SUSPICIOUS,REJECT_RECOMMENDED',
            'notes' => 'nullable|string',
        ]);

        $loa->update([
            'loa_status' => $validated['loa_status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::record('LOA_REVIEWED', 'Prospect', $loa->prospect_id, null, [
            'loa_id' => $loa->id,
            'status' => $validated['loa_status'],
        ]);

        return redirect()->route('prospects.show', $loa->prospect)
            ->with('success', 'Review LOA berhasil disimpan.');
    }
}
