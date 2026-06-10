<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityGroup extends Model
{
    protected $fillable = ['group_name', 'normalized_group_name', 'notes'];

    public function members()
    {
        return $this->belongsToMany(Entity::class, 'entity_group_members')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function singleSupportAssignments()
    {
        return $this->hasMany(SingleSupportAssignment::class);
    }
}
