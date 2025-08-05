<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proximity Alert</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body {
            font-family: sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
        }

        #map {
            height: 100vh;
            width: 70%;
        }

        .form-container {
            width: 30%;
            padding: 2rem;
            background-color: #f9f9f9;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 1rem;
        }

        input {
            width: 100%;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }

        .result {
            margin-top: 2rem;
            padding: 1rem;
            border-left: 5px solid;
            border-radius: 5px;
        }

        .bg-green {
            background-color: #e6ffed;
            border-color: #34d399;
            color: #065f46;
        }

        .bg-red {
            background-color: #ffe4e6;
            border-color: #f87171;
            color: #7f1d1d;
        }

        .bg-yellow {
            background-color: #fef9c3;
            border-color: #facc15;
            color: #78350f;
        }
    </style>
</head>
<body>

<div id="map"></div>

<div class="form-container">
    <h2>Check Delivery Proximity</h2>
    <form method="POST" action="{{ route('check.proximity') }}">
        @csrf

        <label>
            Latitude:
            <input type="number" name="lat" step="any" value="{{ old('lat') }}" required>
        </label>

        <label>
            Longitude:
            <input type="number" name="lng" step="any" value="{{ old('lng') }}" required>
        </label>

        <label>
            Radius (meters):
            <input type="number" name="radius" value="{{ old('radius', $data['radius'] ?? 250) }}">
        </label>

        <button type="submit">Check Proximity</button>
    </form>

<!-- Result Display -->
@if (!is_null($data))

    @if (isset($data['error']))
        <div class="result bg-yellow">
            ⚠️ {{ $data['error'] }}
        </div>

    @elseif (isset($data['within_range'], $data['distance']))
        <div class="result {{ $data['within_range'] ? 'bg-green' : 'bg-red' }}">
            <p>
                @if ($data['within_range'])
                    ✅ Delivery is within <strong>{{ $data['distance'] }} meters</strong>!
                @else
                    ⚠️ Delivery is <strong>{{ $data['distance'] }} meters</strong> away.
                @endif
            </p>
        </div>

    @else
        <div class="result bg-yellow">
            ⚠️ Unexpected response. Please check the Flask server.
        </div>
    @endif

@endif
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('map').setView([14.5995, 120.9842], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const warehouse = L.marker([14.5995, 120.9842]).addTo(map)
        .bindPopup('Warehouse');

    @if (old('lat') && old('lng'))
        const delivery = L.marker([{{ old('lat') }}, {{ old('lng') }}]).addTo(map)
            .bindPopup('Delivery Location').openPopup();

        const radius = {{ old('radius', $data['radius'] ?? 250) }};
        const circle = L.circle([{{ old('lat') }}, {{ old('lng') }}], {
            radius: radius,
            color: '#3182ce',
            fillOpacity: 0.1
        }).addTo(map);

        map.fitBounds([warehouse.getLatLng(), delivery.getLatLng()]);
    @endif
</script>

</body>
</html>
