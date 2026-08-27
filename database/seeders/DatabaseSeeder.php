<?php

namespace Database\Seeders;

use App\Models\User;
use Artisan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@sihandal.go.id',
            'password' => bcrypt('password'),
        ]);

        Artisan::call('app:import-sumber-dana');
        $this->call(SampleDataSeeder::class);
    }
}
