<?php
// SPQ - Diagnostic (DELETE THIS FILE AFTER USE)

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

echo '<pre>';

$db = app('db');

// Check tables
$tables = collect($db->select('SHOW TABLES'))->map(fn($t) => array_values((array)$t)[0]);
echo "Tables: " . $tables->implode(', ') . "\n\n";

// Check mac_machines
if ($tables->contains('mac_machines')) {
    $machines = $db->select('SELECT id, name, project_id, status, LEFT(token,8) as token_preview FROM mac_machines');
    echo "Mac Machines (" . count($machines) . "):\n";
    foreach ($machines as $m) {
        echo "  id={$m->id} name={$m->name} project_id={$m->project_id} status={$m->status} token={$m->token_preview}...\n";
    }
} else {
    echo "TABLE mac_machines MANQUANTE!\n";
}

// Test API route
echo "\nTest API heartbeat:\n";
$request = Illuminate\Http\Request::create('/api/mac/heartbeat', 'POST', [], [], [], [
    'HTTP_AUTHORIZATION' => 'Bearer test_token_invalide',
    'HTTP_ACCEPT' => 'application/json',
]);
$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getContent() . "\n";

echo '</pre>';
