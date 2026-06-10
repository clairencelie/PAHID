<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SingleSupportConflict extends Model
{
    protected $fillable = [
        'new_prospect_id', 'existing_assignment_id', 'conflict_type',
        'conflict_score', 'risk_level', 'detected_alias', 'matched_alias',
        'ai_reasons_json', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_reasons_json' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function newProspect()
    {
        return $this->belongsTo(Prospect::class, 'new_prospect_id');
    }

    public function existingAssignment()
    {
        return $this->belongsTo(SingleSupportAssignment::class, 'existing_assignment_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
