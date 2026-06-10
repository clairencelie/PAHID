<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ho = Branch::where('code', 'HO')->first();
        $sby = Branch::where('code', 'SBY')->first();
        $jkt = Branch::where('code', 'JKT')->first();

        $users = [
            [
                'name' => 'Admin PAHID',
                'email' => 'admin@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'branch_id' => $ho->id,
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'branch_id' => $ho->id,
            ],
            [
                'name' => 'BC Surabaya',
                'email' => 'bc.sby@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'bc',
                'branch_id' => $sby->id,
            ],
            [
                'name' => 'Marketing A (Surabaya)',
                'email' => 'marketing.a@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'marketing',
                'branch_id' => $sby->id,
            ],
            [
                'name' => 'Marketing B (Jakarta)',
                'email' => 'marketing.b@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'marketing',
                'branch_id' => $jkt->id,
            ],
            [
                'name' => 'Underwriter',
                'email' => 'uw@pahid.test',
                'password' => Hash::make('password'),
                'role' => 'underwriter',
                'branch_id' => $ho->id,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }
    }
}
