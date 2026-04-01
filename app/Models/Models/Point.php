<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Point extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'geom'
    ];

    /**
     * Scope to select geometry as GeoJSON
     */
    public function scopeWithGeoJson($query)
    {
        return $query->select('*')
            ->selectRaw('ST_AsGeoJSON(geom) as geojson');
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
            ],
            'geometry' => $geometry
        ];
    }
}
