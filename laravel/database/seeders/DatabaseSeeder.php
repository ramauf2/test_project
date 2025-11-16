<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $capacity = rand(15, 20);
            $remaining = rand(5, 10);
            Slot::create([
                Slot::FIELD_CAPACITY => $capacity,
                Slot::FIELD_REMAINING => $remaining,
            ]);
        }
    }
}
