<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        if (app()->environment('local', 'testing')) {
            // Tenant default untuk pengujian & dev (SMA Negeri 2 Yogyakarta)
            $tenantData = [
                'name' => 'SMA Negeri 2 Yogyakarta',
                'subdomain' => 'sman2jogja',
                'slug' => 'sma-negeri-2-yogyakarta',
                'onboarding_completed' => true,
                'institution_type' => 'education',
                'business_category' => 'education'
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                $tenantData['npsn'] = '20261001';
            }

            \App\Models\Tenant::firstOrCreate(
                ['code' => '20261001'],
                $tenantData
            );

            $this->call([
                HadirSekolahSeeder::class,
                StudentSeeder::class,
                DummyDevSeeder::class,
            ]);
        }
    }
}
