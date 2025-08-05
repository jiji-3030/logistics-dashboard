<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proximity Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto">
        @if ($data)
            <div class="p-6 rounded shadow border-l-4
                {{ $data['within_range'] ? 'bg-green-100 border-green-500' : 'bg-red-100 border-red-500' }}">
                <p class="text-lg font-semibold">
                    @if ($data['within_range'])
                        ✅ Delivery is within <span class="text-green-700">{{ $data['distance'] }} meters</span>!
                    @else
                        ⚠️ Delivery is <span class="text-red-700">{{ $data['distance'] }} meters</span> away.
                    @endif
                </p>
            </div>
        @else
            <div class="p-6 rounded shadow border-l-4 bg-yellow-100 border-yellow-500">
                ⚠️ Unable to retrieve proximity data.
            </div>
        @endif

        <a href="{{ url('/') }}" class="block mt-4 text-blue-600 hover:underline">← Back to form</a>
    </div>
</body>
</html>
