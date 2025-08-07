<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | Logistics Proximity System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-white min-h-screen flex items-center justify-center">

    <div class="max-w-6xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

        <!-- 🛻 Illustration -->
        <div class="flex justify-center">
            <img src="{{ asset('images/truck.png') }}" alt="Delivery Truck" class="w-full max-w-md drop-shadow-2xl">
        </div>

        <!-- ✨ Welcome Content -->
        <div class="text-center md:text-left">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4">
                AI-Powered<br class="hidden md:block">
                <span class="text-yellow-400">Proximity Alerts</span><br>
                for Warehouse Deliveries
            </h1>
            <p class="text-gray-300 text-lg mb-6">
                Monitor your deliveries in real-time and stay within range. Powered by Laravel + AI + Maps.
            </p>

            <!-- 🔘 Call-to-Action -->
            <a href="{{ url('/dashboard/proximity') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-zinc-900 font-semibold text-lg rounded-lg shadow-lg hover:scale-105 hover:shadow-xl transition duration-300">
                Check Proximity
            </a>
        </div>
    </div>

</body>
</html>
