<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable = [
        'legal_name', 'normalized_name', 'npwp', 'nib',
        'address', 'city', 'occupation', 'website', 'notes',
    ];

    public function aliases()
    {
        return $this->hasMany(EntityAlias::class);
    }

    public function groups()
    {
        return $this->belongsToMany(EntityGroup::class, 'entity_group_members')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function singleSupportAssignments()
    {
        return $this->hasMany(SingleSupportAssignment::class);
    }
}
