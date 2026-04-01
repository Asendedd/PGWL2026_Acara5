<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;

class Polygon extends Model
{
    protected $table = 'polygon';

    protected $fillable = [
        'name',
        'description',
        'image',
        'geom'
    ];

    public function scopeWithGeoJson($query)
    {
        return $query->select('*')
            ->selectRaw('ST_AsGeoJSON(geom) as geojson')
            ->selectRaw('ST_Area(geom::geography) / 10000 as area_ha');
    }

    public function toGeoJSON()
    {
        $geometry = json_decode($this->geojson, true);

        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'image' => $this->image ? asset('storage/' . $this->image) : null,
                'area_ha' => round($this->area_ha ?? 0, 2),
            ],
            'geometry' => $geometry
        ];
    }
}
