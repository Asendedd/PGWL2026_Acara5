<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PolygonsController;

Route::get('/', function () { return view('welcome'); })->name('home');
Route::get('/map', function () { return view('map'); });
Route::get('/table', function () {
    return view('table'); // Pastikan filenya resources/views/table.blade.php
});

Route::resource('points', PointsController::class);
Route::resource('polylines', PolylinesController::class);
Route::resource('polygons', PolygonsController::class);
 
Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
 
require __DIR__.'/settings.php';