@extends('layouts.template')

@section('content')
    <div class="container mt-4">
        <h2 class="fw-bold mb-4">Halaman Daftar Data</h2>

        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="point-tab" data-bs-toggle="tab" data-bs-target="#point"
                    type="button" role="tab"><i class="fa-solid fa-location-dot me-1"></i> Titik (Points)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="polyline-tab" data-bs-toggle="tab" data-bs-target="#polyline"
                    type="button" role="tab"><i class="fa-solid fa-route me-1"></i> Garis (Polylines)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="polygon-tab" data-bs-toggle="tab" data-bs-target="#polygon"
                    type="button" role="tab"><i class="fa-solid fa-draw-polygon me-1"></i> Area (Polygons)</button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            {{-- Tab 1: DATA POINTS --}}
            <div class="tab-pane fade show active" id="point" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">Data Points</div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($points as $index => $point)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $point->name }}</td>
                                        <td>{{ $point->description }}</td>
                                        <td>
                                            @if($point->image)
                                                <img src="{{ asset('storage/' . $point->image) }}" alt="{{ $point->name }}" width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tab 2: DATA POLYLINES --}}
            <div class="tab-pane fade" id="polyline" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">Data Polylines</div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($polylines as $index => $polyline)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $polyline->name }}</td>
                                        <td>{{ $polyline->description }}</td>
                                        <td>
                                            @if($polyline->image)
                                                <img src="{{ asset('storage/' . $polyline->image) }}" alt="{{ $polyline->name }}" width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tab 3: DATA POLYGONS --}}
            <div class="tab-pane fade" id="polygon" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">Data Polygons</div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($polygons as $index => $polygon)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $polygon->name }}</td>
                                        <td>{{ $polygon->description }}</td>
                                        <td>
                                            @if($polygon->image)
                                                <img src="{{ asset('storage/' . $polygon->image) }}" alt="{{ $polygon->name }}" width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection