<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('locations')->insert([
            'name' => 'Essen',
            'nest_amount' => 14,
        ]);
        DB::table('locations')->insert([
            'name' => 'Hamburg',
            'nest_amount' => 19,
        ]);
    }
}
