<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient implements AiClientInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->timeout = config('services.gemini.timeout', 60);
    }

    public function verifyEntity(array $prospectData): array
    {
        $prompt = $this->buildEntityVerificationPrompt($prospectData);
        $response = $this->callGemini($prompt);
        return $this->parseJsonResponse($response);
    }

    public function checkLoa(string $loaText, array $context = []): array
    {
        $prompt = $this->buildLoaCheckPrompt($loaText, $context);
        $response = $this->callGemini($prompt);
        return $this->parseJsonResponse($response);
    }

    private function callGemini(string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($this->timeout)->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gemini API request failed: ' . $response->status());
        }

        return $response->json('candidates.0.content.parts.0.text', '{}');
    }

    private function parseJsonResponse(string $text): array
    {
        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response from Gemini');
        }
        return $decoded;
    }

    private function buildEntityVerificationPrompt(array $data): string
    {
        return <<<PROMPT
        You are an expert insurance underwriter analyzing a corporate prospect for A&H insurance.

        Analyze the following prospect input and return a structured JSON response.

        Prospect Data:
        - Name: {$data['prospect_name']}
        - Input Type: {$data['input_type']}
        - Legal Entity Name: {$data['legal_entity_name']}
        - Brand Name: {$data['brand_name']}
        - Group Name: {$data['group_name']}
        - Address: {$data['address']}
        - City: {$data['city']}
        - Occupation: {$data['occupation']}

        Return ONLY valid JSON with this exact structure:
        {
          "detected_type": "LEGAL_ENTITY|BRAND|BRAND_OR_PROPERTY|GROUP|SUBSIDIARY|UNKNOWN",
          "normalized_name": "lowercase normalized name",
          "possible_legal_entities": ["array of possible legal entity names"],
          "possible_brand": "brand name or null",
          "possible_group": "group name or null",
          "possible_occupation": "occupation/industry",
          "possible_address": "city/address",
          "duplicate_risk": "LOW|MEDIUM|HIGH|VERY_HIGH",
          "confidence_score": 0-100,
          "matched_existing_prospects": [],
          "reasons": ["array of reasons"],
          "missing_data": ["array of missing documents or data"],
          "recommended_action": "CLEAR|NEED_CLARIFICATION|HOLD|ESCALATE"
        }
        PROMPT;
    }

    private function buildLoaCheckPrompt(string $loaText, array $context): string
    {
        return <<<PROMPT
        You are an expert insurance underwriter reviewing a Letter of Acknowledgement (LOA) document.

        Analyze the following LOA text and return a structured JSON response.
        Focus on: issuer authority, scope clarity, validity period, appointed party, and red flags.
        Do NOT claim the document is fake. Only flag concerns.

        LOA Text:
        {$loaText}

        Return ONLY valid JSON with this exact structure:
        {
          "loa_status": "VALID|NEED_CLARIFICATION|SUSPICIOUS|REJECT_RECOMMENDED",
          "loa_score": 0-100,
          "issuer_name": "name or Unknown",
          "issuer_position": "position or Unknown",
          "entity_scope": "scope description or Unclear",
          "validity_period": "period or Not mentioned",
          "appointed_party": "party or Unclear",
          "red_flags": ["array of red flags"],
          "recommended_action": "CLEAR|NEED_CLARIFICATION|HOLD|ESCALATE"
        }
        PROMPT;
    }
}
