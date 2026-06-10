<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Kantor Pusat', 'code' => 'HO'],
            ['name' => 'Cabang Surabaya', 'code' => 'SBY'],
            ['name' => 'Cabang Jakarta', 'code' => 'JKT'],
            ['name' => 'Cabang Bandung', 'code' => 'BDG'],
            ['name' => 'Cabang Medan', 'code' => 'MDN'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
