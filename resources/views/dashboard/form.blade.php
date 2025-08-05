<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proximity Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Check Delivery Proximity</h1>
        <form method="POST" action="{{ route('check.proximity') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium">Latitude:</label>
                <input type="text" name="lat" required class="mt-1 block w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Longitude:</label>
                <input type="text" name="lng" required class="mt-1 block w-full border px-3 py-2 rounded">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Check Proximity
            </button>
        </form>
    </div>
</body>
</html>
