<?php

namespace App\Services\Prospect;

use App\Models\LoaDocument;
use App\Services\Ai\AiClientInterface;

class AiLoaCheckerService
{
    public function __construct(private AiClientInterface $aiClient) {}

    public function check(LoaDocument $loa): array
    {
        $text = $loa->extracted_text ?? '';

        if (empty($text)) {
            return [
                'loa_status' => 'NEED_CLARIFICATION',
                'loa_score' => 0,
                'issuer_name' => 'Not provided',
                'issuer_position' => 'Not provided',
                'entity_scope' => 'Not provided',
                'validity_period' => 'Not provided',
                'appointed_party' => 'Not provided',
                'red_flags' => ['LOA text is empty — cannot analyze'],
                'recommended_action' => 'NEED_CLARIFICATION',
            ];
        }

        $result = $this->aiClient->checkLoa($text);

        $loa->update([
            'issuer_name' => $result['issuer_name'] ?? null,
            'issuer_position' => $result['issuer_position'] ?? null,
            'entity_scope' => $result['entity_scope'] ?? null,
            'validity_period' => $result['validity_period'] ?? null,
            'appointed_party' => $result['appointed_party'] ?? null,
            'loa_score' => $result['loa_score'] ?? null,
            'loa_status' => $result['loa_status'] ?? null,
            'red_flags_json' => $result['red_flags'] ?? [],
            'ai_result_json' => $result,
        ]);

        return $result;
    }
}
