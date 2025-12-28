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
        Schema::create('devices_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('device')->constrained('devices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('date');
            $table->time('time');
            $table->string('percentage');
            $table->dateTime('datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices_logs');
    }
};
