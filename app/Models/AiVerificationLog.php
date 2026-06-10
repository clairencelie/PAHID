<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiVerificationLog extends Model
{
    protected $fillable = [
        'prospect_id', 'provider', 'model', 'prompt',
        'response_json', 'confidence_score', 'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
