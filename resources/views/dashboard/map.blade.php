<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proximity Alert</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: linear-gradient(to right, #1e293b, #0f172a);
            color: white;
        }
        header {
        background-color: #1e293b;
        color: #facc15;
        text-align: center;
        padding: 1.2rem;
        font-size: 1.8rem;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        border-bottom: 2px solid #374151;
        }
        .content { display: flex; flex: 1; }
        #map { flex: 1; height: 100%; }
        .form-container {
            width: 350px;
            padding: 2rem;
            background-color: #1e293b;
            border-left: 2px solid #374151;
            display: flex;
            flex-direction: column;
        }
        label {
            display: block;
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 0.95rem;
        }
        input, select {
            width: 100%;
            padding: 0.7rem;
            margin-top: 0.3rem;
            background-color: #0f172a;
            color: white;
            border: 1px solid #374151;
            border-radius: 6px;
            transition: border 0.2s;
        }
        input:focus, select:focus {
            border-color: #10b981;
            outline: none;
        }
        button {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 0.7rem 1rem;
            cursor: pointer;
            margin-top: 1rem;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        button:hover { background-color: #059669; }

        .result {
            margin-top: 2rem;
            padding: 1rem;
            border-left: 5px solid;
            border-radius: 6px;
        }
        .bg-green { background-color: #e6ffed; border-color: #34d399; color: #065f46; }
        .bg-red { background-color: #ffe4e6; border-color: #f87171; color: #7f1d1d; }
        .bg-yellow { background-color: #fef9c3; border-color: #facc15; color: #78350f; }

        .log-title {
            margin-top: 2.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            border-bottom: 2px dashed #10b981;
            padding-bottom: 0.5rem;
            color: #facc15;
        }

        .log-list {
            margin-top: 1rem;
            background: #111827;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 1rem;
            overflow-y: auto;
            max-height: 300px;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.15);
        }

        .log-item {
            background: #1e293b;
            margin-bottom: 0.6rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #e5e7eb;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #10b981;
            transition: all 0.2s ease;
        }

        .log-item:hover {
            background: #0f172a;
            transform: translateX(5px);
            box-shadow: inset 4px 0 0 #10b981;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header>Logistics Proximity Dashboard</header>

<div class="content">
    <div id="map"></div>

    <div class="form-container">
        <h2 style="margin-bottom: 1.5rem;">Check Proximity</h2>
        <form method="POST" action="{{ route('check.proximity') }}">
            @csrf

            <label>
                Latitude
                <input type="number" name="lat" step="any" required value="{{ old('lat') }}">
            </label>

            <label>
                Longitude
                <input type="number" name="lng" step="any" required value="{{ old('lng') }}">
            </label>

            <label>
                Radius (meters)
                <select name="radius" required>
                    @php
                        $selectedRadius = old('radius', $data['radius'] ?? 250);
                        $radiusOptions = [100, 250, 500];
                    @endphp
                    @foreach ($radiusOptions as $option)
                        <option value="{{ $option }}" {{ $selectedRadius == $option ? 'selected' : '' }}>
                            {{ $option }} meters
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit">Check Proximity</button>
        </form>

        <div class="result" id="resultBox" style="display: none;"></div>
        @if (!is_null($data) && isset($data['within_range'], $data['distance']))
            <script>
                const resultBox = document.getElementById('resultBox');
                resultBox.style.display = 'block';
                resultBox.className = 'result {{ $data['within_range'] ? 'bg-green' : 'bg-red' }}';
                resultBox.innerHTML = `
                    <p><strong>{{ $data['within_range'] ? 'Delivery is within range!' : 'Delivery is out of range.' }}</strong></p>
                    <p>Distance: {{ $data['distance'] }} meters</p>
                `;
            </script>
        @endif

        <div class="log-title">Past Proximity Logs</div>
        <div class="log-list" id="logList"></div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([14.5995, 120.9842], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const warehouseIcon = L.icon({
        iconUrl: 'https://img.icons8.com/?size=100&id=63555&format=png&color=4D4D4D',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    const greenIcon = L.icon({
        iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=2e6f40',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    const redIcon = L.icon({
        iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=8B0000',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    const warehouse = L.marker([14.6020, 120.9875], { icon: warehouseIcon })
        .addTo(map)
        .bindPopup('Warehouse');

    const logList = document.getElementById('logList');
    const savedLogs = @json($logs);
    const heatPoints = [];

    // ASCENDING ORDER (1., 2., 3.)
    savedLogs.forEach((log, index) => {
        const latLng = [log.lat, log.lng];
        const icon = log.within_range ? greenIcon : redIcon;

        const marker = L.marker(latLng, { icon })
            .addTo(map)
            .bindPopup(`
                <strong>Delivery ${index + 1}</strong><br>
                Lat: ${log.lat}<br>
                Lng: ${log.lng}<br>
                Distance: ${log.distance}m
            `);

        const item = document.createElement('div');
        item.className = 'log-item';
        item.textContent = `${index + 1}. [${log.lat}, ${log.lng}] (${log.distance}m)`;

        item.onclick = () => {
            map.setView(latLng, 16);
            marker.openPopup();

            const resultBox = document.getElementById('resultBox');
            resultBox.style.display = 'block';
            resultBox.innerHTML = `
                <p><strong>Selected Log</strong></p>
                <p>Distance: ${log.distance} meters</p>
                <p>Status: ${log.within_range ? '✅ Within Range' : '⚠️ Out of Range'}</p>
            `;
            resultBox.className = `result ${log.within_range ? 'bg-green' : 'bg-red'}`;
        };

        logList.appendChild(item);
    });

    // Optional heatmap
    L.heatLayer(heatPoints, { radius: 25 }).addTo(map);

    @if (old('lat') && old('lng'))
        const deliveryLat = {{ old('lat') }};
        const deliveryLng = {{ old('lng') }};
        const radius = {{ old('radius', $data['radius'] ?? 250) }};

        const circle = L.circle([deliveryLat, deliveryLng], {
            radius: radius,
            color: '#3b82f6',
            fillOpacity: 0.1
        }).addTo(map);

        map.fitBounds([
            warehouse.getLatLng(),
            [deliveryLat, deliveryLng]
        ]);
    @endif
</script>
</body>
</html>
