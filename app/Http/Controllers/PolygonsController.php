<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models\Polygon;
use Illuminate\Support\Facades\DB;

class PolygonsController extends Controller
{
    public function index()
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Polygon> $polygons */
        $polygons = Polygon::withGeoJson()->get();

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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'geom' => 'required|string'
            ]);


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . "_polygon." . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = null;
            }


            $result = DB::select(
                "INSERT INTO polygon (name, description, image, geom, created_at, updated_at)
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
            $polygon = Polygon::find($id);

            return response()->json(['success' => true, 'id' => $polygon->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $polygon = Polygon::withGeoJson()->findOrFail($id);
        return response()->json($polygon->toGeoJSON());
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'geom' => 'nullable|string',
            ]);

            $polygon = Polygon::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($polygon->image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($polygon->image);
                }
                $image = $request->file('image');
                $filename = time() . '_polygon.' . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = $polygon->image;
            }

            if ($request->geom) {
                DB::statement(
                    "UPDATE polygon SET name=?, description=?, image=?, geom=ST_GeomFromText(?,4326), updated_at=now() WHERE id=?",
                    [$request->name, $request->description, $name_image, $request->geom, $id]
                );
            } else {
                DB::statement(
                    "UPDATE polygon SET name=?, description=?, image=?, updated_at=now() WHERE id=?",
                    [$request->name, $request->description, $name_image, $id]
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $polygon = Polygon::findOrFail($id);
            if ($polygon->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($polygon->image);
            }
            $polygon->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
