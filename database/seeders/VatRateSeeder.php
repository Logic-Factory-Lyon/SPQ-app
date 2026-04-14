<?php
namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['name' => 'TVA 20%', 'rate' => 0.2000, 'is_default' => true, 'active' => true],
            ['name' => 'TVA 10%', 'rate' => 0.1000, 'is_default' => false, 'active' => true],
            ['name' => 'TVA 5.5%', 'rate' => 0.0550, 'is_default' => false, 'active' => true],
            ['name' => 'TVA 2.1%', 'rate' => 0.0210, 'is_default' => false, 'active' => true],
            ['name' => 'Exonéré (0%)', 'rate' => 0.0000, 'is_default' => false, 'active' => true],
        ];

        foreach ($rates as $rate) {
            VatRate::firstOrCreate(['name' => $rate['name']], $rate);
        }
    }
}
