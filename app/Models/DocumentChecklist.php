<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentChecklist extends Model
{
    protected $fillable = [
        'prospect_id', 'checklist_type', 'item_name', 'is_critical',
        'status', 'notes', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
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
}
