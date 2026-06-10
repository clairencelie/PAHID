<?php

namespace App\Services\Prospect;

use App\Models\AuditLog;
use App\Models\ProtectedProspectAlias;
use App\Models\SingleSupportAssignment;
use App\Services\Ai\AiClientInterface;

class SingleSupportAssignmentService
{
    public function __construct(private AiClientInterface $aiClient) {}

    public function createFromProspect(\App\Models\Prospect $prospect, array $data): SingleSupportAssignment
    {
        $assignment = SingleSupportAssignment::create([
            'prospect_id' => $prospect->id,
            'entity_id' => $data['entity_id'] ?? null,
            'entity_group_id' => $data['entity_group_id'] ?? null,
            'assignment_level' => $data['assignment_level'],
            'branch_id' => $prospect->branch_id,
            'marketing_user_id' => $prospect->marketing_user_id,
            'approved_by' => auth()->id(),
            'approval_source' => $data['approval_source'],
            'approval_reason' => $data['approval_reason'] ?? null,
            'loa_document_id' => $data['loa_document_id'] ?? null,
            'status' => 'ACTIVE',
            'effective_from' => now()->toDateString(),
        ]);

        $this->generateProtectedAliases($assignment, $prospect, $data);

        AuditLog::record('SINGLE_SUPPORT_ASSIGNMENT_CREATED', 'SingleSupportAssignment', $assignment->id, null, $assignment->toArray());

        return $assignment;
    }

    private function generateProtectedAliases(SingleSupportAssignment $assignment, \App\Models\Prospect $prospect, array $data): void
    {
        $aliases = [];

        // Prospect name itself
        $this->addAlias($aliases, $assignment->id, $prospect->prospect_name, 'OTHER', 'USER_INPUT', 100);

        // Legal entity
        if ($prospect->legal_entity_name) {
            $this->addAlias($aliases, $assignment->id, $prospect->legal_entity_name, 'LEGAL_ENTITY', 'USER_INPUT', 100);

            // Short form without PT/CV
            $short = preg_replace('/^(pt|cv)\s+/i', '', $prospect->legal_entity_name);
            if ($short !== $prospect->legal_entity_name) {
                $this->addAlias($aliases, $assignment->id, trim($short), 'LEGAL_ENTITY', 'AI_DETECTED', 90);
            }
        }

        // Brand name
        if ($prospect->brand_name) {
            $this->addAlias($aliases, $assignment->id, $prospect->brand_name, 'BRAND', 'USER_INPUT', 100);
        }

        // Group name
        if ($prospect->group_name) {
            $this->addAlias($aliases, $assignment->id, $prospect->group_name, 'GROUP', 'USER_INPUT', 85);
        }

        // AI-suggested aliases from verification log
        $aiLog = $prospect->latestAiVerification;
        if ($aiLog && $aiLog->response_json) {
            $aiResult = $aiLog->response_json;

            foreach ($aiResult['possible_legal_entities'] ?? [] as $entity) {
                $this->addAlias($aliases, $assignment->id, $entity, 'LEGAL_ENTITY', 'AI_DETECTED', 80);
                $short = preg_replace('/^(pt|cv)\s+/i', '', $entity);
                if ($short !== $entity) {
                    $this->addAlias($aliases, $assignment->id, trim($short), 'LEGAL_ENTITY', 'AI_DETECTED', 75);
                }
            }

            if (!empty($aiResult['possible_brand'])) {
                $this->addAlias($aliases, $assignment->id, $aiResult['possible_brand'], 'BRAND', 'AI_DETECTED', 80);
            }
        }

        ProtectedProspectAlias::insert($aliases);
    }

    private function addAlias(array &$aliases, int $assignmentId, string $name, string $type, string $source, int $confidence): void
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $name));
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        // Avoid duplicates
        foreach ($aliases as $existing) {
            if ($existing['normalized_alias_name'] === $normalized) {
                return;
            }
        }

        $aliases[] = [
            'single_support_assignment_id' => $assignmentId,
            'alias_name' => $name,
            'normalized_alias_name' => $normalized,
            'alias_type' => $type,
            'source' => $source,
            'confidence_score' => $confidence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
