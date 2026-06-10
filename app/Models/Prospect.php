<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    protected $fillable = [
        'prospect_code', 'prospect_name', 'input_type', 'legal_entity_name',
        'brand_name', 'group_name', 'address', 'city', 'occupation',
        'estimated_premium', 'client_pic_name', 'client_pic_position',
        'branch_id', 'marketing_user_id', 'status', 'risk_level',
        'duplicate_score', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_premium' => 'decimal:2',
            'duplicate_score' => 'integer',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function marketing()
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    public function loaDocuments()
    {
        return $this->hasMany(LoaDocument::class);
    }

    public function aiVerificationLogs()
    {
        return $this->hasMany(AiVerificationLog::class);
    }

    public function latestAiVerification()
    {
        return $this->hasOne(AiVerificationLog::class)->latestOfMany();
    }

    public function documentChecklists()
    {
        return $this->hasMany(DocumentChecklist::class);
    }

    public function workflowTasks()
    {
        return $this->hasMany(WorkflowTask::class);
    }

    public function slaLogs()
    {
        return $this->hasMany(SlaLog::class);
    }

    public function prospectConflicts()
    {
        return $this->hasMany(ProspectConflict::class);
    }

    public function singleSupportAssignment()
    {
        return $this->hasOne(SingleSupportAssignment::class);
    }

    public function singleSupportConflicts()
    {
        return $this->hasMany(SingleSupportConflict::class, 'new_prospect_id');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, null, 'entity_type', 'entity_id')
            ->where('entity_type', 'Prospect');
    }

    public function getRiskBadgeClassAttribute(): string
    {
        return match ($this->risk_level) {
            'LOW' => 'badge-success',
            'MEDIUM' => 'badge-warning',
            'HIGH' => 'badge-danger',
            'VERY_HIGH' => 'badge-dark-danger',
            default => 'badge-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'badge-secondary',
            'SUBMITTED' => 'badge-primary',
            'AI_VERIFICATION' => 'badge-info',
            'NEED_CLARIFICATION' => 'badge-warning',
            'DUPLICATE_REVIEW', 'LOA_REVIEW' => 'badge-danger',
            'BC_REVIEW', 'UW_REVIEW', 'DOCUMENT_COMPLETION' => 'badge-primary',
            'APPROVED_FOR_FOLLOW_UP', 'READY_FOR_POLICY', 'POLICY_ISSUED' => 'badge-success',
            'REJECTED', 'CANCELLED' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
