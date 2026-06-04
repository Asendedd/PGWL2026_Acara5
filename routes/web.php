<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PolygonsController;
use App\Http\Controllers\PageController;

Route::get('/', [PageController::class, 'landingpage'])->name('home');
Route::get('/map', function () {
    return view('map');
});
Route::get('/table', [PageController::class, 'peta'])->name('table');

Route::resource('points', PointsController::class);
Route::resource('polylines', PolylinesController::class);
Route::resource('polygons', PolygonsController::class);

// Points
Route::post('/store-point', [PointsController::class, 'store'])->name('points.store');

// Menghapus Points
Route::delete('/delete-points/{id}', [PointsController::class, 'destroy'])->name('points.delete');

// Polylines
Route::post('/store-polylines', [PolylinesController::class, 'store'])->name('polylines.store');

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';