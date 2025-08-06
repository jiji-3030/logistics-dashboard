<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proximity_logs', function (Blueprint $table) {
        $table->id();
        $table->decimal('warehouse_lat', 10, 6);
        $table->decimal('warehouse_lng', 10, 6);
        $table->decimal('lat', 10, 6);
        $table->decimal('lng', 10, 6);
        $table->integer('radius');
        $table->float('distance');
        $table->boolean('within_range');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proximity_logs');
    }
};
