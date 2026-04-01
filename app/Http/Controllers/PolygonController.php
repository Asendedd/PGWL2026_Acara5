<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolygonModel;

class PolygonController extends Controller
{
    public function index()
    {
        $polygons = PolygonModel::withGeoJson()->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $polygons->map(fn($p) => $p->toGeoJSON())->toArray()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'geom' => 'required|string'
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('polygons', 'public');
            }

            $polygon = PolygonModel::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imagePath,
                'geom' => $request->geom
            ]);

            return response()->json(['success' => true, 'id' => $polygon->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        PolygonModel::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
