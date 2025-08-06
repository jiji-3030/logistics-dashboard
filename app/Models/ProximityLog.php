<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProximityLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'warehouse_lat',
        'warehouse_lng',
        'lat',
        'lng',
        'radius',
        'distance',
        'within_range',
    ];
}
