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
        /** @var \Illuminate\Database\Eloquent\Collection<int, Point> $points */
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

        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777);
        }
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'geom' => 'required|string'
            ]);


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . "_point." . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = null;
            }

            $result = DB::select(
                "INSERT INTO points (name, description, image, geom, created_at, updated_at)
                 VALUES (?, ?, ?, ST_GeomFromText(?, 4326), now(), now())
                 RETURNING id",
                [
                    $request->name,
                    $request->description,
                    $name_image,
                    $request->geom,
                ]
            );
            $id = $result[0]->id;
            $point = Point::find($id);

            return response()->json(['success' => true, 'id' => $point->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $point = Point::withGeoJson()->findOrFail($id);
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
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'geom' => 'nullable|string',
            ]);

            $point = Point::findOrFail($id);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists using Storage facade for consistency
                if ($point->image) {
                    Storage::disk('public')->delete($point->image);
                }
                $image = $request->file('image');
                $filename = time() . '_point.' . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = $point->image;
            }

            // Build query — update geom only if provided
            if ($request->geom) {
                DB::statement(
                    "UPDATE points SET name=?, description=?, image=?, geom=ST_GeomFromText(?,4326), updated_at=now() WHERE id=?",
                    [$request->name, $request->description, $name_image, $request->geom, $id]
                );
            } else {
                DB::statement(
                    "UPDATE points SET name=?, description=?, image=?, updated_at=now() WHERE id=?",
                    [$request->name, $request->description, $name_image, $id]
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $point = Point::findOrFail($id);
            if ($point->image) {
                Storage::disk('public')->delete($point->image);
            }
            $point->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
