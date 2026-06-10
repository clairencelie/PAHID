<?php

namespace App\Services\Prospect;

use App\Models\AiVerificationLog;
use App\Models\Prospect;
use App\Services\Ai\AiClientInterface;

class AiEntityVerificationService
{
    public function __construct(private AiClientInterface $aiClient) {}

    public function verify(Prospect $prospect): array
    {
        $prospectData = [
            'prospect_name' => $prospect->prospect_name,
            'input_type' => $prospect->input_type,
            'legal_entity_name' => $prospect->legal_entity_name,
            'brand_name' => $prospect->brand_name,
            'group_name' => $prospect->group_name,
            'address' => $prospect->address,
            'city' => $prospect->city,
            'occupation' => $prospect->occupation,
        ];

        $result = $this->aiClient->verifyEntity($prospectData);

        AiVerificationLog::create([
            'prospect_id' => $prospect->id,
            'provider' => config('services.ai.provider', 'mock'),
            'model' => config('services.gemini.model', 'mock'),
            'response_json' => $result,
            'confidence_score' => $result['confidence_score'] ?? null,
            'risk_level' => $this->mapRiskLevel($result['duplicate_risk'] ?? 'LOW'),
        ]);

        return $result;
    }

    private function mapRiskLevel(string $risk): string
    {
        return match (strtoupper($risk)) {
            'HIGH' => 'HIGH',
            'VERY_HIGH' => 'VERY_HIGH',
            'MEDIUM' => 'MEDIUM',
            default => 'LOW',
        };
    }
}
