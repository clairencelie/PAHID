<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTask extends Model
{
    protected $fillable = [
        'prospect_id', 'assigned_to', 'role', 'task_type',
        'status', 'due_at', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
