<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/buses/search', 'GET', ['origin' => 'Solo', 'destination' => 'Madiun', 'date' => '2026-07-01', 'passengers' => 1]);
$response = app()->make('App\Http\Controllers\Api\BusController')->search($request);
echo $response->getContent();
