<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaLog extends Model
{
    protected $fillable = [
        'prospect_id', 'status', 'started_at', 'due_at', 'completed_at', 'is_overdue',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_overdue' => 'boolean',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
