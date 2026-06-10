<?php

namespace App\Services\Prospect;

use App\Models\Entity;
use App\Models\EntityAlias;
use App\Models\EntityGroup;
use App\Models\Prospect;
use App\Models\ProspectConflict;
use App\Models\ProtectedProspectAlias;
use App\Models\SingleSupportAssignment;
use App\Models\SingleSupportConflict;

class DuplicateScoringService
{
    public function score(Prospect $prospect): array
    {
        $normalizedName = $this->normalize($prospect->prospect_name);
        $matches = [];
        $totalScore = 0;
        $reasons = [];

        // Check against active Single Support Assignments protected aliases (highest priority)
        $aliasMatches = $this->checkProtectedAliases($normalizedName, $prospect);
        if ($aliasMatches['score'] > 0) {
            $totalScore = max($totalScore, $aliasMatches['score']);
            $matches = array_merge($matches, $aliasMatches['matches']);
            $reasons = array_merge($reasons, $aliasMatches['reasons']);
        }

        // Check against existing prospects
        $prospectMatches = $this->checkExistingProspects($normalizedName, $prospect);
        if ($prospectMatches['score'] > 0) {
            $totalScore = max($totalScore, $prospectMatches['score']);
            $matches = array_merge($matches, $prospectMatches['matches']);
            $reasons = array_merge($reasons, $prospectMatches['reasons']);
        }

        // Check against master entities
        $entityMatches = $this->checkEntities($normalizedName, $prospect);
        if ($entityMatches['score'] > 0) {
            $totalScore = max($totalScore, $entityMatches['score']);
            $matches = array_merge($matches, $entityMatches['matches']);
            $reasons = array_merge($reasons, $entityMatches['reasons']);
        }

        return [
            'score' => min(100, $totalScore),
            'matches' => $matches,
            'reasons' => $reasons,
        ];
    }

    public function createConflicts(Prospect $prospect, array $scoringResult): void
    {
        foreach ($scoringResult['matches'] as $match) {
            if ($match['type'] === 'single_support') {
                SingleSupportConflict::create([
                    'new_prospect_id' => $prospect->id,
                    'existing_assignment_id' => $match['assignment_id'],
                    'conflict_type' => $match['conflict_type'],
                    'conflict_score' => $match['score'],
                    'risk_level' => $this->scoreToRiskLevel($match['score']),
                    'detected_alias' => $match['detected_alias'] ?? null,
                    'matched_alias' => $match['matched_alias'] ?? null,
                    'ai_reasons_json' => $scoringResult['reasons'],
                    'status' => 'OPEN',
                ]);
            } else {
                ProspectConflict::create([
                    'prospect_id' => $prospect->id,
                    'matched_prospect_id' => $match['matched_prospect_id'] ?? null,
                    'matched_entity_id' => $match['matched_entity_id'] ?? null,
                    'conflict_type' => $match['conflict_type'],
                    'score' => $match['score'],
                    'risk_level' => $this->scoreToRiskLevel($match['score']),
                    'reasons_json' => $scoringResult['reasons'],
                    'status' => 'OPEN',
                ]);
            }
        }
    }

    private function checkProtectedAliases(string $normalizedName, Prospect $prospect): array
    {
        $score = 0;
        $matches = [];
        $reasons = [];

        $aliases = ProtectedProspectAlias::all();

        foreach ($aliases as $alias) {
            $similarity = $this->similarityScore($normalizedName, $this->normalize($alias->alias_name));

            if ($similarity >= 70) {
                $assignment = $alias->assignment;
                if ($assignment && $assignment->isActive()) {
                    $aliasScore = (int) ($similarity * 0.9);
                    $score = max($score, $aliasScore);
                    $matches[] = [
                        'type' => 'single_support',
                        'assignment_id' => $assignment->id,
                        'conflict_type' => 'ALIAS_MATCH',
                        'score' => $aliasScore,
                        'detected_alias' => $prospect->prospect_name,
                        'matched_alias' => $alias->alias_name,
                    ];
                    $reasons[] = "Prospect name matches protected alias '{$alias->alias_name}' of active Single Support assignment by {$assignment->branch->name}";
                }
            }
        }

        return compact('score', 'matches', 'reasons');
    }

