<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VatRateSeeder::class,
            InvoiceSequenceSeeder::class,
            SuperadminSeeder::class,
            ServiceSeeder::class,
            EmailTemplateSeeder::class,
            SkillSeeder::class,
        ]);
    }
}
