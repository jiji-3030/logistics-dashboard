<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ProximityLog;

class ProximityAlertController extends Controller
{
    // Show the map and logs
    public function map()
    {
        $logs = ProximityLog::latest()->take(50)->get();

        return view('dashboard.map', [
            'logs' => $logs,
            'data' => session('data') // fetch flash data from redirect
        ]);
    }

    // Handle the POST request
    public function checkProximity(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'required|integer'
        ]);

        $warehouse_lat = 14.5995;
        $warehouse_lng = 120.9842;

        $lat = floatval($request->lat);
        $lng = floatval($request->lng);
        $radius = intval($request->radius);

        $data = null;

        try {
            $response = Http::post('http://192.168.1.10:5002/check_proximity', [
                'warehouse' => [$warehouse_lat, $warehouse_lng],
                'delivery' => [$lat, $lng],
                'radius' => $radius
            ]);

            $data = $response->json();
        } catch (\Exception $e) {
            $data = ['error' => '⚠️ Could not connect to Flask API'];
        }

        if (!$response->successful() || !isset($data['distance'], $data['within_range'])) {
            $data = ['error' => '⚠️ Invalid response from Flask API'];
        } else {
            ProximityLog::create([
                'warehouse_lat' => $warehouse_lat,
                'warehouse_lng' => $warehouse_lng,
                'lat' => $lat,
                'lng' => $lng,
                'distance' => $data['distance'],
                'within_range' => $data['within_range'],
                'radius' => $radius,
            ]);
        }

        return redirect()->route('dashboard.map')->with('data', $data);
    }
    public function destroy($id)
    {
        $log = ProximityLog::findOrFail($id);
        $log->delete();

        return redirect()->route('dashboard.map')->with('success', 'Log deleted successfully.');
    }
}
