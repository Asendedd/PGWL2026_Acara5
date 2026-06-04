<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models\Polyline;
use Illuminate\Support\Facades\DB;

class PolylinesController extends Controller
{
    public function index()
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Polyline> $polylines */
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'geom' => 'required|string'
            ]);


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . "_polyline." . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = null;
            }

            $result = DB::select(
                "INSERT INTO polylines (name, description, image, geom, created_at, updated_at)
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
            $polyline = Polyline::find($id);

            return response()->json(['success' => true, 'id' => $polyline->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $polyline = Polyline::withGeoJson()->findOrFail($id);
        return response()->json($polyline->toGeoJSON());
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

            $polyline = Polyline::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($polyline->image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($polyline->image);
                }
                $image = $request->file('image');
                $filename = time() . '_polyline.' . strtolower($image->getClientOriginalExtension());
                $image->move(public_path('storage/images'), $filename);
                $name_image = 'images/' . $filename;
            } else {
                $name_image = $polyline->image;
            }

            if ($request->geom) {
                DB::statement(
                    "UPDATE polylines SET name=?, description=?, image=?, geom=ST_GeomFromText(?,4326), updated_at=now() WHERE id=?",
                    [$request->name, $request->description, $name_image, $request->geom, $id]
                );
            } else {
                DB::statement(
                    "UPDATE polylines SET name=?, description=?, image=?, updated_at=now() WHERE id=?",
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
            $polyline = Polyline::findOrFail($id);
            if ($polyline->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($polyline->image);
            }
            $polyline->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
