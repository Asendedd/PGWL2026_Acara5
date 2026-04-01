<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models\Polyline;

class PolylinesController extends Controller
{
    public function index()
    {
        $polylines = Polyline::withGeoJson()->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $polylines->map(fn($p) => $p->toGeoJSON())->toArray()
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
                $imagePath = $request->file('image')->store('polylines', 'public');
            }

            $polyline = Polyline::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imagePath,
                'geom' => $request->geom
            ]);

            return response()->json(['success' => true, 'id' => $polyline->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        Polyline::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
