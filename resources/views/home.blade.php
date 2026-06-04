@extends('layouts.template')

@section('content')
    <div class="container mt-3">
        <div class="card">
            <div class="card-header">
                <h3>Peta-san: Aplikasi Geospasial CRUD</h3>
            </div>
            <div class="card-body">
                <p>Aplikasi ini dibuat untuk memenuhi tugas mata kuliah Praktikum Pemrograman Geospasial Web Lanjut.
                    Aplikasi ini menampilkan peta interaktif yang menunjukkan objek dengan geometri titik, garis, dan area
                    yang dapat ditambah, ditampilkan, diubah, dan dihapus. Aplikasi ini dikembangkan dengan menggunakan
                    Laravel dan PostgreSQL - PostGIS.</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Jumlah Point</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1>{{ $jumlah_points }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Jumlah Polyline</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1>{{ $jumlah_polylines }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Jumlah Polygon</h4>
                    </div>
                    <div class="card-body text-center">
                        <h1>{{ $jumlah_polygons }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection