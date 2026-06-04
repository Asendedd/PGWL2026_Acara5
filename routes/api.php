<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PolygonsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Geospatial API Routes
Route::apiResource('points', PointsController::class);
Route::apiResource('polylines', PolylinesController::class);
Route::apiResource('polygons', PolygonsController::class);

// Integrated GeoJSON API (All Features)
Route::get('/map-data', function () {
    return response()->json([
        'type' => 'FeatureCollection',
        'features' => array_merge(
            \App\Models\Models\Point::withGeoJson()->latest()->get()->map(fn($p) => $p->toGeoJSON())->toArray(),
            \App\Models\Models\Polyline::withGeoJson()->latest()->get()->map(fn($p) => $p->toGeoJSON())->toArray(),
            \App\Models\Models\Polygon::withGeoJson()->latest()->get()->map(fn($p) => $p->toGeoJSON())->toArray()
        )
    ]);
});


//GeoJSON API
Route::get('/point/{id}/geojson', [PointsController::class, 'geojson']);
Route::get('/polyline/{id}/geojson', [PolylinesController::class, 'geojson']);
Route::get('/polygon/{id}/geojson', [PolygonsController::class, 'geojson']);