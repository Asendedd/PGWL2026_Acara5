<x-layouts::app title="Yogya Map">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        #map { height: calc(100vh - 56px); width: 100%; z-index: 1; }
        .custom-toolbar {
            position: absolute;
            top: 100px; /* Adjusted top position */
            left: 10px;
            z-index: 1000;
            background: white;
            border: 1px solid #aaa;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.65);
        }
        .custom-toolbar a {
            display: block;
            width: 34px;
            height: 34px;
            line-height: 34px;
            text-align: center;
            text-decoration: none;
            color: black;
            font-size: 1.5em;
            border-bottom: 1px solid #ccc;
        }
        .custom-toolbar a:last-child {
            border-bottom: none;
        }
        .custom-toolbar a:hover {
            background-color: #f4f4f4;
        }

        #points-panel {
            position: absolute;
            top: 100px;
            left: -350px;
            width: 350px;
            height: calc(100vh - 140px);
            background: white;
            border: 1px solid #aaa;
            border-radius: 0 4px 4px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            z-index: 1001;
            transition: left 0.3s ease;
            overflow-y: auto;
            padding: 10px;
        }

        #points-panel.open {
            left: 10px;
        }

        .point-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .point-item:hover {
            background-color: #f8f9fa;
        }

        .point-image {
            max-width: 50px;
            height: auto;
            border-radius: 4px;
        }

        .point-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .point-item.active .point-details {
            max-height: 200px;
        }
    </style>

    <div id="map"></div>

    <div class="custom-toolbar">
        <a href="#" id="zoom-in" title="Zoom In">+</a>
        <a href="#" id="zoom-out" title="Zoom Out">-</a>
        <a href="#" id="draw-point" title="Place a point"><i class="fa-solid fa-location-dot"></i></a>
        <a href="#" id="draw-polyline" title="Draw a line"><i class="fa-solid fa-route"></i></a>
        <a href="#" id="draw-polygon" title="Draw an area"><i class="fa-solid fa-draw-polygon"></i></a>
        <a href="#" id="list-points" title="List Points"><i class="fa-solid fa-list"></i></a>
    </div>

    <!-- Points List Panel -->
    <div id="points-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6><i class="fa-solid fa-map-pin me-1"></i>Points List</h6>
            <button id="close-panel" class="btn btn-sm btn-outline-secondary">&times;</button>
        </div>
        <div id="points-list">
            <p class="text-muted">No points yet. Draw some points!</p>
        </div>
    </div>

    <!-- Modal Point -->
    <div class="modal fade" tabindex="-1" id="modalInputPoint">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Point</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="point-form" action="{{ route('points.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="geom" id="geom_point">
                <div class="mb-3">
                    <label for="name_point" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name_point" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description_point" class="form-label">Description</label>
                    <textarea class="form-control" id="description_point" name="description" rows="3"></textarea>
                </div>
                 <div class="mb-3">
                    <label for="image_point" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image_point" name="image">
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="save-point">Save Point</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Polyline -->
    <div class="modal fade" tabindex="-1" id="modalInputPolyline">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Polyline</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="polyline-form" action="{{ route('polylines.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="geom" id="geom_polyline">
                <div class="mb-3">
                    <label for="name_polyline" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name_polyline" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description_polyline" class="form-label">Description</label>
                    <textarea class="form-control" id="description_polyline" name="description" rows="3"></textarea>
                </div>
                 <div class="mb-3">
                    <label for="image_polyline" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image_polyline" name="image">
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="save-polyline">Save Polyline</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Polygon -->
    <div class="modal fade" tabindex="-1" id="modalInputPolygon">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Polygon</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="polygon-form" action="{{ route('polygons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="geom" id="geom_polygon">
                <div class="mb-3">
                    <label for="name_polygon" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name_polygon" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description_polygon" class="form-label">Description</label>
                    <textarea class="form-control" id="description_polygon" name="description" rows="3"></textarea>
                </div>
                 <div class="mb-3">
                    <label for="image_polygon" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image_polygon" name="image">
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="save-polygon">Save Polygon</button>
          </div>
        </div>
      </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        // Inisialisasi Peta
        var map = L.map('map', { zoomControl: false }).setView([-7.7956, 110.3695], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Points markers layer
        var pointsLayer = L.layerGroup().addTo(map);
        var pointsMarkers = L.layerGroup();
        pointsMarkers.addTo(pointsLayer);
        let currentMarkers = {};

        /* Digitize Function */
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Custom Toolbar Actions
        document.getElementById('zoom-in').addEventListener('click', function(e) {
            e.preventDefault();
            map.zoomIn();
        });

        document.getElementById('zoom-out').addEventListener('click', function(e) {
            e.preventDefault();
            map.zoomOut();
        });

        // List points toggle
        document.getElementById('list-points').addEventListener('click', function(e) {
            e.preventDefault();
            const panel = document.getElementById('points-panel');
            if (panel.classList.contains('open')) {
                panel.classList.remove('open');
            } else {
                panel.classList.add('open');
                loadPoints();
            }
        });

        document.getElementById('close-panel').addEventListener('click', function() {
            document.getElementById('points-panel').classList.remove('open');
        });

        // Drawing Tools
        var drawMarker = new L.Draw.Marker(map);
        var drawPolyline = new L.Draw.Polyline(map);
        var drawPolygon = new L.Draw.Polygon(map);

        document.getElementById('draw-point').addEventListener('click', function(e) {
            e.preventDefault();
            drawMarker.enable();
        });

        document.getElementById('draw-polyline').addEventListener('click', function(e) {
            e.preventDefault();
            drawPolyline.enable();
        });

        document.getElementById('draw-polygon').addEventListener('click', function(e) {
            e.preventDefault();
            drawPolygon.enable();
        });

        map.on('draw:created', function(e) {
            var type = e.layerType,
                layer = e.layer;

            if (type === 'marker') {
                var latlng = layer.getLatLng();
                var wkt = `POINT(${latlng.lng} ${latlng.lat})`;
                document.getElementById('geom_point').value = wkt;
                new bootstrap.Modal(document.getElementById('modalInputPoint')).show();
            } 
            else if (type === 'polyline') {
                var latlngs = layer.getLatLngs();
                var points = latlngs.map(ll => `${ll.lng} ${ll.lat}`).join(', ');
                var wkt = `LINESTRING(${points})`;
                document.getElementById('geom_polyline').value = wkt;
                new bootstrap.Modal(document.getElementById('modalInputPolyline')).show();
            } 
            else if (type === 'polygon') {
                var latlngs = layer.getLatLngs()[0];
                var points = latlngs.map(ll => `${ll.lng} ${ll.lat}`).join(', ');
                // WKT Polygon needs to close the loop
                points += `, ${latlngs[0].lng} ${latlngs[0].lat}`;
                var wkt = `POLYGON((${points}))`;
                document.getElementById('geom_polygon').value = wkt;
                new bootstrap.Modal(document.getElementById('modalInputPolygon')).show();
            }

            map.removeLayer(layer);
        });

        // Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Save handlers
        function saveFeature(formId, modalId, refreshFunc) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
                    refreshFunc();
                    form.reset();
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'Data saved successfully!'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed: ' + (data.error || 'Unknown error')
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Something went wrong!'
                });
            });
        }

        document.getElementById('save-point').addEventListener('click', () => 
            saveFeature('point-form', 'modalInputPoint', loadPoints));
        
        document.getElementById('save-polyline').addEventListener('click', () => 
            saveFeature('polyline-form', 'modalInputPolyline', loadPolylines));
        
        document.getElementById('save-polygon').addEventListener('click', () => 
            saveFeature('polygon-form', 'modalInputPolygon', loadPolygons));

        // Load and render points
        function loadPoints() {
            fetch('/points')
            .then(response => response.json())
            .then(data => {
                // Update map markers
                pointsMarkers.clearLayers();
                currentMarkers = {};

                data.features.forEach(feature => {
                    const coords = feature.geometry.coordinates;
                    const latlng = [coords[1], coords[0]]; // GeoJSON is [lng, lat], Leaflet needs [lat, lng]
                    const marker = L.marker(latlng).addTo(pointsMarkers);

                    const popupContent = `
                        <b>${feature.properties.name}</b><br>
                        ${feature.properties.description || ''}
                        ${feature.properties.image ? `<br><img src="${feature.properties.image}" style="max-width:200px;">` : ''}
                    `;
                    marker.bindPopup(popupContent);

                    currentMarkers[feature.properties.id] = marker;
                });

                // Render list
                const listContainer = document.getElementById('points-list');
                if (data.features.length === 0) {
                    listContainer.innerHTML = '<p class="text-muted">No points yet. Draw some points!</p>';
                    return;
                }

                listContainer.innerHTML = data.features.map(feature => `
                    <div class="point-item" data-point-id="${feature.properties.id}">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-map-pin text-primary me-2"></i>
                            <strong>${feature.properties.name}</strong>
                            ${feature.properties.image ? `<img src="${feature.properties.image}" class="point-image ms-2">` : ''}
                        </div>
                        <div class="point-details mt-2">
                            <small>${feature.properties.description || 'No description'}</small><br>
                            <small>Lat: ${feature.geometry.coordinates[1].toFixed(4)}, Lng: ${feature.geometry.coordinates[0].toFixed(4)}</small><br>
                            ${feature.properties.image ? `<img src="${feature.properties.image}" class="img-fluid mt-1" style="max-height:100px;">` : ''}
                        </div>
                    </div>
                `).join('');

                // Add click handlers to list items
                document.querySelectorAll('.point-item').forEach(item => {
                    item.addEventListener('click', function() {
                        document.querySelectorAll('.point-item.active').forEach(active => active.classList.remove('active'));
                        this.classList.add('active');

                        const pointId = this.dataset.pointId;
                        const marker = currentMarkers[pointId];
                        if (marker) {
                            map.flyTo(marker.getLatLng(), 16);
                            marker.openPopup();
                        }
                    });
                });
            })
            .catch(error => console.error('Error loading points:', error));
        }

        // Layer Control - use the existing pointsLayer for points
        var polylinesLayer = L.layerGroup().addTo(map);
        var polygonsLayer = L.layerGroup().addTo(map);

        var overlayMaps = {
            "Point": pointsLayer,
            "Polyline": polylinesLayer,
            "Polygon": polygonsLayer
        };
        L.control.layers(null, overlayMaps, {collapsed: false}).addTo(map);

        // Load polylines from API
        function loadPolylines() {
            fetch('/polylines')
            .then(response => response.json())
            .then(data => {
                polylinesLayer.clearLayers();
                if (data.features && data.features.length > 0) {
                    data.features.forEach(feature => {
                        if (feature.geometry) {
                            var geojsonLayer = L.geoJSON(feature, {
                                style: { color: '#3388ff', weight: 3 }
                            });
                            geojsonLayer.bindPopup(`
                                <b>${feature.properties.name}</b><br>
                                ${feature.properties.description || ''}
                                ${feature.properties.length_m ? '<br>Length: ' + feature.properties.length_m + ' m' : ''}
                                ${feature.properties.image ? '<br><img src="' + feature.properties.image + '" style="max-width:200px;">' : ''}
                            `);
                            geojsonLayer.addTo(polylinesLayer);
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading polylines:', error));
        }

        // Load polygons from API
        function loadPolygons() {
            fetch('/polygons')
            .then(response => response.json())
            .then(data => {
                polygonsLayer.clearLayers();
                if (data.features && data.features.length > 0) {
                    data.features.forEach(feature => {
                        if (feature.geometry) {
                            var geojsonLayer = L.geoJSON(feature, {
                                style: { color: '#ff7800', weight: 2, fillOpacity: 0.3 }
                            });
                            geojsonLayer.bindPopup(`
                                <b>${feature.properties.name}</b><br>
                                ${feature.properties.description || ''}
                                ${feature.properties.area_ha ? '<br>Area: ' + feature.properties.area_ha + ' ha' : ''}
                                ${feature.properties.image ? '<br><img src="' + feature.properties.image + '" style="max-width:200px;">' : ''}
                            `);
                            geojsonLayer.addTo(polygonsLayer);
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading polygons:', error));
        }

        // Init - load all layers
        document.addEventListener('DOMContentLoaded', function() {
            loadPoints();
            loadPolylines();
            loadPolygons();

            const infoLink = document.querySelector('#infoLink');
            if (infoLink) {
                infoLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    var myModal = new bootstrap.Modal(document.getElementById('modalInputPoint'));
                    myModal.show();
                });
            }
        });
    </script>
    @endpush
</x-layouts::app>
