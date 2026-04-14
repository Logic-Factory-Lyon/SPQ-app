<?php
namespace Database\Seeders;

use App\Models\Service;
use App\Models\VatRate;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $vatRate = VatRate::where('is_default', true)->first();
        if (! $vatRate) return;

        $services = [
            [
                'name' => 'Agent OpenClaw - 1 utilisateur',
                'description' => 'Accès à un agent OpenClaw pour un utilisateur, hébergé sur Mac Mini dédié.',
                'unit_price_ht' => 49.00,
                'billing_type' => 'monthly',
            ],
            [
                'name' => 'Agent OpenClaw - Équipe 10 utilisateurs',
                'description' => 'Accès à 10 agents OpenClaw sur Mac Mini dédié.',
                'unit_price_ht' => 399.00,
                'billing_type' => 'monthly',
            ],
            [
                'name' => 'Setup & Configuration',
                'description' => 'Installation, configuration et formation initiale.',
                'unit_price_ht' => 350.00,
                'billing_type' => 'one_time',
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, ['vat_rate_id' => $vatRate->id, 'active' => true])
            );
        }
    }
}
