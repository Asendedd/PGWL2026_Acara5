<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models\Point;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PointsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $points = Point::withGeoJson()->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $points->map(fn($p) => $p->toGeoJSON())->toArray()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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
                $imagePath = $request->file('image')->store('points', 'public');
            }

            $point = Point::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imagePath,
                'geom' => $request->geom // direct WKT as string first
            ]);

            return response()->json(['success' => true, 'id' => $point->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Point $point)
    {
        return response()->json($point->toGeoJSON());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