    private function checkExistingProspects(string $normalizedName, Prospect $prospect): array
    {
        $score = 0;
        $matches = [];
        $reasons = [];

        $existing = Prospect::where('id', '!=', $prospect->id)
            ->whereNotIn('status', ['CANCELLED', 'REJECTED'])
            ->get();

        foreach ($existing as $other) {
            $nameSimilarity = $this->similarityScore($normalizedName, $this->normalize($other->prospect_name));

            $componentScore = 0;
            $componentScore += $nameSimilarity * 0.25;

            if ($prospect->legal_entity_name && $other->legal_entity_name) {
                $componentScore += $this->similarityScore(
                    $this->normalize($prospect->legal_entity_name),
                    $this->normalize($other->legal_entity_name)
                ) * 0.20;
            }

            if ($prospect->city && $other->city) {
                $componentScore += $this->similarityScore(
                    $this->normalize($prospect->city),
                    $this->normalize($other->city)
                ) * 0.15;
            }

            if ($prospect->occupation && $other->occupation) {
                $componentScore += $this->similarityScore(
                    $this->normalize($prospect->occupation),
                    $this->normalize($other->occupation)
                ) * 0.05;
            }

            if ($componentScore >= 40) {
                $score = max($score, (int) $componentScore);
                $matches[] = [
                    'type' => 'prospect',
                    'matched_prospect_id' => $other->id,
                    'conflict_type' => 'DUPLICATE_ENTITY',
                    'score' => (int) $componentScore,
                ];
                $reasons[] = "Similar to existing prospect '{$other->prospect_name}' ({$other->prospect_code}) with {$componentScore}% similarity";
            }
        }

        return compact('score', 'matches', 'reasons');
    }

    private function checkEntities(string $normalizedName, Prospect $prospect): array
    {
        $score = 0;
        $matches = [];
        $reasons = [];

        $entities = Entity::all();

        foreach ($entities as $entity) {
            $nameSimilarity = $this->similarityScore($normalizedName, $this->normalize($entity->legal_name));

            // Also check all aliases
            $aliasSimilarity = 0;
            foreach ($entity->aliases as $alias) {
                $aliasSimilarity = max($aliasSimilarity, $this->similarityScore($normalizedName, $this->normalize($alias->alias_name)));
            }

            $maxSimilarity = max($nameSimilarity, $aliasSimilarity);

            if ($maxSimilarity >= 50) {
                $entityScore = (int) ($maxSimilarity * 0.8);
                $score = max($score, $entityScore);
                $matches[] = [
                    'type' => 'entity',
                    'matched_entity_id' => $entity->id,
                    'conflict_type' => 'DUPLICATE_ENTITY',
                    'score' => $entityScore,
                ];
                $reasons[] = "Matches master entity '{$entity->legal_name}' with {$maxSimilarity}% similarity";
            }
        }

        return compact('score', 'matches', 'reasons');
    }

    private function similarityScore(string $a, string $b): float
    {
        if ($a === $b) return 100.0;
        if (empty($a) || empty($b)) return 0.0;

        // Check substring containment
        if (str_contains($b, $a) || str_contains($a, $b)) {
            return 80.0;
        }

        // Use similar_text for fuzzy match
        similar_text($a, $b, $percent);

        return round($percent, 2);
    }

    public function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/\b(pt|cv|tbk|persero|indonesia|group|hotel|surabaya|jakarta|bandung)\b/', '', $text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function scoreToRiskLevel(int $score): string
    {
        if ($score >= 85) return 'VERY_HIGH';
        if ($score >= 70) return 'HIGH';
        if ($score >= 50) return 'MEDIUM';
        return 'LOW';
    }
}
