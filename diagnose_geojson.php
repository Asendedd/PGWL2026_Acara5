<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Models\Point;

try {
    $points = Point::withGeoJson()->get();
    if ($points->isEmpty()) {
        echo "No points found.\n";
    } else {
        $first = $points->first();
        echo "Class of first item: " . get_class($first) . "\n";
        echo "Item: " . json_encode($first) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
