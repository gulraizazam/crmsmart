<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$candidates = [];
if ($home = getenv('HOME')) {
    $candidates[] = rtrim($home, '/').'/crmsmart';
}
$user = get_current_user();
if (is_string($user) && $user !== '') {
    $candidates[] = '/home/'.$user.'/crmsmart';
}
$candidates[] = '/home/u941750079/crmsmart';
$candidates[] = '/home/u744025943/crmsmart';

$base = null;
foreach (array_unique($candidates) as $candidate) {
    if (is_file($candidate.'/bootstrap/app.php')) {
        $base = $candidate;
        break;
    }
}
if ($base === null) {
    http_response_code(500);
    echo 'Application path not found.';
    exit(1);
}

require $base.'/bootstrap/php84-compat.php';

if (file_exists($base.'/storage/framework/maintenance.php')) {
    require $base.'/storage/framework/maintenance.php';
}

require $base.'/vendor/autoload.php';

$app = require_once $base.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
