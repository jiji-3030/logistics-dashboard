<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProximityAlertController;

Route::get('/', function () {return view('welcome');});

Route::get('/dashboard/map', [ProximityAlertController::class, 'map'])->name('dashboard.map');
Route::post('/check-proximity', [ProximityAlertController::class, 'checkProximity'])->name('check.proximity');
Route::delete('/logs/{log}', [ProximityAlertController::class, 'destroy'])->name('logs.destroy');

