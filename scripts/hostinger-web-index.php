<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$base = '/home/u744025943/crmsmart';

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
