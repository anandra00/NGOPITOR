<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'NGOPITOR'),
        'status' => 'online',
        'version' => '1.0.0',
        'api_version' => 'v1',
    ]);
});
