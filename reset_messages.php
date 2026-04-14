<?php
// SPQ - Reset stuck messages back to pending (DELETE THIS FILE AFTER USE)

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

echo '<pre>';

use Illuminate\Support\Facades\DB;

$count = DB::table('messages')
    ->where('direction', 'out')
    ->whereIn('status', ['processing', 'pending'])
    ->update(['status' => 'pending']);

echo "Reset {$count} message(s) to pending.\n";

echo '</pre>';
