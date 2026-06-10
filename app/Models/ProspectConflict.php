<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectConflict extends Model
{
    protected $fillable = [
        'prospect_id', 'matched_prospect_id', 'matched_entity_id',
        'conflict_type', 'score', 'risk_level', 'reasons_json',
        'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reasons_json' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function matchedProspect()
    {
        return $this->belongsTo(Prospect::class, 'matched_prospect_id');
    }

    public function matchedEntity()
    {
        return $this->belongsTo(Entity::class, 'matched_entity_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
