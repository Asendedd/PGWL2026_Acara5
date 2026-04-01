<x-layouts::app title="Semua Data Geospasial">
    <div class="container mt-4">
        <h2 class="fw-bold mb-4">Halaman Daftar Data</h2>

        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="point-tab" data-bs-toggle="tab" data-bs-target="#point" type="button" role="tab"><i class="fa-solid fa-location-dot me-1"></i> Titik (Points)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="polyline-tab" data-bs-toggle="tab" data-bs-target="#polyline" type="button" role="tab"><i class="fa-solid fa-route me-1"></i> Garis (Polylines)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="polygon-tab" data-bs-toggle="tab" data-bs-target="#polygon" type="button" role="tab"><i class="fa-solid fa-draw-polygon me-1"></i> Area (Polygons)</button>
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
                                <tr><th>No</th><th>Name</th><th>Description</th><th>Coordinates</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td>Stadion Kridosono</td><td>Kota Baru</td><td>-7.78781, 110.374296</td></tr>
                                <tr><td>2</td><td>Bandara Adisutjipto</td><td>Bandara Internasional</td><td>-7.794337, 110.427189</td></tr>
                                <tr><td>3</td><td>Alun-alun Utara</td><td>Halaman depan kraton jogja</td><td>-7.803989, 110.364382</td></tr>
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
                                <tr><th>No</th><th>Name</th><th>Description</th><th>Length (M)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td>Rel KA</td><td>Jalur Kereta Api</td><td>24269.67</td></tr>
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
                                <tr><th>No</th><th>Name</th><th>Description</th><th>Area (Hectares)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td>Kota Baru</td><td>stadion kridosono</td><td>3.53</td></tr>
                                <tr><td>2</td><td>Alun-alun Utara</td><td>Tempat bermain lalala</td><td>5.28</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>