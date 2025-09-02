<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Simple test route
Route::get('/test-simple', function () {
    return 'Hello World - Simple Route Working!';
});

// Test route for debugging
Route::get('/test-debug', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel is working!',
        'database' => [
            'connection' => env('DB_CONNECTION'),
            'connected' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName()
        ]
    ]);
});

// Test route without database access
Route::get('/test-no-db', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel routing works without database!',
        'env' => [
            'app_env' => env('APP_ENV'),
            'app_debug' => env('APP_DEBUG'),
            'db_connection' => env('DB_CONNECTION')
        ]
    ]);
});
