<?php

use App\Models\Models\Point;
use App\Models\Models\Polyline;
use App\Models\Models\Polygon;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('it can fetch all map features via unified api', function () {
    // We need to simulate DB records if we want to test geoJson methods.
    // However, ST_AsGeoJSON requires PostGIS which might not be in SQLite.
    // For now, we just test if the route is defined and reachable.
    
    $response = $this->getJson('/api/map-data');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'type',
        'features'
    ]);
});

test('it can list points via api', function () {
    $response = $this->getJson('/api/points');
    $response->assertStatus(200);
});

test('it can list polylines via api', function () {
    $response = $this->getJson('/api/polylines');
    $response->assertStatus(200);
});

test('it can list polygons via api', function () {
    $response = $this->getJson('/api/polygons');
    $response->assertStatus(200);
});
