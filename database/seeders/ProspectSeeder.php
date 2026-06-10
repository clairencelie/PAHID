<?php

namespace Database\Seeders;

use App\Models\AiVerificationLog;
use App\Models\Branch;
use App\Models\DocumentChecklist;
use App\Models\Entity;
use App\Models\LoaDocument;
use App\Models\Prospect;
use App\Models\ProtectedProspectAlias;
use App\Models\SingleSupportAssignment;
use App\Models\SingleSupportConflict;
use App\Models\SlaLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProspectSeeder extends Seeder
{
    public function run(): void
    {
        $sby = Branch::where('code', 'SBY')->first();
        $jkt = Branch::where('code', 'JKT')->first();
        $marketingA = User::where('email', 'marketing.a@pahid.test')->first();
        $marketingB = User::where('email', 'marketing.b@pahid.test')->first();
        $supervisor = User::where('email', 'supervisor@pahid.test')->first();
        $saripuri = Entity::where('legal_name', 'PT Saripuri Permai Hotel')->first();

        // --- Case 1: Approved Shangri-La (Surabaya) ---
        $approvedProspect = Prospect::firstOrCreate(
            ['prospect_code' => 'PRO-DEMO001'],
            [
                'prospect_name' => 'Shangri-La Hotel Surabaya',
                'input_type' => 'BRAND',
                'legal_entity_name' => 'PT Saripuri Permai Hotel',
                'brand_name' => 'Shangri-La Hotel Surabaya',
                'city' => 'Surabaya',
                'occupation' => 'Hotel / Hospitality',
                'estimated_premium' => 500000000,
                'client_pic_name' => 'Budi Santoso',
                'client_pic_position' => 'HR Director',
                'branch_id' => $sby->id,
                'marketing_user_id' => $marketingA->id,
                'status' => 'APPROVED_FOR_FOLLOW_UP',
                'risk_level' => 'MEDIUM',
                'duplicate_score' => 35,
            ]
        );

        AiVerificationLog::firstOrCreate(
            ['prospect_id' => $approvedProspect->id],
            [
                'provider' => 'mock',
                'model' => 'mock-v1',
                'response_json' => [
                    'detected_type' => 'BRAND_OR_PROPERTY',
                    'normalized_name' => 'shangri la hotel surabaya',
                    'possible_legal_entities' => ['PT Saripuri Permai Hotel'],
                    'possible_brand' => 'Shangri-La Hotel Surabaya',
                    'possible_group' => 'Shangri-La International',
                    'possible_occupation' => 'Hotel / Hospitality',
                    'duplicate_risk' => 'MEDIUM',
                    'confidence_score' => 87,
                    'reasons' => [
                        'Input is a hotel/property brand, not legal entity',
                        'Matched to PT Saripuri Permai Hotel in master data',
                    ],
                    'missing_data' => ['NIB', 'NPWP'],
                    'recommended_action' => 'NEED_CLARIFICATION',
                ],
                'confidence_score' => 87,
                'risk_level' => 'MEDIUM',
            ]
        );

        // Create Active Single Support Assignment
        $assignment = SingleSupportAssignment::firstOrCreate(
            ['prospect_id' => $approvedProspect->id],
            [
                'entity_id' => $saripuri->id,
                'assignment_level' => 'ENTITY',
                'branch_id' => $sby->id,
                'marketing_user_id' => $marketingA->id,
                'approved_by' => $supervisor->id,
                'approval_source' => 'FIRST_VALID_REGISTRATION',
                'approval_reason' => 'First valid registration with legal entity confirmed by BC',
                'status' => 'ACTIVE',
                'effective_from' => now()->subDays(30)->toDateString(),
            ]
        );

        // Protected aliases for this assignment
        $aliases = [
            ['alias_name' => 'Shangri-La Hotel Surabaya', 'alias_type' => 'BRAND', 'source' => 'USER_INPUT', 'confidence_score' => 100],
            ['alias_name' => 'Hotel Shangri-La Surabaya', 'alias_type' => 'BRAND', 'source' => 'AI_DETECTED', 'confidence_score' => 90],
            ['alias_name' => 'PT Saripuri Permai Hotel', 'alias_type' => 'LEGAL_ENTITY', 'source' => 'USER_INPUT', 'confidence_score' => 100],
            ['alias_name' => 'Saripuri Permai Hotel', 'alias_type' => 'LEGAL_ENTITY', 'source' => 'AI_DETECTED', 'confidence_score' => 90],
            ['alias_name' => 'PT Saripuri Permai', 'alias_type' => 'LEGAL_ENTITY', 'source' => 'AI_DETECTED', 'confidence_score' => 85],
        ];

        foreach ($aliases as $aliasData) {
            ProtectedProspectAlias::firstOrCreate(
                ['single_support_assignment_id' => $assignment->id, 'alias_name' => $aliasData['alias_name']],
                array_merge($aliasData, [
                    'single_support_assignment_id' => $assignment->id,
                    'normalized_alias_name' => strtolower($aliasData['alias_name']),
                ])
            );
        }

        // --- Case 2: New submission that conflicts (Jakarta submits Saripuri Permai Hotel) ---
        $conflictProspect = Prospect::firstOrCreate(
            ['prospect_code' => 'PRO-DEMO002'],
            [
                'prospect_name' => 'Saripuri Permai Hotel',
                'input_type' => 'BRAND',
                'city' => 'Surabaya',
                'occupation' => 'Hotel / Hospitality',
                'estimated_premium' => 450000000,
                'client_pic_name' => 'Andi Wijaya',
                'client_pic_position' => 'Finance Manager',
                'branch_id' => $jkt->id,
                'marketing_user_id' => $marketingB->id,
                'status' => 'DUPLICATE_REVIEW',
                'risk_level' => 'VERY_HIGH',
                'duplicate_score' => 91,
            ]
        );

        SingleSupportConflict::firstOrCreate(
            ['new_prospect_id' => $conflictProspect->id, 'existing_assignment_id' => $assignment->id],
            [
                'conflict_type' => 'ALIAS_MATCH',
                'conflict_score' => 91,
                'risk_level' => 'VERY_HIGH',
                'detected_alias' => 'Saripuri Permai Hotel',
                'matched_alias' => 'Saripuri Permai Hotel',
                'ai_reasons_json' => [
                    'Input name matches protected alias "Saripuri Permai Hotel"',
                    'Legal entity appears similar to PT Saripuri Permai Hotel',
                    'Existing Single Support Assignment is ACTIVE by Cabang Surabaya',
                ],
                'status' => 'OPEN',
            ]
        );

        // --- Case 3: Logisly submission ---
        $logislyProspect = Prospect::firstOrCreate(
            ['prospect_code' => 'PRO-DEMO003'],
            [
                'prospect_name' => 'Logisly',
                'input_type' => 'BRAND',
                'city' => 'Jakarta',
                'occupation' => 'Logistics / Technology',
                'estimated_premium' => 200000000,
                'branch_id' => $jkt->id,
                'marketing_user_id' => $marketingB->id,
                'status' => 'NEED_CLARIFICATION',
                'risk_level' => 'HIGH',
                'duplicate_score' => 72,
            ]
        );

        AiVerificationLog::firstOrCreate(
            ['prospect_id' => $logislyProspect->id],
            [
                'provider' => 'mock',
                'model' => 'mock-v1',
                'response_json' => [
                    'detected_type' => 'BRAND',
                    'normalized_name' => 'logisly',
                    'possible_legal_entities' => ['PT Logistik Canggih Indonesia'],
                    'possible_brand' => 'Logisly',
                    'duplicate_risk' => 'HIGH',
                    'confidence_score' => 82,
                    'reasons' => ['Logisly is a brand name for PT Logistik Canggih Indonesia'],
                    'missing_data' => ['Legal entity confirmation', 'NPWP'],
                    'recommended_action' => 'NEED_CLARIFICATION',
                ],
                'confidence_score' => 82,
                'risk_level' => 'HIGH',
            ]
        );

        // --- Case 4: Suspicious LOA ---
        $loaProspect = Prospect::firstOrCreate(
            ['prospect_code' => 'PRO-DEMO004'],
            [
                'prospect_name' => 'PT Maju Bersama Insurance',
                'input_type' => 'LEGAL_ENTITY',
                'legal_entity_name' => 'PT Maju Bersama Insurance',
                'city' => 'Jakarta',
                'occupation' => 'Insurance Services',
                'estimated_premium' => 150000000,
                'branch_id' => $jkt->id,
                'marketing_user_id' => $marketingB->id,
                'status' => 'LOA_REVIEW',
                'risk_level' => 'MEDIUM',
                'duplicate_score' => 20,
            ]
        );

        LoaDocument::firstOrCreate(
            ['prospect_id' => $loaProspect->id],
            [
                'extracted_text' => 'Dengan ini kami memberikan kuasa kepada pihak asuransi untuk melakukan pengurusan. Ttd: Staff Admin.',
                'issuer_name' => 'Unknown',
                'issuer_position' => 'Staff',
                'entity_scope' => 'Unclear',
                'validity_period' => 'Not mentioned',
                'appointed_party' => 'Unclear',
                'loa_score' => 25,
                'loa_status' => 'SUSPICIOUS',
                'red_flags_json' => [
                    'Issuer position may be too low (Staff)',
                    'Scope is not clear',
                    'Validity period is not mentioned',
                    'Document content appears incomplete',
                ],
                'ai_result_json' => [
                    'loa_status' => 'SUSPICIOUS',
                    'loa_score' => 25,
                    'recommended_action' => 'NEED_CLARIFICATION',
                ],
            ]
        );

        // SLA log for demo
        SlaLog::firstOrCreate(
            ['prospect_id' => $conflictProspect->id, 'status' => 'DUPLICATE_REVIEW'],
            [
                'started_at' => now()->subDays(3),
                'due_at' => now()->subDays(1),
                'is_overdue' => true,
            ]
        );
    }
}
