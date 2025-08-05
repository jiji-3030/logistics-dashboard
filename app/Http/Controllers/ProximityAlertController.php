<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProximityAlertController extends Controller
{
    public function checkProximity(Request $request)
    {
        // Validate input
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|numeric'
        ]);

        try {
            // Prepare the payload
            $payload = [
                'warehouse' => [14.5995, 120.9842], // Manila City Hall as reference
                'delivery' => [(float) $request->lat, (float) $request->lng],
                'radius' => (float) ($request->radius ?? 250)
            ];

            // Send POST request to Flask API
            $response = Http::post('http://192.168.1.10:5002/check_proximity', $payload);

            // If response is valid JSON
            if ($response->ok() && $response->json('distance') !== null) {
                $data = $response->json();
                $data['delivery'] = $payload['delivery']; // pass delivery coords to map
                return view('dashboard.map', compact('data'));
            } else {
                return view('dashboard.map', ['data' => null, 'error' => 'Invalid response from Flask API.']);
            }
        } catch (\Exception $e) {
            return view('dashboard.map', [
                'data' => null,
                'error' => '⚠️ Unable to retrieve proximity data. Check the Flask server.',
            ]);
        }
    }
}
