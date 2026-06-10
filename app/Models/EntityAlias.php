<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityAlias extends Model
{
    protected $fillable = [
        'entity_id', 'alias_name', 'normalized_alias_name', 'alias_type', 'notes',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
