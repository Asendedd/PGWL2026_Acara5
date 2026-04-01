<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;

class Polyline extends Model
{
    protected $table = 'polylines';

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
            ->selectRaw('ST_Length(geom::geography) as length_m');
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
                'length_m' => round($this->length_m ?? 0, 2),
            ],
            'geometry' => $geometry
        ];
    }
}
