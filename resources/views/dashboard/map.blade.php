<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logistics Proximity Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Plugins -->
    <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

    <style>
        #map { height: 600px; }
        .marker-glow {
            box-shadow: 0 0 15px 5px rgba(255, 255, 0, 0.7);
            border-radius: 50%;
        }
        @keyframes bounce {
            0% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0); }
        }
        .marker-bounce {
            animation: bounce 0.6s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-900 text-white transition-colors duration-300" id="bodyRoot">

<header class="bg-gray-800 shadow border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-yellow-400">Logistics Proximity Dashboard</h1>
        <button onclick="toggleDarkMode()" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">🌓 Toggle Dark Mode</button>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex space-x-4 mb-6">
        <button onclick="showTab('formTab')" class="tab-btn bg-yellow-400 text-gray-900 px-4 py-2 rounded font-semibold">Check Proximity</button>
        <button onclick="showTab('logsTab')" class="tab-btn bg-gray-700 text-white px-4 py-2 rounded font-semibold">Logs</button>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap space-x-0 lg:space-x-6">
        <!-- Left Panel -->
        <div class="w-full lg:w-1/3 space-y-6">
            <!-- Form Tab -->
            <div id="formTab" class="tab-content space-y-4">
                <form method="POST" action="{{ route('proximity.dashboard')}}" class="bg-gray-700 p-6 rounded-xl shadow-md space-y-4">
                    @csrf
                    <div>
                        <label class="block mb-1 font-semibold">Latitude</label>
                        <input type="number" step="any" name="lat" value="{{ old('lat') }}" class="w-full px-4 py-2 rounded bg-gray-800 border border-gray-600 focus:border-yellow-400">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Longitude</label>
                        <input type="number" step="any" name="lng" value="{{ old('lng') }}" class="w-full px-4 py-2 rounded bg-gray-800 border border-gray-600 focus:border-yellow-400">
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">Alert Radius</label>
                        <select name="radius" class="w-full px-4 py-2 rounded bg-gray-800 border border-gray-600">
                            @foreach ([100, 250, 500] as $option)
                                <option value="{{ $option }}" {{ old('radius', $data['radius'] ?? 250) == $option ? 'selected' : '' }}>{{ $option }} meters</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-yellow-400 text-gray-900 px-4 py-2 rounded font-semibold hover:bg-yellow-300">Check Proximity</button>
                </form>
            </div>

            <!-- Logs Tab -->
            <div id="logsTab" class="tab-content hidden">
                <div id="resultBanner">
                    @if (!is_null($data))
                        @if (isset($data['error']))
                            <div class="p-4 rounded bg-yellow-200 text-yellow-900">{{ $data['error'] }}</div>
                        @elseif (isset($data['within_range'], $data['distance']))
                            <div class="p-4 rounded {{ $data['within_range'] ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900' }}">
                                {{ $data['within_range'] ? 'Delivery is within' : 'Delivery is' }}
                                <strong>{{ $data['distance'] }} meters</strong>
                                {{ $data['within_range'] ? '' : 'away' }}.
                            </div>
                        @endif
                    @endif
                </div>

                <div id="logList" class="bg-gray-700 p-4 rounded-xl space-y-4 max-h-[600px] overflow-y-auto">
                    @foreach ($logs->reverse()->values() as $i => $log)
                        @php
                            $minutesAgo = now()->diffInMinutes($log->created_at);
                            $walkTime = ceil($log->distance / 1.4 / 60);
                            $driveTime = ceil($log->distance / 13.9 / 60);
                        @endphp
                        <div class="log-item flex justify-between items-center bg-gray-800 p-3 rounded hover:bg-gray-700 cursor-pointer"
                             onclick="focusMarker({{ $log->lat }}, {{ $log->lng }}, {{ $log->distance }}, {{ $log->within_range ? 'true' : 'false' }})">
                            <span>
                                {{ $log->distance }} m away
                                <br>
                                <img src="https://img.icons8.com/ios-filled/16/ffffff/walking.png" class="inline"> {{ $walkTime }} min
                                <img src="https://img.icons8.com/ios-filled/16/ffffff/car.png" class="inline ml-2"> {{ $driveTime }} min
                            </span>
                            <form method="POST" action="{{ route('log.delete', $log->id) }}">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-400 hover:underline">Delete</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="w-full lg:w-2/3 mt-6 lg:mt-0">
            <div id="map" class="rounded-xl shadow-lg border border-gray-600"></div>
        </div>
    </div>
</main>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');
    }

    function toggleDarkMode() {
        const root = document.getElementById('bodyRoot');
        root.classList.toggle('bg-gray-900');
        root.classList.toggle('bg-white');
        root.classList.toggle('text-white');
        root.classList.toggle('text-gray-900');
    }

    const map = L.map('map', { zoomControl: true }).setView([14.5995, 120.9842], 13);

    const baseLayers = {
        "Light": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        "Dark": L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_dark/{z}/{x}/{y}{r}.png'),
        "Satellite": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png')
    };
    baseLayers["Light"].addTo(map);
    L.control.layers(baseLayers).addTo(map);

    const warehouse = L.marker([14.6020, 120.9875], {
        icon: new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=808080', iconSize: [30, 30] })
    }).addTo(map).bindPopup("Warehouse");

    const greenIcon = new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=2e6f40', iconSize: [30, 30] });
    const redIcon = new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=8B0000', iconSize: [30, 30] });

    const logs = @json($logs);
    const markerMap = {};
    const heatData = [];

    logs.forEach((log, index) => {
        const icon = log.within_range ? greenIcon : redIcon;
        const latlng = [log.lat, log.lng];

        const marker = L.marker(latlng, { icon }).addTo(map)
            .bindPopup(`<strong>Log ${index + 1}</strong><br>Distance: ${log.distance} meters`);

        marker.on('click', () => {
            focusMarker(log.lat, log.lng, log.distance, log.within_range);
            animateMarker(marker);
        });

        markerMap[`${log.lat},${log.lng}`] = marker;
        heatData.push([...latlng, 0.7]);
    });

    L.heatLayer(heatData, { radius: 30, blur: 15 }).addTo(map);

    function focusMarker(lat, lng, distance, withinRange) {
        map.setView([lat, lng], 16);

        const popup = L.popup()
            .setLatLng([lat, lng])
            .setContent(`<strong>Selected Log</strong><br>Distance: ${distance}m<br>Status: ${withinRange ? '✅ Within Range' : '⚠️ Out of Range'}`)
            .openOn(map);

        document.getElementById("resultBanner").innerHTML = `
        <div class="p-4 rounded ${withinRange ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900'}">
            Delivery is ${withinRange ? 'within' : ''} <strong>${distance} meters</strong> ${withinRange ? '' : 'away'}.
        </div>`;
    }

    function animateMarker(marker) {
        if (!marker._icon) return;

        marker._icon.classList.add('marker-glow', 'marker-bounce');
        setTimeout(() => {
            marker._icon.classList.remove('marker-glow', 'marker-bounce');
        }, 1200);
    }

    @if (old('lat') && old('lng'))
        const deliveryLat = {{ old('lat') }};
        const deliveryLng = {{ old('lng') }};
        const radius = {{ old('radius', $data['radius'] ?? 250) }};

        L.circle([deliveryLat, deliveryLng], {
            radius: radius,
            color: '#3b82f6',
            fillOpacity: 0.2
        }).addTo(map);

        map.fitBounds([
            warehouse.getLatLng(),
            [deliveryLat, deliveryLng]
        ]);
    @endif
</script>

</body>
</html>
