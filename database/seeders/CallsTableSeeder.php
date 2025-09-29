<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;

class CallsTableSeeder extends Seeder
{
    public function run(): void
    {
        Call::factory()->count(50)->create();
    }
}