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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();

            $table->string('airline_code', 2);
            $table->string('number', 10);

            $table->string('departure_airport_code', 3);
            $table->time('departure_time');             // Local time

            $table->string('arrival_airport_code', 3);
            $table->time('arrival_time');               // Local time

            $table->decimal('price', 10, 2);

            $table->timestamps();

            // Внешние ключи
            $table->foreign('airline_code')->references('code')->on('airlines')->onDelete('cascade');
            $table->foreign('departure_airport_code')->references('code')->on('airports')->onDelete('cascade');
            $table->foreign('arrival_airport_code')->references('code')->on('airports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
