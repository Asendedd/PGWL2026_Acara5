<x-layouts::app title="Yogya Map">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        #map {
            height: calc(100vh - 56px);
            width: 100%;
            z-index: 1;
        }

        .custom-toolbar {
            position: absolute;
            top: 100px;
            /* Adjusted top position */
            left: 10px;
            z-index: 1000;
            background: white;
            border: 1px solid #aaa;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.65);
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
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
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

        /* Fix Overlapping: Hide drawing tooltips */
        .leaflet-draw-tooltip {
            display: none !important;
        }

        /* Ensure drawing guides don't interfere with markers */
        .leaflet-draw-guide-dash {
            pointer-events: none;
        }

        /* Layer priority for custom UI */
        .custom-toolbar {
            z-index: 2000 !important;
        }

        #points-panel {
            z-index: 2001 !important;
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

    <!-- Modal Edit Point -->
    <div class="modal fade" tabindex="-1" id="modalEditPoint">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-point-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" id="edit_point_id">
                        <input type="hidden" id="edit_geom_point" name="geom">
                        <div class="mb-3 d-flex justify-content-between align-items-center bg-light p-2 border rounded">
                            <div>
                                <i class="fa-solid fa-arrows-up-down-left-right text-primary me-2"></i>
                                <small>Click to move the marker on the map.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-reposition">
                                Reposition Marker
                            </button>
                        </div>
                        <label for="edit_name_point" class="form-label">Point Name</label>
                        <input type="text" class="form-control" id="edit_name_point" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="edit_description_point" class="form-label">Description</label>
                    <textarea class="form-control" id="edit_description_point" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Image</label><br>
                    <img id="edit_current_image_point" src="" alt="" class="img-thumbnail mb-2" width="200"
                        style="display:none;">
                </div>
                <div class="mb-3">
                    <label for="edit_image_point" class="form-label">Replace Image (optional)</label>
                    <input type="file" class="form-control" id="edit_image_point" name="image"
                        onchange="document.getElementById('edit_preview_image_point').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mb-3">
                    <img src="" alt="" id="edit_preview_image_point" class="img-thumbnail" width="400">
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="update-point">Update Point</button>
            </div>
        </div>
    </div>
    </div> <!-- user's extra closing div -->

    <!-- Floating Button for Reposition Mode -->
    <div id="reposition-mode-ui"
        style="display: none; position: absolute; top: 20px; left: 50%; transform: translateX(-50%); z-index: 2000; background: white; padding: 10px 20px; border-radius: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); text-align: center;">
        <span class="me-3 fw-bold text-primary"><i class="fa-solid fa-arrows-up-down-left-right me-1"></i> Drag to new
            position</span>
        <button class="btn btn-success rounded-pill px-4 shadow-sm" id="btn-reposition-done">Done</button>
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
            document.getElementById('zoom-in').addEventListener('click', function (e) {
                e.preventDefault();
                map.zoomIn();
            });

            document.getElementById('zoom-out').addEventListener('click', function (e) {
                e.preventDefault();
                map.zoomOut();
            });

            // List points toggle
            document.getElementById('list-points').addEventListener('click', function (e) {
                e.preventDefault();
                const panel = document.getElementById('points-panel');
                if (panel.classList.contains('open')) {
                    panel.classList.remove('open');
                } else {
                    panel.classList.add('open');
                    loadPoints();
                }
            });

            document.getElementById('close-panel').addEventListener('click', function () {
                document.getElementById('points-panel').classList.remove('open');
            });

            // Drawing Tools
            var drawMarker = new L.Draw.Marker(map);
            var drawPolyline = new L.Draw.Polyline(map);
            var drawPolygon = new L.Draw.Polygon(map);

            document.getElementById('draw-point').addEventListener('click', function (e) {
                e.preventDefault();
                drawMarker.enable();
            });

            document.getElementById('draw-polyline').addEventListener('click', function (e) {
                e.preventDefault();
                drawPolyline.enable();
            });

            document.getElementById('draw-polygon').addEventListener('click', function (e) {
                e.preventDefault();
                drawPolygon.enable();
            });

            map.on('draw:created', function (e) {
                var type = e.layerType,
                    layer = e.layer;

                if (type === 'marker') {
                    var latlng = layer.getLatLng();
                    updatePointGeom(latlng.lat, latlng.lng);

                    // Optional: Clear previous search text
                    document.getElementById('city_search').value = '';
                    document.getElementById('geocode-result').innerText = '';

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

            // Geocoding Support
            document.getElementById('btn-geocode').addEventListener('click', function () {
                const query = document.getElementById('city_search').value;
                if (!query) return;

                const geocodeResult = document.getElementById('geocode-result');
                geocodeResult.innerText = 'Searching...';

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(result.lat);
                            const lng = parseFloat(result.lon);

                            updatePointGeom(lat, lng);
                            map.flyTo([lat, lng], 15);

                            // Update preview marker if possible or just inform
                            geocodeResult.className = 'form-text text-success';
                            geocodeResult.innerText = `Found: ${result.display_name}`;

                            // Auto-fill Name if empty
                            const nameInput = document.getElementById('name_point');
                            if (!nameInput.value) {
                                nameInput.value = result.name || query;
                            }
                        } else {
                            geocodeResult.className = 'form-text text-danger';
                            geocodeResult.innerText = 'Location not found.';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        geocodeResult.innerText = 'Error searching location.';
                    });
            });

            function updatePointGeom(lat, lng) {
                const wkt = `POINT(${lng} ${lat})`;
                document.getElementById('geom_point').value = wkt;
            }

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
                        if (data.success) {
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

            // Delete Handler
            function deleteFeature(id, type) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This feature will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/${type}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Deleted successfully'
                                    });
                                    loadMapData();
                                } else {
                                    throw new Error(data.error || 'Delete failed');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Toast.fire({
                                    icon: 'error',
                                    title: err.message
                                });
                            });
                    }
                });
            }
            window.deleteFeature = deleteFeature; // Expose to onclick

            // Load and render all map data from unified API
            function loadMapData() {
                fetch('/api/map-data')
                    .then(response => response.json())
                    .then(data => {
                        // Clear existing layers
                        pointsMarkers.clearLayers();
                        polylinesLayer.clearLayers();
                        polygonsLayer.clearLayers();
                        currentMarkers = {};

                        const pointsList = [];

                        data.features.forEach(feature => {
                            const type = feature.geometry.type;

                            if (type === 'Point') {
                                const coords = feature.geometry.coordinates;
                                const latlng = [coords[1], coords[0]];
                                const marker = L.marker(latlng).addTo(pointsMarkers);

                                const popupContent = `
                                                                                                <b>${feature.properties.name}</b><br>
                                                                                                ${feature.properties.description || ''}
                                                                                                ${feature.properties.image ? `<br><img src="${feature.properties.image}" style="max-width:200px;">` : ''}
                                                                                                <hr>
                                                                                                <div class="d-flex gap-1">
                                                                                                    <button class="btn btn-sm btn-warning flex-fill" onclick="editFeature('${feature.properties.id}', 'points')"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                                                                                                    <button class="btn btn-sm btn-danger flex-fill" onclick="deleteFeature('${feature.properties.id}', 'points')"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                                                                                </div>
                                                                                            `;
                                marker.bindPopup(popupContent);
                                currentMarkers[feature.properties.id] = marker;
                                pointsList.push(feature);
                            }
                            else if (type === 'LineString' || type === 'MultiLineString') {
                                const geojsonLayer = L.geoJSON(feature, {
                                    style: { color: '#3388ff', weight: 4 }
                                });
                                geojsonLayer.bindPopup(`
                                                                                                <b>${feature.properties.name}</b><br>
                                                                                                ${feature.properties.description || ''}
                                                                                                ${feature.properties.image ? '<br><img src="' + feature.properties.image + '" style="max-width:200px;">' : ''}
                                                                                                <hr>
                                                                                                <div class="d-flex gap-1">
                                                                                                    <button class="btn btn-sm btn-warning flex-fill" onclick="editFeature('${feature.properties.id}', 'polylines')"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                                                                                                    <button class="btn btn-sm btn-danger flex-fill" onclick="deleteFeature('${feature.properties.id}', 'polylines')"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                                                                                </div>
                                                                                            `);
                                geojsonLayer.addTo(polylinesLayer);
                            }
                            else if (type === 'Polygon' || type === 'MultiPolygon') {
                                const geojsonLayer = L.geoJSON(feature, {
                                    style: { color: '#ff7800', weight: 2, fillOpacity: 0.3 }
                                });
                                geojsonLayer.bindPopup(`
                                                                                                <b>${feature.properties.name}</b><br>
                                                                                                ${feature.properties.description || ''}
                                                                                                ${feature.properties.area_ha ? '<br>Area: ' + feature.properties.area_ha + ' ha' : ''}
                                                                                                ${feature.properties.image ? '<br><img src="' + feature.properties.image + '" style="max-width:200px;">' : ''}
                                                                                                <hr>
                                                                                                <div class="d-flex gap-1">
                                                                                                    <button class="btn btn-sm btn-warning flex-fill" onclick="editFeature('${feature.properties.id}', 'polygons')"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                                                                                                    <button class="btn btn-sm btn-danger flex-fill" onclick="deleteFeature('${feature.properties.id}', 'polygons')"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                                                                                </div>
                                                                                            `);
                                geojsonLayer.addTo(polygonsLayer);
                            }
                        });

                        // Update Points Side Panel
                        renderPointsList(pointsList);
                    })
                    .catch(error => console.error('Error loading map data:', error));
            }

            // Helper to render the points side list
            function renderPointsList(features) {
                const listContainer = document.getElementById('points-list');
                if (features.length === 0) {
                    listContainer.innerHTML = '<p class="text-muted">No points yet. Draw some points!</p>';
                    return;
                }

                listContainer.innerHTML = features.map(feature => `
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
                                                                                                <div class="mt-2 d-flex gap-1 justify-content-end">
                                                                                                    <button class="btn btn-xs btn-outline-warning" onclick="event.stopPropagation(); editFeature('${feature.properties.id}', 'points')">
                                                                                                        <i class="fa-solid fa-pen"></i>
                                                                                                    </button>
                                                                                                    <button class="btn btn-xs btn-outline-danger" onclick="event.stopPropagation(); deleteFeature('${feature.properties.id}', 'points')">
                                                                                                        <i class="fa-solid fa-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                    </div>
                                                                                `).join('');

                // Add click handlers to list items
                document.querySelectorAll('.point-item').forEach(item => {
                    item.addEventListener('click', function () {
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
            }

            // Layer Control - use the existing pointsLayer for points
            var polylinesLayer = L.layerGroup().addTo(map);
            var polygonsLayer = L.layerGroup().addTo(map);

            var overlayMaps = {
                "Point": pointsLayer,
                "Polyline": polylinesLayer,
                "Polygon": polygonsLayer
            };
            L.control.layers(null, overlayMaps, { collapsed: false }).addTo(map);

            // ─── Edit Feature ────────────────────────────────────────────
            // Map of type -> { idField, nameField, descField, currentImgEl, previewImgEl, imageField, modalId }
            const editConfig = {
                points: { idField: 'edit_point_id', nameField: 'edit_name_point', descField: 'edit_description_point', currentImgEl: 'edit_current_image_point', previewImgEl: 'edit_preview_image_point', imageField: 'edit_image_point', modalId: 'modalEditPoint', formId: 'edit-point-form', updateBtnId: 'update-point' },
                polylines: { idField: 'edit_polyline_id', nameField: 'edit_name_polyline', descField: 'edit_description_polyline', currentImgEl: 'edit_current_image_polyline', previewImgEl: 'edit_preview_image_polyline', imageField: 'edit_image_polyline', modalId: 'modalEditPolyline', formId: 'edit-polyline-form', updateBtnId: 'update-polyline' },
                polygons: { idField: 'edit_polygon_id', nameField: 'edit_name_polygon', descField: 'edit_description_polygon', currentImgEl: 'edit_current_image_polygon', previewImgEl: 'edit_preview_image_polygon', imageField: 'edit_image_polygon', modalId: 'modalEditPolygon', formId: 'edit-polygon-form', updateBtnId: 'update-polygon' },
            };

            function editFeature(id, type) {
                const cfg = editConfig[type];
                fetch(`/${type}/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        const props = data.properties || data;
                        document.getElementById(cfg.idField).value = id;
                        document.getElementById(cfg.nameField).value = props.name || '';
                        document.getElementById(cfg.descField).value = props.description || '';

                        // Reset new image preview
                        document.getElementById(cfg.previewImgEl).src = '';
                        document.getElementById(cfg.imageField).value = '';

                        // Show existing image if any
                        const currentImg = document.getElementById(cfg.currentImgEl);
                        if (props.image) {
                            currentImg.src = props.image;
                            currentImg.style.display = 'block';
                        } else {
                            currentImg.style.display = 'none';
                        }

                        // Store currently edited marker reference globally
                        if (type === 'points') {
                            window.currentEditingMarkerId = id;
                        }

                        // Just show modal, don't auto-enable dragging
                        const modalEl = document.getElementById(cfg.modalId);
                        let modalObj = bootstrap.Modal.getInstance(modalEl);
                        if (!modalObj) modalObj = new bootstrap.Modal(modalEl);
                        modalObj.show();
                    })
                    .catch(err => {
                        console.error(err);
                        Toast.fire({ icon: 'error', title: 'Failed to load data!' });
                    });
            }
            window.editFeature = editFeature;

            function updateFeature(formId, modalId, idFieldId, type) {
                const id = document.getElementById(idFieldId).value;
                const form = document.getElementById(formId);
                const formData = new FormData(form);

                // Laravel METHOD spoofing via FormData already has _method=PUT
                fetch(`/${type}/${id}?_method=PUT`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const modalEl = document.getElementById(modalId);
                            let modalObj = bootstrap.Modal.getInstance(modalEl);
                            if (modalObj) modalObj.hide();

                            loadMapData();
                            Toast.fire({ icon: 'success', title: 'Data updated successfully!' });
                        } else {
                            Toast.fire({ icon: 'error', title: 'Failed: ' + (data.error || 'Unknown error') });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Toast.fire({ icon: 'error', title: 'Something went wrong!' });
                    });
            }

            // Handle Reposition UX
            document.getElementById('btn-reposition').addEventListener('click', function (e) {
                e.preventDefault();
                // 1. Hide modal safely
                const mapModalEl = document.getElementById('modalEditPoint');
                let modalObj = bootstrap.Modal.getInstance(mapModalEl);
                if (modalObj) modalObj.hide();

                // 2. Enable dragging on marker
                if (window.currentEditingMarkerId && currentMarkers[window.currentEditingMarkerId]) {
                    const marker = currentMarkers[window.currentEditingMarkerId];
                    marker.dragging.enable();
                    marker.closePopup();

                    // Put map focus on marker
                    map.setView(marker.getLatLng(), map.getZoom());

                    // 3. Show floating UI
                    document.getElementById('reposition-mode-ui').style.display = 'block';

                    // Update hidden field when marker is dragged
                    marker.off('dragend').on('dragend', function (e) {
                        const pos = e.target.getLatLng();
                        document.getElementById('edit_geom_point').value = `POINT(${pos.lng} ${pos.lat})`;
                    });
                }
            });

            document.getElementById('btn-reposition-done').addEventListener('click', function (e) {
                e.preventDefault();
                // 1. Hide floating UI
                document.getElementById('reposition-mode-ui').style.display = 'none';

                // 2. Disable dragging
                if (window.currentEditingMarkerId && currentMarkers[window.currentEditingMarkerId]) {
                    const marker = currentMarkers[window.currentEditingMarkerId];
                    marker.dragging.disable();
                    marker.off('dragend');

                    // Initialize if they didn't drag it at all
                    const pos = marker.getLatLng();
                    document.getElementById('edit_geom_point').value = `POINT(${pos.lng} ${pos.lat})`;
                }

                // 3. Show modal again
                const mapModalEl = document.getElementById('modalEditPoint');
                let modalObj = bootstrap.Modal.getInstance(mapModalEl);
                if (!modalObj) modalObj = new bootstrap.Modal(mapModalEl);
                modalObj.show();
            });

            // Wire update buttons
            document.getElementById('update-point').addEventListener('click', () =>
                updateFeature('edit-point-form', 'modalEditPoint', 'edit_point_id', 'points'));
            document.getElementById('update-polyline').addEventListener('click', () =>
                updateFeature('edit-polyline-form', 'modalEditPolyline', 'edit_polyline_id', 'polylines'));
            document.getElementById('update-polygon').addEventListener('click', () =>
                updateFeature('edit-polygon-form', 'modalEditPolygon', 'edit_polygon_id', 'polygons'));

            // Wrapper functions for backward compatibility with draw events
            function loadPoints() { loadMapData(); }
            function loadPolylines() { loadMapData(); }
            function loadPolygons() { loadMapData(); }

            // Init - load all layers efficiently
            document.addEventListener('DOMContentLoaded', function () {
                loadMapData();

                const infoLink = document.querySelector('#infoLink');
                if (infoLink) {
                    infoLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        var myModal = new bootstrap.Modal(document.getElementById('modalInputPoint'));
                        myModal.show();
                    });
                }
            });
        </script>
    @endpush
</x-layouts::app>