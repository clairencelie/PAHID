<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaDocument extends Model
{
    protected $fillable = [
        'prospect_id', 'file_path', 'extracted_text', 'issuer_name',
        'issuer_position', 'entity_scope', 'validity_period', 'appointed_party',
        'loa_score', 'loa_status', 'red_flags_json', 'ai_result_json',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'red_flags_json' => 'array',
            'ai_result_json' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->loa_status) {
            'VALID' => 'badge-success',
            'NEED_CLARIFICATION' => 'badge-warning',
            'SUSPICIOUS' => 'badge-danger',
            'REJECT_RECOMMENDED' => 'badge-dark',
            default => 'badge-secondary',
        };
    }
}
