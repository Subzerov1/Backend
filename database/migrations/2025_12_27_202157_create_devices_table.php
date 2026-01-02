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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string("organization_name");
            $table->integer("serial_number")->unique();
            $table->string('software_release');
            $table->dateTime("first_launch");
            $table->dateTime("last_update")->nullable();
            $table->string("history_length");
            $table->string("status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
