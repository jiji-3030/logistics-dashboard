<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProximityAlertController;

Route::get('/', function () {return view('welcome');});
Route::get('/dashboard/proximity', [ProximityAlertController::class, 'showMap'])->name('proximity.dashboard');
Route::post('/dashboard/proximity', [ProximityAlertController::class, 'checkProximity']);
Route::delete('/logs/{id}', [ProximityAlertController::class, 'delete'])->name('log.delete');
