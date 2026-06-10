<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'old_values_json', 'new_values_json', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'old_values_json' => 'array',
            'new_values_json' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $entityType, int $entityId, array $oldValues = null, array $newValues = null, string $notes = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values_json' => $oldValues,
            'new_values_json' => $newValues,
            'notes' => $notes,
        ]);
    }
}
