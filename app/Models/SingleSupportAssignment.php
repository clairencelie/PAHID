<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SingleSupportAssignment extends Model
{
    protected $fillable = [
        'prospect_id', 'entity_id', 'entity_group_id', 'assignment_level',
        'branch_id', 'marketing_user_id', 'approved_by', 'approval_source',
        'approval_reason', 'loa_document_id', 'status', 'effective_from', 'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function entityGroup()
    {
        return $this->belongsTo(EntityGroup::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function marketing()
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loaDocument()
    {
        return $this->belongsTo(LoaDocument::class);
    }

    public function protectedAliases()
    {
        return $this->hasMany(ProtectedProspectAlias::class);
    }

    public function conflicts()
    {
        return $this->hasMany(SingleSupportConflict::class, 'existing_assignment_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
