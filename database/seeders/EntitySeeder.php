<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\EntityAlias;
use App\Models\EntityGroup;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    public function run(): void
    {
        // Case 1 — Shangri-La / PT Saripuri Permai Hotel
        $saripuri = Entity::firstOrCreate(
            ['legal_name' => 'PT Saripuri Permai Hotel'],
            [
                'normalized_name' => 'pt saripuri permai hotel',
                'city' => 'Surabaya',
                'occupation' => 'Hotel / Hospitality',
            ]
        );

        foreach (['Shangri-La Hotel Surabaya', 'Hotel Shangri-La Surabaya', 'Saripuri Permai Hotel', 'Shangri-La Surabaya'] as $alias) {
            EntityAlias::firstOrCreate(
                ['entity_id' => $saripuri->id, 'alias_name' => $alias],
                ['normalized_alias_name' => strtolower($alias), 'alias_type' => 'BRAND']
            );
        }

        // Case 2 — Logisly / PT Logistik Canggih Indonesia
        $logisly = Entity::firstOrCreate(
            ['legal_name' => 'PT Logistik Canggih Indonesia'],
            [
                'normalized_name' => 'pt logistik canggih indonesia',
                'city' => 'Jakarta',
                'occupation' => 'Logistics / Technology',
            ]
        );

        EntityAlias::firstOrCreate(
            ['entity_id' => $logisly->id, 'alias_name' => 'Logisly'],
            ['normalized_alias_name' => 'logisly', 'alias_type' => 'BRAND']
        );

        // Case 3 — Dharma Wibawa Guna Group
        $group = EntityGroup::firstOrCreate(
            ['group_name' => 'Dharma Wibawa Guna Group'],
            ['normalized_group_name' => 'dharma wibawa guna group']
        );

        $groupMembers = [
            ['legal_name' => 'PT Alam Semesta Agro', 'city' => 'Jakarta', 'occupation' => 'Agriculture'],
            ['legal_name' => 'PT Bangun Sahabat Tani', 'city' => 'Surabaya', 'occupation' => 'Agriculture'],
            ['legal_name' => 'PT Delta Giri Wacana', 'city' => 'Bandung', 'occupation' => 'Agriculture'],
        ];

        foreach ($groupMembers as $memberData) {
            $member = Entity::firstOrCreate(
                ['legal_name' => $memberData['legal_name']],
                array_merge($memberData, ['normalized_name' => strtolower($memberData['legal_name'])])
            );

            if (!$group->members()->where('entity_id', $member->id)->exists()) {
                $group->members()->attach($member->id, ['relationship_type' => 'SUBSIDIARY']);
            }
        }
    }
}
