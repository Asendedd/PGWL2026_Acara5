<x-layouts::app title="Edit Polyline - Yogya Map">
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

        #polylines-panel {
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

        #polylines-panel.open {
            left: 10px;
        }

        .polyline-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .polyline-item:hover {
            background-color: #f8f9fa;
        }

        .polyline-image {
            max-width: 50px;
            height: auto;
            border-radius: 4px;
        }

        .polyline-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .polyline-item.active .polyline-details {
            max-height: 200px;
        }

        /* Fix Overlapping: Hide drawing tooltips */
        .leaflet-draw-tooltip {
            display: none !important;
        }

        /* Layer priority for custom UI */
        .custom-toolbar {
            z-index: 2000 !important;
        }

        #polylines-panel {
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
        <a href="#" id="list-polylines" title="List Polylines"><i class="fa-solid fa-list"></i></a>
    </div>

    <!-- Polylines List Panel -->
    <div id="polylines-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6><i class="fa-solid fa-route me-1"></i>Polylines List</h6>
            <button id="close-panel" class="btn btn-sm btn-outline-secondary">&times;</button>
        </div>
        <div id="polylines-list">
            <p class="text-muted">No polylines yet. Draw some lines!</p>
        </div>
    </div>

    <!-- Modal Edit Polyline -->
    <div class="modal fade" tabindex="-1" id="modalEditPolyline" data-bs-backdrop="false"
        style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Polyline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-polyline-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" id="edit_polyline_id">
                        <input type="hidden" id="edit_geom_polyline" name="geom">
                        <div class="mb-3 d-flex justify-content-between align-items-center bg-light p-2 border rounded">
                            <div>
                                <i class="fa-solid fa-arrows-up-down-left-right text-primary me-2"></i>
                                <small>Click to move polyline points on the map.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-reposition-polyline">
                                Reposition Polyline
                            </button>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name_polyline" class="form-label">Polyline Name</label>
                            <input type="text" class="form-control" id="edit_name_polyline" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description_polyline" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description_polyline" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label><br>
                            <img id="edit_current_image_polyline" src="" alt="" class="img-thumbnail mb-2" width="200"
                                style="display:none;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_polyline" class="form-label">Replace Image (optional)</label>
                            <input type="file" class="form-control" id="edit_image_polyline" name="image"
                                onchange="document.getElementById('edit_preview_image_polyline').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                        <div class="mb-3">
                            <img src="" alt="" id="edit_preview_image_polyline" class="img-thumbnail" width="400">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="update-polyline">Update Polyline</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Button for Reposition Mode -->
    <div id="reposition-polyline-ui"
        style="display: none; position: absolute; top: 20px; left: 50%; transform: translateX(-50%); z-index: 2000; background: white; padding: 10px 20px; border-radius: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); text-align: center;">
        <span class="me-3 fw-bold text-primary"><i class="fa-solid fa-route me-1"></i> Drag vertex squares to
            edit</span>
        <button class="btn btn-success rounded-pill px-4 shadow-sm" id="btn-reposition-polyline-done">Done</button>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
        <script>
            // Inisialisasi Peta
            var map = L.map('map', { zoomControl: false }).setView([-7.7956, 110.3695], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Layers
            var pointsLayer = L.layerGroup().addTo(map);
            var polylinesLayer = L.layerGroup().addTo(map);
            var polygonsLayer = L.layerGroup().addTo(map);

            let currentFeatures = {};

            /* Toolbar Actions */
            document.getElementById('zoom-in').addEventListener('click', function (e) {
                e.preventDefault();
                map.zoomIn();
            });

            document.getElementById('zoom-out').addEventListener('click', function (e) {
                e.preventDefault();
                map.zoomOut();
            });

            // List polylines toggle
            document.getElementById('list-polylines').addEventListener('click', function (e) {
                e.preventDefault();
                const panel = document.getElementById('polylines-panel');
                if (panel.classList.contains('open')) {
                    panel.classList.remove('open');
                } else {
                    panel.classList.add('open');
                    loadMapData();
                }
            });

            document.getElementById('close-panel').addEventListener('click', function () {
                document.getElementById('polylines-panel').classList.remove('open');
            });

            // Drawing Tools
            var drawMarker = new L.Draw.Marker(map);
            var drawPolyline = new L.Draw.Polyline(map);
            var drawPolygon = new L.Draw.Polygon(map);

            document.getElementById('draw-point').addEventListener('click', (e) => { e.preventDefault(); drawMarker.enable(); });
            document.getElementById('draw-polyline').addEventListener('click', (e) => { e.preventDefault(); drawPolyline.enable(); });
            document.getElementById('draw-polygon').addEventListener('click', (e) => { e.preventDefault(); drawPolygon.enable(); });

            map.on('draw:created', function (e) {
                var type = e.layerType,
                    layer = e.layer;

                if (type === 'marker') {
                    // Logic for point if needed
                }
                else if (type === 'polyline') {
                    var latlngs = layer.getLatLngs();
                    var points = latlngs.map(ll => `${ll.lng} ${ll.lat}`).join(', ');
                    var wkt = `LINESTRING(${points})`;
                    // Show a modal or handle creation
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
            });

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
                        fetch(`/api/${type}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Toast.fire({ icon: 'success', title: 'Deleted successfully' });
                                    loadMapData();
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Toast.fire({ icon: 'error', title: 'Delete failed' });
                            });
                    }
                });
            }
            window.deleteFeature = deleteFeature;

            // Load all map data
            function loadMapData() {
                fetch('/api/map-data')
                    .then(response => response.json())
                    .then(data => {
                        pointsLayer.clearLayers();
                        polylinesLayer.clearLayers();
                        polygonsLayer.clearLayers();
                        currentFeatures = {};

                        const polylinesList = [];

                        data.features.forEach(feature => {
                            const type = feature.geometry.type;
                            const id = feature.properties.id;

                            if (type === 'Point') {
                                const coords = feature.geometry.coordinates;
                                L.marker([coords[1], coords[0]]).addTo(pointsLayer);
                            }
                            else if (type === 'LineString' || type === 'MultiLineString') {
                                const layer = L.geoJSON(feature, {
                                    style: { color: '#3388ff', weight: 4 }
                                }).addTo(polylinesLayer);

                                layer.bindPopup(`
                                                                    <b>${feature.properties.name}</b><br>
                                                                    ${feature.properties.description || ''}
                                                                    <hr>
                                                                    <div class="d-flex gap-1">
                                                                        <button class="btn btn-sm btn-warning flex-fill" onclick="editFeature('${id}', 'polylines')"><i class="fa-solid fa-pen me-1"></i>Edit</button>
                                                                        <button class="btn btn-sm btn-danger flex-fill" onclick="deleteFeature('${id}', 'polylines')"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                                                    </div>
                                                                `);

                                currentFeatures[id] = layer;
                                polylinesList.push(feature);
                            }
                            else if (type === 'Polygon' || type === 'MultiPolygon') {
                                L.geoJSON(feature, {
                                    style: { color: '#ff7800', weight: 2, fillOpacity: 0.3 }
                                }).addTo(polygonsLayer);
                            }
                        });

                        renderPolylinesList(polylinesList);
                    });
            }

            // Render Polylines Side Panel
            function renderPolylinesList(features) {
                const listContainer = document.getElementById('polylines-list');
                if (features.length === 0) {
                    listContainer.innerHTML = '<p class="text-muted">No polylines yet.</p>';
                    return;
                }

                listContainer.innerHTML = features.map(f => `
                                                    <div class="polyline-item" data-id="${f.properties.id}">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fa-solid fa-route text-primary me-2"></i>
                                                            <strong>${f.properties.name}</strong>
                                                            ${f.properties.image ? `<img src="${f.properties.image}" class="polyline-image ms-2">` : ''}
                                                        </div>
                                                        <div class="polyline-details mt-2">
                                                            <small>${f.properties.description || 'No description'}</small><br>
                                                            <div class="mt-2 d-flex gap-1 justify-content-end">
                                                                <button class="btn btn-xs btn-outline-warning" onclick="event.stopPropagation(); editFeature('${f.properties.id}', 'polylines')">
                                                                    <i class="fa-solid fa-pen"></i>
                                                                </button>
                                                                <button class="btn btn-xs btn-outline-danger" onclick="event.stopPropagation(); deleteFeature('${f.properties.id}', 'polylines')">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `).join('');

                document.querySelectorAll('.polyline-item').forEach(item => {
                    item.addEventListener('click', function () {
                        document.querySelectorAll('.polyline-item.active').forEach(a => a.classList.remove('active'));
                        this.classList.add('active');
                        const id = this.dataset.id;
                        const layer = currentFeatures[id];
                        if (layer) {
                            map.fitBounds(layer.getBounds());
                            layer.openPopup();
                        }
                    });
                });
            }

            // Edit Feature Logic
            const editConfig = {
                polylines: { idField: 'edit_polyline_id', nameField: 'edit_name_polyline', descField: 'edit_description_polyline', currentImgEl: 'edit_current_image_polyline', previewImgEl: 'edit_preview_image_polyline', imageField: 'edit_image_polyline', modalId: 'modalEditPolyline', formId: 'edit-polyline-form' },
            };

            function editFeature(id, type) {
                const cfg = editConfig[type] || editConfig.polylines;
                fetch(`/api/${type}/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        const props = data.properties || data;
                        document.getElementById(cfg.idField).value = id;
                        document.getElementById(cfg.nameField).value = props.name || '';
                        document.getElementById(cfg.descField).value = props.description || '';
                        document.getElementById(cfg.previewImgEl).src = '';
                        document.getElementById(cfg.imageField).value = '';

                        const currentImg = document.getElementById(cfg.currentImgEl);
                        if (props.image) {
                            currentImg.src = props.image;
                            currentImg.style.display = 'block';
                        } else {
                            currentImg.style.display = 'none';
                        }

                        // Store currently edited polyline reference globally
                        window.currentEditingPolylineId = id;

                        // Safely show modal
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

                fetch(`/api/${type}/${id}?_method=PUT`, {
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
                            Toast.fire({ icon: 'error', title: 'Failed to update' });
                        }
                    });
            }

            // Handle Reposition Polyline UX
            document.getElementById('btn-reposition-polyline').addEventListener('click', function (e) {
                e.preventDefault();
                // 1. Hide modal safely
                const mapModalEl = document.getElementById('modalEditPolyline');
                let modalObj = bootstrap.Modal.getInstance(mapModalEl);
                if (modalObj) modalObj.hide();

                // 2. Enable dragging on polyline markers
                if (window.currentEditingPolylineId && currentFeatures[window.currentEditingPolylineId]) {
                    const layer = currentFeatures[window.currentEditingPolylineId];
                    // If layer is a GeoJSON Feature Group, we iterate
                    layer.eachLayer(function (l) {
                        if (l.editing) {
                            l.editing.enable();

                            // Initialize hidden field with current position just in case
                            const latlngs = l.getLatLngs();
                            const pointsStr = latlngs.map(ll => `${ll.lng} ${ll.lat}`).join(', ');
                            document.getElementById('edit_geom_polyline').value = `LINESTRING(${pointsStr})`;
                        }
                    });

                    layer.closePopup();
                    map.fitBounds(layer.getBounds());

                    // 3. Show floating UI
                    document.getElementById('reposition-polyline-ui').style.display = 'block';
                }
            });

            document.getElementById('btn-reposition-polyline-done').addEventListener('click', function (e) {
                e.preventDefault();
                // 1. Hide floating UI
                document.getElementById('reposition-polyline-ui').style.display = 'none';

                // 2. Disable dragging & capture positions
                if (window.currentEditingPolylineId && currentFeatures[window.currentEditingPolylineId]) {
                    const layer = currentFeatures[window.currentEditingPolylineId];
                    layer.eachLayer(function (l) {
                        if (l.editing) {
                            l.editing.disable();

                            // Calculate new position
                            const latlngs = l.getLatLngs();
                            const pointsStr = latlngs.map(ll => `${ll.lng} ${ll.lat}`).join(', ');
                            document.getElementById('edit_geom_polyline').value = `LINESTRING(${pointsStr})`;
                        }
                    });
                }

                // 3. Show modal again safely
                const mapModalEl = document.getElementById('modalEditPolyline');
                let modalObj = bootstrap.Modal.getInstance(mapModalEl);
                if (!modalObj) modalObj = new bootstrap.Modal(mapModalEl);
                modalObj.show();
            });

            document.getElementById('update-polyline').addEventListener('click', () =>
                updateFeature('edit-polyline-form', 'modalEditPolyline', 'edit_polyline_id', 'polylines'));

            document.addEventListener('DOMContentLoaded', loadMapData);
        </script>
    @endpush
</x-layouts::app>