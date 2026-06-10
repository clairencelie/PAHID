<?php

namespace App\Services\Ai;

class MockAiClient implements AiClientInterface
{
    public function verifyEntity(array $prospectData): array
    {
        $name = strtolower($prospectData['prospect_name'] ?? '');

        // Case 1 — Shangri-La Hotel Surabaya
        if (str_contains($name, 'shangri') || str_contains($name, 'saripuri')) {
            return [
                'detected_type' => 'BRAND_OR_PROPERTY',
                'normalized_name' => 'shangri la hotel surabaya',
                'possible_legal_entities' => ['PT Saripuri Permai Hotel'],
                'possible_brand' => 'Shangri-La Hotel Surabaya',
                'possible_group' => 'Shangri-La International',
                'possible_occupation' => 'Hotel / Hospitality',
                'possible_address' => 'Surabaya',
                'duplicate_risk' => 'HIGH',
                'confidence_score' => 87,
                'matched_existing_prospects' => [],
                'reasons' => [
                    'Input appears to be a hotel/property brand rather than legal entity',
                    'Possible legal entity is PT Saripuri Permai Hotel',
                    'Occupation and city suggest hospitality sector in Surabaya',
                ],
                'missing_data' => ['NIB', 'NPWP', 'Official legal entity confirmation'],
                'recommended_action' => 'NEED_CLARIFICATION',
            ];
        }

        // Case 2 — Logisly
        if (str_contains($name, 'logisly') || str_contains($name, 'logistik canggih')) {
            return [
                'detected_type' => 'BRAND',
                'normalized_name' => 'logisly',
                'possible_legal_entities' => ['PT Logistik Canggih Indonesia'],
                'possible_brand' => 'Logisly',
                'possible_group' => null,
                'possible_occupation' => 'Logistics / Technology',
                'possible_address' => 'Jakarta',
                'duplicate_risk' => 'HIGH',
                'confidence_score' => 82,
                'matched_existing_prospects' => [],
                'reasons' => [
                    'Logisly is a known brand name for PT Logistik Canggih Indonesia',
                    'Brand and legal entity may already have active Single Support',
                ],
                'missing_data' => ['Legal entity confirmation', 'NPWP'],
                'recommended_action' => 'NEED_CLARIFICATION',
            ];
        }

        // Case 3 — Dharma Wibawa Guna Group
        if (str_contains($name, 'dharma wibawa') || str_contains($name, 'dwg')) {
            return [
                'detected_type' => 'GROUP',
                'normalized_name' => 'dharma wibawa guna group',
                'possible_legal_entities' => [
                    'PT Alam Semesta Agro',
                    'PT Bangun Sahabat Tani',
                    'PT Delta Giri Wacana',
                ],
                'possible_brand' => null,
                'possible_group' => 'Dharma Wibawa Guna Group',
                'possible_occupation' => 'Agriculture / Agribusiness',
                'possible_address' => null,
                'duplicate_risk' => 'MEDIUM',
                'confidence_score' => 75,
                'matched_existing_prospects' => [],
                'reasons' => [
                    'Input is a group name without specific legal entity',
                    'Group has multiple subsidiary entities',
                    'Group-level lock requires valid group-level evidence',
                ],
                'missing_data' => ['Specific legal entity name', 'LOA from group level', 'NIB'],
                'recommended_action' => 'NEED_CLARIFICATION',
            ];
        }

        // Case 4 — Generic ABC Group
        if (str_contains($name, 'group') && strlen($name) < 20) {
            return [
                'detected_type' => 'GROUP',
                'normalized_name' => $name,
                'possible_legal_entities' => [],
                'possible_brand' => null,
                'possible_group' => $prospectData['prospect_name'],
                'possible_occupation' => null,
                'possible_address' => null,
                'duplicate_risk' => 'MEDIUM',
                'confidence_score' => 45,
                'matched_existing_prospects' => [],
                'reasons' => [
                    'Input appears to be a group name only',
                    'No legal entity specified',
                    'Cannot determine subsidiaries from group name alone',
                ],
                'missing_data' => ['Legal entity name', 'Group ownership structure', 'NPWP', 'NIB'],
                'recommended_action' => 'NEED_CLARIFICATION',
            ];
        }

        // Default: clear prospect
        return [
            'detected_type' => $prospectData['input_type'] ?? 'LEGAL_ENTITY',
            'normalized_name' => strtolower($prospectData['prospect_name'] ?? ''),
            'possible_legal_entities' => [],
            'possible_brand' => null,
            'possible_group' => null,
            'possible_occupation' => $prospectData['occupation'] ?? null,
            'possible_address' => $prospectData['city'] ?? null,
            'duplicate_risk' => 'LOW',
            'confidence_score' => 70,
            'matched_existing_prospects' => [],
            'reasons' => [
                'Input appears to be a valid legal entity name',
                'No significant duplicate risk detected in current data',
            ],
            'missing_data' => [],
            'recommended_action' => 'CLEAR',
        ];
    }

    public function checkLoa(string $loaText, array $context = []): array
    {
        $text = strtolower($loaText);

        $hasIssuerName = strlen($loaText) > 50;
        $hasDate = preg_match('/\d{4}/', $loaText);
        $hasScope = str_contains($text, 'asuransi') || str_contains($text, 'insurance') || str_contains($text, 'kuasa');
        $hasHighPosition = str_contains($text, 'direktur') || str_contains($text, 'director')
            || str_contains($text, 'presiden') || str_contains($text, 'ceo')
            || str_contains($text, 'komisaris');

        $redFlags = [];
        $score = 50;

        if (!$hasIssuerName) {
            $redFlags[] = 'Issuer name is not clearly mentioned';
            $score -= 15;
        }
        if (!$hasDate) {
            $redFlags[] = 'Validity date or signing date is not mentioned';
            $score -= 15;
        }
        if (!$hasScope) {
            $redFlags[] = 'LOA scope related to insurance is unclear';
            $score -= 15;
        }
        if (!$hasHighPosition) {
            $redFlags[] = 'Issuer position may be too low or not mentioned';
            $score -= 10;
        }

        $score = max(0, min(100, $score));

        if ($score >= 75) {
            $status = 'VALID';
            $action = 'CLEAR';
        } elseif ($score >= 50) {
            $status = 'NEED_CLARIFICATION';
            $action = 'NEED_CLARIFICATION';
        } else {
            $status = 'SUSPICIOUS';
            $action = 'NEED_CLARIFICATION';
        }

        return [
            'loa_status' => $status,
            'loa_score' => $score,
            'issuer_name' => $hasIssuerName ? 'Detected from text' : 'Not clearly identified',
            'issuer_position' => $hasHighPosition ? 'Adequate authority' : 'Staff or unknown',
            'entity_scope' => $hasScope ? 'Insurance related' : 'Unclear',
            'validity_period' => $hasDate ? 'Mentioned' : 'Not mentioned',
            'appointed_party' => 'Check document for details',
            'red_flags' => $redFlags,
            'recommended_action' => $action,
        ];
    }
}
