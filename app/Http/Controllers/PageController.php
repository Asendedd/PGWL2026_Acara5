<?php

namespace App\Http\Controllers;

use App\Models\Models\Point;
use App\Models\Models\Polyline;
use App\Models\Models\Polygon;

class PageController extends Controller
{
    protected $points;
    protected $polylines;
    protected $polygons;

    public function __construct()
    {
        $this->points = new Point();
        $this->polylines = new Polyline();
        $this->polygons = new Polygon();
    }

    public function landingpage()
    {
        $data = [
            'title' => 'Peta-san',
            'jumlah_points' => $this->points->count(),
            'jumlah_polylines' => $this->polylines->count(),
            'jumlah_polygons' => $this->polygons->count(),
        ];

        return view('home', $data);
    }

    public function peta()
    {
        $data = [
            'title' => 'Tabel Data',
            'points' => $this->points->latest()->get(),
            'polylines' => $this->polylines->latest()->get(),
            'polygons' => $this->polygons->latest()->get(),
        ];

        return view('table', $data);
    }
}
