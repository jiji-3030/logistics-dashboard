<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logistics Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

    <style>
        .html, .body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

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
        #map { height: 600px; border-radius: 1rem; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 transition duration-300" id="bodyRoot">

<header class="bg-yellow-400 px-6 py-4 shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold">📦 Logistics Dashboard</h1>
    <div class="flex items-center gap-4">
        <select id="mapTheme" class="px-3 py-1 rounded border border-gray-300">
            <option value="Light">Light</option>
            <option value="Dark">Dark</option>
            <option value="Satellite">Satellite</option>
        </select>
    </div>
</header>

<main class="bg-gray-100 dark:bg-gray-900 overflow-hidden" id="bodyRoot">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sidebar (Form + Logs) -->
<div class="flex flex-col bg-white dark:bg-gray-800 p-6 space-y-4 shadow-inner rounded-xl mt-4 mb-6 ml-4 mr-4 max-h-[calc(100vh-6rem)] overflow-hidden text-sm w-full">
          <!-- Form -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow flex-none">

                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Check Proximity</h2>
                <form method="POST" action="{{ route('proximity.dashboard') }}" class="space-y-4">
                    @csrf
                    <input name="lat" step="any" type="number" placeholder="Latitude" class="w-full p-2 rounded border dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="{{ old('lat') }}">
                    <input name="lng" step="any" type="number" placeholder="Longitude" class="w-full p-2 rounded border dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="{{ old('lng') }}">
                    <select name="radius" class="w-full p-2 rounded border dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        @foreach ([100, 250, 500] as $r)
                            <option value="{{ $r }}" {{ old('radius', $data['radius'] ?? 250) == $r ? 'selected' : '' }}>{{ $r }} meters</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-semibold py-2 rounded">Submit</button>
                </form>
            </div>

            <!-- Logs -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow max-h-[500px] overflow-y-auto">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Proximity Logs</h2>
                <div id="resultBanner" class="mb-4"></div>
                @foreach ($logs->reverse()->values() as $log)
                    <div id="log-entry-{{ $log->id }}" class="flex justify-between items-center items-center bg-gray-100 dark:bg-gray-700 p-3 rounded mb-3 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                        onclick="focusMarker({{ $log->lat }}, {{ $log->lng }}, {{ $log->distance }}, {{ $log->within_range ? 'true' : 'false' }})"
                    >
                        <div>
                            <div class="font-medium text-gray-800 dark:text-white">{{ $log->distance }}m away</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ ceil($log->distance / 1.4 / 60) }} min walk 🚶🏻‍♀️,
                                {{ ceil($log->distance / 13.9 / 60) }} min drive 🚚
                            </div>
                        </div>
                        <form method="POST" action="{{ route('log.delete', $log->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 text-sm hover:underline ml-4">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

<!-- Map -->
<div class="lg:col-span-2 flex items-center justify-center p-6 h-[calc(100vh-6rem)]">

           <div id="map" class="w-full h-full min-h-[400px] shadow-lg border border-gray-300 rounded-lg"></div>
        </div>
    </div>
</main>


<script>
    const map = L.map('map').setView([14.5995, 120.9842], 13);
    const baseLayers = {
        Light: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        Dark: L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_dark/{z}/{x}/{y}{r}.png'),
        Satell: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png')
    };
    baseLayers.Light.addTo(map);

    document.getElementById('mapTheme').addEventListener('change', e => {
        const selected = e.target.value;
        Object.values(baseLayers).forEach(layer => map.removeLayer(layer));
        baseLayers[selected].addTo(map);
    });

    const warehouse = L.marker([14.6020, 120.9875], {
        icon: new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=808080', iconSize: [30, 30] })
    }).addTo(map).bindPopup("🏢 Warehouse");

    const greenIcon = new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=2e6f40', iconSize: [30, 30] });
    const redIcon = new L.Icon({ iconUrl: 'https://img.icons8.com/?size=100&id=59830&format=png&color=8B0000', iconSize: [30, 30] });

    const logs = @json($logs);
    logs.forEach(log => {
    const icon = log.within_range ? greenIcon : redIcon;
    const marker = L.marker([log.lat, log.lng], { icon }).addTo(map);

    // Calculate time estimates
    const walk = Math.ceil(log.distance / 1.4 / 60);
    const drive = Math.ceil(log.distance / 13.9 / 60);

    // Bind popup
    const popupContent = `
        <strong>${log.distance} meters away</strong><br>
        🚶‍♂️ ${walk} mins walk<br>
        🚚 ${drive} mins drive
    `;
    marker.bindPopup(popupContent);

    // On click
    marker.on('click', () => {
        focusMarker(log.lat, log.lng, log.distance, log.within_range);
        animateMarker(marker);
        marker.openPopup();
        highlightLog(log.id);
        });
    });

    function focusMarker(lat, lng, distance, withinRange) {
        map.setView([lat, lng], 16);
        document.getElementById('resultBanner').innerHTML = `
            <div class="p-3 rounded ${withinRange ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                Delivery is ${withinRange ? 'within' : 'outside'} <strong>${distance} meters</strong>.
            </div>`;
    }

    function animateMarker(marker) {
        if (!marker._icon) return;
        marker._icon.classList.add('marker-glow', 'marker-bounce');
        setTimeout(() => marker._icon.classList.remove('marker-glow', 'marker-bounce'), 1200);
    }
</script>

</body>
</html>

