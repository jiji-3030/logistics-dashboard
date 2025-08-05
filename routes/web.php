<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProximityAlertController;

Route::get('/', function () {
    return view('dashboard.map', ['data' => null]);
});

Route::post('/check_proximity', [ProximityAlertController::class, 'checkProximity'])->name('check.proximity');
