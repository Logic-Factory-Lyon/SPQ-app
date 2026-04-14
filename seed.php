<?php
// SPQ - Create admin user (DELETE THIS FILE AFTER USE)

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

echo '<pre>';

try {
    $exists = \App\Models\User::where('email', 'admin@spq.app')->first();
    if ($exists) {
        echo "Utilisateur existant, mise a jour du mot de passe...\n";
        $exists->password = \Illuminate\Support\Facades\Hash::make('changeme_immediately');
        $exists->save();
        echo "OK - mot de passe reinitialise.\n";
    } else {
        \App\Models\User::create([
            'name'              => 'Admin SPQ',
            'email'             => 'admin@spq.app',
            'password'          => \Illuminate\Support\Facades\Hash::make('changeme_immediately'),
            'role'              => 'superadmin',
            'client_id'         => null,
            'email_verified_at' => now(),
        ]);
        echo "Utilisateur admin cree avec succes.\n";
    }
    echo "\nEmail    : admin@spq.app\n";
    echo "Password : changeme_immediately\n";
} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo '</pre>';
