<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtectedProspectAlias extends Model
{
    protected $fillable = [
        'single_support_assignment_id', 'alias_name', 'normalized_alias_name',
        'alias_type', 'source', 'confidence_score',
    ];

    public function assignment()
    {
        return $this->belongsTo(SingleSupportAssignment::class, 'single_support_assignment_id');
    }
}
