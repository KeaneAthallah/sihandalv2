<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SihandalImportService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@sihandal.go.id'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        app(SihandalImportService::class)->import(
            base_path(self::DATA_CSV),
            base_path(self::DATA_XLSX)
        );

        $this->call(SampleDataSeeder::class);
    }

    private const DATA_CSV = 'database/seeders/data/sumberdana26.csv';

    private const DATA_XLSX = 'database/seeders/data/penerimaan-2026.xlsx';
}
