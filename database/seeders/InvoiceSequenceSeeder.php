<?php
namespace Database\Seeders;

use App\Models\InvoiceSequence;
use Illuminate\Database\Seeder;

class InvoiceSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $sequences = [
            ['type' => 'quote', 'prefix' => 'DEV', 'year' => $year, 'last_number' => 0],
            ['type' => 'invoice', 'prefix' => 'FAC', 'year' => $year, 'last_number' => 0],
            ['type' => 'credit_note', 'prefix' => 'AVO', 'year' => $year, 'last_number' => 0],
        ];

        foreach ($sequences as $seq) {
            InvoiceSequence::firstOrCreate(
                ['type' => $seq['type'], 'year' => $seq['year']],
                $seq
            );
        }
    }
}
