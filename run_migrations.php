<?php
// SPQ - Run migrations + seeders (DELETE THIS FILE AFTER USE)

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

echo '<pre>';

use Illuminate\Support\Facades\Artisan;

echo "Running migrations...\n";
$exit = Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
echo "Exit code: {$exit}\n\n";

echo "Running EmailTemplateSeeder...\n";
$exit = Artisan::call('db:seed', ['--class' => 'EmailTemplateSeeder', '--force' => true]);
echo Artisan::output();
echo "Exit code: {$exit}\n";

echo '</pre>';
