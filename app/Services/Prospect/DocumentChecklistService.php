<?php

namespace App\Services\Prospect;

use App\Models\DocumentChecklist;
use App\Models\Prospect;

class DocumentChecklistService
{
    private array $spqItems = [
        ['item_name' => 'Form declaration tersedia', 'is_critical' => true],
        ['item_name' => 'Form declaration tidak kosong', 'is_critical' => true],
        ['item_name' => 'Data peserta tersedia', 'is_critical' => true],
        ['item_name' => 'Benefit existing tersedia (jika ada polis existing)', 'is_critical' => false],
        ['item_name' => 'TC existing tersedia (jika ada polis existing)', 'is_critical' => false],
        ['item_name' => 'Claim history tersedia', 'is_critical' => false],
        ['item_name' => 'Claim DOL tersedia', 'is_critical' => false],
        ['item_name' => 'Claim proposed tersedia', 'is_critical' => false],
        ['item_name' => 'Claim paid tersedia', 'is_critical' => false],
        ['item_name' => 'Detail occupasi/LOB tersedia', 'is_critical' => true],
    ];

    private array $policyItems = [
        ['item_name' => 'NPWP perusahaan tersedia', 'is_critical' => true],
        ['item_name' => 'NPWP bukan milik pribadi pemilik', 'is_critical' => true],
        ['item_name' => 'NIB tersedia', 'is_critical' => true],
        ['item_name' => 'SIUP/SITU/TDP tersedia (jika diperlukan)', 'is_critical' => false],
        ['item_name' => 'Nama legal entity sesuai dengan prospect', 'is_critical' => true],
        ['item_name' => 'Alamat sesuai atau memerlukan klarifikasi', 'is_critical' => false],
        ['item_name' => 'LOA/surat kuasa tersedia (jika berlaku)', 'is_critical' => false],
    ];

    public function generate(Prospect $prospect): void
    {
        // Don't regenerate if already exists
        if ($prospect->documentChecklists()->exists()) {
            return;
        }

        $items = [];

        foreach ($this->spqItems as $item) {
            $items[] = [
                'prospect_id' => $prospect->id,
                'checklist_type' => 'SPQ',
                'item_name' => $item['item_name'],
                'is_critical' => $item['is_critical'],
                'status' => 'INCOMPLETE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($this->policyItems as $item) {
            $items[] = [
                'prospect_id' => $prospect->id,
                'checklist_type' => 'POLICY_ISSUANCE',
                'item_name' => $item['item_name'],
                'is_critical' => $item['is_critical'],
                'status' => 'INCOMPLETE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DocumentChecklist::insert($items);
    }

    public function hasCriticalIncomplete(Prospect $prospect): bool
    {
        return $prospect->documentChecklists()
            ->where('is_critical', true)
            ->whereIn('status', ['INCOMPLETE', 'INVALID'])
            ->exists();
    }
}
